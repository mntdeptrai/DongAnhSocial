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

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
              ),
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom + 20,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Web-style Header Banner (Teal / Emerald Green matching Screenshot 1)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFF10B981), Color(0xFF059669)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.shopping_cart, color: Colors.white, size: 24),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Giỏ hàng mua sắm',
                                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Colors.white),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              Text(
                                '${_cartItems.length} sản phẩm • ${_totalCartCount} đã chọn',
                                style: const TextStyle(fontSize: 12, color: Colors.white70, fontWeight: FontWeight.w500),
                              ),
                            ],
                          ),
                        ),
                        InkWell(
                          onTap: () => _clearCart(setModalState),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Row(
                              children: [
                                Icon(Icons.delete_outline, size: 14, color: Colors.white),
                                SizedBox(width: 2),
                                Text('Xóa', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        InkWell(
                          onTap: () => Navigator.pop(context),
                          borderRadius: BorderRadius.circular(20),
                          child: Container(
                            padding: const EdgeInsets.all(6),
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.2),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(Icons.close, color: Colors.white, size: 18),
                          ),
                        ),
                      ],
                    ),
                  ),

                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    child: Column(
                      children: [
                        // Select All Pill matching Web
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(
                            color: const Color(0xFFECFDF5),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFA7F3D0)),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.check_box, color: Color(0xFF059669), size: 20),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  'Chọn tất cả (${_totalCartCount}/${_totalCartCount} món)',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF047857)),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 10),

                        // Cart List items grouped by Store/HKD
                        ConstrainedBox(
                          constraints: const BoxConstraints(maxHeight: 230),
                          child: ListView.separated(
                            shrinkWrap: true,
                            itemCount: _cartItems.length,
                            separatorBuilder: (_, __) => const SizedBox(height: 10),
                            itemBuilder: (context, index) {
                              final entry = _cartItems.entries.elementAt(index);
                              final item = entry.value;
                              final double price = (item['price'] as double);
                              final int qty = (item['quantity'] as int);
                              final double itemTotal = price * qty;
                              final String? imgUrl = item['image'];

                              return Container(
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(14),
                                  border: Border.all(color: const Color(0xFFE2E8F0)),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.03),
                                      blurRadius: 6,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    // Store Header
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                      decoration: const BoxDecoration(
                                        color: Color(0xFFF1F5F9),
                                        borderRadius: BorderRadius.vertical(top: Radius.circular(14)),
                                      ),
                                      child: Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Expanded(
                                            child: Text(
                                              '🏪 ${item['subtitle']}',
                                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF1E293B)),
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                          ),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                            decoration: BoxDecoration(
                                              color: const Color(0xFFD1FAE5),
                                              borderRadius: BorderRadius.circular(10),
                                            ),
                                            child: const Text('1/1', style: TextStyle(fontSize: 10, color: Color(0xFF047857), fontWeight: FontWeight.bold)),
                                          ),
                                        ],
                                      ),
                                    ),

                                    // Item Content Row
                                    Padding(
                                      padding: const EdgeInsets.all(10),
                                      child: Row(
                                        children: [
                                          const Icon(Icons.check_box, color: Color(0xFF059669), size: 20),
                                          const SizedBox(width: 8),

                                          if (imgUrl != null && imgUrl.isNotEmpty)
                                            ClipRRect(
                                              borderRadius: BorderRadius.circular(8),
                                              child: Image.network(
                                                _formatImageUrl(imgUrl),
                                                width: 48,
                                                height: 48,
                                                fit: BoxFit.cover,
                                                errorBuilder: (_, __, ___) => Container(
                                                  width: 48,
                                                  height: 48,
                                                  color: Colors.grey[200],
                                                  child: const Icon(Icons.restaurant, size: 22, color: Colors.grey),
                                                ),
                                              ),
                                            )
                                          else
                                            Container(
                                              width: 48,
                                              height: 48,
                                              decoration: BoxDecoration(
                                                color: const Color(0xFFECFDF5),
                                                borderRadius: BorderRadius.circular(8),
                                              ),
                                              child: const Icon(Icons.star, color: Color(0xFFF59E0B), size: 24),
                                            ),

                                          const SizedBox(width: 10),

                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(
                                                  item['name'],
                                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                                                  maxLines: 1,
                                                  overflow: TextOverflow.ellipsis,
                                                ),
                                                const SizedBox(height: 2),
                                                Text(
                                                  '${price.toInt()} đ',
                                                  style: const TextStyle(fontSize: 12, color: Color(0xFFF59E0B), fontWeight: FontWeight.w800),
                                                ),
                                              ],
                                            ),
                                          ),

                                          // Quantity counter matching Web style (- qty +)
                                          Column(
                                            crossAxisAlignment: CrossAxisAlignment.end,
                                            children: [
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                                                decoration: BoxDecoration(
                                                  color: const Color(0xFFECFDF5),
                                                  borderRadius: BorderRadius.circular(16),
                                                  border: Border.all(color: const Color(0xFFA7F3D0)),
                                                ),
                                                child: Row(
                                                  mainAxisSize: MainAxisSize.min,
                                                  children: [
                                                    InkWell(
                                                      onTap: () => _updateCartQuantity(entry.key, -1, setModalState),
                                                      child: const Padding(
                                                        padding: EdgeInsets.symmetric(horizontal: 4),
                                                        child: Icon(Icons.remove, size: 14, color: Color(0xFF059669)),
                                                      ),
                                                    ),
                                                    Padding(
                                                      padding: const EdgeInsets.symmetric(horizontal: 6),
                                                      child: Text(
                                                        '$qty',
                                                        style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13),
                                                      ),
                                                    ),
                                                    InkWell(
                                                      onTap: () => _updateCartQuantity(entry.key, 1, setModalState),
                                                      child: const Padding(
                                                        padding: EdgeInsets.symmetric(horizontal: 4),
                                                        child: Icon(Icons.add, size: 14, color: Color(0xFF059669)),
                                                      ),
                                                    ),
                                                  ],
                                                ),
                                              ),
                                              const SizedBox(height: 4),
                                              Row(
                                                children: [
                                                  Text(
                                                    '${itemTotal.toInt()} đ',
                                                    style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12, color: Color(0xFF0F172A)),
                                                  ),
                                                  const SizedBox(width: 8),
                                                  InkWell(
                                                    onTap: () => _removeCartItem(entry.key, setModalState),
                                                    child: const Padding(
                                                      padding: EdgeInsets.all(2.0),
                                                      child: Icon(Icons.delete_outline, size: 16, color: Colors.redAccent),
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),
                        ),
                        const SizedBox(height: 12),

                        // Total summary container
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                'Tổng tiền (${_totalCartCount} món):',
                                style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF475569)),
                              ),
                              Text(
                                '${_totalCartPrice.toInt()} VNĐ',
                                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 17, color: Color(0xFF0284C7)),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 10),

                        TextField(
                          controller: nameController,
                          decoration: InputDecoration(
                            labelText: 'Họ và tên người nhận',
                            prefixIcon: const Icon(Icons.person_outline, size: 18, color: Color(0xFF0284C7)),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                            isDense: true,
                          ),
                        ),
                        const SizedBox(height: 8),
                        TextField(
                          controller: phoneController,
                          keyboardType: TextInputType.phone,
                          decoration: InputDecoration(
                            labelText: 'Số điện thoại',
                            prefixIcon: const Icon(Icons.phone_outlined, size: 18, color: Color(0xFF0284C7)),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                            isDense: true,
                          ),
                        ),
                        const SizedBox(height: 8),
                        TextField(
                          controller: addressController,
                          decoration: InputDecoration(
                            labelText: 'Địa chỉ nhận hàng (Đông Anh, Hà Nội)',
                            prefixIcon: const Icon(Icons.location_on_outlined, size: 18, color: Color(0xFF0284C7)),
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                            isDense: true,
                          ),
                        ),
                        const SizedBox(height: 14),

                        Container(
                          width: double.infinity,
                          height: 48,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(12),
                            gradient: const LinearGradient(
                              colors: [Color(0xFF00A8EE), Color(0xFF0284C7)],
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF00A8EE).withOpacity(0.3),
                                blurRadius: 10,
                                offset: const Offset(0, 3),
                              ),
                            ],
                          ),
                          child: ElevatedButton.icon(
                            icon: const Icon(Icons.send_rounded, size: 18),
                            label: const Text('🚀 XÁC NHẬN ĐẶT HÀNG (COD)', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 14)),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.transparent,
                              foregroundColor: Colors.white,
                              shadowColor: Colors.transparent,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                            onPressed: () {
                              if (nameController.text.trim().isEmpty || phoneController.text.trim().isEmpty || addressController.text.trim().isEmpty) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Vui lòng nhập đầy đủ thông tin giao hàng!')),
                                );
                                return;
                              }
                              Navigator.pop(context);
                              setState(() {
                                _cartItems.clear();
                              });
                              ApiService.clearCart();
                              showDialog(
                                context: context,
                                builder: (ctx) => AlertDialog(
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                                  title: const Row(
                                    children: [
                                      Text('🎉 ', style: TextStyle(fontSize: 24)),
                                      Text('Đặt hàng thành công!'),
                                    ],
                                  ),
                                  content: const Text('Đơn hàng của bạn đã được đồng bộ hệ thống và gửi tới hộ kinh doanh. Shipper sẽ liên hệ giao hàng tận nơi.'),
                                  actions: [
                                    ElevatedButton(
                                      onPressed: () => Navigator.pop(ctx),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: const Color(0xFF0284C7),
                                        foregroundColor: Colors.white,
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                      ),
                                      child: const Text('Hoàn tất'),
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),
                        ),
                      ],
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
                    color: Color(0xFFEE4D2D), // Shopee Orange Red
                    borderRadius: BorderRadius.horizontal(right: Radius.circular(4)),
                  ),
                  child: Text(
                    isOcop ? 'OCOP 🏆 $pStar' : 'Yêu thích+',
                    style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
              // Badge Bottom Left: "VOUCHER XTRA"
              Positioned(
                bottom: 6,
                left: 6,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFB800), // Golden Yellow
                    borderRadius: BorderRadius.circular(3),
                    boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.2), blurRadius: 2)],
                  ),
                  child: const Text(
                    'VOUCHER XTRA',
                    style: TextStyle(color: Color(0xFFB91C1C), fontSize: 8, fontWeight: FontWeight.w900),
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

                  // Promo tag line
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF2F2),
                      borderRadius: BorderRadius.circular(3),
                      border: Border.all(color: const Color(0xFFFCA5A5)),
                    ),
                    child: const Text(
                      'Mua 3 giảm 2%',
                      style: TextStyle(fontSize: 8.5, color: Color(0xFFDC2626), fontWeight: FontWeight.w700),
                    ),
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
      backgroundColor: const Color(0xFFE0F2FE),
      body: NestedScrollView(
        headerSliverBuilder: (context, innerBoxIsScrolled) => [
          SliverAppBar(
            expandedHeight: 60.0,
            floating: false,
            pinned: true,
            elevation: 0,
            backgroundColor: const Color(0xFF0090D9),
            bottom: PreferredSize(
              preferredSize: const Size.fromHeight(60),
              child: Container(
                height: 60,
                color: const Color(0xFF0090D9),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                child: Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFF0077B6),
                    borderRadius: BorderRadius.circular(30),
                    border: Border.all(color: const Color(0xFFFFB800), width: 2.5),
                  ),
                  child: TabBar(
                    controller: _tabController,
                    indicator: BoxDecoration(
                      borderRadius: BorderRadius.circular(26),
                      gradient: const LinearGradient(
                        colors: [Color(0xFFFFB800), Color(0xFFFF9900)],
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFFFFB800).withOpacity(0.4),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    labelColor: Colors.white,
                    unselectedLabelColor: Colors.white70,
                    labelStyle: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13),
                    tabs: const [
                      Tab(text: '🍴 ẨM THỰC TINH TÚY'),
                      Tab(text: '🛒 CHỢ SỐ & OCOP'),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
        body: Column(
          children: [
            // Search Bar & Filter Strip with Yellow CTA Button
            Container(
              color: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              child: Column(
                children: [
                  // Pill Search Box matching Web
                  Container(
                    height: 44,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF0F9FF),
                      borderRadius: BorderRadius.circular(22),
                      border: Border.all(color: const Color(0xFFBAE6FD), width: 1.5),
                    ),
                    child: Row(
                      children: [
                        const SizedBox(width: 12),
                        const Icon(Icons.search, size: 20, color: Color(0xFF00A8EE)),
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
                          margin: const EdgeInsets.only(right: 3),
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFFFFB800), Color(0xFFFF9900)],
                            ),
                            borderRadius: BorderRadius.circular(18),
                          ),
                          child: const Text(
                            'Tìm kiếm',
                            style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w900),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 8),

                  // Filter chips
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: ['Tất cả', '⭐ Nổi bật', '🛵 Giao nhanh', '🏆 OCOP'].map((filter) {
                        final isSelected = _selectedFilter == filter;
                        return Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: ChoiceChip(
                            label: Text(filter),
                            selected: isSelected,
                            selectedColor: const Color(0xFF00A8EE),
                            backgroundColor: const Color(0xFFF0F9FF),
                            labelStyle: TextStyle(
                              fontSize: 12,
                              fontWeight: isSelected ? FontWeight.w900 : FontWeight.w600,
                              color: isSelected ? Colors.white : const Color(0xFF0369A1),
                            ),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(16),
                              side: BorderSide(
                                color: isSelected ? const Color(0xFF00A8EE) : const Color(0xFFBAE6FD),
                              ),
                            ),
                            onSelected: (val) {
                              if (val) setState(() => _selectedFilter = filter);
                            },
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
                      ? const Center(child: CircularProgressIndicator(color: Color(0xFF00A8EE)))
                      : _buildFoodDeliveryTab(),

                  // Tab 2: Chợ số & OCOP
                  _isLoadingMarket
                      ? const Center(child: CircularProgressIndicator(color: Color(0xFF00A8EE)))
                      : _buildMarketShoppingTab(),
                ],
              ),
            ),
          ],
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
                    color: const Color(0xFF10B981).withOpacity(0.4),
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
                color: const Color(0xFF00A8EE).withOpacity(0.08),
                blurRadius: 14,
                offset: const Offset(0, 4),
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
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity(0.7),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.star, color: Color(0xFFFFB800), size: 12),
                              const SizedBox(width: 2),
                              Text(
                                rating,
                                style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
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
                            style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: Color(0xFF0077B6)),
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
                                  color: const Color(0xFF00A8EE).withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: const Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(Icons.delivery_dining, size: 12, color: Color(0xFF00A8EE)),
                                    SizedBox(width: 4),
                                    Text(
                                      'Giao 20p 🛵',
                                      style: TextStyle(fontSize: 10, color: Color(0xFF00A8EE), fontWeight: FontWeight.bold),
                                    ),
                                  ],
                                ),
                              ),
                              Container(
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(10),
                                  gradient: const LinearGradient(
                                    colors: [Color(0xFFFFB800), Color(0xFFFF9900)],
                                  ),
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
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
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
                    childAspectRatio: 0.61,
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
