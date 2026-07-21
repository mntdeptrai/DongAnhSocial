import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'eatery_detail_screen.dart';

class UtilitiesScreen extends StatefulWidget {
  const UtilitiesScreen({super.key});

  @override
  State<UtilitiesScreen> createState() => _UtilitiesScreenState();
}

class _UtilitiesScreenState extends State<UtilitiesScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  List<dynamic> _foodEateries = [];
  List<dynamic> _marketEateries = [];
  List<dynamic> _marketProducts = [];

  bool _isLoadingFood = true;
  bool _isLoadingMarket = true;

  String _searchQuery = '';
  String _selectedFilter = 'Tất cả';

  // Cart Management synchronized with Web API
  final Map<String, Map<String, dynamic>> _cartItems = {};

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _fetchFoodData();
    _fetchMarketData();
    _fetchCartData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetchCartData() async {
    try {
      final res = await ApiService.getCart();
      if (res['success'] == true && res['data'] is List) {
        final List items = res['data'];
        final Map<String, Map<String, dynamic>> loadedCart = {};
        for (var item in items) {
          final String key = 'api_${item['id']}';
          loadedCart[key] = {
            'id': item['id'],
            'name': item['name'] ?? 'Sản phẩm OCOP',
            'price': double.tryParse(item['price']?.toString() ?? '0') ?? 0.0,
            'quantity': item['quantity'] ?? 1,
            'subtitle': item['eatery_name'] ?? 'Gian hàng chợ',
            'image': item['image'],
            'checked': true,
          };
        }
        if (mounted && loadedCart.isNotEmpty) {
          setState(() {
            _cartItems.clear();
            _cartItems.addAll(loadedCart);
          });
        }
      }
    } catch (_) {}
  }

  Future<void> _fetchFoodData() async {
    setState(() => _isLoadingFood = true);
    try {
      final res = await ApiService.getEateries('dong-anh-food-map');
      if (mounted) {
        setState(() {
          _foodEateries = res;
          _isLoadingFood = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoadingFood = false);
    }
  }

  Future<void> _fetchMarketData() async {
    setState(() => _isLoadingMarket = true);
    try {
      final markets = await ApiService.getEateries('dong-anh-market');
      final products = await ApiService.getMarketProducts();

      if (mounted) {
        setState(() {
          _marketEateries = markets;
          _marketProducts = products;
          _isLoadingMarket = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoadingMarket = false);
    }
  }

  Future<void> _addToCart(
    String key,
    String name,
    double price,
    String subtitle, {
    String? imagePath,
    int? dishId,
    int? ocopProductId,
  }) async {
    setState(() {
      if (_cartItems.containsKey(key)) {
        _cartItems[key]!['quantity'] = (_cartItems[key]!['quantity'] as int) + 1;
      } else {
        _cartItems[key] = {
          'name': name,
          'price': price,
          'quantity': 1,
          'subtitle': subtitle,
          'image': imagePath,
          'checked': true,
        };
      }
    });

    ScaffoldMessenger.of(context).clearSnackBars();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle, color: Color(0xFFFFB800), size: 20),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                'Đã thêm "$name" vào giỏ hàng đồng bộ!',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
            ),
          ],
        ),
        duration: const Duration(seconds: 2),
        backgroundColor: const Color(0xFF059669),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );

    if (dishId != null || ocopProductId != null) {
      await ApiService.addToCart(dishId: dishId, ocopProductId: ocopProductId);
      _fetchCartData();
    }
  }

  int get _totalCartCount {
    int count = 0;
    _cartItems.forEach((_, item) {
      if (item['checked'] != false) {
        count += (item['quantity'] as int);
      }
    });
    return count;
  }

  double get _totalCartPrice {
    double total = 0;
    _cartItems.forEach((_, item) {
      if (item['checked'] != false) {
        total += (item['price'] as double) * (item['quantity'] as int);
      }
    });
    return total;
  }

  Future<void> _updateCartQuantity(String key, int delta, StateSetter setModalState) async {
    final item = _cartItems[key];
    if (item == null) return;
    int newQty = (item['quantity'] as int) + delta;

    setState(() {
      if (newQty <= 0) {
        _cartItems.remove(key);
      } else {
        item['quantity'] = newQty;
      }
    });
    setModalState(() {});

    if (item['id'] != null) {
      if (newQty <= 0) {
        await ApiService.removeCartItem(item['id']);
      } else {
        await ApiService.updateCartItem(item['id'], newQty);
      }
      _fetchCartData();
    }

    if (_cartItems.isEmpty) {
      Navigator.pop(context);
    }
  }

  Future<void> _removeCartItem(String key, StateSetter setModalState) async {
    final item = _cartItems[key];
    setState(() {
      _cartItems.remove(key);
    });
    setModalState(() {});

    if (item != null && item['id'] != null) {
      await ApiService.removeCartItem(item['id']);
      _fetchCartData();
    }

    if (_cartItems.isEmpty) {
      Navigator.pop(context);
    }
  }

  Future<void> _clearCart(StateSetter setModalState) async {
    setState(() {
      _cartItems.clear();
    });
    setModalState(() {});
    await ApiService.clearCart();
    Navigator.pop(context);
  }

  void _showCheckoutModal() {
    if (_cartItems.isEmpty) return;

    final nameController = TextEditingController();
    final phoneController = TextEditingController();
    final addressController = TextEditingController();
    final promoController = TextEditingController();
    final noteController = TextEditingController();

    String? appliedPromoCode;
    String promoMessage = '';
    bool isPromoError = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            final double subtotal = _totalCartPrice;
            final double shippingFee = subtotal >= 200000 ? 0.0 : (subtotal > 0 ? 15000.0 : 0.0);
            final double discountAmount = appliedPromoCode == 'GIAM10' ? subtotal * 0.10 : 0.0;
            final double finalTotal = (subtotal + shippingFee - discountAmount).clamp(0, double.infinity);
            final double amountNeededForFreeShip = 200000 - subtotal;

            return Container(
              height: MediaQuery.of(context).size.height * 0.90,
              decoration: const BoxDecoration(
                color: Color(0xFFF8FAFC),
                borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
              ),
              child: Column(
                children: [
                  // Blue Header matching screenshot
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.fromLTRB(12, 14, 12, 14),
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFF0F4C8C), Color(0xFF1565C0), Color(0xFF1E88E5)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
                    ),
                    child: Row(
                      children: [
                        // Back Button (<)
                        InkWell(
                          onTap: () => Navigator.pop(context),
                          borderRadius: BorderRadius.circular(12),
                          child: Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(Icons.arrow_back_ios_new, color: Colors.white, size: 18),
                          ),
                        ),
                        const SizedBox(width: 10),

                        // Title Column
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'GIỎ HÀNG CỦA BẠN',
                                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: Colors.white, letterSpacing: 0.5),
                              ),
                              Text(
                                '${_totalCartCount} món đã chọn',
                                style: const TextStyle(fontSize: 12, color: Colors.white70, fontWeight: FontWeight.w600),
                              ),
                            ],
                          ),
                        ),

                        // Center Total Order Pill
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Tổng đơn hàng', style: TextStyle(fontSize: 10, color: Colors.white70)),
                              Text(
                                '${subtotal.toInt()}đ',
                                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: Colors.white),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 10),

                        // Red Delete Trash Button (Xóa toàn bộ giỏ)
                        InkWell(
                          onTap: () => _clearCart(setModalState),
                          borderRadius: BorderRadius.circular(12),
                          child: Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: const Color(0xFFEF4444),
                              borderRadius: BorderRadius.circular(12),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFFEF4444).withValues(alpha: 0.4),
                                  blurRadius: 8,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: const Icon(Icons.delete_outline, color: Colors.white, size: 20),
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Main Scrollable Cart Body
                  Expanded(
                    child: SingleChildScrollView(
                      padding: const EdgeInsets.all(14),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Free Shipping Alert Banner
                          if (subtotal < 200000)
                            Container(
                              width: double.infinity,
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
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
                            )
                          else
                            Container(
                              width: double.infinity,
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              decoration: BoxDecoration(
                                color: const Color(0xFFECFDF5),
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(color: const Color(0xFFA7F3D0)),
                              ),
                              child: const Row(
                                children: [
                                  Text('🎉 ', style: TextStyle(fontSize: 16)),
                                  Expanded(
                                    child: Text(
                                      'Bạn đã đạt điều kiện MIỄN PHÍ GIAO HÀNG (đơn > 200.000đ)!',
                                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF047857)),
                                    ),
                                  ),
                                ],
                              ),
                            ),

                          // Section Header: MÓN ĐÃ CHỌN
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                'MÓN ĐÃ CHỌN · ${_cartItems.length} MỤC',
                                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF64748B), letterSpacing: 0.5),
                              ),
                              Text(
                                '${_totalCartCount} món',
                                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF1565C0)),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),

                          // Item list matching Screenshot
                          ListView.separated(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: _cartItems.length,
                            separatorBuilder: (_, __) => const SizedBox(height: 10),
                            itemBuilder: (context, index) {
                              final entry = _cartItems.entries.elementAt(index);
                              final item = entry.value;
                              final double price = (item['price'] as double);
                              final int qty = (item['quantity'] as int);
                              final String? imgUrl = item['image'];
                              final String subtitle = item['subtitle'] ?? 'Đặc sản Đông Anh';

                              return Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(16),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withValues(alpha: 0.04),
                                      blurRadius: 10,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: Row(
                                  children: [
                                    // Item Image
                                    ClipRRect(
                                      borderRadius: BorderRadius.circular(12),
                                      child: imgUrl != null && imgUrl.isNotEmpty
                                          ? Image.network(
                                              _formatImageUrl(imgUrl),
                                              width: 58,
                                              height: 58,
                                              fit: BoxFit.cover,
                                              errorBuilder: (_, __, ___) => Container(
                                                width: 58,
                                                height: 58,
                                                color: const Color(0xFFF1F5F9),
                                                child: const Icon(Icons.restaurant, color: Colors.grey, size: 28),
                                              ),
                                            )
                                          : Container(
                                              width: 58,
                                              height: 58,
                                              color: const Color(0xFFF1F5F9),
                                              child: const Icon(Icons.restaurant, color: Color(0xFF1565C0), size: 28),
                                            ),
                                    ),
                                    const SizedBox(width: 12),

                                    // Name & Price
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            item['name'],
                                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 14, color: Color(0xFF0F172A)),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                          const SizedBox(height: 2),
                                          Text(
                                            subtitle,
                                            style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                            '${price.toInt()}đ',
                                            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: Color(0xFF1565C0)),
                                          ),
                                        ],
                                      ),
                                    ),

                                    // Counter Controls: (- qty +)
                                    Row(
                                      children: [
                                        // Minus Button (-)
                                        InkWell(
                                          onTap: () => _updateCartQuantity(entry.key, -1, setModalState),
                                          borderRadius: BorderRadius.circular(16),
                                          child: Container(
                                            width: 32,
                                            height: 32,
                                            decoration: const BoxDecoration(
                                              color: Color(0xFFF1F5F9),
                                              shape: BoxShape.circle,
                                            ),
                                            child: const Center(
                                              child: Icon(Icons.remove, size: 16, color: Color(0xFF64748B)),
                                            ),
                                          ),
                                        ),

                                        // Qty Text
                                        Padding(
                                          padding: const EdgeInsets.symmetric(horizontal: 10),
                                          child: Text(
                                            '$qty',
                                            style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15, color: Color(0xFF0F172A)),
                                          ),
                                        ),

                                        // Plus Button (+)
                                        InkWell(
                                          onTap: () => _updateCartQuantity(entry.key, 1, setModalState),
                                          borderRadius: BorderRadius.circular(16),
                                          child: Container(
                                            width: 32,
                                            height: 32,
                                            decoration: const BoxDecoration(
                                              color: Color(0xFF1565C0),
                                              shape: BoxShape.circle,
                                            ),
                                            child: const Center(
                                              child: Icon(Icons.add, size: 16, color: Colors.white),
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),
                          const SizedBox(height: 14),

                          // Section 2: Mã giảm giá
                          Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.04),
                                  blurRadius: 10,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Row(
                                  children: [
                                    Icon(Icons.confirmation_number_outlined, color: Color(0xFFFF6B35), size: 18),
                                    SizedBox(width: 6),
                                    Text('Mã giảm giá', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: Color(0xFF0F172A))),
                                  ],
                                ),
                                const SizedBox(height: 10),
                                Row(
                                  children: [
                                    Expanded(
                                      child: Container(
                                        height: 42,
                                        padding: const EdgeInsets.symmetric(horizontal: 12),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFF8FAFC),
                                          borderRadius: BorderRadius.circular(12),
                                          border: Border.all(color: const Color(0xFFE2E8F0)),
                                        ),
                                        child: TextField(
                                          controller: promoController,
                                          textCapitalization: TextCapitalization.characters,
                                          decoration: const InputDecoration(
                                            hintText: 'Nhập mã khuyến mãi...',
                                            hintStyle: TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                                            border: InputBorder.none,
                                            isDense: true,
                                          ),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    ElevatedButton(
                                      onPressed: () {
                                        final code = promoController.text.trim().toUpperCase();
                                        if (code == 'GIAM10') {
                                          appliedPromoCode = 'GIAM10';
                                          promoMessage = 'Đã áp dụng mã GIAM10 (Giảm 10%)';
                                          isPromoError = false;
                                        } else {
                                          appliedPromoCode = null;
                                          promoMessage = 'Mã giảm giá không hợp lệ!';
                                          isPromoError = true;
                                        }
                                        setModalState(() {});
                                      },
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: const Color(0xFF1565C0),
                                        foregroundColor: Colors.white,
                                        elevation: 0,
                                        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                      ),
                                      child: const Text('Áp dụng', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 6),
                                if (promoMessage.isNotEmpty)
                                  Padding(
                                    padding: const EdgeInsets.only(top: 2),
                                    child: Text(
                                      promoMessage,
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        color: isPromoError ? Colors.redAccent : const Color(0xFF059669),
                                      ),
                                    ),
                                  )
                                else
                                  const Text.rich(
                                    TextSpan(
                                      children: [
                                        TextSpan(text: 'Thử mã: '),
                                        TextSpan(
                                          text: 'GIAM10',
                                          style: TextStyle(fontWeight: FontWeight.w900, color: Color(0xFFFF6B35)),
                                        ),
                                        TextSpan(text: ' để giảm 10%'),
                                      ],
                                    ),
                                    style: TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                                  ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 14),

                          // Section 3: Ghi chú cho shipper
                          Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.04),
                                  blurRadius: 10,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Row(
                                  children: [
                                    Icon(Icons.chat_bubble_outline, color: Color(0xFF1565C0), size: 18),
                                    SizedBox(width: 6),
                                    Text('Ghi chú cho shipper', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: Color(0xFF0F172A))),
                                  ],
                                ),
                                const SizedBox(height: 10),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF8FAFC),
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(color: const Color(0xFFE2E8F0)),
                                  ),
                                  child: TextField(
                                    controller: noteController,
                                    decoration: const InputDecoration(
                                      hintText: 'VD: Gọi trước khi giao, không hành, ít cay...',
                                      hintStyle: TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                                      border: InputBorder.none,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 14),

                          // Section 4: Thông tin nhận hàng (Tùy chọn)
                          Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.04),
                                  blurRadius: 10,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Row(
                                  children: [
                                    Icon(Icons.location_on_outlined, color: Color(0xFF1565C0), size: 18),
                                    SizedBox(width: 6),
                                    Text('Địa chỉ giao hàng', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: Color(0xFF0F172A))),
                                  ],
                                ),
                                const SizedBox(height: 10),
                                TextField(
                                  controller: nameController,
                                  decoration: InputDecoration(
                                    labelText: 'Họ và tên người nhận',
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                    isDense: true,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                TextField(
                                  controller: phoneController,
                                  keyboardType: TextInputType.phone,
                                  decoration: InputDecoration(
                                    labelText: 'Số điện thoại',
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                    isDense: true,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                TextField(
                                  controller: addressController,
                                  decoration: InputDecoration(
                                    labelText: 'Địa chỉ (Đông Anh, Hà Nội)',
                                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                    isDense: true,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 14),

                          // Section 5: Chi tiết thanh toán matching Screenshot
                          Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.04),
                                  blurRadius: 10,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'Chi tiết thanh toán',
                                  style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: Color(0xFF0F172A)),
                                ),
                                const SizedBox(height: 10),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text('Tạm tính', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                                    Text('${subtotal.toInt()}đ', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                                  ],
                                ),
                                const SizedBox(height: 6),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text('Phí giao hàng', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                                    Text(
                                      shippingFee == 0 ? 'Miễn phí' : '${shippingFee.toInt()}đ',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                        color: shippingFee == 0 ? const Color(0xFF059669) : const Color(0xFF0F172A),
                                      ),
                                    ),
                                  ],
                                ),
                                if (discountAmount > 0) ...[
                                  const SizedBox(height: 6),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      const Text('Giảm giá (GIAM10)', style: TextStyle(fontSize: 12, color: Color(0xFF059669))),
                                      Text('-${discountAmount.toInt()}đ', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF059669))),
                                    ],
                                  ),
                                ],
                                const Padding(
                                  padding: EdgeInsets.symmetric(vertical: 8),
                                  child: Divider(height: 1, color: Color(0xFFE2E8F0)),
                                ),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text('Tổng cộng', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: Color(0xFF0F172A))),
                                    Text(
                                      '${finalTotal.toInt()}đ',
                                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Color(0xFF1565C0)),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 16),
                        ],
                      ),
                    ),
                  ),

                  // Bottom Sticky CTA Button (Đặt Hàng)
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.08),
                          blurRadius: 10,
                          offset: const Offset(0, -4),
                        ),
                      ],
                    ),
                    child: SafeArea(
                      top: false,
                      child: SizedBox(
                        width: double.infinity,
                        height: 50,
                        child: ElevatedButton(
                          onPressed: () {
                            final String orderCode = '#DA-${(10000 + (subtotal.toInt() % 89999))}';
                            final String noteText = noteController.text.trim();

                            Navigator.pop(context);
                            setState(() {
                              _cartItems.clear();
                            });
                            ApiService.clearCart();

                            // Show Order Success Dialog with details
                            showDialog(
                              context: context,
                              builder: (ctx) => AlertDialog(
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
                                contentPadding: const EdgeInsets.all(20),
                                content: Column(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Container(
                                      width: 64,
                                      height: 64,
                                      decoration: const BoxDecoration(
                                        color: Color(0xFFECFDF5),
                                        shape: BoxShape.circle,
                                      ),
                                      child: const Icon(Icons.check_circle_rounded, color: Color(0xFF059669), size: 44),
                                    ),
                                    const SizedBox(height: 14),
                                    const Text(
                                      'ĐẶT HÀNG THÀNH CÔNG!',
                                      style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900, color: Color(0xFF047857)),
                                    ),
                                    const SizedBox(height: 8),
                                    Text(
                                      'Mã đơn: $orderCode',
                                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1565C0)),
                                    ),
                                    const SizedBox(height: 4),
                                    const Text(
                                      '⏱️ Thời gian dự kiến: 20 - 30 phút',
                                      style: TextStyle(fontSize: 12, color: Color(0xFF64748B), fontWeight: FontWeight.w600),
                                    ),
                                    if (noteText.isNotEmpty) ...[
                                      const SizedBox(height: 8),
                                      Container(
                                        padding: const EdgeInsets.all(8),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFF1F5F9),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          '💬 Ghi chú: $noteText',
                                          style: const TextStyle(fontSize: 11, color: Color(0xFF475569)),
                                        ),
                                      ),
                                    ],
                                    const SizedBox(height: 14),
                                    Text(
                                      'Tổng tiền thanh toán: ${finalTotal.toInt()}đ',
                                      style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                                    ),
                                    const SizedBox(height: 18),
                                    SizedBox(
                                      width: double.infinity,
                                      height: 44,
                                      child: ElevatedButton(
                                        onPressed: () => Navigator.pop(ctx),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: const Color(0xFF1565C0),
                                          foregroundColor: Colors.white,
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                        ),
                                        child: const Text('Hoàn tất', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF1565C0),
                            foregroundColor: Colors.white,
                            elevation: 0,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          ),
                          child: Text(
                            '🚀 ĐẶT HÀNG • ${finalTotal.toInt()}đ',
                            style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15),
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  void _openStallDetail(Map<String, dynamic> item) {
    String eaterySlug = item['eatery_slug']?.toString() ?? (item['slug']?.toString() ?? '');
    String catSlug = item['category_slug']?.toString() ?? (item['category']?['slug']?.toString() ?? 'dong-anh-market');

    if (eaterySlug.isEmpty && _marketEateries.isNotEmpty) {
      final matched = _marketEateries.firstWhere(
        (m) => m['id'] == item['eatery_id'] || m['name'] == item['stall_name'] || m['name'] == item['name'],
        orElse: () => _marketEateries.first,
      );
      eaterySlug = matched['slug']?.toString() ?? '';
      catSlug = matched['category']?['slug']?.toString() ?? 'dong-anh-market';
    }

    if (eaterySlug.isNotEmpty) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => EateryDetailScreen(
            categorySlug: catSlug,
            eaterySlug: eaterySlug,
          ),
        ),
      );
    }
  }

  Widget _buildShopeeProductCard(BuildContext context, Map<String, dynamic> item, {bool isOcop = true}) {
    final String pName = item['name'] ?? item['product_name'] ?? 'Sản phẩm OCOP';
    final double pPrice = double.tryParse(item['price']?.toString() ?? '0') ?? 0;
    final String pStar = item['star_rating'] ?? (item['star'] != null ? '${item['star']} SAO' : '4 SAO');
    final String pImgRaw = item['image_path'] ?? item['image'] ?? item['cover_image_url'] ?? item['avatar'] ?? '';
    final String pImgUrl = _formatImageUrl(pImgRaw);

    final int rawId = item['id'] is int ? item['id'] : (int.tryParse(item['id']?.toString() ?? '') ?? 1);
    final int salesCount = rawId * 14 + 18;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Product Thumbnail Image with Shopee Badges Overlaid
          Stack(
            children: [
              AspectRatio(
                aspectRatio: 1.0,
                child: ClipRRect(
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                  child: Image.network(
                    pImgUrl,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      color: const Color(0xFFFFFBEB),
                      child: const Icon(Icons.shopping_bag_outlined, color: Colors.amber, size: 40),
                    ),
                  ),
                ),
              ),
              // Badge Top Left: "OCOP 4 SAO" / "Yêu thích+"
              Positioned(
                top: 6,
                left: 0,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: const BoxDecoration(
                    color: Color(0xFFEE4D2D),
                    borderRadius: BorderRadius.horizontal(right: Radius.circular(4)),
                  ),
                  child: Text(
                    isOcop ? 'OCOP 🏆 $pStar' : 'Yêu thích+',
                    style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ],
          ),

          // Product Information Container
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(7.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  // Title (2 lines max)
                  Text(
                    pName,
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                      color: Color(0xFF0F172A),
                      height: 1.2,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),

                  // Price & Discount Line
                  Row(
                    children: [
                      Text(
                        pPrice > 0 ? '${pPrice.toInt()}đ' : 'Liên hệ',
                        style: const TextStyle(
                          color: Color(0xFFEE4D2D), // Shopee Price Red
                          fontWeight: FontWeight.w900,
                          fontSize: 13.5,
                        ),
                      ),
                      if (pPrice > 0) ...[
                        const SizedBox(width: 4),
                        Text(
                          '${(pPrice * 1.25).toInt()}đ',
                          style: const TextStyle(
                            color: Colors.grey,
                            fontSize: 9.5,
                            decoration: TextDecoration.lineThrough,
                          ),
                        ),
                      ],
                    ],
                  ),

                  // Rating ⭐ and Sales Count Row
                  Row(
                    children: [
                      const Icon(Icons.star, color: Color(0xFFFFB800), size: 11),
                      const SizedBox(width: 2),
                      const Text('5.0', style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                      const SizedBox(width: 4),
                      Text('| Đã bán $salesCount', style: const TextStyle(fontSize: 9.5, color: Colors.grey)),
                    ],
                  ),

                  // CTA Button: "+ Thêm Giỏ"
                  SizedBox(
                    width: double.infinity,
                    height: 26,
                    child: ElevatedButton(
                      onPressed: () {
                        final int? itemId = item['id'] is int ? item['id'] : int.tryParse(item['id']?.toString() ?? '');
                        if (isOcop) {
                          ApiService.addToCart(ocopProductId: itemId);
                        } else {
                          ApiService.addToCart(dishId: itemId);
                        }
                        _fetchCartData();
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('🛒 Đã thêm "$pName" vào giỏ hàng!'),
                            backgroundColor: const Color(0xFF059669),
                            duration: const Duration(seconds: 2),
                          ),
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFFFB800),
                        foregroundColor: Colors.white,
                        padding: EdgeInsets.zero,
                        elevation: 0,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                      ),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.add_shopping_cart, size: 12),
                          SizedBox(width: 3),
                          Text('+ Thêm Giỏ', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w900)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showNotificationsModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          return FutureBuilder<List<dynamic>>(
            future: ApiService.getAppNotifications(),
            builder: (context, snapshot) {
              final List<dynamic> notifs = snapshot.data ?? [];
              final bool isLoading = snapshot.connectionState == ConnectionState.waiting;

              return Container(
                padding: const EdgeInsets.all(20),
                constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.7),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.notifications_active, color: Color(0xFFFFB800), size: 24),
                            SizedBox(width: 8),
                            Text('Thông báo hệ thống & Đơn hàng', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0077B6))),
                          ],
                        ),
                        IconButton(
                          icon: const Icon(Icons.close, color: Colors.grey),
                          onPressed: () => Navigator.pop(ctx),
                        ),
                      ],
                    ),
                    const Divider(height: 20),
                    if (isLoading)
                      const Padding(
                        padding: EdgeInsets.all(32.0),
                        child: Center(child: CircularProgressIndicator(color: Color(0xFF0284C7))),
                      )
                    else if (notifs.isEmpty)
                      const Padding(
                        padding: EdgeInsets.all(32.0),
                        child: Center(child: Text('Chưa có thông báo mới', style: TextStyle(color: Colors.grey))),
                      )
                    else
                      Expanded(
                        child: ListView.separated(
                          shrinkWrap: true,
                          itemCount: notifs.length,
                          separatorBuilder: (_, __) => const Divider(height: 1),
                          itemBuilder: (context, index) {
                            final item = notifs[index];
                            final String title = item['title'] ?? 'Thông báo';
                            final String body = item['body'] ?? '';
                            final String time = item['time'] ?? 'Vừa xong';
                            final String iconType = item['icon'] ?? 'notifications';

                            IconData iconData = Icons.notifications;
                            Color bg = const Color(0xFFE0F2FE);
                            Color fg = const Color(0xFF0284C7);

                            if (iconType == 'comment') {
                              iconData = Icons.comment;
                              bg = const Color(0xFFE0F2FE);
                              fg = const Color(0xFF0284C7);
                            } else if (iconType == 'card_giftcard') {
                              iconData = Icons.card_giftcard;
                              bg = const Color(0xFFFFFBEB);
                              fg = const Color(0xFFD97706);
                            } else if (iconType == 'local_shipping') {
                              iconData = Icons.local_shipping;
                              bg = const Color(0xFFECFDF5);
                              fg = const Color(0xFF059669);
                            }

                            return ListTile(
                              contentPadding: const EdgeInsets.symmetric(vertical: 4, horizontal: 0),
                              leading: CircleAvatar(backgroundColor: bg, child: Icon(iconData, color: fg, size: 20)),
                              title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              subtitle: Text(body, style: const TextStyle(fontSize: 12)),
                              trailing: Text(time, style: const TextStyle(fontSize: 10, color: Colors.grey)),
                            );
                          },
                        ),
                      ),
                  ],
                ),
              );
            },
          );
        },
      ),
    );
  }

  String _formatImageUrl(String? rawPath, {String fallback = 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80'}) {
    if (rawPath == null || rawPath.trim().isEmpty) return fallback;
    final path = rawPath.trim();
    if (path.startsWith('http://') || path.startsWith('https://')) {
      return path;
    }
    final cleanPath = path.startsWith('/') ? path.substring(1) : path;
    return 'https://donganhdiscovery.xadonganh.com/' + cleanPath;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      body: NestedScrollView(
        headerSliverBuilder: (context, innerBoxIsScrolled) => [
          SliverAppBar(
            expandedHeight: 70.0,
            floating: false,
            pinned: true,
            elevation: 0,
            backgroundColor: const Color(0xFF0F4C8C),
            flexibleSpace: Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    Color(0xFF0F4C8C),
                    Color(0xFF1565C0),
                    Color(0xFF1E88E5),
                    Color(0xFF29B6F6),
                  ],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
              ),
            ),
            bottom: PreferredSize(
              preferredSize: const Size.fromHeight(64),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                child: Container(
                  height: 48,
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.18),
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(
                      color: Colors.white.withValues(alpha: 0.25),
                      width: 1,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.1),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: TabBar(
                    controller: _tabController,
                    indicatorSize: TabBarIndicatorSize.tab,
                    dividerColor: Colors.transparent,
                    labelPadding: EdgeInsets.zero,
                    indicator: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.12),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    labelColor: const Color(0xFF1565C0),
                    unselectedLabelColor: Colors.white,
                    labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13, letterSpacing: 0.3),
                    unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, letterSpacing: 0.3),
                    tabs: const [
                      Tab(
                        height: 40,
                        child: Center(child: Text('ẨM THỰC TINH TÚY')),
                      ),
                      Tab(
                        height: 40,
                        child: Center(child: Text('CHỢ SỐ & OCOP')),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
        body: Container(
          decoration: const BoxDecoration(
            color: Color(0xFFF8FAFC),
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: Column(
            children: [
              // Search Bar & Filter Strip with Red-Orange CTA Button
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                child: Column(
                  children: [
                    // Pill Search Box with shadow-[0_4px_20px_rgba(0,0,0,0.15)]
                    Container(
                      height: 48,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(24),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.15),
                            blurRadius: 20,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Row(
                        children: [
                          const SizedBox(width: 14),
                          const Icon(Icons.search, size: 20, color: Color(0xFF1565C0)),
                          const SizedBox(width: 8),
                          Expanded(
                            child: TextField(
                              onChanged: (val) => setState(() => _searchQuery = val.trim().toLowerCase()),
                              decoration: const InputDecoration(
                                hintText: "Tìm 'Bún chả', 'Chợ Tó', 'Đặc sản OCOP'...",
                                hintStyle: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                border: InputBorder.none,
                                isDense: true,
                              ),
                            ),
                          ),
                          Container(
                            margin: const EdgeInsets.only(right: 4),
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [Color(0xFFFF6B35), Color(0xFFE53935)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(20),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFFE53935).withValues(alpha: 0.35),
                                  blurRadius: 8,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: const Text(
                              'Tìm kiếm',
                              style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 10),

                    // Filter chips
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: ['Tất cả', '⭐ Nổi bật', '🛵 Giao nhanh', '🏆 OCOP'].map((filter) {
                          final isSelected = _selectedFilter == filter;
                          return GestureDetector(
                            onTap: () {
                              setState(() => _selectedFilter = filter);
                            },
                            child: Container(
                              margin: const EdgeInsets.only(right: 8, top: 4, bottom: 4),
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                              decoration: BoxDecoration(
                                color: isSelected ? const Color(0xFF1565C0) : Colors.white,
                                borderRadius: BorderRadius.circular(16),
                                boxShadow: [
                                  isSelected
                                      ? const BoxShadow(
                                          color: Color(0x661565C0),
                                          blurRadius: 12,
                                          offset: Offset(0, 4),
                                        )
                                      : BoxShadow(
                                          color: Colors.black.withValues(alpha: 0.07),
                                          blurRadius: 8,
                                          offset: const Offset(0, 2),
                                        ),
                                ],
                              ),
                              child: Text(
                                filter,
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                                  color: isSelected ? Colors.white : const Color(0xFF64748B),
                                ),
                              ),
                            ),
                          );
                        }).toList(),
                      ),
                    ),
                  ],
                ),
              ),

              // Main Tab View
              Expanded(
                child: TabBarView(
                  controller: _tabController,
                  children: [
                    // Tab 1: Đặt đồ ăn
                    _isLoadingFood
                        ? const Center(child: CircularProgressIndicator(color: Color(0xFF1565C0)))
                        : _buildFoodDeliveryTab(),

                    // Tab 2: Chợ số & OCOP
                    _isLoadingMarket
                        ? const Center(child: CircularProgressIndicator(color: Color(0xFF1565C0)))
                        : _buildMarketShoppingTab(),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),

      // Floating Glassmorphic Cart Bar
      bottomNavigationBar: _cartItems.isNotEmpty
          ? Container(
              margin: const EdgeInsets.all(12),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF059669), Color(0xFF10B981)],
                ),
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: const Color(0xFFFFB800), width: 2),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF10B981).withValues(alpha: 0.4),
                    blurRadius: 16,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Stack(
                        clipBehavior: Clip.none,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: const BoxDecoration(
                              color: Color(0xFFFFB800),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(Icons.shopping_bag_outlined, color: Colors.white, size: 22),
                          ),
                          Positioned(
                            top: -4,
                            right: -4,
                            child: Container(
                              padding: const EdgeInsets.all(5),
                              decoration: const BoxDecoration(
                                color: Colors.redAccent,
                                shape: BoxShape.circle,
                              ),
                              child: Text(
                                '$_totalCartCount',
                                style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(width: 12),
                      Column(
                        mainAxisSize: MainAxisSize.min,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${_cartItems.length} loại sản phẩm (Đồng bộ Web)',
                            style: const TextStyle(fontSize: 11, color: Colors.white70),
                          ),
                          Text(
                            '${_totalCartPrice.toInt()} VNĐ',
                            style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 17, color: Color(0xFFFFB800)),
                          ),
                        ],
                      ),
                    ],
                  ),

                  // Button Xem giỏ
                  Container(
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(20),
                      gradient: const LinearGradient(
                        colors: [Color(0xFFFFB800), Color(0xFFFF9900)],
                      ),
                    ),
                    child: ElevatedButton.icon(
                      onPressed: _showCheckoutModal,
                      icon: const Icon(Icons.arrow_forward, size: 16),
                      label: const Text('Xem Giỏ Hàng', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.transparent,
                        foregroundColor: Colors.white,
                        shadowColor: Colors.transparent,
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                      ),
                    ),
                  ),
                ],
              ),
            )
          : null,
    );
  }

  Widget _buildFoodDeliveryTab() {
    var eateries = _foodEateries.where((eatery) {
      final name = (eatery['name'] ?? '').toString().toLowerCase();
      final address = (eatery['address'] ?? '').toString().toLowerCase();
      final matchesSearch = _searchQuery.isEmpty || name.contains(_searchQuery) || address.contains(_searchQuery);
      if (!matchesSearch) return false;

      if (_selectedFilter == '⭐ Nổi bật') return eatery['is_featured'] == true || (eatery['rating'] != null && double.tryParse(eatery['rating'].toString())! >= 4.5);
      if (_selectedFilter == '🛵 Giao nhanh') return true;
      if (_selectedFilter == '🏆 OCOP') return (eatery['ocop_stars'] != null && eatery['ocop_stars'] > 0);
      return true;
    }).toList();

    if (eateries.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.restaurant_menu_outlined, size: 54, color: Colors.grey[400]),
            const SizedBox(height: 12),
            Text(
              'Không tìm thấy quán ăn phù hợp',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.grey[700]),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: eateries.length,
      itemBuilder: (context, index) {
        final eatery = eateries[index];
        final String name = eatery['name'] ?? 'Quán ăn';
        final String address = eatery['address'] ?? 'Đông Anh, Hà Nội';
        final String rating = (eatery['rating'] ?? '4.8').toString();
        final String? rawPhoto = eatery['image_path'] ?? eatery['cover_image_url'] ?? eatery['avatar'] ?? eatery['image'];
        final String photo = _formatImageUrl(rawPhoto);

        return Container(
          margin: const EdgeInsets.only(bottom: 14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.07),
                blurRadius: 12,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              borderRadius: BorderRadius.circular(16),
              onTap: () {
                final catSlug = eatery['category']?['slug']?.toString() ?? 'dong-anh-food-map';
                final eaterySlug = eatery['slug']?.toString() ?? '';
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => EateryDetailScreen(
                      categorySlug: catSlug,
                      eaterySlug: eaterySlug,
                      initialData: Map<String, dynamic>.from(eatery),
                    ),
                  ),
                );
              },
              child: Row(
                children: [
                  Stack(
                    children: [
                      ClipRRect(
                        borderRadius: const BorderRadius.horizontal(left: Radius.circular(16)),
                        child: Image.network(
                          photo,
                          width: 125,
                          height: 115,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(
                            width: 125,
                            height: 115,
                            color: Colors.grey[200],
                            child: const Icon(Icons.restaurant, color: Colors.grey, size: 36),
                          ),
                        ),
                      ),
                      Positioned(
                        top: 6,
                        left: 6,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.6),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.star, color: Color(0xFFFFB800), size: 12),
                              const SizedBox(width: 3),
                              Text(
                                rating,
                                style: const TextStyle(color: Colors.white, fontSize: 10.5, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),

                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.all(12.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            name,
                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: Color(0xFF1565C0)),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              const Icon(Icons.location_on_outlined, size: 14, color: Color(0xFF64748B)),
                              const SizedBox(width: 2),
                              Expanded(
                                child: Text(
                                  address,
                                  style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),

                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF1565C0).withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(Icons.delivery_dining, size: 12, color: Color(0xFF1565C0)),
                                    SizedBox(width: 4),
                                    Text(
                                      'Giao 20p 🛵',
                                      style: TextStyle(fontSize: 10, color: Color(0xFF1565C0), fontWeight: FontWeight.bold),
                                    ),
                                  ],
                                ),
                              ),
                              Container(
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(10),
                                  gradient: const LinearGradient(
                                    colors: [Color(0xFFFB923C), Color(0xFFEF4444)],
                                    begin: Alignment.topLeft,
                                    end: Alignment.bottomRight,
                                  ),
                                  boxShadow: const [
                                    BoxShadow(
                                      color: Color(0x59EF4444),
                                      blurRadius: 10,
                                      offset: Offset(0, 3),
                                    ),
                                  ],
                                ),
                                child: ElevatedButton(
                                  onPressed: () {
                                    final int? dishId = eatery['dishes'] != null && (eatery['dishes'] as List).isNotEmpty ? eatery['dishes'][0]['id'] : null;
                                    _addToCart('eatery_${eatery['id']}', 'Món ngon từ $name', 45000, name, imagePath: photo, dishId: dishId);
                                  },
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: Colors.transparent,
                                    foregroundColor: Colors.white,
                                    shadowColor: Colors.transparent,
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                    minimumSize: Size.zero,
                                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                  ),
                                  child: const Text('+ Đặt món', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w900)),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildMarketShoppingTab() {
    var products = _marketProducts.where((product) {
      final name = (product['name'] ?? '').toString().toLowerCase();
      final stall = (product['stall_name'] ?? '').toString().toLowerCase();
      final matchesSearch = _searchQuery.isEmpty || name.contains(_searchQuery) || stall.contains(_searchQuery);
      if (!matchesSearch) return false;

      if (_selectedFilter == '⭐ Nổi bật') return true;
      if (_selectedFilter == '🏆 OCOP') return true;
      return true;
    }).toList();

    return SingleChildScrollView(
      padding: const EdgeInsets.all(12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Banner showcase
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0077B6), Color(0xFF00A8EE)],
              ),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFFFB800), width: 2),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: const BoxDecoration(
                    color: Color(0xFFFFB800),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.workspace_premium, color: Colors.white, size: 28),
                ),
                const SizedBox(width: 12),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '🏪 CHỢ SỐ & NÔNG SẢN OCOP ĐÔNG ANH',
                        style: TextStyle(color: Color(0xFFFFB800), fontWeight: FontWeight.w900, fontSize: 13),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'Trực tiếp từ gian hàng chính gốc của các hộ kinh doanh',
                        style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Polaroid Style Horizontal Stalls List
          if (_marketEateries.isNotEmpty) ...[
            const Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  '🏬 Chợ & Gian Hàng OCOP Nổi Bật',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: Color(0xFF0077B6)),
                ),
                Text(
                  'Xem tất cả >',
                  style: TextStyle(fontSize: 12, color: Color(0xFF00A8EE), fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 10),
            SizedBox(
              height: 135,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: _marketEateries.length,
                itemBuilder: (context, idx) {
                  final m = _marketEateries[idx];
                  final mName = m['name'] ?? 'Gian hàng OCOP';
                  final mImg = _formatImageUrl(m['image_path'] ?? m['cover_image_url'] ?? m['avatar']);

                  return Container(
                    width: 155,
                    margin: const EdgeInsets.only(right: 12),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [
                        BoxShadow(color: const Color(0xFF00A8EE).withOpacity(0.12), blurRadius: 10, offset: const Offset(0, 4)),
                      ],
                    ),
                    child: InkWell(
                      onTap: () => _openStallDetail(Map<String, dynamic>.from(m)),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: ClipRRect(
                              borderRadius: const BorderRadius.vertical(top: Radius.circular(14)),
                              child: Image.network(
                                mImg,
                                width: double.infinity,
                                fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => Container(
                                  color: Colors.orange[50],
                                  child: const Icon(Icons.store, color: Colors.orange, size: 36),
                                ),
                              ),
                            ),
                          ),
                          Padding(
                            padding: const EdgeInsets.all(8.0),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  mName,
                                  style: const TextStyle(color: Color(0xFF0077B6), fontWeight: FontWeight.bold, fontSize: 12),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 2),
                                const Row(
                                  children: [
                                    Icon(Icons.verified, color: Color(0xFFFFB800), size: 12),
                                    SizedBox(width: 3),
                                    Text(
                                      'Ghé gian hàng ➔',
                                      style: TextStyle(color: Color(0xFF00A8EE), fontSize: 10, fontWeight: FontWeight.w900),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: 18),
          ],

          const Text(
            '🛒 Danh Mục Sản Phẩm OCOP & Đặc Sản',
            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: Color(0xFF0077B6)),
          ),
          const SizedBox(height: 10),

          products.isEmpty
              ? Container(
                  padding: const EdgeInsets.all(30),
                  alignment: Alignment.center,
                  child: const Text('Chưa có sản phẩm OCOP nào.', style: TextStyle(color: Colors.grey)),
                )
              : GridView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    crossAxisSpacing: 10,
                    mainAxisSpacing: 10,
                    childAspectRatio: 0.67,
                  ),
                  itemCount: products.length,
                  itemBuilder: (context, index) {
                    final product = products[index];
                    return _buildShopeeProductCard(context, product, isOcop: true);
                  },
                ),
        ],
      ),
    );
  }
}
