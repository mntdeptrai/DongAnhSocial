import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/api_service.dart';
import '../widgets/custom_loader.dart';
import 'eatery_detail_screen.dart';

class MapScreen extends StatefulWidget {
  const MapScreen({super.key});

  @override
  State<MapScreen> createState() => _MapScreenState();
}

class _MapScreenState extends State<MapScreen> with TickerProviderStateMixin {
  final MapController _mapController = MapController();
  final LatLng _dongAnhCenter = const LatLng(21.1352, 105.8458);
  final TextEditingController _searchController = TextEditingController();
  late final PageController _pageController;

  List<dynamic> _allEateries = [];
  List<dynamic> _filteredEateries = [];
  List<dynamic> _displayedEateries = [];
  String _selectedCategorySlug = 'all';
  bool _isLoading = true;
  bool _isListView = false;
  LatLng? _userLocation;
  int _selectedEateryIndex = 0;
  List<Marker> _cachedMarkers = [];
  AnimationController? _mapAnimationController;
  bool _isProgrammaticScroll = false;

  final List<Map<String, String>> _categoryTabs = const [
    {'slug': 'all', 'name': 'Tất cả', 'icon': '🔥'},
    {'slug': 'hanh-trinh-di-san', 'name': 'Di tích & Di sản', 'icon': '⛩️'},
    {'slug': 'dong-anh-food-map', 'name': 'Ẩm thực', 'icon': '🍜'},
    {'slug': 'smart-education-map', 'name': 'Trường học', 'icon': '🎓'},
    {'slug': 'wellness-care', 'name': 'Y tế', 'icon': '🏥'},
    {'slug': 'stay-in-dong-anh', 'name': 'Lưu trú', 'icon': '🏨'},
    {'slug': 'dong-anh-market', 'name': 'Chợ & OCOP', 'icon': '🛍️'},
    {'slug': 'discover-dong-anh-community-culture-hub', 'name': 'Văn hóa', 'icon': '🏛️'},
  ];

  @override
  void initState() {
    super.initState();
    _pageController = PageController(viewportFraction: 0.86);
    _loadData();
  }

