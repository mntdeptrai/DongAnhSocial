import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/custom_loader.dart';
import '../widgets/squircle_helper.dart';
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

  // Synchronized Cart State
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
      if (res is Map && res['success'] == true && res['data'] is List) {
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
      final res = await ApiService.getAllEateries();
      if (mounted) {
        setState(() {
          _foodEateries = (res is List) ? List<dynamic>.from(res) : [];
          _isLoadingFood = false;
        });
      }
    } catch (e) {
      debugPrint('_fetchFoodData API error: $e');
      if (mounted) {
        setState(() {
          _foodEateries = [];
          _isLoadingFood = false;
        });
      }
    }
  }

  Future<void> _fetchMarketData() async {
    setState(() => _isLoadingMarket = true);
    try {
      final markets = await ApiService.getEateries('dong-anh-market');
      final products = await ApiService.getMarketProducts();

      if (mounted) {
        setState(() {
          _marketEateries = (markets is List) ? List<dynamic>.from(markets) : [];
          _marketProducts = (products is List) ? List<dynamic>.from(products) : [];
          _isLoadingMarket = false;
        });
      }
    } catch (e) {
      debugPrint('_fetchMarketData API error: $e');
      if (mounted) {
        setState(() {
          _marketEateries = [];
          _marketProducts = [];
          _isLoadingMarket = false;
        });
      }
    }
  }

  void _addToCart(Map<String, dynamic> product) async {
    final int productId = product['id'] is int ? product['id'] : (int.tryParse(product['id']?.toString() ?? '0') ?? 0);
    final bool isOcop = product['ocop_star'] != null || product['seller_name'] != null;

    // 1. Gọi API Backend lưu vào CSDL để đồng bộ với Web
    try {
      if (productId > 0) {
        if (isOcop) {
          await ApiService.addToCart(ocopProductId: productId, quantity: 1);
        } else {
          await ApiService.addToCart(dishId: productId, quantity: 1);
        }
      }
    } catch (e) {
      debugPrint('Cart API sync error: $e');
    }

    // 2. Lấy lại dữ liệu giỏ hàng mới nhất từ Server
    await _fetchCartData();

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.check_circle, color: Colors.white),
              const SizedBox(width: 8),
              Expanded(child: Text('Đã thêm "${product['name']}" vào giỏ hàng!')),
            ],
          ),
          backgroundColor: const Color(0xFF0EA5E9),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          duration: const Duration(seconds: 2),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    const primaryColor = Color(0xFF0EA5E9);
    const accentColor = Color(0xFF06B6D4);

    final displayFood = _foodEateries.where((item) {
      final name = (item['name'] ?? '').toString().toLowerCase();
      
      String catStr = '';
      if (item['category'] is Map) {
        catStr = (item['category']['name'] ?? item['category']['slug'] ?? '').toString();
      } else {
        catStr = (item['category'] ?? '').toString();
      }
      final cat = catStr.toLowerCase();
      final q = _searchQuery.toLowerCase();
      final matchesSearch = name.contains(q) || cat.contains(q);

      if (_selectedFilter == '⭐ Nổi bật') {
        final r = item['rating'];
        final ratingVal = r is num ? r.toDouble() : (double.tryParse(r?.toString() ?? '') ?? 0.0);
        return matchesSearch && ratingVal >= 4.5;
      }
      if (_selectedFilter == '🏆 OCOP') {
        final isOcopSubject = item['is_ocop'] == true ||
                             item['is_ocop'] == 1 ||
                             item['is_ocop'] == '1' ||
                             item['ocop_star'] != null ||
                             catStr.contains('dong-anh-market') ||
                             catStr.toUpperCase().contains('OCOP') ||
                             (item['name'] ?? '').toString().toUpperCase().contains('OCOP') ||
                             (item['description'] ?? '').toString().toUpperCase().contains('OCOP');
        return matchesSearch && isOcopSubject;
      }
      return matchesSearch;
    }).toList();

    final displayProducts = _marketProducts.where((p) {
      final name = (p['name'] ?? '').toString().toLowerCase();
      final seller = (p['seller_name'] ?? '').toString().toLowerCase();
      final desc = (p['description'] ?? '').toString().toLowerCase();
      final q = _searchQuery.toLowerCase();
      final matchesSearch = name.contains(q) || seller.contains(q) || desc.contains(q);

      final isOcopProduct = p['is_ocop'] == true ||
          p['is_ocop'] == 1 ||
          p['is_ocop'] == '1' ||
          (p['star_rating'] != null && p['star_rating'].toString().isNotEmpty) ||
          (p['ocop_star'] != null && p['ocop_star'].toString().isNotEmpty) ||
          name.contains('ocop') || seller.contains('ocop') || desc.contains('ocop') ||
          seller.contains('hợp tác xã') || seller.contains('htx') ||
          seller.contains('hộ kinh doanh') || seller.contains('công ty') || seller.contains('tnhh') || seller.contains('doanh nghiệp') ||
          desc.contains('chủ thể') || desc.contains('qđ số');

      if (_selectedFilter == '🏆 OCOP') {
        return matchesSearch && isOcopProduct;
      }
      if (_selectedFilter == '⭐ Nổi bật') {
        final star = (p['star_rating'] ?? '').toString();
        return matchesSearch && (star.contains('4') || star.contains('5') || p['is_featured'] == true || isOcopProduct);
      }
      return matchesSearch;
    }).toList();

    return Scaffold(
      backgroundColor: const Color(0xFFF0FDFA),
      body: SafeArea(
        child: Column(
          children: [
            // Top Modern Search & Tab Bar Navigation
            Container(
              padding: const EdgeInsets.fromLTRB(14, 10, 14, 10),
              decoration: BoxDecoration(
                color: Colors.white,
                boxShadow: [
                  BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 10, offset: const Offset(0, 4)),
                ],
              ),
              child: Column(
                children: [
                  // Tab Buttons (Ẩm thực Tinh túy vs Chợ số & OCOP)
                  Container(
                    height: 46,
                    padding: const EdgeInsets.all(3),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(24),
                    ),
                    child: LayoutBuilder(
                      builder: (context, constraints) {
                        final tabWidth = (constraints.maxWidth - 6) / 2;
                        return AnimatedBuilder(
                          animation: _tabController.animation!,
                          builder: (context, child) {
                            final animValue = (_tabController.animation?.value ?? _tabController.index.toDouble()).clamp(0.0, 1.0);
                            final leftPos = animValue * tabWidth;
                            final activeTab1Color = Color.lerp(Colors.white, Colors.grey.shade700, animValue)!;
                            final activeTab2Color = Color.lerp(Colors.grey.shade700, Colors.white, animValue)!;

                            return Stack(
                              children: [
                                Positioned(
                                  left: leftPos,
                                  top: 0,
                                  bottom: 0,
                                  width: tabWidth,
                                  child: Container(
                                    decoration: BoxDecoration(
                                      gradient: const LinearGradient(colors: [primaryColor, accentColor]),
                                      borderRadius: BorderRadius.circular(20),
                                      boxShadow: [
                                        BoxShadow(color: primaryColor.withValues(alpha: 0.3), blurRadius: 8, offset: const Offset(0, 2)),
                                      ],
                                    ),
                                  ),
                                ),
                                Row(
                                  children: [
                                    Expanded(
                                      child: GestureDetector(
                                        onTap: () => _tabController.animateTo(0),
                                        behavior: HitTestBehavior.opaque,
                                        child: Center(
                                          child: Row(
                                            mainAxisAlignment: MainAxisAlignment.center,
                                            children: [
                                              Icon(
                                                Icons.restaurant_menu_rounded,
                                                size: 15,
                                                color: activeTab1Color,
                                              ),
                                              const SizedBox(width: 4),
                                              Text(
                                                'ẨM THỰC TINH TÚY',
                                                style: TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 12,
                                                  color: activeTab1Color,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ),
                                    Expanded(
                                      child: GestureDetector(
                                        onTap: () => _tabController.animateTo(1),
                                        behavior: HitTestBehavior.opaque,
                                        child: Center(
                                          child: Row(
                                            mainAxisAlignment: MainAxisAlignment.center,
                                            children: [
                                              Icon(
                                                Icons.storefront_rounded,
                                                size: 15,
                                                color: activeTab2Color,
                                              ),
                                              const SizedBox(width: 4),
                                              Text(
                                                'CHỢ SỐ & OCOP',
                                                style: TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 12,
                                                  color: activeTab2Color,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            );
                          },
                        );
                      },
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Search Bar Input
                  Container(
                    height: 46,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.grey.shade200),
                    ),
                    child: TextField(
                      onChanged: (v) => setState(() => _searchQuery = v),
                      decoration: InputDecoration(
                        hintText: 'Tìm đặc sản, quán ăn Cổ Loa, sản phẩm OCOP...',
                        hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                        prefixIcon: const Icon(Icons.search_rounded, color: primaryColor, size: 22),
                        suffixIcon: _searchQuery.isNotEmpty
                            ? IconButton(
                                icon: const Icon(Icons.clear, size: 18),
                                onPressed: () => setState(() => _searchQuery = ''),
                              )
                            : null,
                        border: InputBorder.none,
                        contentPadding: const EdgeInsets.symmetric(vertical: 12),
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),

                  // Quick Filter Chips Bar
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: [
                        _buildFilterChip('Tất cả', Icons.apps_rounded),
                        _buildFilterChip('⭐ Nổi bật', Icons.star_rounded),
                        _buildFilterChip('🏆 OCOP', Icons.workspace_premium_rounded),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // Tab View Body Content
            Expanded(
              child: TabBarView(
                controller: _tabController,
                children: [
                  // Tab 1: Food & Specialties Showcase
                  _KeepAliveTabContent(child: _buildFoodTabContent(displayFood, primaryColor)),

                  // Tab 2: OCOP Market & Products Showcase
                  _KeepAliveTabContent(child: _buildMarketTabContent(displayProducts, primaryColor)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChip(String label, IconData icon) {
    final isSelected = _selectedFilter == label;
    const primaryColor = Color(0xFF0EA5E9);

    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ChoiceChip(
        avatar: Icon(icon, size: 14, color: isSelected ? Colors.white : primaryColor),
        label: Text(label),
        selected: isSelected,
        onSelected: (val) {
          if (val) setState(() => _selectedFilter = label);
        },
        selectedColor: primaryColor,
        backgroundColor: Colors.white,
        side: BorderSide(color: isSelected ? primaryColor : Colors.grey.shade300),
        labelStyle: TextStyle(
          color: isSelected ? Colors.white : Colors.grey.shade800,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
          fontSize: 12,
        ),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );
  }

  Widget _buildFoodTabContent(List<dynamic> eateries, Color primaryColor) {
    if (_isLoadingFood) {
      return const CustomPulseLoader(
        message: 'Đang tải danh sách Ẩm thực Cổ Loa...',
        icon: Icons.restaurant_menu_rounded,
        primaryColor: Color(0xFF0EA5E9),
      );
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Bento Grid Asymmetric Layout Header
        Column(
          children: [
            Row(
              children: [
                Expanded(
                  flex: 3,
                  child: Container(
                    height: 140,
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF059669), Color(0xFF10B981)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(22),
                      boxShadow: [
                        BoxShadow(color: const Color(0xFF059669).withValues(alpha: 0.3), blurRadius: 10, offset: const Offset(0, 4)),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: const [
                            Text('🛍️ OCOP ĐÔNG ANH', style: TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.bold)),
                            CircleAvatar(radius: 12, backgroundColor: Colors.white24, child: Icon(Icons.star_rounded, size: 14, color: Colors.amber)),
                          ],
                        ),
                        const Text('Chợ Số & Nông Sản 4-5★', style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w900)),
                        const Text('Tương nếp, bánh chưng nương', style: TextStyle(color: Colors.white70, fontSize: 10)),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  flex: 2,
                  child: Container(
                    height: 140,
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFFEA580C), Color(0xFFF59E0B)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(22),
                      boxShadow: [
                        BoxShadow(color: const Color(0xFFEA580C).withValues(alpha: 0.3), blurRadius: 10, offset: const Offset(0, 4)),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: const [
                        Icon(Icons.soup_kitchen_rounded, color: Colors.white, size: 24),
                        Text('Bún Chả Cổ Loa', style: TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold)),
                        Text('Chuẩn ATTP', style: TextStyle(color: Colors.white70, fontSize: 10)),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(
                  flex: 2,
                  child: Container(
                    height: 100,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF0284C7), Color(0xFF06B6D4)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: const [
                        Icon(Icons.school_rounded, color: Colors.white, size: 20),
                        Text('Bản Đồ Giáo Dục', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  flex: 3,
                  child: Container(
                    height: 100,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF7C3AED), Color(0xFFA855F7)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: const [
                        Icon(Icons.account_balance_rounded, color: Colors.white, size: 20),
                        Text('Di Sản & Cổ Loa Hub', style: TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold)),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
        const SizedBox(height: 20),

        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Expanded(
              child: Text(
                'Danh Sách Quán Ăn Nổi Bật (${eateries.length})',
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
              ),
            ),
            const SizedBox(width: 8),
            const Text('Đông Anh, Hà Nội', style: TextStyle(fontSize: 12, color: Color(0xFF0EA5E9), fontWeight: FontWeight.w600)),
          ],
        ),
        const SizedBox(height: 12),

        ...eateries.map((item) => _buildEateryCard(item)).toList(),
      ],
    );
  }

  Widget _buildEateryCard(Map<String, dynamic> item) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      shape: SquircleHelper.shape(radius: 20),
      elevation: 2,
      child: InkWell(
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => EateryDetailScreen(
                categorySlug: (item['category'] is Map)
                    ? (item['category']['slug']?.toString() ?? 'dong-anh-food-map')
                    : (item['category_slug']?.toString() ?? 'dong-anh-food-map'),
                eaterySlug: item['slug'] ?? 'eatery-${item['id']}',
                initialData: item,
              ),
            ),
          );
        },
        borderRadius: SquircleHelper.radius(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image with Badges
            Stack(
              children: [
                ClipRRect(
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
                  child: Image.network(
                    (item['image_path'] != null && item['image_path'].toString().isNotEmpty)
                        ? item['image_path']
                        : ((item['image'] != null && item['image'].toString().isNotEmpty)
                            ? item['image']
                            : 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=400&q=60'),
                    height: 160,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    cacheWidth: 400,
                    filterQuality: FilterQuality.low,
                    errorBuilder: (_, __, ___) => Container(
                      height: 160,
                      color: const Color(0xFFE0F2FE),
                      child: const Center(child: Icon(Icons.restaurant_rounded, size: 48, color: Color(0xFF0EA5E9))),
                    ),
                  ),
                ),
                Positioned(
                  top: 12,
                  right: 12,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.black.withValues(alpha: 0.7),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.star_rounded, color: Colors.amber, size: 14),
                        const SizedBox(width: 4),
                        Text(
                          '${item['rating'] ?? 5.0}',
                          style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ),
                ),
                if (item['discount'] != null)
                  Positioned(
                    top: 12,
                    left: 12,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEF4444),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        item['discount'],
                        style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ),
              ],
            ),

            // Info Body
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE0F2FE),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          item['category'] is Map
                              ? (item['category']['name'] ?? 'Ẩm thực')
                              : (item['category']?.toString() ?? 'Ẩm thực'),
                          style: const TextStyle(color: Color(0xFF0EA5E9), fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                      ),
                      const SizedBox(width: 8),
                      if (item['is_verified'] == true)
                        const Row(
                          children: [
                            Icon(Icons.verified_rounded, color: Color(0xFF10B981), size: 14),
                            SizedBox(width: 2),
                            Text('Đã kiểm tra ATTP', style: TextStyle(color: Color(0xFF10B981), fontSize: 11, fontWeight: FontWeight.bold)),
                          ],
                        ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    item['name'] ?? '',
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(Icons.location_on_rounded, size: 14, color: Colors.grey),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          item['address'] ?? 'Đông Anh, Hà Nội',
                          style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const Divider(height: 20),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        item['price_range'] ?? 'Liên hệ niêm yết giá',
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0EA5E9)),
                      ),
                      ElevatedButton.icon(
                        onPressed: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => EateryDetailScreen(
                                categorySlug: item['category_slug'] ?? 'dong-anh-food-map',
                                eaterySlug: item['slug'] ?? 'eatery-${item['id']}',
                                initialData: item,
                              ),
                            ),
                          );
                        },
                        icon: const Icon(Icons.arrow_forward, size: 14),
                        label: const Text('Xem Thực Đơn'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0EA5E9),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
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
  }

  Widget _buildMarketTabContent(List<dynamic> products, Color primaryColor) {
    if (_isLoadingMarket) {
      return const CustomPulseLoader(
        message: 'Đang kết nối Chợ Số OCOP 4.0...',
        icon: Icons.storefront_rounded,
        primaryColor: Color(0xFF059669),
      );
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // OCOP Header Banner
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF059669), Color(0xFF10B981)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(color: const Color(0xFF059669).withValues(alpha: 0.3), blurRadius: 10, offset: const Offset(0, 4)),
            ],
          ),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: const [
                    Text('🏆 GIỜ NÔNG SẢN & OCOP ĐÔNG ANH', style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
                    SizedBox(height: 4),
                    Text('Sản Phẩm Đạt Chuẩn OCOP 4-5 Sao', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                    SizedBox(height: 4),
                    Text('Trực tiếp từ hợp tác xã và hộ kinh doanh chính gốc', style: TextStyle(color: Colors.white70, fontSize: 11)),
                  ],
                ),
              ),
              const CircleAvatar(
                radius: 28,
                backgroundColor: Colors.white24,
                child: Icon(Icons.workspace_premium_rounded, color: Colors.white, size: 28),
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),

        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Expanded(
              child: Text(
                'Danh Mục Sản Phẩm ${_selectedFilter == '🏆 OCOP' ? 'OCOP Chuẩn' : (_selectedFilter == '⭐ Nổi bật' ? 'Nổi Bật' : 'Chợ Số & OCOP')} (${products.length})',
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
              ),
            ),
            const SizedBox(width: 8),
            const Text('Cam Kết Chính Hãng', style: TextStyle(fontSize: 12, color: Color(0xFF059669), fontWeight: FontWeight.w600)),
          ],
        ),
        const SizedBox(height: 12),

        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            childAspectRatio: 0.68,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12,
          ),
          itemCount: products.length,
          itemBuilder: (context, index) {
            final p = products[index];
            return _buildProductGridCard(p);
          },
        ),
      ],
    );
  }

  Widget _buildProductGridCard(Map<String, dynamic> product) {
    // Resolve Image URL from image_path or image
    String imgUrl = '';
    final rawImg = product['image_path'] ?? product['image'] ?? product['img_url'];
    if (rawImg != null && rawImg.toString().trim().isNotEmpty) {
      final s = rawImg.toString().trim();
      if (s.startsWith('http')) {
        imgUrl = s;
      } else {
        imgUrl = 'https://donganhdiscovery.xadonganh.com/' + (s.startsWith('/') ? s.substring(1) : s);
      }
    } else {
      // Clean agricultural product fallback photo
      imgUrl = 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=400&q=60';
    }

    // Format Price
    String priceText = 'Liên hệ';
    if (product['price_formatted'] != null && product['price_formatted'].toString().isNotEmpty) {
      priceText = product['price_formatted'].toString();
    } else if (product['price'] != null) {
      final pNum = double.tryParse(product['price'].toString());
      if (pNum != null && pNum > 0) {
        priceText = '${pNum.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]}.')}đ';
      }
    }

    // Determine if it is a certified OCOP product vs standard market vendor
    final String sellerUpper = (product['seller_name'] ?? '').toString().toUpperCase();
    final String nameUpper = (product['name'] ?? '').toString().toUpperCase();
    final String descUpper = (product['description'] ?? '').toString().toUpperCase();

    final bool isOcop = (product['is_ocop'] == true || product['is_ocop'] == 1 || product['is_ocop'] == '1') ||
        (product['star_rating'] != null && product['star_rating'].toString().isNotEmpty) ||
        (product['ocop_star'] != null && product['ocop_star'].toString().isNotEmpty) ||
        nameUpper.contains('OCOP') || sellerUpper.contains('OCOP') || descUpper.contains('OCOP') ||
        sellerUpper.contains('HTX') || sellerUpper.contains('HỢP TÁC XÃ') ||
        sellerUpper.contains('HỘ KINH DOANH') || sellerUpper.contains('CÔNG TY') || sellerUpper.contains('TNHH') || sellerUpper.contains('DOANH NGHIỆP') ||
        descUpper.contains('CHỦ THỂ') || descUpper.contains('QĐ SỐ');

    final String badgeText = isOcop
        ? (product['ocop_star'] ?? product['star_rating'] ?? 'OCOP CHUẨN')
        : 'CHỢ SỐ DÂN SINH';
    final Color badgeBg = isOcop ? const Color(0xFF059669) : const Color(0xFF0EA5E9);

    return Card(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      elevation: 2,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Product Image & OCOP Star Tag
          Stack(
            children: [
              ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                child: Image.network(
                  imgUrl,
                  height: 120,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  cacheWidth: 300,
                  filterQuality: FilterQuality.low,
                  errorBuilder: (_, __, ___) => Container(
                    height: 120,
                    color: const Color(0xFFECFDF5),
                    child: const Center(child: Icon(Icons.inventory_2_rounded, size: 40, color: Color(0xFF059669))),
                  ),
                ),
              ),
              Positioned(
                top: 8,
                left: 8,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: badgeBg,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    badgeText,
                    style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ],
          ),

          // Product Title & Details
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product['name'] ?? 'Sản phẩm OCOP',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    product['seller_name'] ?? 'Gian hàng OCOP Đông Anh',
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 11),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const Spacer(),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        priceText,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF059669)),
                      ),
                      InkWell(
                        onTap: () => _addToCart(product),
                        borderRadius: BorderRadius.circular(10),
                        child: Container(
                          padding: const EdgeInsets.all(6),
                          decoration: BoxDecoration(
                            color: const Color(0xFF059669),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.add_shopping_cart_rounded, color: Colors.white, size: 16),
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
    );
  }
}

class _KeepAliveTabContent extends StatefulWidget {
  final Widget child;
  const _KeepAliveTabContent({required this.child});

  @override
  State<_KeepAliveTabContent> createState() => _KeepAliveTabContentState();
}

class _KeepAliveTabContentState extends State<_KeepAliveTabContent> with AutomaticKeepAliveClientMixin {
  @override
  bool get wantKeepAlive => true;

  @override
  Widget build(BuildContext context) {
    super.build(context);
    return widget.child;
  }
}
