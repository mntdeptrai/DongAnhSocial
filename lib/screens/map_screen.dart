import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/api_service.dart';
import 'eatery_detail_screen.dart';
import 'notifications_screen.dart';

class MapScreen extends StatefulWidget {
  const MapScreen({super.key});

  @override
  State<MapScreen> createState() => _MapScreenState();
}

class _LoginCheckinModal extends StatefulWidget {
  final int eateryId;
  final String eateryName;
  final Function(int rating, String comment, String? guestName, String? imagePath) onSubmit;

  const _LoginCheckinModal({
    required this.eateryId,
    required this.eateryName,
    required this.onSubmit,
  });

  @override
  State<_LoginCheckinModal> createState() => _LoginCheckinModalState();
}

class _LoginCheckinModalState extends State<_LoginCheckinModal> {
  int _rating = 5;
  final _commentController = TextEditingController();
  final _guestNameController = TextEditingController();
  bool _isSending = false;
  String? _imagePath;

  @override
  void dispose() {
    _commentController.dispose();
    _guestNameController.dispose();
    super.dispose();
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final picker = ImagePicker();
      final pickedFile = await picker.pickImage(
        source: source,
        maxWidth: 1000,
        maxHeight: 1000,
        imageQuality: 80,
      );
      if (pickedFile != null) {
        setState(() {
          _imagePath = pickedFile.path;
        });
      }
    } catch (e) {
      debugPrint('Lỗi chọn ảnh: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    final isGuest = !ApiService.isAuthenticated;

    return Padding(
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom,
        left: 20,
        right: 20,
        top: 20,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'Check-in tại ${widget.eateryName}',
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),
          if (isGuest) ...[
            TextField(
              controller: _guestNameController,
              decoration: const InputDecoration(
                labelText: 'Tên của bạn (Khách vãng lai)',
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 16),
          ],
          const Text('Đánh giá của bạn:'),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(5, (index) {
              return IconButton(
                icon: Icon(
                  index < _rating ? Icons.star : Icons.star_border,
                  color: Colors.amber,
                  size: 32,
                ),
                onPressed: () {
                  setState(() {
                    _rating = index + 1;
                  });
                },
              );
            }),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _commentController,
            maxLines: 3,
            decoration: const InputDecoration(
              labelText: 'Nhập cảm nhận của bạn...',
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 16),
          if (_imagePath == null)
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _pickImage(ImageSource.camera),
                    icon: const Icon(Icons.camera_alt),
                    label: const Text('Chụp ảnh'),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      side: const BorderSide(color: Color(0xFF0EA5E9)),
                      foregroundColor: const Color(0xFF0EA5E9),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _pickImage(ImageSource.gallery),
                    icon: const Icon(Icons.photo),
                    label: const Text('Thư viện'),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      side: const BorderSide(color: Color(0xFF0EA5E9)),
                      foregroundColor: const Color(0xFF0EA5E9),
                    ),
                  ),
                ),
              ],
            )
          else
            Stack(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.file(
                    File(_imagePath!),
                    height: 140,
                    width: double.infinity,
                    fit: BoxFit.cover,
                  ),
                ),
                Positioned(
                  top: 8,
                  right: 8,
                  child: GestureDetector(
                    onTap: () {
                      setState(() {
                        _imagePath = null;
                      });
                    },
                    child: Container(
                      padding: const EdgeInsets.all(4),
                      decoration: const BoxDecoration(
                        color: Colors.black54,
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.close, color: Colors.white, size: 20),
                    ),
                  ),
                ),
              ],
            ),
          const SizedBox(height: 20),
          ElevatedButton(
            onPressed: _isSending
                ? null
                : () async {
                    setState(() {
                      _isSending = true;
                    });
                    await widget.onSubmit(
                      _rating,
                      _commentController.text,
                      isGuest ? _guestNameController.text : null,
                      _imagePath,
                    );
                    if (mounted) {
                      setState(() {
                        _isSending = false;
                      });
                      Navigator.pop(context);
                    }
                  },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF0EA5E9),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 14),
            ),
            child: _isSending
                ? const SizedBox(
                    height: 20,
                    width: 20,
                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                  )
                : const Text('Gửi Check-in', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}

