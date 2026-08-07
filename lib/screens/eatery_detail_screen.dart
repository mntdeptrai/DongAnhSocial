import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/api_service.dart';
import '../widgets/custom_loader.dart';

class EateryDetailScreen extends StatefulWidget {
  final String categorySlug;
  final String eaterySlug;
  final Map<String, dynamic>? initialData;

  const EateryDetailScreen({
    super.key,
    required this.categorySlug,
    required this.eaterySlug,
    this.initialData,
  });

  @override
  State<EateryDetailScreen> createState() => _EateryDetailScreenState();
}

class _EateryDetailScreenState extends State<EateryDetailScreen> {
  Map<String, dynamic>? _eatery;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    if (widget.initialData != null) {
      _eatery = widget.initialData;
    }
    _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    try {
      final detail = await ApiService.getEateryDetail(widget.categorySlug, widget.eaterySlug);
      if (detail != null) {
        _eatery = Map<String, dynamic>.from(detail);
      }
    } catch (e) {
      debugPrint('Lỗi tải chi tiết: $e');
    }

    // Bổ sung check-in photos từ feed công khai nếu chưa có
    try {
      final List photosList = (_eatery?['checkin_photos'] is List) ? _eatery!['checkin_photos'] as List : [];
      if (photosList.isEmpty) {
        final feed = await ApiService.getFeed();
        final String currentSlug = widget.eaterySlug.trim().toLowerCase();
        final String currentName = (_eatery?['name'] ?? widget.initialData?['name'] ?? '').toString().trim().toLowerCase();

        final matchedCheckins = feed.where((item) {
          final eat = item['eatery'];
          if (eat == null) return false;
          final String eSlug = eat['slug']?.toString().trim().toLowerCase() ?? '';
          final String eName = eat['name']?.toString().trim().toLowerCase() ?? '';
          return (currentSlug.isNotEmpty && eSlug == currentSlug) ||
                 (currentName.isNotEmpty && eName == currentName);
        }).toList();

        if (matchedCheckins.isNotEmpty) {
          _eatery ??= Map<String, dynamic>.from(widget.initialData ?? {});
          final List<dynamic> photos = matchedCheckins.where((c) => c['image_path'] != null && c['image_path'].toString().isNotEmpty).toList();
          _eatery!['checkin_photos'] = photos;
          _eatery!['checkin_reviews'] = matchedCheckins;
        }
      }
    } catch (e) {
      debugPrint('Lỗi tải checkin feed bổ sung: $e');
    }

    // Bổ sung OCOP Products từ Market API nếu rỗng
    try {
      final List existingOcop = (_eatery?['ocop_products'] is List && (_eatery!['ocop_products'] as List).isNotEmpty)
          ? _eatery!['ocop_products'] as List
          : ((_eatery?['ocopProducts'] is List && (_eatery!['ocopProducts'] as List).isNotEmpty)
              ? _eatery!['ocopProducts'] as List
              : []);

      if (existingOcop.isEmpty) {
        final allMarketProds = await ApiService.getMarketProducts();
        final String currentSlug = widget.eaterySlug.trim().toLowerCase();
        final String currentName = (_eatery?['name'] ?? widget.initialData?['name'] ?? '').toString().trim().toLowerCase();
        final dynamic currentId = _eatery?['id'];

        final matchedProds = allMarketProds.where((p) {
          final pEateryId = p['eatery_id'];
          final pSlug = p['eatery_slug']?.toString().trim().toLowerCase() ?? '';
          final pStall = p['stall_name']?.toString().trim().toLowerCase() ?? '';

          return (currentId != null && pEateryId == currentId) ||
                 (currentSlug.isNotEmpty && pSlug == currentSlug) ||
                 (currentName.isNotEmpty && pStall == currentName) ||
                 (currentName.isNotEmpty && currentName.contains(pStall)) ||
                 (pStall.isNotEmpty && currentName.contains(pStall));
        }).toList();

        if (matchedProds.isNotEmpty) {
          _eatery ??= Map<String, dynamic>.from(widget.initialData ?? {});
          _eatery!['ocop_products'] = matchedProds;
        }
      }
    } catch (e) {
      debugPrint('Lỗi tải OCOP products bổ sung: $e');
    }

