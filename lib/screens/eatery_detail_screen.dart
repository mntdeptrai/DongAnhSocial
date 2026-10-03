import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
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
      _eatery = Map<String, dynamic>.from(widget.initialData!);
    }
    _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    try {
      final effectiveCatSlug = widget.categorySlug.isNotEmpty
          ? widget.categorySlug
          : (_eatery?['category']?['slug']?.toString() ??
              _eatery?['category_slug']?.toString() ??
              'dong-anh-food-map');
      final effectiveEaterySlug = widget.eaterySlug.isNotEmpty
          ? widget.eaterySlug
          : (_eatery?['slug']?.toString() ?? '');

      if (effectiveEaterySlug.isNotEmpty) {
        final detail = await ApiService.getEateryDetail(effectiveCatSlug, effectiveEaterySlug);
        if (detail != null) {
          _eatery = {
            ...?widget.initialData,
            ...detail,
          };
        }
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
            ? 'https://donganhdiscovery.xadonganh.com/${pImgRaw.startsWith('/') ? pImgRaw.substring(1) : pImgRaw}'
            : 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=400&q=80');

    final int salesCount = int.tryParse(item['sales_count']?.toString() ?? item['sold_count']?.toString() ?? '0') ?? 0;

    String formatVnd(num price) {
      if (price <= 0) return 'Liên hệ';
      final String str = price.toInt().toString();
      final String formatted = str.replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]}.');
      return '$formattedđ';
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
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
      case 'co-so-kinh-doanh':
        return const Color(0xFF0D9488);
      case 'traditional-market':
        return const Color(0xFFD97706);
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
      case 'co-so-kinh-doanh':
        return '🏪';
      case 'traditional-market':
        return '🏮';
      default:
        return '📍';
    }
  }

  Future<void> _makePhoneCall(String phoneNumber) async {
    final cleanPhone = phoneNumber.replaceAll(RegExp(r'[^0-9+]'), '');
    final Uri launchUri = Uri(
      scheme: 'tel',
      path: cleanPhone,
    );
    try {
      if (await canLaunchUrl(launchUri)) {
        await launchUrl(launchUri);
      }
    } catch (e) {
      debugPrint('Lỗi gọi điện: $e');
    }
  }

  void _openCheckinModal(BuildContext context, dynamic eatery) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => _DetailCheckinModal(
        eatery: eatery,
        onSubmit: (rating, comment, guestName, imagePath) async {
          final res = await ApiService.storeCheckin(
            eateryId: eatery['id'] ?? 0,
            rating: rating,
            comment: comment,
            guestName: guestName,
            imagePath: imagePath,
          );
          if (mounted && context.mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(res['message'] ?? 'Thành công'),
                backgroundColor: res['success'] == true ? Colors.green : Colors.red,
              ),
            );
            _fetchDetail();
          }
        },
      ),
    );
  }

  Widget _buildOperatingInfoCard(Map<String, dynamic> eatery, Color catColor, String catSlug) {
    final phone = eatery['phone']?.toString().trim() ?? '';
    final rawHours = eatery['opening_hours']?.toString().trim() ?? '';
    final hours = rawHours.isNotEmpty
        ? rawHours
        : (catSlug == 'wellness-care' ? 'Trực cấp cứu 24/7' : '07:00 - 22:00 hàng ngày');
    final price = eatery['price_range']?.toString().trim() ?? '';
    final commune = (eatery['commune'] is Map)
        ? eatery['commune']['name']?.toString()
        : eatery['commune_name']?.toString();
    final bType = (eatery['storytelling_data'] is Map)
        ? eatery['storytelling_data']['business_type']?.toString()
        : null;
    final cert = eatery['food_safety_certificate'] ?? eatery['foodSafetyCertificate'];
    final announcements = eatery['announcements']?.toString().trim() ?? '';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (announcements.isNotEmpty) ...[
          Container(
            padding: const EdgeInsets.all(12),
            margin: const EdgeInsets.only(bottom: 14),
            decoration: BoxDecoration(
              color: const Color(0xFFFFFBEB),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFFDE68A)),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(Icons.campaign_rounded, color: Color(0xFFD97706), size: 20),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    announcements,
                    style: const TextStyle(fontSize: 12.5, color: Color(0xFF92400E), height: 1.4),
                  ),
                ),
              ],
            ),
          ),
        ],
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFE2E8F0)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.03),
                blurRadius: 10,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(7),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(Icons.access_time_rounded, size: 16, color: Color(0xFF0284C7)),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Thời gian hoạt động', style: TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                        Text(hours, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                      ],
                    ),
                  ),
                ],
              ),
              const Divider(height: 18, color: Color(0xFFF1F5F9)),
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(7),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(Icons.payments_outlined, size: 16, color: Color(0xFF10B981)),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Chi phí / Mức giá', style: TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                        Text(
                          price.isNotEmpty ? price : 'Theo quy định / Liên hệ',
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              if (phone.isNotEmpty) ...[
                const Divider(height: 18, color: Color(0xFFF1F5F9)),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(7),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Icon(Icons.phone_rounded, size: 16, color: Color(0xFF0D9488)),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Hotline / Điện thoại', style: TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                          Text(phone, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                        ],
                      ),
                    ),
                    InkWell(
                      onTap: () => _makePhoneCall(phone),
                      borderRadius: BorderRadius.circular(8),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: const Color(0xFFCCFBF1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.call_rounded, size: 13, color: Color(0xFF0F766E)),
                            SizedBox(width: 4),
                            Text('Gọi', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Color(0xFF0F766E))),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ],
              if (commune != null && commune.isNotEmpty) ...[
                const Divider(height: 18, color: Color(0xFFF1F5F9)),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(7),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Icon(Icons.holiday_village_rounded, size: 16, color: Color(0xFFF59E0B)),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Khu vực hành chính', style: TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                          Text(commune, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                        ],
                      ),
                    ),
                    if (bType != null)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(color: const Color(0xFFCBD5E1)),
                        ),
                        child: Text(bType, style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: Color(0xFF475569))),
                      ),
                  ],
                ),
              ],
            ],
          ),
        ),
        if (cert != null && cert is Map) ...[
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFECFDF5),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFA7F3D0)),
            ),
            child: Row(
              children: [
                const Icon(Icons.verified_user_rounded, color: Color(0xFF059669), size: 28),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'CHỨNG NHẬN VỆ SINH AN TOÀN THỰC PHẨM',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11.5, color: Color(0xFF065F46)),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Số: ${cert['certificate_number'] ?? 'Đã xác thực'}',
                        style: const TextStyle(fontSize: 11, color: Color(0xFF047857), fontWeight: FontWeight.w600),
                      ),
                      if (cert['issued_by'] != null)
                        Text(
                          'Cấp bởi: ${cert['issued_by']}',
                          style: const TextStyle(fontSize: 10.5, color: Color(0xFF065F46)),
                        ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
        const SizedBox(height: 20),
      ],
    );
  }

  Widget _buildWellnessServicesSection(Map<String, dynamic> eatery, Color catColor) {
    final List services = (eatery['wellness_services'] is List && (eatery['wellness_services'] as List).isNotEmpty)
        ? eatery['wellness_services'] as List
        : ((eatery['wellnessServices'] is List && (eatery['wellnessServices'] as List).isNotEmpty)
            ? eatery['wellnessServices'] as List
            : []);

    if (services.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Icon(Icons.local_hospital_rounded, color: Color(0xFF059669), size: 20),
            const SizedBox(width: 8),
            Text(
              'Dịch vụ Y tế & Chuyên khoa (${services.length})',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
            ),
          ],
        ),
        const SizedBox(height: 12),
        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: services.length,
          separatorBuilder: (_, __) => const SizedBox(height: 10),
          itemBuilder: (context, idx) {
            final s = services[idx];
            final name = s['name']?.toString() ?? 'Dịch vụ y tế';
            final desc = s['description']?.toString() ?? '';
            final duration = s['duration']?.toString() ?? '';
            final priceRaw = double.tryParse(s['price']?.toString() ?? '0') ?? 0;
            final priceStr = priceRaw > 0
                ? '${priceRaw.toInt().toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]}.')}đ'
                : 'Khám BHYT / Miễn phí';

            return Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE2E8F0)),
                boxShadow: [
                  BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 6, offset: const Offset(0, 2)),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          name,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFFECFDF5),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: const Color(0xFFA7F3D0)),
                        ),
                        child: Text(
                          priceStr,
                          style: const TextStyle(color: Color(0xFF059669), fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ),
                  if (duration.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        const Icon(Icons.access_time_rounded, size: 13, color: Color(0xFF64748B)),
                        const SizedBox(width: 4),
                        Text(
                          duration,
                          style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                        ),
                      ],
                    ),
                  ],
                  if (desc.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(
                      desc,
                      style: const TextStyle(fontSize: 12.5, color: Color(0xFF475569), height: 1.4),
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
  }

  Widget _buildRoomsSection(Map<String, dynamic> eatery, Color catColor) {
    final List rooms = (eatery['rooms'] is List && (eatery['rooms'] as List).isNotEmpty)
        ? eatery['rooms'] as List
        : [];

    if (rooms.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Icon(Icons.hotel_rounded, color: Color(0xFF9334E6), size: 20),
            const SizedBox(width: 8),
            Text(
              'Hạng phòng & Lưu trú (${rooms.length})',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
            ),
          ],
        ),
        const SizedBox(height: 12),
        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: rooms.length,
          separatorBuilder: (_, __) => const SizedBox(height: 10),
          itemBuilder: (context, idx) {
            final r = rooms[idx];
            final name = r['name']?.toString() ?? 'Hạng phòng';
            final desc = r['description']?.toString() ?? '';
            final bed = r['bed_type']?.toString() ?? '';
            final cap = r['capacity']?.toString() ?? '';
            final priceRaw = double.tryParse(r['price']?.toString() ?? '0') ?? 0;
            final priceStr = priceRaw > 0
                ? '${priceRaw.toInt().toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]}.')}đ / đêm'
                : 'Liên hệ';

            return Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          name,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF3E8FF),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: const Color(0xFFD8B4FE)),
                        ),
                        child: Text(
                          priceStr,
                          style: const TextStyle(color: Color(0xFF7E22CE), fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ),
                  if (bed.isNotEmpty || cap.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        if (bed.isNotEmpty) ...[
                          const Icon(Icons.bed_rounded, size: 14, color: Color(0xFF64748B)),
                          const SizedBox(width: 4),
                          Text(bed, style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                          const SizedBox(width: 12),
                        ],
                        if (cap.isNotEmpty) ...[
                          const Icon(Icons.people_outline_rounded, size: 14, color: Color(0xFF64748B)),
                          const SizedBox(width: 4),
                          Text('$cap người', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                        ],
                      ],
                    ),
                  ],
                  if (desc.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(desc, style: const TextStyle(fontSize: 12.5, color: Color(0xFF475569))),
                  ],
                ],
              ),
            );
          },
        ),
        const SizedBox(height: 24),
      ],
    );
  }

  Widget _buildEducationProgramsSection(Map<String, dynamic> eatery, Color catColor) {
    final List progs = (eatery['education_programs'] is List && (eatery['education_programs'] as List).isNotEmpty)
        ? eatery['education_programs'] as List
        : ((eatery['educationPrograms'] is List && (eatery['educationPrograms'] as List).isNotEmpty)
            ? eatery['educationPrograms'] as List
            : []);

    if (progs.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Icon(Icons.school_rounded, color: Color(0xFF1A73E8), size: 20),
            const SizedBox(width: 8),
            Text(
              'Chương trình Giáo dục & Tuyển sinh (${progs.length})',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
            ),
          ],
        ),
        const SizedBox(height: 12),
        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: progs.length,
          separatorBuilder: (_, __) => const SizedBox(height: 10),
          itemBuilder: (context, idx) {
            final p = progs[idx];
            final name = p['name']?.toString() ?? 'Chương trình đào tạo';
            final desc = p['description']?.toString() ?? '';
            final fee = p['tuition_fee']?.toString() ?? '';
            final duration = p['duration']?.toString() ?? '';

            return Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          name,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                        ),
                      ),
                      if (fee.isNotEmpty)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEFF6FF),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFFBFDBFE)),
                          ),
                          child: Text(
                            fee,
                            style: const TextStyle(color: Color(0xFF1D4ED8), fontSize: 11, fontWeight: FontWeight.bold),
                          ),
                        ),
                    ],
                  ),
                  if (duration.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text('Thời lượng: $duration', style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B))),
                  ],
                  if (desc.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(desc, style: const TextStyle(fontSize: 12.5, color: Color(0xFF475569))),
                  ],
                ],
              ),
            );
          },
        ),
        const SizedBox(height: 24),
      ],
    );
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
            ? 'https://donganhdiscovery.xadonganh.com/${rawPath.startsWith('/') ? rawPath.substring(1) : rawPath}'
            : 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80');

    final double? lat = double.tryParse(eatery?['latitude']?.toString() ?? '');
    final double? lng = double.tryParse(eatery?['longitude']?.toString() ?? '');
    final phone = eatery?['phone']?.toString().trim() ?? '';

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
                                color: catColor.withValues(alpha: 0.85),
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

                            // Action Buttons Row (Call, Directions, Check-in)
                            Row(
                              children: [
                                if (phone.isNotEmpty) ...[
                                  Expanded(
                                    child: OutlinedButton.icon(
                                      onPressed: () => _makePhoneCall(phone),
                                      icon: const Icon(Icons.call_rounded, size: 17),
                                      label: const Text('Gọi điện', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                                      style: OutlinedButton.styleFrom(
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                        side: const BorderSide(color: Color(0xFF0D9488), width: 1.2),
                                        foregroundColor: const Color(0xFF0D9488),
                                        shape: RoundedRectangleBorder(
                                          borderRadius: BorderRadius.circular(10),
                                        ),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                ],
                                if (lat != null && lng != null) ...[
                                  Expanded(
                                    child: OutlinedButton.icon(
                                      onPressed: () => _openGoogleMapsDirections(lat, lng),
                                      icon: const Icon(Icons.directions, size: 17),
                                      label: const Text('Chỉ đường', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                                      style: OutlinedButton.styleFrom(
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                        side: BorderSide(color: catColor, width: 1.2),
                                        foregroundColor: catColor,
                                        shape: RoundedRectangleBorder(
                                          borderRadius: BorderRadius.circular(10),
                                        ),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                ],
                                Expanded(
                                  child: ElevatedButton.icon(
                                    onPressed: () => _openCheckinModal(context, eatery),
                                    icon: const Icon(Icons.camera_alt, size: 17),
                                    label: const Text('Check-in', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: const Color(0xFF0EA5E9),
                                      foregroundColor: Colors.white,
                                      elevation: 0,
                                      padding: const EdgeInsets.symmetric(vertical: 12),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),

                            const SizedBox(height: 20),

                            // Operating & Administrative Information Card
                            _buildOperatingInfoCard(eatery, catColor, catSlug),

                            // Medical & Wellness Services (Hospitals, Clinics)
                            _buildWellnessServicesSection(eatery, catColor),

                            // Accommodations & Rooms (Hotels, Homestays)
                            _buildRoomsSection(eatery, catColor),

                            // Education Programs (Schools, Training Centers)
                            _buildEducationProgramsSection(eatery, catColor),

                            // Description Section with Smart Contextual Fallback
                            Builder(
                              builder: (context) {
                                final rawDesc = eatery['description']?.toString().replaceAll(RegExp(r'<[^>]*>'), '').trim() ?? '';
                                final fallbackDesc = catSlug == 'wellness-care'
                                    ? '${eatery['name']} là cơ sở y tế, chăm sóc sức khỏe phục vụ nhân dân và người lao động trên địa bàn Đông Anh và khu vực lân cận. Cơ sở cung cấp dịch vụ thăm khám, chẩn đoán và điều trị với đội ngũ y bác sĩ tận tâm.'
                                    : catSlug == 'co-so-kinh-doanh'
                                        ? '${eatery['name']} là cơ sở sản xuất kinh doanh, thương mại dịch vụ uy tín tại huyện Đông Anh, phục vụ nhu cầu đời sống, sản xuất và tiêu dùng của nhân dân địa phương.'
                                        : catSlug == 'traditional-market'
                                            ? '${eatery['name']} là chợ truyền thống lâu đời trên địa bàn Đông Anh, nơi diễn ra các hoạt động giao thương sầm uất, cung cấp thực phẩm tươi sống, nông sản và nhu yếu phẩm hàng ngày.'
                                            : catSlug == 'education'
                                                ? '${eatery['name']} là đơn vị đào tạo, cơ sở giáo dục trên địa bàn Đông Anh với chương trình giảng dạy chuẩn mực và cơ sở vật chất đáp ứng nhu cầu học tập.'
                                                : '${eatery['name']} là địa điểm hoạt động phục vụ đời sống văn hóa, xã hội và kinh tế trên địa bàn huyện Đông Anh, TP. Hà Nội.';

                                final descToShow = rawDesc.isNotEmpty ? rawDesc : fallbackDesc;

                                return Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
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
                                      descToShow,
                                      style: TextStyle(
                                        fontSize: 14,
                                        height: 1.5,
                                        color: Colors.grey[800],
                                      ),
                                    ),
                                    const SizedBox(height: 24),
                                  ],
                                );
                              },
                            ),

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
                                                        color: Colors.black.withValues(alpha: 0.5),
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

                                if (combinedReviews.isEmpty) {
                                  return Container(
                                    width: double.infinity,
                                    margin: const EdgeInsets.only(bottom: 24),
                                    padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      borderRadius: BorderRadius.circular(14),
                                      border: Border.all(color: const Color(0xFFE2E8F0)),
                                    ),
                                    child: Column(
                                      children: [
                                        const Icon(Icons.rate_review_outlined, color: Color(0xFF94A3B8), size: 36),
                                        const SizedBox(height: 8),
                                        const Text(
                                          'Chưa có đánh giá hoặc check-in nào',
                                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF475569)),
                                        ),
                                        const SizedBox(height: 4),
                                        const Text(
                                          'Hãy là người đầu tiên chia sẻ cảm nhận hoặc hình ảnh tại đây!',
                                          textAlign: TextAlign.center,
                                          style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                        ),
                                        const SizedBox(height: 14),
                                        ElevatedButton.icon(
                                          onPressed: () => _openCheckinModal(context, eatery),
                                          icon: const Icon(Icons.star_rounded, size: 16),
                                          label: const Text('Viết đánh giá & Check-in'),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: catColor,
                                            foregroundColor: Colors.white,
                                            elevation: 0,
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                          ),
                                        ),
                                      ],
                                    ),
                                  );
                                }

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
                                                ? 'https://donganhdiscovery.xadonganh.com/$imgPath'
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

class _DetailCheckinModal extends StatefulWidget {
  final dynamic eatery;
  final Function(int rating, String comment, String guestName, String? imagePath) onSubmit;

  const _DetailCheckinModal({
    required this.eatery,
    required this.onSubmit,
  });

  @override
  State<_DetailCheckinModal> createState() => _DetailCheckinModalState();
}

class _DetailCheckinModalState extends State<_DetailCheckinModal> {
  int _rating = 5;
  final TextEditingController _nameController = TextEditingController(text: 'Khách Đông Anh');
  final TextEditingController _commentController = TextEditingController();
  File? _selectedImage;
  bool _isSubmitting = false;

  final ImagePicker _picker = ImagePicker();

  Future<void> _pickImage(ImageSource source) async {
    try {
      final XFile? picked = await _picker.pickImage(source: source, imageQuality: 85);
      if (picked != null) {
        setState(() {
          _selectedImage = File(picked.path);
        });
      }
    } catch (e) {
      debugPrint('Lỗi chọn ảnh: $e');
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _commentController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;
    final eateryName = widget.eatery?['name']?.toString() ?? 'Địa điểm';

    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      padding: EdgeInsets.fromLTRB(20, 16, 20, 20 + bottomInset),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(
                  color: Colors.grey[300],
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Đánh giá & Check-in',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                      ),
                      Text(
                        eateryName,
                        style: const TextStyle(fontSize: 13, color: Color(0xFF64748B)),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: Colors.grey),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Center(
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: List.generate(5, (index) {
                  final starVal = index + 1;
                  return IconButton(
                    onPressed: () => setState(() => _rating = starVal),
                    icon: Icon(
                      starVal <= _rating ? Icons.star_rounded : Icons.star_outline_rounded,
                      color: Colors.amber,
                      size: 34,
                    ),
                  );
                }),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _nameController,
              decoration: InputDecoration(
                labelText: 'Họ tên người đánh giá',
                labelStyle: const TextStyle(fontSize: 13, color: Color(0xFF64748B)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _commentController,
              maxLines: 3,
              decoration: InputDecoration(
                hintText: 'Chia sẻ trải nghiệm hoặc cảm nghĩ của bạn tại đây...',
                hintStyle: const TextStyle(fontSize: 13, color: Color(0xFF94A3B8)),
                contentPadding: const EdgeInsets.all(12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
            const SizedBox(height: 14),
            if (_selectedImage != null) ...[
              Stack(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(10),
                    child: Image.file(_selectedImage!, height: 120, width: double.infinity, fit: BoxFit.cover),
                  ),
                  Positioned(
                    top: 6,
                    right: 6,
                    child: CircleAvatar(
                      backgroundColor: Colors.black54,
                      radius: 14,
                      child: IconButton(
                        padding: EdgeInsets.zero,
                        icon: const Icon(Icons.close, color: Colors.white, size: 16),
                        onPressed: () => setState(() => _selectedImage = null),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
            ] else ...[
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () => _pickImage(ImageSource.camera),
                      icon: const Icon(Icons.camera_alt_outlined, size: 18),
                      label: const Text('Chụp ảnh', style: TextStyle(fontSize: 13)),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () => _pickImage(ImageSource.gallery),
                      icon: const Icon(Icons.photo_library_outlined, size: 18),
                      label: const Text('Chọn ảnh', style: TextStyle(fontSize: 13)),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
            ],
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _isSubmitting
                    ? null
                    : () async {
                        setState(() => _isSubmitting = true);
                        Navigator.pop(context);
                        await widget.onSubmit(
                          _rating,
                          _commentController.text.trim(),
                          _nameController.text.trim().isNotEmpty ? _nameController.text.trim() : 'Khách Đông Anh',
                          _selectedImage?.path,
                        );
                      },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0EA5E9),
                  foregroundColor: Colors.white,
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(vertical: 13),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                child: _isSubmitting
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text('Gửi đánh giá & Check-in', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
