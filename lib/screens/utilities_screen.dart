import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/my_cart_modal.dart';
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
                aspectRatio: 1.15,
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
      backgroundColor: const Color(0xFFF0FDFA),
      body: NestedScrollView(
        headerSliverBuilder: (context, innerBoxIsScrolled) => [
          SliverAppBar(
            toolbarHeight: 0,
            expandedHeight: 0.0,
            floating: false,
            pinned: true,
            elevation: 0,
            backgroundColor: const Color(0xFFF0FDFA),
            flexibleSpace: Container(
              decoration: const BoxDecoration(
                color: Color(0xFFF0FDFA),
                border: Border(
                  bottom: BorderSide(color: Color(0x1F0EA5E9), width: 1.0),
                ),
              ),
            ),
            bottom: PreferredSize(
              preferredSize: const Size.fromHeight(46),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                child: Container(
                  height: 38,
                  padding: const EdgeInsets.all(3),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: const Color(0xFF0EA5E9).withValues(alpha: 0.18),
                      width: 1,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF06B6D4).withValues(alpha: 0.06),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: TabBar(
                    controller: _tabController,
                    indicatorSize: TabBarIndicatorSize.tab,
                    dividerColor: Colors.transparent,
                    labelPadding: EdgeInsets.zero,
                    indicator: BoxDecoration(
                      color: const Color(0xFF0EA5E9).withValues(alpha: 0.14),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: const Color(0xFF0EA5E9).withValues(alpha: 0.4),
                        width: 1,
                      ),
                    ),
                    labelColor: const Color(0xFF0EA5E9),
                    unselectedLabelColor: const Color(0xFF64748B),
                    labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5, letterSpacing: 0.2),
                    unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12.5, letterSpacing: 0.2),
                    tabs: const [
                      Tab(
                        height: 32,
                        child: Center(child: Text('ẨM THỰC TINH TÚY')),
                      ),
                      Tab(
                        height: 32,
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
            color: Color(0xFFF0FDFA),
            borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
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
                  Expanded(
                    child: Row(
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
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${_cartItems.length} loại sản phẩm (Đồng bộ Web)',
                                style: const TextStyle(fontSize: 11, color: Colors.white70),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              Text(
                                '${_totalCartPrice.toInt()} VNĐ',
                                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: Color(0xFFFFB800)),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),

                  // Button Xem giỏ
                  Container(
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(20),
                      gradient: const LinearGradient(
                        colors: [Color(0xFFFFB800), Color(0xFFFF9900)],
                      ),
                    ),
                    child: ElevatedButton.icon(
                      onPressed: () => MyCartModal.show(context, onCartUpdated: _fetchCartData),
                      icon: const Icon(Icons.arrow_forward, size: 16),
                      label: const Text('Xem Giỏ Hàng', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 12.5)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.transparent,
                        foregroundColor: Colors.white,
                        shadowColor: Colors.transparent,
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
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
                    childAspectRatio: 0.58,
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