    if (mounted) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _openGoogleMapsDirections(double lat, double lng) async {
    final Uri googleMapsUrl = Uri.parse('https://www.google.com/maps/dir/?api=1&destination=$lat,$lng');
    try {
      if (await canLaunchUrl(googleMapsUrl)) {
        await launchUrl(googleMapsUrl, mode: LaunchMode.externalApplication);
      } else {
        await launchUrl(googleMapsUrl, mode: LaunchMode.platformDefault);
      }
    } catch (e) {
      debugPrint('Lỗi mở Google Maps: $e');
    }
  }

  Widget _buildShopeeProductCard(BuildContext context, Map<String, dynamic> item, {bool isOcop = true}) {
    final String pName = item['name'] ?? item['product_name'] ?? 'Sản phẩm OCOP';
    final double pPrice = double.tryParse(item['price']?.toString() ?? '0') ?? 0;
    final String pStar = item['star_rating'] ?? (item['star'] != null ? '${item['star']} SAO' : '4 SAO');
    final String pImgRaw = item['image_path'] ?? item['image'] ?? item['cover_image_url'] ?? item['avatar'] ?? '';
    final String pImgUrl = pImgRaw.startsWith('http')
        ? pImgRaw
        : (pImgRaw.isNotEmpty
            ? 'https://donganhdiscovery.xadonganh.com/' + (pImgRaw.startsWith('/') ? pImgRaw.substring(1) : pImgRaw)
            : 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=400&q=80');

    final int salesCount = int.tryParse(item['sales_count']?.toString() ?? item['sold_count']?.toString() ?? '0') ?? 0;

    String formatVnd(num price) {
      if (price <= 0) return 'Liên hệ';
      final String str = price.toInt().toString();
      final String formatted = str.replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]}.');
      return '${formatted}đ';
    }

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
                    cacheWidth: 300,
                    filterQuality: FilterQuality.low,
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
                        formatVnd(pPrice),
                        style: const TextStyle(
                          color: Color(0xFFEE4D2D), // Shopee Price Red
                          fontWeight: FontWeight.w900,
                          fontSize: 13.5,
                        ),
                      ),
                      if (pPrice > 0) ...[
                        const SizedBox(width: 4),
                        Text(
                          formatVnd(pPrice * 1.25),
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
                      Text(
                        salesCount > 0 ? '| Đã bán $salesCount' : '| Mới',
                        style: const TextStyle(fontSize: 9.5, color: Colors.grey),
                      ),
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

  void _showImagePreviewDialog(BuildContext context, String imageUrl) {
    showDialog(
      context: context,
      builder: (context) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(10),
        child: Stack(
          alignment: Alignment.topRight,
          children: [
            InteractiveViewer(
              child: ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: Image.network(
                  imageUrl,
                  fit: BoxFit.contain,
                  cacheWidth: 800,
                  filterQuality: FilterQuality.low,
                ),
              ),
            ),
            IconButton(
              onPressed: () => Navigator.pop(context),
              icon: const Icon(Icons.cancel, color: Colors.white, size: 30),
            ),
          ],
        ),
      ),
    );
  }

  Color _getCategoryColor(String? slug) {
    switch (slug) {
      case 'hanh-trinh-di-san':
        return const Color(0xFF8B4513);
      case 'smart-education-map':
        return const Color(0xFF1A73E8);
      case 'wellness-care':
        return const Color(0xFF34A853);
      case 'stay-in-dong-anh':
        return const Color(0xFF9334E6);
      case 'dong-anh-market':
        return const Color(0xFFF29900);
      case 'dong-anh-food-map':
        return const Color(0xFFEA4335);
      case 'discover-dong-anh-community-culture-hub':
        return const Color(0xFFE81E63);
      default:
        return const Color(0xFF0EA5E9);
    }
  }

  String _getCategoryIcon(String? slug) {
    switch (slug) {
      case 'hanh-trinh-di-san':
        return '⛩️';
      case 'smart-education-map':
        return '🎓';
      case 'wellness-care':
        return '🏥';
      case 'stay-in-dong-anh':
        return '🏨';
      case 'dong-anh-market':
        return '🛍️';
      case 'dong-anh-food-map':
        return '🍜';
      case 'discover-dong-anh-community-culture-hub':
        return '🏛️';
      default:
        return '📍';
    }
  }

  @override
  Widget build(BuildContext context) {
    final eatery = _eatery;
    final catSlug = (eatery?['category'] is Map)
        ? (eatery!['category']['slug']?.toString() ?? widget.categorySlug)
        : (eatery?['category_slug']?.toString() ?? widget.categorySlug);
    final catColor = _getCategoryColor(catSlug);
    final catIcon = _getCategoryIcon(catSlug);

    final String rawPath = eatery?['image_path'] ?? eatery?['cover_image_url'] ?? eatery?['avatar'] ?? '';
    final String fullImgUrl = rawPath.startsWith('http')
        ? rawPath
        : (rawPath.isNotEmpty
            ? 'https://donganhdiscovery.xadonganh.com/' + (rawPath.startsWith('/') ? rawPath.substring(1) : rawPath)
            : 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80');

    final double? lat = double.tryParse(eatery?['latitude']?.toString() ?? '');
    final double? lng = double.tryParse(eatery?['longitude']?.toString() ?? '');

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: _isLoading && eatery == null
          ? const CustomPulseLoader(
              message: 'Đang tải thông tin địa điểm...',
              icon: Icons.store_rounded,
              primaryColor: Color(0xFF0EA5E9),
            )
          : eatery == null
              ? Scaffold(
                  appBar: AppBar(title: const Text('Chi tiết địa điểm')),
                  body: const Center(child: Text('Không tìm thấy thông tin địa điểm.')),
                )
              : CustomScrollView(
                  slivers: [
                    // Hero Cover Image AppBar
                    SliverAppBar(
                      expandedHeight: 260,
                      pinned: true,
                      backgroundColor: catColor,
                      leading: Padding(
                        padding: const EdgeInsets.all(8.0),
                        child: CircleAvatar(
                          backgroundColor: Colors.black45,
                          child: IconButton(
                            icon: const Icon(Icons.arrow_back, color: Colors.white),
                            onPressed: () => Navigator.pop(context),
                          ),
                        ),
                      ),
                      flexibleSpace: FlexibleSpaceBar(
                        background: Stack(
                          fit: StackFit.expand,
                          children: [
                            if (fullImgUrl.isNotEmpty)
                              Image.network(
                                fullImgUrl,
                                fit: BoxFit.cover,
                                cacheWidth: 600,
                                filterQuality: FilterQuality.low,
                                errorBuilder: (_, __, ___) => Container(
                                  color: const Color(0xFF1E293B),
                                  child: Center(
                                    child: Text(catIcon, style: const TextStyle(fontSize: 60)),
                                  ),
                                ),
                              )
                            else
                              Container(
                                color: catColor.withOpacity(0.85),
                                child: Center(
                                  child: Text(catIcon, style: const TextStyle(fontSize: 70)),
                                ),
                              ),
                            Container(
                              decoration: const BoxDecoration(
                                gradient: LinearGradient(
                                  begin: Alignment.topCenter,
                                  end: Alignment.bottomCenter,
                                  colors: [
                                    Colors.black38,
                                    Colors.transparent,
                                    Colors.black54,
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),

                    // Main Content Body
                    SliverToBoxAdapter(
                      child: Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Category Tag & Rating
                            Row(
                              children: [
                                Expanded(
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: catColor,
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Row(
                                      children: [
                                        Text(catIcon, style: const TextStyle(fontSize: 11)),
                                        const SizedBox(width: 4),
                                        Expanded(
                                          child: Text(
                                            (eatery['category'] is Map)
                                                ? (eatery['category']['name']?.toString() ?? 'Địa điểm')
                                                : (eatery['category'] is String ? eatery['category'] : (eatery['category_name']?.toString() ?? 'Địa điểm')),
                                            style: const TextStyle(
                                              color: Colors.white,
                                              fontWeight: FontWeight.bold,
                                              fontSize: 10,
                                            ),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 6),
                                Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(Icons.star, color: Colors.amber, size: 15),
                                    const SizedBox(width: 2),
                                    Text(
                                      '${eatery['rating_avg'] ?? '5.0'}',
                                      style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 12,
                                      ),
                                    ),
                                    const SizedBox(width: 2),
                                    Text(
                                      '(${eatery['reviews_count'] ?? '0'})',
                                      style: TextStyle(color: Colors.grey[500], fontSize: 10),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),

                            // Place Title
                            Text(
                              eatery['name'] ?? 'Địa điểm',
                              style: const TextStyle(
                                fontSize: 22,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF0F172A),
                              ),
                            ),
                            const SizedBox(height: 6),

                            // Address
                            Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Icon(Icons.location_on_outlined, size: 18, color: Colors.grey),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: Text(
                                    eatery['address'] ?? 'Đông Anh, Hà Nội',
                                    style: TextStyle(color: Colors.grey[700], fontSize: 13, height: 1.3),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 16),

                            // Action Buttons Row
                            Row(
                              children: [
                                if (lat != null && lng != null)
                                  Expanded(
                                    child: OutlinedButton.icon(
                                      onPressed: () => _openGoogleMapsDirections(lat, lng),
                                      icon: const Icon(Icons.directions, size: 18),
                                      label: const Text('Chỉ đường'),
                                      style: OutlinedButton.styleFrom(
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                        side: BorderSide(color: catColor),
                                        foregroundColor: catColor,
                                        shape: RoundedRectangleBorder(
                                          borderRadius: BorderRadius.circular(10),
                                        ),
                                      ),
                                    ),
                                  ),
                                if (lat != null && lng != null) const SizedBox(width: 10),
                                Expanded(
                                  child: ElevatedButton.icon(
                                    onPressed: () {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(
                                          content: Text('Vui lòng quay lại bản đồ hoặc feed để check-in.'),
                                        ),
                                      );
                                    },
                                    icon: const Icon(Icons.camera_alt, size: 18),
                                    label: const Text('Check-in'),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: const Color(0xFF0EA5E9),
                                      foregroundColor: Colors.white,
                                      padding: const EdgeInsets.symmetric(vertical: 12),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),

                            const Divider(height: 32),

                            // Description Section
                            if (eatery['description'] != null &&
                                eatery['description'].toString().trim().isNotEmpty) ...[
                              const Text(
                                'Giới thiệu',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF0F172A),
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                eatery['description'].toString().replaceAll(RegExp(r'<[^>]*>'), ''),
                                style: TextStyle(
                                  fontSize: 14,
                                  height: 1.5,
                                  color: Colors.grey[800],
                                ),
                              ),
                              const SizedBox(height: 24),
                            ],

                            // Menu / OCOP Products / Dishes / Offerings List Section
                            Builder(
                              builder: (context) {
                                final List ocopList = (eatery['ocop_products'] is List && (eatery['ocop_products'] as List).isNotEmpty)
                                    ? eatery['ocop_products'] as List
                                    : ((eatery['ocopProducts'] is List && (eatery['ocopProducts'] as List).isNotEmpty)
                                        ? eatery['ocopProducts'] as List
                                        : []);

                                final List dishList = (eatery['dishes'] is List) ? eatery['dishes'] as List : [];

                                if (ocopList.isEmpty && dishList.isEmpty) return const SizedBox.shrink();

                                return Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    if (ocopList.isNotEmpty) ...[
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          const Text(
                                            '🛒 Sản phẩm OCOP & Đặc sản',
                                            style: TextStyle(
                                              fontSize: 16,
                                              fontWeight: FontWeight.w900,
                                              color: Color(0xFF0F172A),
                                            ),
                                          ),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                            decoration: BoxDecoration(
                                              color: const Color(0xFFFEF3C7),
                                              borderRadius: BorderRadius.circular(12),
                                              border: Border.all(color: const Color(0xFFF59E0B)),
                                            ),
                                            child: Text(
                                              '${ocopList.length} sản phẩm',
                                              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFFD97706)),
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 12),
                                      GridView.builder(
                                        shrinkWrap: true,
                                        physics: const NeverScrollableScrollPhysics(),
                                        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                          crossAxisCount: 2,
                                          crossAxisSpacing: 10,
                                          mainAxisSpacing: 10,
                                          childAspectRatio: 0.58,
                                        ),
                                        itemCount: ocopList.length,
                                        itemBuilder: (context, idx) {
                                          final item = ocopList[idx];
                                          return _buildShopeeProductCard(context, item, isOcop: true);
                                        },
                                      ),
                                      const SizedBox(height: 24),
                                    ],

                                    if (dishList.isNotEmpty) ...[
                                      const Text(
                                        'Thực đơn & Món đặc sắc',
                                        style: TextStyle(
                                          fontSize: 16,
                                          fontWeight: FontWeight.bold,
                                          color: Color(0xFF0F172A),
                                        ),
                                      ),
                                      const SizedBox(height: 12),
                                      GridView.builder(
                                        shrinkWrap: true,
                                        physics: const NeverScrollableScrollPhysics(),
                                        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                          crossAxisCount: 2,
                                          crossAxisSpacing: 10,
                                          mainAxisSpacing: 10,
                                          childAspectRatio: 0.58,
                                        ),
                                        itemCount: dishList.length,
                                        itemBuilder: (context, idx) {
                                          final dish = dishList[idx];
                                          return _buildShopeeProductCard(context, dish, isOcop: false);
                                        },
                                      ),
                                      const SizedBox(height: 24),
                                    ],
                                  ],
                                );
                              },
                            ),

                            // Check-in Photos Gallery Section
                            Builder(
                              builder: (context) {
                                final List<dynamic> photos = [];
                                if (eatery['checkin_photos'] is List) {
                                  photos.addAll(eatery['checkin_photos']);
                                }
                                if (eatery['checkin_reviews'] is List) {
                                  for (var cr in eatery['checkin_reviews']) {
                                    if (cr['image_path'] != null &&
                                        cr['image_path'].toString().isNotEmpty &&
                                        !photos.any((p) => p['id'] == cr['id'])) {
                                      photos.add(cr);
                                    }
                                  }
                                }

                                if (photos.isEmpty) return const SizedBox.shrink();

                                return Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        const Icon(Icons.photo_library, size: 18, color: Color(0xFF0EA5E9)),
                                        const SizedBox(width: 6),
                                        Text(
                                          'Hình ảnh thực tế từ thực khách (${photos.length})',
                                          style: const TextStyle(
                                            fontSize: 16,
                                            fontWeight: FontWeight.bold,
                                            color: Color(0xFF0F172A),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 12),
                                    SizedBox(
                                      height: 110,
                                      child: ListView.separated(
                                        scrollDirection: Axis.horizontal,
                                        itemCount: photos.length,
                                        separatorBuilder: (_, __) => const SizedBox(width: 10),
                                        itemBuilder: (context, pIdx) {
                                          final item = photos[pIdx];
                                          final String path = item['image_path'] ?? '';
                                          String imgUrl = path;
                                          if (path.isNotEmpty && !path.startsWith('http')) {
                                            imgUrl = path.startsWith('/')
                                                ? 'https://donganhdiscovery.xadonganh.com$path'
                                                : 'https://donganhdiscovery.xadonganh.com/$path';
                                          }
                                          if (imgUrl.isEmpty) return const SizedBox.shrink();

                                          return GestureDetector(
                                            onTap: () => _showImagePreviewDialog(context, imgUrl),
                                            child: ClipRRect(
                                              borderRadius: BorderRadius.circular(12),
                                              child: Stack(
                                                children: [
                                                  Image.network(
                                                    imgUrl,
                                                    width: 110,
                                                    height: 110,
                                                    fit: BoxFit.cover,
                                                    cacheWidth: 220,
                                                    filterQuality: FilterQuality.low,
                                                    errorBuilder: (_, __, ___) => Container(
                                                      width: 110,
                                                      height: 110,
                                                      color: Colors.grey[200],
                                                      child: const Icon(Icons.broken_image, color: Colors.grey),
                                                    ),
                                                  ),
                                                  Positioned(
                                                    bottom: 4,
                                                    left: 4,
                                                    right: 4,
                                                    child: Container(
                                                      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                                                      decoration: BoxDecoration(
                                                        color: Colors.black.withOpacity(0.5),
                                                        borderRadius: BorderRadius.circular(4),
                                                      ),
                                                      child: Text(
                                                        item['user']?['name'] ?? item['guest_name'] ?? 'Check-in',
                                                        style: const TextStyle(
                                                          color: Colors.white,
                                                          fontSize: 9,
                                                          fontWeight: FontWeight.bold,
                                                        ),
                                                        maxLines: 1,
                                                        overflow: TextOverflow.ellipsis,
                                                      ),
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ),
                                          );
                                        },
                                      ),
                                    ),
                                    const SizedBox(height: 24),
                                  ],
                                );
                              },
                            ),

                            // Reviews List Section (Combined Reviews & Check-ins)
                            Builder(
                              builder: (context) {
                                final List<dynamic> combinedReviews = [];
                                if (eatery['reviews'] is List) {
                                  combinedReviews.addAll(eatery['reviews']);
                                }
                                if (eatery['checkin_reviews'] is List) {
                                  for (var cr in eatery['checkin_reviews']) {
                                    combinedReviews.add(cr);
                                  }
                                }

                                if (combinedReviews.isEmpty) return const SizedBox.shrink();

                                return Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Đánh giá từ du khách (${combinedReviews.length})',
                                      style: const TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFF0F172A),
                                      ),
                                    ),
                                    const SizedBox(height: 12),
                                    ListView.separated(
                                      shrinkWrap: true,
                                      physics: const NeverScrollableScrollPhysics(),
                                      itemCount: combinedReviews.length,
                                      separatorBuilder: (_, __) => const SizedBox(height: 10),
                                      itemBuilder: (context, idx) {
                                        final rev = combinedReviews[idx];
                                        final String imgPath = rev['image_path'] ?? '';
                                        final String revImgUrl = imgPath.startsWith('http')
                                            ? imgPath
                                            : (imgPath.isNotEmpty
                                                ? 'https://donganhdiscovery.xadonganh.com/' + imgPath
                                                : '');

                                        return Container(
                                          padding: const EdgeInsets.all(12),
                                          decoration: BoxDecoration(
                                            color: Colors.white,
                                            borderRadius: BorderRadius.circular(12),
                                            border: Border.all(color: Colors.grey[200]!),
                                          ),
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Row(
                                                children: [
                                                  const CircleAvatar(
                                                    radius: 16,
                                                    backgroundColor: Color(0xFF0EA5E9),
                                                    child: Icon(Icons.person, size: 18, color: Colors.white),
                                                  ),
                                                  const SizedBox(width: 8),
                                                  Expanded(
                                                    child: Column(
                                                      crossAxisAlignment: CrossAxisAlignment.start,
                                                      children: [
                                                        Text(
                                                          rev['user']?['name'] ?? rev['guest_name'] ?? 'Khách du lịch',
                                                          style: const TextStyle(
                                                            fontWeight: FontWeight.bold,
                                                            fontSize: 13,
                                                          ),
                                                        ),
                                                        if (rev['image_path'] != null)
                                                          const Text(
                                                            '📍 Đã check-in tại quán',
                                                            style: TextStyle(
                                                              color: Color(0xFF0EA5E9),
                                                              fontSize: 10,
                                                              fontWeight: FontWeight.w500,
                                                            ),
                                                          ),
                                                      ],
                                                    ),
                                                  ),
                                                  Row(
                                                    children: List.generate(5, (sIdx) {
                                                      final rating = rev['rating'] ?? 5;
                                                      return Icon(
                                                        sIdx < rating ? Icons.star : Icons.star_border,
                                                        color: Colors.amber,
                                                        size: 14,
                                                      );
                                                    }),
                                                  ),
                                                ],
                                              ),
                                              if (rev['comment'] != null && rev['comment'].toString().trim().isNotEmpty) ...[
                                                const SizedBox(height: 6),
                                                Text(
                                                  rev['comment'],
                                                  style: TextStyle(color: Colors.grey[800], fontSize: 13),
                                                ),
                                              ],
                                              if (revImgUrl.isNotEmpty) ...[
                                                const SizedBox(height: 8),
                                                GestureDetector(
                                                  onTap: () => _showImagePreviewDialog(context, revImgUrl),
                                                  child: ClipRRect(
                                                    borderRadius: BorderRadius.circular(8),
                                                    child: Image.network(
                                                      revImgUrl,
                                                      height: 140,
                                                      width: double.infinity,
                                                      fit: BoxFit.cover,
                                                      cacheWidth: 400,
                                                      filterQuality: FilterQuality.low,
                                                      errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                                                    ),
                                                  ),
                                                ),
                                              ],
                                            ],
                                          ),
                                        );
                                      },
                                    ),
                                    const SizedBox(height: 24),
                                  ],
                                );
                              },
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