class _MapScreenState extends State<MapScreen> with TickerProviderStateMixin, WidgetsBindingObserver {
  final MapController _mapController = MapController();
  final LatLng _center = const LatLng(21.1352, 105.8458); // Đông Anh Center
  final TextEditingController _searchController = TextEditingController();
  final DraggableScrollableController _sheetController = DraggableScrollableController();

  late final AnimationController _pulseController;
  late final Animation<double> _pulseAnimation;

  List<dynamic> _categories = [];
  List<dynamic> _allEateries = [];
  List<dynamic> _filteredEateries = [];
  final Set<String> _expandedCategories = {};
  bool _isLoading = true;
  LatLng? _userLocation;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);

    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    )..repeat(reverse: true);

    _pulseAnimation = Tween<double>(begin: 1.0, end: 1.4).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );

    _loadAllData();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused ||
        state == AppLifecycleState.inactive ||
        state == AppLifecycleState.hidden) {
      _pulseController.stop();
    } else if (state == AppLifecycleState.resumed) {
      if (!_pulseController.isAnimating) {
        _pulseController.repeat(reverse: true);
      }
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _pulseController.dispose();
    _searchController.dispose();
    _sheetController.dispose();
    super.dispose();
  }

  void _animatedMapMove(LatLng destLocation, double destZoom) {
    final latTween = Tween<double>(
      begin: _mapController.camera.center.latitude,
      end: destLocation.latitude,
    );
    final lngTween = Tween<double>(
      begin: _mapController.camera.center.longitude,
      end: destLocation.longitude,
    );
    final zoomTween = Tween<double>(
      begin: _mapController.camera.zoom,
      end: destZoom,
    );

    final controller = AnimationController(
      duration: const Duration(milliseconds: 850),
      vsync: this,
    );

    final Animation<double> animation = CurvedAnimation(
      parent: controller,
      curve: Curves.fastOutSlowIn,
    );

    controller.addListener(() {
      _mapController.move(
        LatLng(latTween.evaluate(animation), lngTween.evaluate(animation)),
        zoomTween.evaluate(animation),
      );
    });

    animation.addStatusListener((status) {
      if (status == AnimationStatus.completed || status == AnimationStatus.dismissed) {
        controller.dispose();
      }
    });

    controller.forward();
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

  void _toggleSheet() {
    if (_sheetController.isAttached) {
      final currentSize = _sheetController.size;
      final targetSize = currentSize < 0.5 ? 0.75 : 0.28;
      _sheetController.animateTo(
        targetSize,
        duration: const Duration(milliseconds: 350),
        curve: Curves.fastOutSlowIn,
      );
    }
  }

  void _onHeaderDragUpdate(DragUpdateDetails details) {
    if (_sheetController.isAttached) {
      final screenHeight = MediaQuery.of(context).size.height;
      final delta = details.primaryDelta! / screenHeight;
      final newSize = (_sheetController.size - delta).clamp(0.12, 0.75);
      _sheetController.jumpTo(newSize);
    }
  }

  void _onHeaderDragEnd(DragEndDetails details) {
    if (_sheetController.isAttached) {
      final velocity = details.primaryVelocity ?? 0;
      final currentSize = _sheetController.size;
      double targetSize = 0.28;
      if (velocity < -300 || currentSize > 0.45) {
        targetSize = 0.75;
      } else if (velocity > 300 || currentSize < 0.2) {
        targetSize = 0.12;
      }
      _sheetController.animateTo(
        targetSize,
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeOut,
      );
    }
  }

  Color _getCategoryColor(String? slug) {
    switch (slug) {
      case 'hanh-trinh-di-san':
        return const Color(0xFF8B4513); // Nâu đất di tích
      case 'smart-education-map':
        return const Color(0xFF1A73E8); // Xanh blue trường học
      case 'wellness-care':
        return const Color(0xFF34A853); // Xanh lá y tế
      case 'stay-in-dong-anh':
        return const Color(0xFF9334E6); // Tím khách sạn
      case 'dong-anh-market':
        return const Color(0xFFF29900); // Vàng chợ
      case 'dong-anh-food-map':
        return const Color(0xFFEA4335); // Đỏ ẩm thực
      case 'discover-dong-anh-community-culture-hub':
        return const Color(0xFFE81E63); // Hồng văn hóa
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

  String _getCategoryLabel(String? slug, String originalName) {
    switch (slug) {
      case 'hanh-trinh-di-san':
        return 'DI TÍCH QUỐC GIA & DI SẢN';
      case 'smart-education-map':
        return 'HỆ THỐNG TRƯỜNG HỌC';
      case 'wellness-care':
        return 'BỆNH VIỆN & CƠ SỞ Y TẾ';
      case 'stay-in-dong-anh':
        return 'KHÁCH SẠN & LƯU TRÚ';
      case 'dong-anh-market':
        return 'NÔNG SẢN SỐ & ĐẶC SẢN OCOP';
      case 'dong-anh-food-map':
        return 'ĐỊA ĐIỂM ẨM THỰC';
      case 'discover-dong-anh-community-culture-hub':
        return 'NHÀ VĂN HÓA & THỂ THAO';
      default:
        return originalName.toUpperCase();
    }
  }

  Future<void> _loadAllData() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final categories = await ApiService.getCategories();
      _categories = categories;

      final combined = await ApiService.getAllEateries();

      if (mounted) {
        setState(() {
          _allEateries = combined;
          _filteredEateries = combined;
          if (_categories.isNotEmpty) {
            _expandedCategories.add(_categories[0]['slug'].toString());
          }
          _isLoading = false;
        });
      }
    } catch (e) {
      debugPrint('Lỗi tải dữ liệu bản đồ: $e');
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  void _onSearchChanged(String query) {
    final q = query.trim().toLowerCase();
    if (q.isEmpty) {
      setState(() {
        _filteredEateries = _allEateries;
      });
      return;
    }

    final filtered = _allEateries.where((eat) {
      final name = eat['name']?.toString().toLowerCase() ?? '';
      final address = eat['address']?.toString().toLowerCase() ?? '';
      final catName = eat['category']?['name']?.toString().toLowerCase() ?? '';
      return name.contains(q) || address.contains(q) || catName.contains(q);
    }).toList();

    setState(() {
      _filteredEateries = filtered;
    });
  }

  Future<void> _getLocation() async {
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) return;
      }

      final position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );

      if (mounted) {
        setState(() {
          _userLocation = LatLng(position.latitude, position.longitude);
        });
        _animatedMapMove(_userLocation!, 15.5);
      }
    } catch (e) {
      debugPrint('Lỗi vị trí: $e');
    }
  }

  Map<String, Map<String, dynamic>> _groupEateriesByCategory() {
    final Map<String, Map<String, dynamic>> grouped = {};

    for (var eat in _filteredEateries) {
      final cat = eat['category'];
      final slug = cat?['slug']?.toString() ?? 'other';
      final origName = cat?['name']?.toString() ?? 'Khác';
      final label = _getCategoryLabel(slug, origName);

      if (!grouped.containsKey(slug)) {
        grouped[slug] = {
          'slug': slug,
          'label': label,
          'items': [],
        };
      }
      (grouped[slug]!['items'] as List).add(eat);
    }
    return grouped;
  }

  void _focusAndShowEatery(dynamic eatery, double lat, double lng) {
    _animatedMapMove(LatLng(lat, lng), 16.0);
    _showEateryDetails(eatery);
  }

  void _showEateryDetails(dynamic eatery) {
    double? lat = double.tryParse(eatery['latitude']?.toString() ?? '');
    double? lng = double.tryParse(eatery['longitude']?.toString() ?? '');
    final catSlug = eatery['category']?['slug']?.toString();
    final color = _getCategoryColor(catSlug);
    final icon = _getCategoryIcon(catSlug);

    final String imagePath = eatery['image_path'] ?? '';
    final String fullImgUrl = imagePath.startsWith('http')
        ? imagePath
        : (imagePath.isNotEmpty ? 'https://donganhdiscovery.xadonganh.com/' + imagePath : '');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) {
        final screenHeight = MediaQuery.of(context).size.height;
        final maxSheetHeight = screenHeight * 0.75;
        final imageHeight = screenHeight * 0.18 < 120 ? screenHeight * 0.18 : 120.0;

        return ConstrainedBox(
          constraints: BoxConstraints(maxHeight: maxSheetHeight),
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Drag handle
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    margin: const EdgeInsets.only(bottom: 12),
                    decoration: BoxDecoration(
                      color: Colors.grey[300],
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),

                // Cover image
                if (fullImgUrl.isNotEmpty)
                  ClipRRect(
                    borderRadius: BorderRadius.circular(16),
                    child: Image.network(
                      fullImgUrl,
                      height: imageHeight,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(
                        height: imageHeight,
                        color: const Color(0xFF1E293B),
                        child: const Icon(Icons.location_on, color: Colors.white, size: 36),
                      ),
                    ),
                  )
                else
                  Container(
                    height: imageHeight * 0.7,
                    decoration: BoxDecoration(
                      color: color.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Center(
                      child: Text(icon, style: const TextStyle(fontSize: 36)),
                    ),
                  ),
                const SizedBox(height: 10),

                // Category tag + rating row
                Row(
                  children: [
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                        decoration: BoxDecoration(
                          color: color,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Row(
                          children: [
                            Text(icon, style: const TextStyle(fontSize: 10)),
                            const SizedBox(width: 3),
                            Expanded(
                              child: Text(
                                eatery['category']?['name'] ?? 'Địa điểm',
                                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 9),
                                overflow: TextOverflow.ellipsis,
                                maxLines: 1,
                              ),
                            ),
                            IconButton(
                              icon: const Icon(Icons.notifications_outlined, color: Colors.white),
                              tooltip: 'Thông báo',
                              onPressed: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(builder: (context) => const NotificationsScreen()),
                                );
                              },
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(width: 6),
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.star, color: Colors.amber, size: 14),
                        const SizedBox(width: 2),
                        Text(
                          '${eatery['rating_avg'] ?? '5.0'}',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11),
                        ),
                      ],
                    ),
                  ],
                ),
                const SizedBox(height: 6),

                // Name
                Text(
                  eatery['name'] ?? 'Địa điểm',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),

                // Address
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.location_on_outlined, size: 14, color: Colors.grey),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        eatery['address'] ?? 'Đông Anh, Hà Nội',
                        style: TextStyle(color: Colors.grey[600], fontSize: 11),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),

                // Action buttons row
                Row(
                  children: [
                    if (lat != null && lng != null)
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () {
                            Navigator.pop(context);
                            _openGoogleMapsDirections(lat, lng);
                          },
                          icon: const Icon(Icons.directions, size: 16),
                          label: const Text('Chỉ đường', style: TextStyle(fontSize: 12)),
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            side: BorderSide(color: color),
                            foregroundColor: color,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                        ),
                      ),
                    if (lat != null && lng != null) const SizedBox(width: 8),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () {
                          Navigator.pop(context);
                          _openCheckinDialog(eatery);
                        },
                        icon: const Icon(Icons.camera_alt, size: 16),
                        label: const Text('Check-in', style: TextStyle(fontSize: 12)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0EA5E9),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 10),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),

                // Full detail button
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () {
                      Navigator.pop(context);
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => EateryDetailScreen(
                            categorySlug: catSlug ?? 'dong-anh-food-map',
                            eaterySlug: eatery['slug'] ?? '',
                            initialData: eatery,
                          ),
                        ),
                      );
                    },
                    icon: const Icon(Icons.arrow_forward, size: 16),
                    label: const Text('Xem chi tiết ➔', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1E293B),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void _openCheckinDialog(dynamic eatery) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return _LoginCheckinModal(
          eateryId: eatery['id'],
          eateryName: eatery['name'],
          onSubmit: (rating, comment, guestName, imagePath) async {
            final res = await ApiService.storeCheckin(
              eateryId: eatery['id'],
              rating: rating,
              comment: comment,
              guestName: guestName,
              imagePath: imagePath,
            );
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(res['message'] ?? 'Thành công'),
                  backgroundColor: res['success'] == true ? Colors.green : Colors.red,
                ),
              );
            }
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = const Color(0xFF0EA5E9);
    final groupedData = _groupEateriesByCategory();

    // Build map markers
    final markers = <Marker>[];
    for (var eatery in _filteredEateries) {
      double? lat = double.tryParse(eatery['latitude']?.toString() ?? '');
      double? lng = double.tryParse(eatery['longitude']?.toString() ?? '');
      if (lat != null && lng != null) {
        final catSlug = eatery['category']?['slug']?.toString();
        final color = _getCategoryColor(catSlug);
        final iconStr = _getCategoryIcon(catSlug);

        markers.add(
          Marker(
            point: LatLng(lat, lng),
            width: 44,
            height: 44,
            alignment: Alignment.topCenter,
            child: GestureDetector(
              onTap: () => _focusAndShowEatery(eatery, lat, lng),
              child: Stack(
                alignment: Alignment.center,
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: color,
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.white, width: 2.5),
                      boxShadow: const [
                        BoxShadow(
                          color: Colors.black26,
                          blurRadius: 6,
                          offset: Offset(0, 3),
                        )
                      ],
                    ),
                    child: Center(
                      child: Text(
                        iconStr,
                        style: const TextStyle(fontSize: 18),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      }
    }

    if (_userLocation != null) {
      markers.add(
        Marker(
          point: _userLocation!,
          width: 44,
          height: 44,
          child: AnimatedBuilder(
            animation: _pulseAnimation,
            builder: (context, child) {
              return Stack(
                alignment: Alignment.center,
                children: [
                  Container(
                    width: 32 * _pulseAnimation.value,
                    height: 32 * _pulseAnimation.value,
                    decoration: BoxDecoration(
                      color: Colors.blue.withOpacity(0.35 / _pulseAnimation.value),
                      shape: BoxShape.circle,
                    ),
                  ),
                  Container(
                    width: 14,
                    height: 14,
                    decoration: BoxDecoration(
                      color: Colors.blue,
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.white, width: 2.5),
                      boxShadow: const [
                        BoxShadow(color: Colors.black26, blurRadius: 4),
                      ],
                    ),
                  ),
                ],
              );
            },
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: SafeArea(
        child: Stack(
          children: [
            // 1. Leaflet Map Viewport
            FlutterMap(
              mapController: _mapController,
              options: MapOptions(
                initialCenter: _center,
                initialZoom: 12.5,
              ),
              children: [
                TileLayer(
                  urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                  userAgentPackageName: 'com.donganh.discovery',
                ),
                MarkerLayer(markers: markers),
              ],
            ),

            // 2. Top Banner Header
            Positioned(
              top: 10,
              left: 12,
              right: 12,
              child: Column(
                children: [
                  // Web-style Sky Blue Header Title Card matching DongAnh Discovery theme
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF38BDF8), Color(0xFF00A8EE), Color(0xFF0284C7)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFFFFB800), width: 2),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF00A8EE).withOpacity(0.3),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 32,
                          height: 32,
                          decoration: const BoxDecoration(
                            color: Color(0xFFFFB800),
                            shape: BoxShape.circle,
                          ),
                          child: const Center(
                            child: Text('📍', style: TextStyle(fontSize: 16)),
                          ),
                        ),
                        const SizedBox(width: 10),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Text(
                                    'DongAnh',
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontWeight: FontWeight.w900,
                                      fontSize: 15,
                                    ),
                                  ),
                                  Text(
                                    ' Discovery',
                                    style: TextStyle(
                                      color: Color(0xFFFFB800),
                                      fontWeight: FontWeight.w900,
                                      fontSize: 15,
                                    ),
                                  ),
                                ],
                              ),
                              Text(
                                'Bản đồ số du lịch, di sản & chợ OCOP 🗺️',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                        ),
                        IconButton(
                          padding: EdgeInsets.zero,
                          constraints: const BoxConstraints(),
                          icon: const Icon(Icons.notifications_active, color: Color(0xFFFFB800), size: 20),
                          onPressed: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(builder: (context) => const NotificationsScreen()),
                            );
                          },
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 8),

                  // Real-time Search Input Box
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: const [
                        BoxShadow(
                          color: Colors.black12,
                          blurRadius: 8,
                          offset: Offset(0, 3),
                        ),
                      ],
                    ),
                    child: TextField(
                      controller: _searchController,
                      onChanged: _onSearchChanged,
                      style: const TextStyle(fontSize: 13),
                      decoration: InputDecoration(
                        hintText: 'Tìm kiếm địa danh, trường học...',
                        hintStyle: TextStyle(color: Colors.grey[400], fontSize: 13),
                        prefixIcon: const Icon(Icons.search, color: Color(0xFF0EA5E9), size: 20),
                        suffixIcon: _searchController.text.isNotEmpty
                            ? IconButton(
                                icon: const Icon(Icons.clear, size: 18, color: Colors.grey),
                                onPressed: () {
                                  _searchController.clear();
                                  _onSearchChanged('');
                                },
                              )
                            : null,
                        border: InputBorder.none,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // 3. Floating Action Buttons (GPS location button)
            Positioned(
              right: 14,
              bottom: 220,
              child: FloatingActionButton.small(
                onPressed: _getLocation,
                backgroundColor: Colors.white,
                foregroundColor: primaryColor,
                elevation: 4,
                child: const Icon(Icons.my_location),
              ),
            ),

            // 4. Loading Overlay Indicator
            if (_isLoading)
              Positioned(
                top: 130,
                left: 0,
                right: 0,
                child: Center(
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    decoration: BoxDecoration(
                      color: const Color(0xBF000000),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        SizedBox(
                          width: 14,
                          height: 14,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        ),
                        SizedBox(width: 8),
                        Text(
                          'Đang kết nối bản đồ dữ liệu...',
                          style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ),
                ),
              ),

            // 5. Draggable Category Accordion Bottom Sheet (Mirroring Web Search Sidebar!)
            DraggableScrollableSheet(
              controller: _sheetController,
              initialChildSize: 0.28,
              minChildSize: 0.12,
              maxChildSize: 0.75,
              builder: (context, scrollController) {
                return Material(
                  elevation: 12,
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                  color: Colors.white,
                  child: Column(
                    children: [
                      // Interactive Sheet handle bar & Header
                      GestureDetector(
                        onTap: _toggleSheet,
                        onVerticalDragUpdate: _onHeaderDragUpdate,
                        onVerticalDragEnd: _onHeaderDragEnd,
                        behavior: HitTestBehavior.opaque,
                        child: Column(
                          children: [
                            Container(
                              margin: const EdgeInsets.only(top: 10, bottom: 6),
                              width: 44,
                              height: 5,
                              decoration: BoxDecoration(
                                color: Colors.grey[400],
                                borderRadius: BorderRadius.circular(3),
                              ),
                            ),
                            Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                              child: Row(
                                children: [
                                  Text(
                                    'DANH SÁCH ĐỊA ĐIỂM (${_filteredEateries.length})',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w900,
                                      color: Colors.grey[700],
                                      letterSpacing: 0.5,
                                    ),
                                  ),
                                  const Spacer(),
                                  Row(
                                    children: [
                                      Icon(Icons.swipe_vertical, size: 14, color: Colors.grey[500]),
                                      const SizedBox(width: 4),
                                      Text(
                                        'Chạm hoặc vuốt để mở',
                                        style: TextStyle(
                                          fontSize: 10,
                                          color: Colors.grey[500],
                                          fontWeight: FontWeight.bold,
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

                      const Divider(height: 12),

                      // Category Accordion List
                      Expanded(
                        child: groupedData.isEmpty
                            ? Center(
                                child: Text(
                                  'Không tìm thấy địa điểm nào phù hợp.',
                                  style: TextStyle(color: Colors.grey[500], fontSize: 13),
                                ),
                              )
                            : ListView.builder(
                                controller: scrollController,
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                itemCount: groupedData.length,
                                itemBuilder: (context, index) {
                                  final groupKey = groupedData.keys.elementAt(index);
                                  final group = groupedData[groupKey]!;
                                  final slug = group['slug'].toString();
                                  final label = group['label'].toString();
                                  final items = group['items'] as List;
                                  final isExpanded = _expandedCategories.contains(slug);
                                  final color = _getCategoryColor(slug);
                                  final iconStr = _getCategoryIcon(slug);

                                  return Container(
                                    margin: const EdgeInsets.only(bottom: 8),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFF8FAFC),
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(color: Colors.grey[200]!),
                                    ),
                                    child: Column(
                                      children: [
                                        // Category Accordion Header
                                        InkWell(
                                          onTap: () {
                                            setState(() {
                                              if (isExpanded) {
                                                _expandedCategories.remove(slug);
                                              } else {
                                                _expandedCategories.add(slug);
                                              }
                                            });
                                          },
                                          borderRadius: BorderRadius.circular(12),
                                          child: Padding(
                                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                            child: Row(
                                              children: [
                                                Container(
                                                  width: 30,
                                                  height: 30,
                                                  decoration: BoxDecoration(
                                                    color: color,
                                                    borderRadius: BorderRadius.circular(8),
                                                  ),
                                                  child: Center(
                                                    child: Text(iconStr, style: const TextStyle(fontSize: 14)),
                                                  ),
                                                ),
                                                const SizedBox(width: 10),
                                                Expanded(
                                                  child: Text(
                                                    label,
                                                    style: const TextStyle(
                                                      fontWeight: FontWeight.bold,
                                                      fontSize: 12,
                                                      color: Color(0xFF1E293B),
                                                    ),
                                                  ),
                                                ),
                                                Container(
                                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                                  decoration: BoxDecoration(
                                                    color: color.withOpacity(0.12),
                                                    borderRadius: BorderRadius.circular(10),
                                                  ),
                                                  child: Text(
                                                    '${items.length}',
                                                    style: TextStyle(
                                                      color: color,
                                                      fontWeight: FontWeight.bold,
                                                      fontSize: 11,
                                                    ),
                                                  ),
                                                ),
                                                const SizedBox(width: 6),
                                                Icon(
                                                  isExpanded ? Icons.keyboard_arrow_up : Icons.keyboard_arrow_down,
                                                  color: Colors.grey[600],
                                                  size: 20,
                                                ),
                                              ],
                                            ),
                                          ),
                                        ),

                                        // Category Items List (if expanded)
                                        if (isExpanded) ...[
                                          const Divider(height: 1),
                                          ListView.separated(
                                            shrinkWrap: true,
                                            physics: const NeverScrollableScrollPhysics(),
                                            itemCount: items.length,
                                            separatorBuilder: (_, __) => Divider(height: 1, color: Colors.grey[200]),
                                            itemBuilder: (context, itemIdx) {
                                              final eat = items[itemIdx];
                                              final String img = eat['image_path'] ?? '';
                                              final String thumbUrl = img.startsWith('http')
                                                  ? img
                                                  : (img.isNotEmpty ? 'https://donganhdiscovery.xadonganh.com/' + img : '');

                                              final double? lat = double.tryParse(eat['latitude']?.toString() ?? '');
                                              final double? lng = double.tryParse(eat['longitude']?.toString() ?? '');

                                              return Material(
                                                color: Colors.transparent,
                                                child: ListTile(
                                                  dense: true,
                                                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 2),
                                                  leading: ClipRRect(
                                                    borderRadius: BorderRadius.circular(8),
                                                    child: Container(
                                                      width: 42,
                                                      height: 42,
                                                      color: color.withOpacity(0.1),
                                                      child: thumbUrl.isNotEmpty
                                                          ? Image.network(
                                                              thumbUrl,
                                                              fit: BoxFit.cover,
                                                              errorBuilder: (_, __, ___) => Center(child: Text(iconStr)),
                                                            )
                                                          : Center(child: Text(iconStr)),
                                                    ),
                                                  ),
                                                  title: Text(
                                                    eat['name'] ?? '',
                                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                                    maxLines: 1,
                                                    overflow: TextOverflow.ellipsis,
                                                  ),
                                                  subtitle: Text(
                                                    eat['address'] ?? 'Đang cập nhật địa chỉ...',
                                                    style: TextStyle(color: Colors.grey[600], fontSize: 11),
                                                    maxLines: 1,
                                                    overflow: TextOverflow.ellipsis,
                                                  ),
                                                  trailing: IconButton(
                                                    icon: const Icon(Icons.arrow_forward_ios, size: 13, color: Colors.grey),
                                                    onPressed: () {
                                                      Navigator.push(
                                                        context,
                                                        MaterialPageRoute(
                                                          builder: (context) => EateryDetailScreen(
                                                            categorySlug: slug,
                                                            eaterySlug: eat['slug'] ?? '',
                                                            initialData: eat,
                                                          ),
                                                        ),
                                                      );
                                                    },
                                                  ),
                                                  onTap: () {
                                                    if (lat != null && lng != null) {
                                                      _focusAndShowEatery(eat, lat, lng);
                                                    }
                                                  },
                                                ),
                                              );
                                            },
                                          ),
                                        ],
                                      ],
                                    ),
                                  );
                                },
                              ),
                      ),
                    ],
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}
