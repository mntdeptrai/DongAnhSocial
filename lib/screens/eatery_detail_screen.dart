import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/api_service.dart';

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
    final catSlug = eatery?['category']?['slug']?.toString() ?? widget.categorySlug;
    final catColor = _getCategoryColor(catSlug);
    final catIcon = _getCategoryIcon(catSlug);

    final String imagePath = eatery?['image_path'] ?? '';
    final String fullImgUrl = imagePath.startsWith('http')
        ? imagePath
        : (imagePath.isNotEmpty ? 'https://donganhdiscovery.xadonganh.com/' + imagePath : '');

    final double? lat = double.tryParse(eatery?['latitude']?.toString() ?? '');
    final double? lng = double.tryParse(eatery?['longitude']?.toString() ?? '');

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: _isLoading && eatery == null
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF0EA5E9)))
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
                                            eatery['category']?['name'] ?? 'Địa điểm',
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

                            // Menu / Dishes / Offerings List Section
                            if (eatery['dishes'] != null && (eatery['dishes'] as List).isNotEmpty) ...[
                              const Text(
                                'Thực đơn & Món đặc sắc',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF0F172A),
                                ),
                              ),
                              const SizedBox(height: 12),
                              ListView.separated(
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                itemCount: (eatery['dishes'] as List).length,
                                separatorBuilder: (_, __) => const SizedBox(height: 10),
                                itemBuilder: (context, idx) {
                                  final dish = eatery['dishes'][idx];
                                  final dishImg = dish['image_path'] ?? '';
                                  final dishImgUrl = dishImg.startsWith('http')
                                      ? dishImg
                                      : (dishImg.isNotEmpty ? 'https://donganhdiscovery.xadonganh.com/' + dishImg : '');

                                  return Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(color: Colors.grey[200]!),
                                    ),
                                    child: Row(
                                      children: [
                                        ClipRRect(
                                          borderRadius: BorderRadius.circular(8),
                                          child: Container(
                                            width: 54,
                                            height: 54,
                                            color: Colors.grey[100],
                                            child: dishImgUrl.isNotEmpty
                                                ? Image.network(dishImgUrl, fit: BoxFit.cover)
                                                : const Icon(Icons.restaurant_menu, color: Colors.grey),
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                dish['name'] ?? '',
                                                style: const TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 14,
                                                ),
                                              ),
                                              if (dish['price'] != null)
                                                Text(
                                                  '${dish['price']} VNĐ',
                                                  style: const TextStyle(
                                                    color: Color(0xFF0EA5E9),
                                                    fontWeight: FontWeight.bold,
                                                    fontSize: 12,
                                                  ),
                                                ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                  );
                                },
                              ),
                              const SizedBox(height: 24),
                            ],

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