  @override
  void dispose() {
    _mapAnimationController?.stop();
    _mapAnimationController?.dispose();
    _searchController.dispose();
    _pageController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final data = await ApiService.getAllEateries();
      if (!mounted) return;
      _allEateries = data;
      _applyFilter();
    } catch (e) {
      debugPrint('Lỗi tải dữ liệu bản đồ: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _applyFilter() {
    final query = _searchController.text.trim().toLowerCase();

    final result = _allEateries.where((eat) {
      final catSlug = eat['category']?['slug']?.toString() ?? '';
      if (_selectedCategorySlug != 'all' && catSlug != _selectedCategorySlug) {
        return false;
      }

      if (query.isNotEmpty) {
        final name = eat['name']?.toString().toLowerCase() ?? '';
        final address = eat['address']?.toString().toLowerCase() ?? '';
        final catName = eat['category']?['name']?.toString().toLowerCase() ?? '';
        if (!name.contains(query) && !address.contains(query) && !catName.contains(query)) {
          return false;
        }
      }
      return true;
    }).toList();

    result.sort((a, b) {
      final aFeatured = (a['is_featured'] == true || a['is_featured'] == 1) ? 1 : 0;
      final bFeatured = (b['is_featured'] == true || b['is_featured'] == 1) ? 1 : 0;
      if (aFeatured != bFeatured) return bFeatured.compareTo(aFeatured);

      final aHasImg = (a['image_path']?.toString().isNotEmpty ?? false) ? 1 : 0;
      final bHasImg = (b['image_path']?.toString().isNotEmpty ?? false) ? 1 : 0;
      return bHasImg.compareTo(aHasImg);
    });

    _filteredEateries = result;
    _displayedEateries = result.take(60).toList();
    _selectedEateryIndex = 0;
    _rebuildMarkers();

    if (_pageController.hasClients && _displayedEateries.isNotEmpty) {
      _pageController.jumpToPage(0);
    }
  }

  void _rebuildMarkers() {
    final markers = <Marker>[];
    Marker? activeSelectedMarker;

    for (int i = 0; i < _displayedEateries.length; i++) {
      final eatery = _displayedEateries[i];
      final double? lat = double.tryParse(eatery['latitude']?.toString() ?? '');
      final double? lng = double.tryParse(eatery['longitude']?.toString() ?? '');
      if (lat == null || lng == null) continue;

      final catSlug = eatery['category']?['slug']?.toString();
      final color = _getCategoryColor(catSlug);
      final iconStr = _getCategoryIcon(catSlug);
      final isSelected = i == _selectedEateryIndex;
      final name = eatery['name']?.toString() ?? '';

      if (isSelected) {
        activeSelectedMarker = Marker(
          point: LatLng(lat, lng),
          width: 140,
          height: 76,
          alignment: Alignment.center,
          child: GestureDetector(
            onTap: () => _onMarkerTapped(i, lat, lng),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFF0F172A),
                    borderRadius: BorderRadius.circular(8),
                    boxShadow: const [
                      BoxShadow(
                        color: Colors.black26,
                        blurRadius: 4,
                        offset: Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Text(
                    name,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 10.5,
                      fontWeight: FontWeight.bold,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                const SizedBox(height: 3),
                Stack(
                  alignment: Alignment.center,
                  children: [
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: color.withValues(alpha: 0.25),
                        border: Border.all(color: color.withValues(alpha: 0.6), width: 1.5),
                      ),
                    ),
                    Container(
                      width: 36,
                      height: 36,
                      decoration: BoxDecoration(
                        color: color,
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.white, width: 2.5),
                        boxShadow: [
                          BoxShadow(
                            color: color.withValues(alpha: 0.5),
                            blurRadius: 8,
                            spreadRadius: 1,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Center(
                        child: Text(
                          iconStr,
                          style: const TextStyle(fontSize: 16),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      } else {
        markers.add(
          Marker(
            point: LatLng(lat, lng),
            width: 28,
            height: 28,
            alignment: Alignment.center,
            child: GestureDetector(
              onTap: () => _onMarkerTapped(i, lat, lng),
              child: Container(
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.35),
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: Colors.white.withValues(alpha: 0.7),
                    width: 1.5,
                  ),
                ),
                child: Center(
                  child: Text(
                    iconStr,
                    style: const TextStyle(fontSize: 12),
                  ),
                ),
              ),
            ),
          ),
        );
      }
    }

    if (activeSelectedMarker != null) {
      markers.add(activeSelectedMarker);
    }

    if (_userLocation != null) {
      markers.add(
        Marker(
          point: _userLocation!,
          width: 32,
          height: 32,
          child: const _UserLocationDot(),
        ),
      );
    }

    _cachedMarkers = markers;
  }

  void _onMarkerTapped(int index, double lat, double lng) {
    if (_selectedEateryIndex == index) return;

    setState(() {
      _selectedEateryIndex = index;
      _rebuildMarkers();
    });

    _animatedMapMove(LatLng(lat, lng), 15.2);

    if (_pageController.hasClients) {
      _isProgrammaticScroll = true;
      _pageController.animateToPage(
        index,
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeOutCubic,
      ).then((_) {
        _isProgrammaticScroll = false;
      });
    }
  }

  void _onCardPageChanged(int index) {
    if (_isProgrammaticScroll) return;
    if (index >= 0 && index < _displayedEateries.length) {
      if (_selectedEateryIndex == index) return;
      final eatery = _displayedEateries[index];
      final double? lat = double.tryParse(eatery['latitude']?.toString() ?? '');
      final double? lng = double.tryParse(eatery['longitude']?.toString() ?? '');

      setState(() {
        _selectedEateryIndex = index;
        _rebuildMarkers();
      });

      if (lat != null && lng != null) {
        _animatedMapMove(LatLng(lat, lng), 15.2);
      }
    }
  }

  void _animatedMapMove(LatLng destLocation, double destZoom) {
    _mapAnimationController?.stop();
    _mapAnimationController?.dispose();

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
      duration: const Duration(milliseconds: 400),
      vsync: this,
    );
    _mapAnimationController = controller;

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
        if (_mapAnimationController == controller) {
          _mapAnimationController = null;
        }
        controller.dispose();
      }
    });

    controller.forward();
  }

  Future<void> _getLocation() async {
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) return;
      }

      final position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.medium,
      );

      if (mounted) {
        setState(() {
          _userLocation = LatLng(position.latitude, position.longitude);
          _rebuildMarkers();
        });
        _animatedMapMove(_userLocation!, 15.5);
      }
    } catch (e) {
      debugPrint('Lỗi vị trí: $e');
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

  Color _getCategoryColor(String? slug) {
    switch (slug) {
      case 'hanh-trinh-di-san':
        return const Color(0xFFB45309);
      case 'smart-education-map':
        return const Color(0xFF0284C7);
      case 'wellness-care':
        return const Color(0xFF10B981);
      case 'stay-in-dong-anh':
        return const Color(0xFF8B5CF6);
      case 'dong-anh-market':
        return const Color(0xFFF59E0B);
      case 'dong-anh-food-map':
        return const Color(0xFFEF4444);
      case 'discover-dong-anh-community-culture-hub':
        return const Color(0xFFEC4899);
      default:
        return const Color(0xFF0284C7);
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
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: SafeArea(
        child: Stack(
          children: [
            // 1. LAYER BẢN ĐỒ (Hoặc Danh Sách Toàn Màn Hình)
            Positioned.fill(
              child: _isListView ? _buildListViewMode() : _buildMapViewMode(),
            ),

            // 2. LAYER ĐIỀU KHIỂN NỔI PHÍA TRÊN (Search & Category Pills)
            Positioned(
              top: 10,
              left: 12,
              right: 12,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildFloatingSearchBar(),
                  const SizedBox(height: 8),
                  _buildFloatingCategoryBar(),
                ],
              ),
            ),

            // 3. LAYER NÚT TIỆN ÍCH NỔI BÊN PHẢI (GPS & Đổi Chế Độ Xem)
            if (!_isListView)
              Positioned(
                right: 14,
                bottom: _displayedEateries.isNotEmpty ? 165 : 20,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    FloatingActionButton.small(
                      heroTag: 'map_mode_toggle',
                      onPressed: () {
                        setState(() => _isListView = true);
                      },
                      backgroundColor: Colors.white,
                      foregroundColor: const Color(0xFF0284C7),
                      elevation: 4,
                      child: const Icon(Icons.format_list_bulleted_rounded, size: 20),
                    ),
                    const SizedBox(height: 10),
                    FloatingActionButton.small(
                      heroTag: 'map_gps_locate',
                      onPressed: _getLocation,
                      backgroundColor: Colors.white,
                      foregroundColor: const Color(0xFF0284C7),
                      elevation: 4,
                      child: const Icon(Icons.my_location_rounded, size: 20),
                    ),
                  ],
                ),
              ),

            // 4. BĂNG CHUYỀN THẺ ĐỊA ĐIỂM NỔI PHÍA DƯỚI (Bottom Card Carousel)
            if (!_isListView && _displayedEateries.isNotEmpty)
              Positioned(
                left: 0,
                right: 0,
                bottom: 14,
                height: 135,
                child: _buildBottomCardCarousel(),
              ),

            // 5. LOADING OVERLAY
            if (_isLoading)
              const Positioned.fill(
                child: CustomPulseLoader(
                  message: 'Đang kết nối dữ liệu bản đồ...',
                  icon: Icons.map_rounded,
                  primaryColor: Color(0xFF0284C7),
                ),
              ),
          ],
        ),
      ),
    );
  }

  // --- WIDGET CẤU PHẦN BẢN ĐỒ ---
  Widget _buildMapViewMode() {
    return FlutterMap(
      mapController: _mapController,
      options: MapOptions(
        initialCenter: _dongAnhCenter,
        initialZoom: 12.8,
        minZoom: 10.0,
        maxZoom: 18.0,
        interactionOptions: const InteractionOptions(
          flags: InteractiveFlag.all & ~InteractiveFlag.rotate,
        ),
      ),
      children: [
        TileLayer(
          urlTemplate: 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png',
          subdomains: const ['a', 'b', 'c', 'd'],
          userAgentPackageName: 'com.donganh.discovery',
          maxZoom: 18,
          keepBuffer: 2,
          panBuffer: 1,
        ),
        MarkerLayer(markers: _cachedMarkers),
      ],
    );
  }

  // --- WIDGET CHẾ ĐỘ XEM DANH SÁCH ---
  Widget _buildListViewMode() {
    return Container(
      color: const Color(0xFFF8FAFC),
      padding: const EdgeInsets.only(top: 115),
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              children: [
                Text(
                  'KẾT QUẢ (${_filteredEateries.length} địa điểm)',
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF64748B),
                    letterSpacing: 0.5,
                  ),
                ),
                const Spacer(),
                TextButton.icon(
                  onPressed: () {
                    setState(() => _isListView = false);
                  },
                  icon: const Icon(Icons.map_rounded, size: 16),
                  label: const Text('Xem trên bản đồ', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                  style: TextButton.styleFrom(
                    foregroundColor: const Color(0xFF0284C7),
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: _filteredEateries.isEmpty
                ? const Center(
                    child: Text(
                      'Không tìm thấy địa điểm nào phù hợp.',
                      style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
                    ),
                  )
                : ListView.separated(
                    padding: const EdgeInsets.fromLTRB(14, 0, 14, 80),
                    itemCount: _filteredEateries.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 10),
                    itemBuilder: (context, index) {
                      final item = _filteredEateries[index];
                      return _buildPlaceCard(item, index);
                    },
                  ),
          ),
        ],
      ),
    );
  }

  // --- THANH TÌM KIẾM NỔI ---
  Widget _buildFloatingSearchBar() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: TextField(
        controller: _searchController,
        onChanged: (_) {
          setState(() {
            _applyFilter();
          });
        },
        style: const TextStyle(fontSize: 13.5),
        decoration: InputDecoration(
          hintText: 'Tìm địa danh, trường học, ẩm thực, OCOP...',
          hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
          prefixIcon: const Icon(Icons.search_rounded, color: Color(0xFF0284C7), size: 22),
          suffixIcon: _searchController.text.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear_rounded, size: 18, color: Color(0xFF94A3B8)),
                  onPressed: () {
                    _searchController.clear();
                    setState(() => _applyFilter());
                  },
                )
              : null,
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
        ),
      ),
    );
  }

  // --- THANH CUỘN BỘ LỌC DANH MỤC NỔI ---
  Widget _buildFloatingCategoryBar() {
    return SizedBox(
      height: 38,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: _categoryTabs.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          final tab = _categoryTabs[index];
          final slug = tab['slug']!;
          final isSelected = _selectedCategorySlug == slug;
          final color = _getCategoryColor(slug);

          return GestureDetector(
            onTap: () {
              setState(() {
                _selectedCategorySlug = slug;
                _applyFilter();
              });
            },
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: isSelected ? color : Colors.white,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: isSelected
                        ? color.withValues(alpha: 0.35)
                        : Colors.black.withValues(alpha: 0.05),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ],
                border: Border.all(
                  color: isSelected ? Colors.transparent : const Color(0xFFE2E8F0),
                ),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(tab['icon']!, style: const TextStyle(fontSize: 13)),
                  const SizedBox(width: 6),
                  Text(
                    tab['name']!,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                      color: isSelected ? Colors.white : const Color(0xFF334155),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  // --- BĂNG CHUYỀN THẺ ĐỊA ĐIỂM (BOTTOM CAROUSEL) ---
  Widget _buildBottomCardCarousel() {
    return PageView.builder(
      controller: _pageController,
      itemCount: _displayedEateries.length,
      onPageChanged: _onCardPageChanged,
      itemBuilder: (context, index) {
        final eatery = _displayedEateries[index];
        final isSelected = index == _selectedEateryIndex;

        return AnimatedScale(
          scale: isSelected ? 1.0 : 0.95,
          duration: const Duration(milliseconds: 250),
          child: _buildCarouselCard(eatery, index),
        );
      },
    );
  }

  // --- THẺ ĐỊA ĐIỂM TRONG BĂNG CHUYỀN ---
  Widget _buildCarouselCard(dynamic eatery, int index) {
    final catSlug = eatery['category']?['slug']?.toString();
    final color = _getCategoryColor(catSlug);
    final icon = _getCategoryIcon(catSlug);
    final double? lat = double.tryParse(eatery['latitude']?.toString() ?? '');
    final double? lng = double.tryParse(eatery['longitude']?.toString() ?? '');

    final String imagePath = eatery['image_path'] ?? '';
    final String fullImgUrl = imagePath.startsWith('http')
        ? imagePath
        : (imagePath.isNotEmpty ? 'https://donganhdiscovery.xadonganh.com/$imagePath' : '');

    return GestureDetector(
      onTap: () => _showEateryDetails(eatery),
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.1),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Row(
          children: [
            // Ảnh đại diện
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Container(
                width: 90,
                height: 90,
                color: color.withValues(alpha: 0.1),
                child: fullImgUrl.isNotEmpty
                    ? Image.network(
                        fullImgUrl,
                        fit: BoxFit.cover,
                        cacheWidth: 180,
                        filterQuality: FilterQuality.low,
                        errorBuilder: (_, __, ___) => Center(child: Text(icon, style: const TextStyle(fontSize: 32))),
                      )
                    : Center(child: Text(icon, style: const TextStyle(fontSize: 32))),
              ),
            ),
            const SizedBox(width: 12),

            // Thông tin địa điểm
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Danh mục tag
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: color.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          eatery['category']?['name'] ?? 'Địa điểm',
                          style: TextStyle(color: color, fontSize: 10, fontWeight: FontWeight.bold),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const Spacer(),
                      const Icon(Icons.star_rounded, color: Colors.amber, size: 14),
                      const SizedBox(width: 2),
                      Text(
                        '${eatery['rating_avg'] ?? '5.0'}',
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),

                  // Tên địa điểm
                  Text(
                    eatery['name'] ?? '',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: Color(0xFF0F172A)),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 2),

                  // Địa chỉ
                  Text(
                    eatery['address'] ?? 'Đông Anh, Hà Nội',
                    style: const TextStyle(color: Color(0xFF64748B), fontSize: 11),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 6),

                  // Nút thao tác nhanh
                  Row(
                    children: [
                      if (lat != null && lng != null)
                        InkWell(
                          onTap: () => _openGoogleMapsDirections(lat, lng),
                          borderRadius: BorderRadius.circular(8),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF1F5F9),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.directions_rounded, size: 13, color: Color(0xFF0284C7)),
                                SizedBox(width: 4),
                                Text('Chỉ đường', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: Color(0xFF0284C7))),
                              ],
                            ),
                          ),
                        ),
                      const Spacer(),
                      const Text(
                        'Chi tiết ➔',
                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
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

  // --- THẺ ĐỊA ĐIỂM DẠNG DANH SÁCH ---
  Widget _buildPlaceCard(dynamic eatery, int index) {
    final catSlug = eatery['category']?['slug']?.toString();
    final color = _getCategoryColor(catSlug);
    final icon = _getCategoryIcon(catSlug);
    final double? lat = double.tryParse(eatery['latitude']?.toString() ?? '');
    final double? lng = double.tryParse(eatery['longitude']?.toString() ?? '');

    final String imagePath = eatery['image_path'] ?? '';
    final String fullImgUrl = imagePath.startsWith('http')
        ? imagePath
        : (imagePath.isNotEmpty ? 'https://donganhdiscovery.xadonganh.com/$imagePath' : '');

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: Container(
                  width: 50,
                  height: 50,
                  color: color.withValues(alpha: 0.12),
                  child: fullImgUrl.isNotEmpty
                      ? Image.network(
                          fullImgUrl,
                          fit: BoxFit.cover,
                          cacheWidth: 100,
                          filterQuality: FilterQuality.low,
                          errorBuilder: (_, __, ___) => Center(child: Text(icon, style: const TextStyle(fontSize: 22))),
                        )
                      : Center(child: Text(icon, style: const TextStyle(fontSize: 22))),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      eatery['name'] ?? '',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 3),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: color.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            eatery['category']?['name'] ?? 'Địa điểm',
                            style: TextStyle(color: color, fontSize: 10, fontWeight: FontWeight.bold),
                          ),
                        ),
                        const SizedBox(width: 8),
                        const Icon(Icons.star_rounded, color: Colors.amber, size: 14),
                        const SizedBox(width: 2),
                        Text(
                          '${eatery['rating_avg'] ?? '5.0'}',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              const Icon(Icons.location_on_outlined, size: 14, color: Color(0xFF94A3B8)),
              const SizedBox(width: 4),
              Expanded(
                child: Text(
                  eatery['address'] ?? 'Đông Anh, Hà Nội',
                  style: const TextStyle(color: Color(0xFF64748B), fontSize: 11.5),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              if (lat != null && lng != null)
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _openGoogleMapsDirections(lat, lng),
                    icon: const Icon(Icons.directions_rounded, size: 15),
                    label: const Text('Chỉ đường', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF0284C7),
                      side: const BorderSide(color: Color(0xFF0284C7)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      padding: const EdgeInsets.symmetric(vertical: 8),
                    ),
                  ),
                ),
              if (lat != null && lng != null) const SizedBox(width: 8),
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () => _showEateryDetails(eatery),
                  icon: const Icon(Icons.info_outline_rounded, size: 15),
                  label: const Text('Chi tiết', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0284C7),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    padding: const EdgeInsets.symmetric(vertical: 8),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // --- MODAL CHI TIẾT ĐỊA ĐIỂM ---
  void _showEateryDetails(dynamic eatery) {
    final double? lat = double.tryParse(eatery['latitude']?.toString() ?? '');
    final double? lng = double.tryParse(eatery['longitude']?.toString() ?? '');
    final catSlug = eatery['category']?['slug']?.toString();
    final color = _getCategoryColor(catSlug);
    final icon = _getCategoryIcon(catSlug);

    final String imagePath = eatery['image_path'] ?? '';
    final String fullImgUrl = imagePath.startsWith('http')
        ? imagePath
        : (imagePath.isNotEmpty ? 'https://donganhdiscovery.xadonganh.com/$imagePath' : '');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                    color: const Color(0xFFCBD5E1),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              if (fullImgUrl.isNotEmpty)
                ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: Image.network(
                    fullImgUrl,
                    height: 160,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    cacheWidth: 400,
                    filterQuality: FilterQuality.low,
                    errorBuilder: (_, __, ___) => Container(
                      height: 120,
                      color: color.withValues(alpha: 0.15),
                      child: Center(child: Text(icon, style: const TextStyle(fontSize: 40))),
                    ),
                  ),
                )
              else
                Container(
                  height: 100,
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Center(child: Text(icon, style: const TextStyle(fontSize: 40))),
                ),
              const SizedBox(height: 14),

              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: color.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      eatery['category']?['name'] ?? 'Địa điểm',
                      style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 11),
                    ),
                  ),
                  const Spacer(),
                  const Icon(Icons.star_rounded, color: Colors.amber, size: 16),
                  const SizedBox(width: 3),
                  Text(
                    '${eatery['rating_avg'] ?? '5.0'}',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ],
              ),
              const SizedBox(height: 8),

              Text(
                eatery['name'] ?? 'Địa điểm',
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
              ),
              const SizedBox(height: 4),

              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.location_on_outlined, size: 16, color: Color(0xFF64748B)),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      eatery['address'] ?? 'Đông Anh, Hà Nội',
                      style: const TextStyle(color: Color(0xFF64748B), fontSize: 12.5),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 18),

              Row(
                children: [
                  if (lat != null && lng != null)
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () {
                          Navigator.pop(context);
                          _openGoogleMapsDirections(lat, lng);
                        },
                        icon: const Icon(Icons.directions_rounded, size: 16),
                        label: const Text('Chỉ đường', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5)),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          side: const BorderSide(color: Color(0xFF0284C7)),
                          foregroundColor: const Color(0xFF0284C7),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  if (lat != null && lng != null) const SizedBox(width: 10),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.pop(context);
                        _openCheckinDialog(eatery);
                      },
                      icon: const Icon(Icons.camera_alt_rounded, size: 16),
                      label: const Text('Check-in', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0284C7),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),

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
                  icon: const Icon(Icons.arrow_forward_rounded, size: 16),
                  label: const Text('Xem toàn bộ chi tiết ➔', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0F172A),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ),
              const SizedBox(height: 10),
            ],
          ),
        );
      },
    );
  }

  void _openCheckinDialog(dynamic eatery) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
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
            if (mounted && context.mounted) {
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
}

