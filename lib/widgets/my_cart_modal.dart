import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/cart_service.dart';
import 'custom_loader.dart';
import 'squircle_helper.dart';

class MyCartModal extends StatefulWidget {
  final VoidCallback? onCartUpdated;

  const MyCartModal({super.key, this.onCartUpdated});

  static Future<void> show(BuildContext context, {VoidCallback? onCartUpdated}) async {
    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => MyCartModal(onCartUpdated: onCartUpdated),
    );
  }

  @override
  State<MyCartModal> createState() => _MyCartModalState();
}

class _MyCartModalState extends State<MyCartModal> {
  List<dynamic> _cartItems = [];
  bool _isLoading = true;

  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _addressController = TextEditingController();
  final TextEditingController _noteController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchCartData();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    _noteController.dispose();
    super.dispose();
  }

  String _formatCurrency(num amount) {
    final int val = amount.toInt();
    if (val == 0) return '0đ';
    final String str = val.abs().toString();
    final buffer = StringBuffer();
    for (int i = 0; i < str.length; i++) {
      if (i > 0 && (str.length - i) % 3 == 0) {
        buffer.write('.');
      }
      buffer.write(str[i]);
    }
    return '${val < 0 ? '-' : ''}${buffer.toString()}đ';
  }

  Future<void> _fetchCartData() async {
    setState(() => _isLoading = true);
    try {
      final res = await ApiService.getCart();
      if (mounted) {
        setState(() {
          if (res['success'] == true && res['data'] is List) {
            _cartItems = List<dynamic>.from(res['data']);
          } else {
            _cartItems = [];
          }
          _isLoading = false;
        });

        // Pre-fill user profile info if available
        if (ApiService.currentUser != null) {
          final u = ApiService.currentUser!;
          if (_nameController.text.isEmpty && u['name'] != null) {
            _nameController.text = u['name'].toString();
          }
          if (_phoneController.text.isEmpty && u['phone'] != null) {
            _phoneController.text = u['phone'].toString();
          }
          if (_addressController.text.isEmpty && u['address'] != null) {
            _addressController.text = u['address'].toString();
          }
        }

        CartService.updateCountLocally(_totalCartCount);
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  double get _totalCartPrice {
    double total = 0;
    for (var item in _cartItems) {
      final double price = double.tryParse(item['price']?.toString() ?? '0') ?? 0;
      final int qty = item['quantity'] is int ? item['quantity'] : (int.tryParse(item['quantity']?.toString() ?? '1') ?? 1);
      total += price * qty;
    }
    return total;
  }

  int get _totalCartCount {
    int total = 0;
    for (var item in _cartItems) {
      final int qty = item['quantity'] is int ? item['quantity'] : (int.tryParse(item['quantity']?.toString() ?? '1') ?? 1);
      total += qty;
    }
    return total;
  }

  Future<void> _updateQuantity(dynamic item, int change) async {
    final int currentQty = item['quantity'] is int ? item['quantity'] : (int.tryParse(item['quantity']?.toString() ?? '1') ?? 1);
    final int newQty = currentQty + change;
    final int cartId = item['cart_id'] ?? item['id'] ?? 0;

    // Optimistic update: cập nhật UI ngay lập tức, không reload trang
    if (mounted) {
      setState(() {
        if (newQty <= 0) {
          _cartItems.removeWhere((e) => (e['cart_id'] ?? e['id']) == cartId);
        } else {
          item['quantity'] = newQty;
        }
      });
      CartService.updateCountLocally(_totalCartCount);
    }

    // Đồng bộ lên server ở background
    try {
      if (newQty <= 0) {
        await ApiService.removeCartItem(cartId);
      } else {
        await ApiService.updateCartItem(cartId, newQty);
      }
      widget.onCartUpdated?.call();
    } catch (_) {
      // Nếu API lỗi, reload lại dữ liệu thực từ server
      await _fetchCartData();
    }
  }

  Future<void> _clearCart() async {
    await ApiService.clearCart();
    await _fetchCartData();
    widget.onCartUpdated?.call();
  }

  Future<void> _handleCheckout() async {
    if (_cartItems.isEmpty) return;

    if (_nameController.text.trim().isEmpty || _phoneController.text.trim().isEmpty || _addressController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('⚠️ Vui lòng nhập đầy đủ Họ tên, SĐT và Địa chỉ giao hàng!'),
          backgroundColor: Color(0xFFDC2626),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final double subtotal = _totalCartPrice;
    final double shippingFee = subtotal >= 200000 ? 0.0 : (subtotal > 0 ? 15000.0 : 0.0);
    final double finalTotal = (subtotal + shippingFee).clamp(0, double.infinity);

    try {
      final res = await ApiService.checkout(
        customerName: _nameController.text.trim(),
        customerPhone: _phoneController.text.trim(),
        shippingAddress: _addressController.text.trim(),
        note: _noteController.text.trim(),
        totalAmount: finalTotal,
      );

      if (mounted) {
        CartService.refreshCartCount();
        Navigator.pop(context);
        widget.onCartUpdated?.call();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message'] ?? '🎉 Đặt hàng thành công! Đơn hàng đang được chuẩn bị.'),
            backgroundColor: const Color(0xFF059669),
            behavior: SnackBarBehavior.floating,
            duration: const Duration(seconds: 4),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Lỗi đặt hàng: $e'), backgroundColor: Colors.red, behavior: SnackBarBehavior.floating),
        );
      }
    }
  }

  InputDecoration _buildInputDecoration({
    required String label,
    required IconData icon,
  }) {
    return InputDecoration(
      labelText: label,
      labelStyle: const TextStyle(color: Color(0xFF64748B), fontSize: 13),
      prefixIcon: Icon(icon, color: const Color(0xFF0EA5E9), size: 20),
      filled: true,
      fillColor: const Color(0xFFF8FAFC),
      isDense: true,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: Color(0xFF0EA5E9), width: 1.8),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: Color(0xFFEF4444)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final double subtotal = _totalCartPrice;
    final double shippingFee = subtotal >= 200000 ? 0.0 : (subtotal > 0 ? 15000.0 : 0.0);
    final double finalTotal = (subtotal + shippingFee).clamp(0, double.infinity);
    final double amountNeededForFreeShip = (200000 - subtotal).clamp(0, double.infinity);
    final double freeShipProgress = (subtotal / 200000).clamp(0.0, 1.0);

    return Container(
      height: MediaQuery.of(context).size.height * 0.90,
      decoration: const BoxDecoration(
        color: Color(0xFFF8FAFC),
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      child: Column(
        children: [
          // Drag handle pill
          const SizedBox(height: 10),
          Container(
            width: 38,
            height: 4.5,
            decoration: BoxDecoration(
              color: const Color(0xFFCBD5E1),
              borderRadius: BorderRadius.circular(10),
            ),
          ),
          const SizedBox(height: 6),

          // Header Bar
          Container(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 14),
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
              border: Border(bottom: BorderSide(color: Color(0xFFF1F5F9))),
            ),
            child: Row(
              children: [
                InkWell(
                  onTap: () => Navigator.pop(context),
                  borderRadius: BorderRadius.circular(14),
                  child: Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: const Icon(Icons.arrow_back_ios_new_rounded, color: Color(0xFF1E293B), size: 16),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.shopping_cart_outlined, color: Color(0xFF0EA5E9), size: 20),
                          SizedBox(width: 6),
                          Text(
                            'Giỏ hàng của tôi',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF0F172A),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '$_totalCartCount sản phẩm trong giỏ',
                        style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),
                ),
                if (_cartItems.isNotEmpty)
                  InkWell(
                    onTap: _clearCart,
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFEF2F2),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFFEE2E2)),
                      ),
                      child: const Row(
                        children: [
                          Icon(Icons.delete_outline_rounded, color: Color(0xFFEF4444), size: 16),
                          SizedBox(width: 4),
                          Text('Xóa tất cả', style: TextStyle(color: Color(0xFFEF4444), fontSize: 12, fontWeight: FontWeight.bold)),
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          ),

          // Main Cart Body
          Expanded(
            child: _isLoading
                ? const CustomPulseLoader(
                    message: 'Đang tải giỏ hàng...',
                    icon: Icons.shopping_bag_rounded,
                    primaryColor: Color(0xFF0EA5E9),
                  )
                : _cartItems.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(24),
                              decoration: const BoxDecoration(
                                color: Color(0xFFE0F2FE),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.shopping_bag_outlined, size: 54, color: Color(0xFF0284C7)),
                            ),
                            const SizedBox(height: 18),
                            const Text(
                              'Giỏ hàng của bạn đang trống',
                              style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Hãy khám phá và chọn những sản phẩm ưng ý nhé!',
                              style: TextStyle(fontSize: 13, color: Color(0xFF64748B)),
                            ),
                            const SizedBox(height: 22),
                            ElevatedButton.icon(
                              onPressed: () => Navigator.pop(context),
                              icon: const Icon(Icons.storefront_rounded, size: 18),
                              label: const Text('Khám phá Chợ Số', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF0EA5E9),
                                foregroundColor: Colors.white,
                                elevation: 0,
                                padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 13),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                              ),
                            ),
                          ],
                        ),
                      )
                    : ListView(
                        padding: const EdgeInsets.all(16),
                        children: [
                          // Free Shipping Alert Banner
                          Container(
                            padding: const EdgeInsets.all(14),
                            margin: const EdgeInsets.only(bottom: 16),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [Color(0xFFFFFBEB), Color(0xFFFEF3C7)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(18),
                              border: Border.all(color: const Color(0xFFFDE68A)),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFFD97706).withValues(alpha: 0.06),
                                  blurRadius: 10,
                                  offset: const Offset(0, 3),
                                ),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    const Text('🚚', style: TextStyle(fontSize: 18)),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: subtotal >= 200000
                                          ? const Text(
                                              'Chúc mừng! Đơn hàng của bạn được MIỄN PHÍ GIAO HÀNG!',
                                              style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF059669)),
                                            )
                                          : Text.rich(
                                              TextSpan(
                                                children: [
                                                  const TextSpan(text: 'Mua thêm '),
                                                  TextSpan(
                                                    text: '${_formatCurrency(amountNeededForFreeShip)} ',
                                                    style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFFD97706)),
                                                  ),
                                                  const TextSpan(text: 'để được '),
                                                  const TextSpan(
                                                    text: 'MIỄN PHÍ GIAO HÀNG!',
                                                    style: TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF059669)),
                                                  ),
                                                ],
                                              ),
                                              style: const TextStyle(fontSize: 13, color: Color(0xFF78350F)),
                                            ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 10),
                                ClipRRect(
                                  borderRadius: BorderRadius.circular(10),
                                  child: LinearProgressIndicator(
                                    value: freeShipProgress,
                                    minHeight: 6,
                                    backgroundColor: const Color(0xFFFDE68A),
                                    valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF059669)),
                                  ),
                                ),
                              ],
                            ),
                          ),

                          // Cart Product Items
                          ..._cartItems.map((item) {
                            final name = item['name'] ?? item['product_name'] ?? 'Sản phẩm';
                            final double price = double.tryParse(item['price']?.toString() ?? '0') ?? 0;
                            final int qty = item['quantity'] is int ? item['quantity'] : (int.tryParse(item['quantity']?.toString() ?? '1') ?? 1);
                            final String imgRaw = item['image_path'] ?? item['image'] ?? item['avatar'] ?? '';
                            final String imgUrl = imgRaw.startsWith('http')
                                ? imgRaw
                                : (imgRaw.isNotEmpty ? 'https://donganhdiscovery.xadonganh.com/${imgRaw.startsWith('/') ? imgRaw.substring(1) : imgRaw}' : '');

                            return Container(
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(18),
                                border: Border.all(color: const Color(0xFFE2E8F0)),
                                boxShadow: [
                                  BoxShadow(
                                    color: const Color(0xFF0F172A).withValues(alpha: 0.04),
                                    blurRadius: 10,
                                    offset: const Offset(0, 3),
                                  ),
                                ],
                              ),
                              child: Row(
                                children: [
                                  // Thumbnail Image
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(12),
                                    child: imgUrl.isNotEmpty
                                        ? Image.network(imgUrl, width: 62, height: 62, fit: BoxFit.cover, cacheWidth: 124, filterQuality: FilterQuality.low)
                                        : Container(
                                            width: 62,
                                            height: 62,
                                            color: const Color(0xFFF0FDFA),
                                            child: const Icon(Icons.shopping_bag_outlined, color: Color(0xFF0EA5E9), size: 26),
                                          ),
                                  ),
                                  const SizedBox(width: 14),

                                  // Name & Price
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          name,
                                          style: const TextStyle(
                                            fontWeight: FontWeight.w700,
                                            fontSize: 14,
                                            color: Color(0xFF0F172A),
                                            height: 1.25,
                                          ),
                                          maxLines: 2,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                        const SizedBox(height: 6),
                                        Text(
                                          _formatCurrency(price),
                                          style: const TextStyle(
                                            fontWeight: FontWeight.w900,
                                            fontSize: 14.5,
                                            color: Color(0xFFEE4D2D),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),

                                  // Quantity Counter Pills [-] N [+]
                                  Container(
                                    padding: const EdgeInsets.all(3),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFF1F5F9),
                                      borderRadius: BorderRadius.circular(22),
                                      border: Border.all(color: const Color(0xFFE2E8F0)),
                                    ),
                                    child: Row(
                                      children: [
                                        InkWell(
                                          onTap: () => _updateQuantity(item, -1),
                                          borderRadius: BorderRadius.circular(18),
                                          child: Container(
                                            padding: const EdgeInsets.all(6),
                                            decoration: const BoxDecoration(
                                              color: Colors.white,
                                              shape: BoxShape.circle,
                                            ),
                                            child: const Icon(Icons.remove_rounded, size: 14, color: Color(0xFF334155)),
                                          ),
                                        ),
                                        Padding(
                                          padding: const EdgeInsets.symmetric(horizontal: 10),
                                          child: Text(
                                            '$qty',
                                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: Color(0xFF0F172A)),
                                          ),
                                        ),
                                        InkWell(
                                          onTap: () => _updateQuantity(item, 1),
                                          borderRadius: BorderRadius.circular(18),
                                          child: Container(
                                            padding: const EdgeInsets.all(6),
                                            decoration: const BoxDecoration(
                                              color: Color(0xFF0EA5E9),
                                              shape: BoxShape.circle,
                                            ),
                                            child: const Icon(Icons.add_rounded, size: 14, color: Colors.white),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }),
                          const SizedBox(height: 10),

                          // Customer Delivery Order Form Section
                          Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFF0F172A).withValues(alpha: 0.03),
                                  blurRadius: 10,
                                  offset: const Offset(0, 3),
                                ),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.all(6),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFE0F2FE),
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                      child: const Icon(Icons.local_shipping_outlined, color: Color(0xFF0284C7), size: 18),
                                    ),
                                    const SizedBox(width: 10),
                                    const Text(
                                      'THÔNG TIN GIAO HÀNG',
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 13,
                                        color: Color(0xFF0F172A),
                                        letterSpacing: 0.3,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 14),
                                TextField(
                                  controller: _nameController,
                                  style: const TextStyle(fontSize: 14, color: Color(0xFF0F172A), fontWeight: FontWeight.w500),
                                  decoration: _buildInputDecoration(
                                    label: 'Họ và tên người nhận *',
                                    icon: Icons.person_outline_rounded,
                                  ),
                                ),
                                const SizedBox(height: 12),
                                TextField(
                                  controller: _phoneController,
                                  keyboardType: TextInputType.phone,
                                  style: const TextStyle(fontSize: 14, color: Color(0xFF0F172A), fontWeight: FontWeight.w500),
                                  decoration: _buildInputDecoration(
                                    label: 'Số điện thoại liên hệ *',
                                    icon: Icons.phone_outlined,
                                  ),
                                ),
                                const SizedBox(height: 12),
                                TextField(
                                  controller: _addressController,
                                  style: const TextStyle(fontSize: 14, color: Color(0xFF0F172A), fontWeight: FontWeight.w500),
                                  decoration: _buildInputDecoration(
                                    label: 'Địa chỉ nhận hàng chi tiết *',
                                    icon: Icons.location_on_outlined,
                                  ),
                                ),
                                const SizedBox(height: 12),
                                TextField(
                                  controller: _noteController,
                                  style: const TextStyle(fontSize: 14, color: Color(0xFF0F172A), fontWeight: FontWeight.w500),
                                  decoration: _buildInputDecoration(
                                    label: 'Ghi chú cho gian hàng (tùy chọn)',
                                    icon: Icons.edit_note_rounded,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Bill Payment Summary Card
                          Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFF0F172A).withValues(alpha: 0.03),
                                  blurRadius: 10,
                                  offset: const Offset(0, 3),
                                ),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.all(6),
                                      decoration: BoxDecoration(
                                        color: const Color(0xFFFEF3C7),
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                      child: const Icon(Icons.receipt_long_outlined, color: Color(0xFFD97706), size: 18),
                                    ),
                                    const SizedBox(width: 10),
                                    const Text(
                                      'CHI TIẾT THANH TOÁN',
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 13,
                                        color: Color(0xFF0F172A),
                                        letterSpacing: 0.3,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 14),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text('Tạm tính:', style: TextStyle(color: Color(0xFF64748B), fontSize: 13.5)),
                                    Text(_formatCurrency(subtotal), style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13.5, color: Color(0xFF1E293B))),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text('Phí giao hàng:', style: TextStyle(color: Color(0xFF64748B), fontSize: 13.5)),
                                    Text(
                                      shippingFee == 0 ? 'MIỄN PHÍ' : _formatCurrency(shippingFee),
                                      style: TextStyle(
                                        fontWeight: FontWeight.w700,
                                        fontSize: 13.5,
                                        color: shippingFee == 0 ? const Color(0xFF059669) : const Color(0xFF1E293B),
                                      ),
                                    ),
                                  ],
                                ),
                                const Padding(
                                  padding: EdgeInsets.symmetric(vertical: 12),
                                  child: Divider(height: 1, color: Color(0xFFE2E8F0)),
                                ),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text(
                                      'TỔNG THANH TOÁN:',
                                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14.5, color: Color(0xFF0F172A)),
                                    ),
                                    Text(
                                      _formatCurrency(finalTotal),
                                      style: const TextStyle(
                                        fontWeight: FontWeight.w900,
                                        fontSize: 18.5,
                                        color: Color(0xFFEE4D2D),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 80),
                        ],
                      ),
          ),

          // Bottom Order Action Floating Container
          if (_cartItems.isNotEmpty)
            Container(
              padding: EdgeInsets.fromLTRB(16, 12, 16, MediaQuery.of(context).padding.bottom + 12),
              decoration: BoxDecoration(
                color: Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.08),
                    blurRadius: 16,
                    offset: const Offset(0, -4),
                  ),
                ],
                border: const Border(top: BorderSide(color: Color(0xFFF1F5F9))),
              ),
              child: SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton(
                  onPressed: _handleCheckout,
                  style: ElevatedButton.styleFrom(
                    padding: EdgeInsets.zero,
                    elevation: 0,
                    shape: SquircleHelper.shape(radius: 24),
                    backgroundColor: Colors.transparent,
                  ).copyWith(
                    elevation: WidgetStateProperty.all(0),
                  ),
                  child: Ink(
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFFEE4D2D), Color(0xFFF97316)],
                        begin: Alignment.centerLeft,
                        end: Alignment.centerRight,
                      ),
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFFEE4D2D).withValues(alpha: 0.35),
                          blurRadius: 12,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Container(
                      alignment: Alignment.center,
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                          const SizedBox(width: 8),
                          Text(
                            'ĐẶT HÀNG NGAY • ${_formatCurrency(finalTotal)}',
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w900,
                              color: Colors.white,
                              letterSpacing: 0.3,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
