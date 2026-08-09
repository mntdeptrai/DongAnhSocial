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

  Future<void> _fetchCartData() async {
    setState(() => _isLoading = true);
    try {
      final res = await ApiService.getCart();
      if (mounted) {
        setState(() {
          if (res is Map && res['success'] == true && res['data'] is List) {
            _cartItems = List<dynamic>.from(res['data']);
          } else {
            _cartItems = [];
          }
          _isLoading = false;
        });
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

    if (newQty <= 0) {
      await ApiService.removeCartItem(cartId);
    } else {
      await ApiService.updateCartItem(cartId, newQty);
    }
    await _fetchCartData();
    widget.onCartUpdated?.call();
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
            duration: const Duration(seconds: 4),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Lỗi đặt hàng: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final double subtotal = _totalCartPrice;
    final double shippingFee = subtotal >= 200000 ? 0.0 : (subtotal > 0 ? 15000.0 : 0.0);
    final double finalTotal = (subtotal + shippingFee).clamp(0, double.infinity);
    final double amountNeededForFreeShip = 200000 - subtotal;

    return Container(
      height: MediaQuery.of(context).size.height * 0.90,
      decoration: const BoxDecoration(
        color: Color(0xFFF0FDFA),
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        children: [
          // Header Bar
          Container(
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 12),
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              border: Border(bottom: BorderSide(color: Color(0x1F0EA5E9))),
            ),
            child: Row(
              children: [
                InkWell(
                  onTap: () => Navigator.pop(context),
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.arrow_back_ios_new, color: Color(0xFF0F172A), size: 18),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        '🛒 GIỎ HÀNG CỦA TÔI',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: Color(0xFF0F172A), letterSpacing: 0.3),
                      ),
                      Text(
                        '${_totalCartCount} sản phẩm trong giỏ',
                        style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
                if (_cartItems.isNotEmpty)
                  InkWell(
                    onTap: _clearCart,
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFEE2E2),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.delete_outline_rounded, color: Color(0xFFEF4444), size: 20),
                    ),
                  ),
              ],
            ),
          ),

          // Main Cart Body
          Expanded(
            child: _isLoading
                ? const CustomPulseLoader(
                    message: 'Đang kết nối giỏ hàng...',
                    icon: Icons.shopping_bag_rounded,
                    primaryColor: Color(0xFF0EA5E9),
                  )
                : _cartItems.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.shopping_bag_outlined, size: 64, color: Color(0xFF94A3B8)),
                            const SizedBox(height: 14),
                            const Text(
                              'Giỏ hàng của bạn đang trống!',
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Hãy thêm vài món ăn hoặc đặc sản OCOP nhé.',
                              style: TextStyle(fontSize: 13, color: Color(0xFF64748B)),
                            ),
                            const SizedBox(height: 20),
                            ElevatedButton.icon(
                              onPressed: () => Navigator.pop(context),
                              icon: const Icon(Icons.storefront_rounded, size: 18),
                              label: const Text('Khám phá Chợ Số', style: TextStyle(fontWeight: FontWeight.bold)),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF0EA5E9),
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                              ),
                            ),
                          ],
                        ),
                      )
                    : ListView(
                        padding: const EdgeInsets.all(14),
                        children: [
                          // Free Shipping Alert
                          if (subtotal < 200000)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              margin: const EdgeInsets.only(bottom: 12),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFFFBEB),
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(color: const Color(0xFFFDE68A)),
                              ),
                              child: Row(
                                children: [
                                  const Text('🚚 ', style: TextStyle(fontSize: 16)),
                                  Expanded(
                                    child: Text.rich(
                                      TextSpan(
                                        children: [
                                          const TextSpan(text: 'Mua thêm '),
                                          TextSpan(
                                            text: '${amountNeededForFreeShip.toInt()}đ ',
                                            style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFFD97706)),
                                          ),
                                          const TextSpan(text: 'để được '),
                                          const TextSpan(
                                            text: 'MIỄN PHÍ GIAO HÀNG!',
                                            style: TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF059669)),
                                          ),
                                        ],
                                      ),
                                      style: const TextStyle(fontSize: 12, color: Color(0xFF92400E)),
                                    ),
                                  ),
                                ],
                              ),
                            ),

                          // Cart Items List
                          ..._cartItems.map((item) {
                            final name = item['name'] ?? item['product_name'] ?? 'Sản phẩm';
                            final double price = double.tryParse(item['price']?.toString() ?? '0') ?? 0;
                            final int qty = item['quantity'] is int ? item['quantity'] : (int.tryParse(item['quantity']?.toString() ?? '1') ?? 1);
                            final String imgRaw = item['image_path'] ?? item['image'] ?? item['avatar'] ?? '';
                            final String imgUrl = imgRaw.startsWith('http')
                                ? imgRaw
                                : (imgRaw.isNotEmpty ? 'https://donganhdiscovery.xadonganh.com/' + (imgRaw.startsWith('/') ? imgRaw.substring(1) : imgRaw) : '');

                            return Container(
                              margin: const EdgeInsets.only(bottom: 10),
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: const Color(0xFF0EA5E9).withValues(alpha: 0.12)),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.03),
                                    blurRadius: 6,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: Row(
                                children: [
                                  // Thumbnail
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(10),
                                    child: imgUrl.isNotEmpty
                                        ? Image.network(imgUrl, width: 54, height: 54, fit: BoxFit.cover, cacheWidth: 108, filterQuality: FilterQuality.low)
                                        : Container(
                                            width: 54,
                                            height: 54,
                                            color: const Color(0xFFF0FDFA),
                                            child: const Icon(Icons.shopping_bag_outlined, color: Color(0xFF0EA5E9)),
                                          ),
                                  ),
                                  const SizedBox(width: 12),

                                  // Name & Price
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          name,
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                                          maxLines: 2,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          '${price.toInt()}đ',
                                          style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13, color: Color(0xFFEE4D2D)),
                                        ),
                                      ],
                                    ),
                                  ),

                                  // Quantity Counter Controls [-] N [+]
                                  Container(
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFF1F5F9),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(color: const Color(0xFFE2E8F0)),
                                    ),
                                    child: Row(
                                      children: [
                                        InkWell(
                                          onTap: () => _updateQuantity(item, -1),
                                          borderRadius: BorderRadius.circular(20),
                                          child: const Padding(
                                            padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                            child: Icon(Icons.remove, size: 14, color: Color(0xFF0F172A)),
                                          ),
                                        ),
                                        Text(
                                          '$qty',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                                        ),
                                        InkWell(
                                          onTap: () => _updateQuantity(item, 1),
                                          borderRadius: BorderRadius.circular(20),
                                          child: const Padding(
                                            padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                            child: Icon(Icons.add, size: 14, color: Color(0xFF0EA5E9)),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }),
                          const SizedBox(height: 14),

                          // Customer Order Form Section
                          Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: const Color(0xFF0EA5E9).withValues(alpha: 0.15)),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  '📋 THÔNG TIN GIAO HÀNG',
                                  style: TextStyle(fontWeight: FontWeight.w900, fontSize: 12, color: Color(0xFF0EA5E9), letterSpacing: 0.5),
                                ),
                                const SizedBox(height: 10),
                                TextField(
                                  controller: _nameController,
                                  style: const TextStyle(fontSize: 13),
                                  decoration: InputDecoration(
                                    labelText: 'Họ và tên người nhận *',
                                    isDense: true,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                  ),
                                ),
                                const SizedBox(height: 8),
                                TextField(
                                  controller: _phoneController,
                                  keyboardType: TextInputType.phone,
                                  style: const TextStyle(fontSize: 13),
                                  decoration: InputDecoration(
                                    labelText: 'Số điện thoại *',
                                    isDense: true,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                  ),
                                ),
                                const SizedBox(height: 8),
                                TextField(
                                  controller: _addressController,
                                  style: const TextStyle(fontSize: 13),
                                  decoration: InputDecoration(
                                    labelText: 'Địa chỉ nhận hàng chi tiết *',
                                    isDense: true,
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 14),

                          // Bill Summary
                          Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: const Color(0xFF0EA5E9).withValues(alpha: 0.15)),
                            ),
                            child: Column(
                              children: [
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Expanded(child: Text('Tạm tính:', style: TextStyle(color: Color(0xFF64748B), fontSize: 13))),
                                    Text('${subtotal.toInt()}đ', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                  ],
                                ),
                                const SizedBox(height: 4),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Expanded(child: Text('Phí giao hàng:', style: TextStyle(color: Color(0xFF64748B), fontSize: 13))),
                                    Text(shippingFee == 0 ? 'MIỄN PHÍ' : '${shippingFee.toInt()}đ', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: shippingFee == 0 ? const Color(0xFF059669) : const Color(0xFF0F172A))),
                                  ],
                                ),
                                const Divider(height: 18),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Expanded(child: Text('TỔNG THANH TOÁN:', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14, color: Color(0xFF0F172A)))),
                                    Text('${finalTotal.toInt()}đ', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 17, color: Color(0xFFEE4D2D))),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
          ),

          // Bottom Order Button
          if (_cartItems.isNotEmpty)
            Container(
              padding: const EdgeInsets.all(14),
              decoration: const BoxDecoration(
                color: Colors.white,
                border: Border(top: BorderSide(color: Color(0x1F0EA5E9))),
              ),
              child: SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton.icon(
                  onPressed: _handleCheckout,
                  icon: const Icon(Icons.check_circle_rounded, size: 20),
                  label: Text(
                    'ĐẶT HÀNG NGAY • ${finalTotal.toInt()}đ',
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900, letterSpacing: 0.3),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFEE4D2D),
                    foregroundColor: Colors.white,
                    elevation: 4,
                    shape: SquircleHelper.shape(radius: 24),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