// --- WIDGET ĐỊNH VỊ VỊ TRÍ NGƯỜI DÙNG TÁCH BIỆT (KHÔNG GÂY RE-RENDER TOÀN BỘ MAP) ---
class _UserLocationDot extends StatelessWidget {
  const _UserLocationDot();

  @override
  Widget build(BuildContext context) {
    return Stack(
      alignment: Alignment.center,
      children: [
        Container(
          width: 32,
          height: 32,
          decoration: BoxDecoration(
            color: const Color(0xFF0284C7).withValues(alpha: 0.25),
            shape: BoxShape.circle,
          ),
        ),
        Container(
          width: 14,
          height: 14,
          decoration: BoxDecoration(
            color: const Color(0xFF0284C7),
            shape: BoxShape.circle,
            border: Border.all(color: Colors.white, width: 2.5),
            boxShadow: const [
              BoxShadow(color: Colors.black26, blurRadius: 4),
            ],
          ),
        ),
      ],
    );
  }
}

// --- MODAL CHECK-IN NHANH ---
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

    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom + 20,
        left: 20,
        right: 20,
        top: 20,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Center(
            child: Container(
              width: 40,
              height: 4,
              margin: const EdgeInsets.only(bottom: 14),
              decoration: BoxDecoration(
                color: const Color(0xFFCBD5E1),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          Text(
            'Check-in tại ${widget.eateryName}',
            style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
          ),
          const SizedBox(height: 14),
          if (isGuest) ...[
            TextField(
              controller: _guestNameController,
              decoration: InputDecoration(
                labelText: 'Tên của bạn',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              ),
            ),
            const SizedBox(height: 12),
          ],
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(5, (index) {
              return IconButton(
                icon: Icon(
                  index < _rating ? Icons.star_rounded : Icons.star_border_rounded,
                  color: Colors.amber,
                  size: 32,
                ),
                onPressed: () {
                  setState(() => _rating = index + 1);
                },
              );
            }),
          ),
          const SizedBox(height: 10),
          TextField(
            controller: _commentController,
            maxLines: 3,
            decoration: InputDecoration(
              hintText: 'Cảm nghĩ hoặc trải nghiệm của bạn tại đây...',
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              contentPadding: const EdgeInsets.all(12),
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              OutlinedButton.icon(
                onPressed: () => _pickImage(ImageSource.gallery),
                icon: const Icon(Icons.photo_library_rounded, size: 16),
                label: const Text('Thêm ảnh', style: TextStyle(fontSize: 12)),
                style: OutlinedButton.styleFrom(
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
              const SizedBox(width: 8),
              OutlinedButton.icon(
                onPressed: () => _pickImage(ImageSource.camera),
                icon: const Icon(Icons.camera_alt_rounded, size: 16),
                label: const Text('Chụp ảnh', style: TextStyle(fontSize: 12)),
                style: OutlinedButton.styleFrom(
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ],
          ),
          if (_imagePath != null) ...[
            const SizedBox(height: 10),
            ClipRRect(
              borderRadius: BorderRadius.circular(10),
              child: Image.file(
                File(_imagePath!),
                height: 100,
                width: double.infinity,
                fit: BoxFit.cover,
              ),
            ),
          ],
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _isSending
                ? null
                : () async {
                    setState(() => _isSending = true);
                    await widget.onSubmit(
                      _rating,
                      _commentController.text,
                      isGuest ? _guestNameController.text : null,
                      _imagePath,
                    );
                    if (mounted && context.mounted) {
                      setState(() => _isSending = false);
                      Navigator.pop(context);
                    }
                  },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF0284C7),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 13),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: _isSending
                ? const SizedBox(
                    height: 18,
                    width: 18,
                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                  )
                : const Text('Gửi Check-in', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
          ),
        ],
      ),
    );
  }
}
