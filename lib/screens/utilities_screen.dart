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

  // Synchronized Cart State
  final Map<String, Map<String, dynamic>> _cartItems = {};

  // Curated Featured Showcase Data for Dong Anh Specialties (Fallback Hydration)
  final List<Map<String, dynamic>> _defaultSpecialties = [
    {
      'id': 101,
      'name': 'Bún chả Cổ Loa Truyền Thống',
      'address': 'Cổ Loa, Huyện Đông Anh, Hà Nội',
      'rating': 4.9,
      'reviews_count': 128,
      'image': 'https://images.unsplash.com/photo-1541832676-9b763b0239ab?w=600&q=80',
      'category': 'Ẩm thực Cổ Loa',
      'price_range': '35.000đ - 60.000đ',
      'is_verified': true,
      'discount': 'Giảm 10%',
      'distance': '0.8 km',
    },
    {
      'id': 102,
      'name': 'HTX Nông Nghiệp Dược Liệu KOVI',
      'address': 'Thôn Lộc Hà, Xã Mai Lâm, Đông Anh',
      'rating': 5.0,
      'reviews_count': 96,
      'image': 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600&q=80',
      'category': 'OCOP 5 Sao',
      'price_range': '50.000đ - 250.000đ',
      'is_verified': true,
      'discount': 'OCOP Chuẩn',
      'distance': '1.5 km',
    },
    {
      'id': 103,
      'name': 'HKD Thảo Loan - Tương Nếp Cổ Loa',
      'address': 'Xã Xuân Canh, Huyện Đông Anh',
      'rating': 4.8,
      'reviews_count': 74,
      'image': 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=600&q=80',
      'category': 'Đặc sản Nông sản',
      'price_range': '45.000đ - 120.000đ',
      'is_verified': true,
      'discount': 'Freeship 2km',
      'distance': '2.1 km',
    },
    {
      'id': 104,
      'name': 'Bánh Chưng Nếp Tranh Khúc',
      'address': 'Xã Dục Tú, Huyện Đông Anh',
      'rating': 4.9,
      'reviews_count': 210,
      'image': 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&q=80',
      'category': 'Truyền thống OCOP',
      'price_range': '60.000đ - 150.000đ',
      'is_verified': true,
      'discount': 'Đặc sản Tết',
      'distance': '3.2 km',
    },
  ];

  final List<Map<String, dynamic>> _defaultOcopProducts = [
    {
      'id': 201,
      'name': 'Gạo Nếp Cái Hoa Vàng Cổ Loa (Túi 5kg)',
      'price': 180000,
      'price_formatted': '180.000đ',
      'image': 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&q=80',
      'seller_name': 'HTX Nông Nghiệp Cổ Loa',
      'ocop_star': '🏆 OCOP 5 SAO',
      'in_stock': true,
      'rating': 5.0,
      'unit': 'Túi 5kg',
    },
    {
      'id': 202,
      'name': 'Trà Hữu Cơ KOVI Đông Anh (Hộp 200g)',
      'price': 125000,
      'price_formatted': '125.000đ',
      'image': 'https://images.unsplash.com/photo-1597481499750-3e6b22637e12?w=600&q=80',
      'seller_name': 'HTX Dược Liệu KOVI',
      'ocop_star': '🏆 OCOP 4 SAO',
      'in_stock': true,
      'rating': 4.9,
      'unit': 'Hộp 200g',
    },
    {
      'id': 203,
      'name': 'Bún Tươi Khô Uy Nỗ Đóng Gói (Gói 1kg)',
      'price': 35000,
      'price_formatted': '35.000đ',
      'image': 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=600&q=80',
      'seller_name': 'Cơ sở sản xuất Uy Nỗ',
      'ocop_star': '🏆 OCOP 3 SAO',
      'in_stock': true,
      'rating': 4.8,
      'unit': 'Gói 1kg',
    },
    {
      'id': 204,
      'name': 'Tương Nếp Truyền Thống Nếp Cái (Chai 500ml)',
      'price': 45000,
      'price_formatted': '45.000đ',
      'image': 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?w=600&q=80',
      'seller_name': 'HKD Thảo Loan',
      'ocop_star': '🏆 OCOP 4 SAO',
      'in_stock': true,
      'rating': 4.9,
      'unit': 'Chai 500ml',
    },
  ];

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
          _foodEateries = (res is List && res.isNotEmpty) ? res : _defaultSpecialties;
          _isLoadingFood = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _foodEateries = _defaultSpecialties;
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
          _marketEateries = (markets is List && markets.isNotEmpty) ? markets : _defaultSpecialties;
          _marketProducts = (products is List && products.isNotEmpty) ? products : _defaultOcopProducts;
          _isLoadingMarket = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _marketEateries = _defaultSpecialties;
          _marketProducts = _defaultOcopProducts;
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
        final hasOcopBadge = catStr.toUpperCase().contains('OCOP') ||
                             item['is_ocop'] == true ||
                             item['is_ocop'] == 1 ||
                             item['is_ocop'] == '1' ||
                             item['ocop_star'] != null ||
                             (item['name'] ?? '').toString().toUpperCase().contains('OCOP') ||
                             (item['description'] ?? '').toString().toUpperCase().contains('OCOP');
        return matchesSearch && hasOcopBadge;
      }
      if (_selectedFilter == '🛵 Giao nhanh') {
        return matchesSearch && (item['has_delivery'] == true || item['has_delivery'] == 1 || item['has_delivery'] == '1');
      }
      return matchesSearch;
    }).toList();

    final displayProducts = _marketProducts.where((p) {
      final name = (p['name'] ?? '').toString().toLowerCase();
      final seller = (p['seller_name'] ?? '').toString().toLowerCase();
      final q = _searchQuery.toLowerCase();
      return name.contains(q) || seller.contains(q);
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
                    child: TabBar(
                      controller: _tabController,
                      indicatorSize: TabBarIndicatorSize.tab,
                      dividerColor: Colors.transparent,
                      indicator: BoxDecoration(
                        gradient: const LinearGradient(colors: [primaryColor, accentColor]),
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(color: primaryColor.withValues(alpha: 0.3), blurRadius: 8, offset: const Offset(0, 2)),
                        ],
                      ),
                      labelColor: Colors.white,
                      unselectedLabelColor: Colors.grey.shade700,
                      labelPadding: const EdgeInsets.symmetric(horizontal: 4),
                      tabs: const [
                        Tab(
                          child: FittedBox(
                            fit: BoxFit.scaleDown,
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.restaurant_menu_rounded, size: 15),
                                SizedBox(width: 3),
                                Text(
                                  'ẨM THỰC TINH TÚY',
                                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                                ),
                              ],
                            ),
                          ),
                        ),
                        Tab(
                          child: FittedBox(
                            fit: BoxFit.scaleDown,
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.storefront_rounded, size: 15),
                                SizedBox(width: 3),
                                Text(
                                  'CHỢ SỐ & OCOP',
                                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
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
                        _buildFilterChip('🛵 Giao nhanh', Icons.electric_scooter_rounded),
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
                  _buildFoodTabContent(displayFood, primaryColor),

                  // Tab 2: OCOP Market & Products Showcase
                  _buildMarketTabContent(displayProducts, primaryColor),
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
      return const Center(child: CircularProgressIndicator(color: Color(0xFF0EA5E9)));
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Featured Hero Banner
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF0EA5E9), Color(0xFF0284C7)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(color: const Color(0xFF0EA5E9).withValues(alpha: 0.3), blurRadius: 10, offset: const Offset(0, 4)),
            ],
          ),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: const [
                    Text('🍜 ẨM THỰC TRUYỀN THỐNG ĐÔNG ANH', style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
                    SizedBox(height: 4),
                    Text('Bún Chả Cổ Loa & Tương Nếp', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                    SizedBox(height: 4),
                    Text('Thưởng thức đặc sản đạt chuẩn vệ sinh an toàn thực phẩm', style: TextStyle(color: Colors.white70, fontSize: 11)),
                  ],
                ),
              ),
              const CircleAvatar(
                radius: 28,
                backgroundColor: Colors.white24,
                child: Icon(Icons.soup_kitchen_rounded, color: Colors.white, size: 28),
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),

        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('Danh Sách Quán Ăn Nổi Bật (${eateries.length})', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
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
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
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
        borderRadius: BorderRadius.circular(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image with Badges
            Stack(
              children: [
                ClipRRect(
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
                  child: Image.network(
                    item['image'] ?? 'https://picsum.photos/600/300',
                    height: 160,
                    width: double.infinity,
                    fit: BoxFit.cover,
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
      return const Center(child: CircularProgressIndicator(color: Color(0xFF0EA5E9)));
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
                'Danh Mục Sản Phẩm OCOP (${products.length})',
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
                  product['image'] ?? 'https://picsum.photos/300/300',
                  height: 120,
                  width: double.infinity,
                  fit: BoxFit.cover,
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
                    color: const Color(0xFF059669),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    product['ocop_star'] ?? 'OCOP CHUẨN',
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
                        product['price_formatted'] ?? '${product['price'] ?? 50000}đ',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF059669)),
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
