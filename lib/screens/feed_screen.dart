import 'dart:io';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:camera/camera.dart';
import 'package:geolocator/geolocator.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:share_plus/share_plus.dart';
import '../services/api_service.dart';
import '../widgets/public_profile_modal.dart';
import '../widgets/custom_loader.dart';
import '../widgets/squircle_helper.dart';

class FeedScreen extends StatefulWidget {
  final dynamic targetPostId;
  final String? targetTitle;

  const FeedScreen({
    super.key,
    this.targetPostId,
    this.targetTitle,
  });

  @override
  State<FeedScreen> createState() => FeedScreenState();
}

class FeedScreenState extends State<FeedScreen> with WidgetsBindingObserver {
  List<dynamic> _feedItems = [];
  bool _isLoading = false;

  // Locket-style Camera Check-in fields
  CameraController? _cameraController;
  List<CameraDescription>? _cameras;
  bool _isCameraInitialized = false;
  int _selectedCameraIndex = 0;

  // Camera Zoom States
  double _minZoomLevel = 1.0;
  double _maxZoomLevel = 1.0;
  double _currentZoomLevel = 1.0;
  double _baseZoomLevel = 1.0;

  List<dynamic> _eateries = [];
  int? _selectedEateryId;
  int _rating = 5;
  final _commentController = TextEditingController();
  final _guestNameController = TextEditingController();
  bool _isSendingCheckin = false;
  String? _checkinImagePath;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _loadFeed();
    _loadEateries();
    _initializeCamera();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _cameraController?.dispose();
    _commentController.dispose();
    _guestNameController.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    final CameraController? cameraController = _cameraController;

    if (cameraController == null || !cameraController.value.isInitialized) {
      if (state == AppLifecycleState.resumed) {
        _initializeCamera();
      }
      return;
    }

    if (state == AppLifecycleState.inactive || state == AppLifecycleState.paused) {
      setState(() {
        _isCameraInitialized = false;
      });
      _cameraController?.dispose();
      _cameraController = null;
    } else if (state == AppLifecycleState.resumed) {
      _initializeCamera();
    }
  }

  Future<void> pauseCamera() async {
    if (_cameraController != null) {
      if (mounted) {
        setState(() {
          _isCameraInitialized = false;
        });
      }
      await _cameraController?.dispose();
      _cameraController = null;
    }
  }

  Future<void> resumeCamera() async {
    if (_cameraController == null || !_isCameraInitialized) {
      await _initializeCamera();
    }
  }

  Future<void> refreshCamera() async {
    await resumeCamera();
  }

  Future<void> _initializeCamera() async {
    try {
      if (_cameraController != null) {
        await _cameraController!.dispose();
        _cameraController = null;
      }
      _cameras = await availableCameras();
      if (_cameras != null && _cameras!.isNotEmpty) {
        final controller = CameraController(
          _cameras![_selectedCameraIndex % _cameras!.length],
          ResolutionPreset.max,
          enableAudio: false,
          imageFormatGroup: ImageFormatGroup.jpeg,
        );
        _cameraController = controller;

        await controller.initialize();
        try {
          _minZoomLevel = await controller.getMinZoomLevel();
          _maxZoomLevel = await controller.getMaxZoomLevel();
          _currentZoomLevel = _minZoomLevel;
        } catch (_) {}

        if (mounted) {
          setState(() {
            _isCameraInitialized = true;
          });
        }
      }
    } catch (e) {
      debugPrint('Lỗi khởi tạo Camera: $e');
      if (mounted) {
        setState(() {
          _isCameraInitialized = false;
        });
      }
    }
  }

  Future<void> _setZoomLevel(double zoom) async {
    if (_cameraController == null || !_isCameraInitialized) return;
    final clamped = zoom.clamp(_minZoomLevel, _maxZoomLevel);
    try {
      await _cameraController!.setZoomLevel(clamped);
      if (mounted) {
        setState(() {
          _currentZoomLevel = clamped;
        });
      }
    } catch (e) {
      debugPrint('Lỗi setZoomLevel: $e');
    }
  }

  Future<void> _switchCamera() async {
    if (_cameras == null || _cameras!.isEmpty) return;

    _selectedCameraIndex = (_selectedCameraIndex + 1) % _cameras!.length;
    setState(() {
      _isCameraInitialized = false;
    });

    await _cameraController?.dispose();
    _cameraController = CameraController(
      _cameras![_selectedCameraIndex],
      ResolutionPreset.max,
      enableAudio: false,
      imageFormatGroup: ImageFormatGroup.jpeg,
    );

    try {
      await _cameraController!.initialize();
      try {
        _minZoomLevel = await _cameraController!.getMinZoomLevel();
        _maxZoomLevel = await _cameraController!.getMaxZoomLevel();
        _currentZoomLevel = _minZoomLevel;
      } catch (_) {}

      if (mounted) {
        setState(() {
          _isCameraInitialized = true;
        });
      }
    } catch (e) {
      debugPrint('Lỗi chuyển camera: $e');
    }
  }

  Future<void> _takeCheckinPicture() async {
    if (_cameraController == null || !_cameraController!.value.isInitialized) {
      return;
    }
    try {
      final XFile photo = await _cameraController!.takePicture();
      setState(() {
        _checkinImagePath = photo.path;
      });
    } catch (e) {
      debugPrint('Lỗi chụp ảnh: $e');
    }
  }

  Future<void> _loadFeed() async {
    setState(() {
      _isLoading = true;
    });
    try {
      final items = await ApiService.getFeed();
      if (mounted) {
        setState(() {
          _feedItems = List<dynamic>.from(items);

          if (widget.targetPostId != null || (widget.targetTitle != null && widget.targetTitle!.isNotEmpty)) {
            final targetIdStr = widget.targetPostId?.toString();
            final targetTitleClean = widget.targetTitle?.toLowerCase().trim();

            int targetIdx = -1;
            for (int i = 0; i < _feedItems.length; i++) {
              final p = _feedItems[i];
              final pId = p['id']?.toString();
              final pTitle = (p['comment'] ?? p['eatery']?['name'] ?? '').toString().toLowerCase().trim();

              if ((targetIdStr != null && pId == targetIdStr) ||
                  (targetTitleClean != null && targetTitleClean.isNotEmpty && pTitle.contains(targetTitleClean))) {
                targetIdx = i;
                break;
              }
            }

            if (targetIdx > 0) {
              final targetItem = _feedItems.removeAt(targetIdx);
              _feedItems.insert(0, targetItem);
            }
          }

          _isLoading = false;
        });
      }
    } catch (e) {
      debugPrint('Feed API fetch error: $e');
      if (mounted) {
        setState(() {
          _feedItems = [];
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _loadEateries() async {
    try {
      final eateries = await ApiService.getAllEateries();
      if (mounted) {
        setState(() {
          _eateries = List<dynamic>.from(eateries);
          // Mặc định không tự gán cơ sở — chỉ gán khi người dùng chủ động chọn
        });
      }
    } catch (e) {
      debugPrint('Eateries API fetch error: $e');
    }
  }

  Future<void> _autoDetectCurrentLocationAndSelectEatery() async {
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) return;
      }
      if (permission == LocationPermission.deniedForever) return;

      final position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );

      if (_eateries.isEmpty) return;

      int? nearestId;
      double minDistance = double.infinity;

      for (var eatery in _eateries) {
        final double? lat = double.tryParse(eatery['latitude']?.toString() ?? '');
        final double? lng = double.tryParse(eatery['longitude']?.toString() ?? '');
        if (lat != null && lng != null) {
          final distance = Geolocator.distanceBetween(
            position.latitude,
            position.longitude,
            lat,
            lng,
          );
          if (distance < minDistance) {
            minDistance = distance;
            nearestId = eatery['id'];
          }
        }
      }

      if (nearestId != null && mounted) {
        setState(() {
          _selectedEateryId = nearestId;
        });
      }
    } catch (e) {
      debugPrint('Lỗi tự động lấy vị trí hiện tại: $e');
    }
  }

  Future<void> _pickCheckinImageFromGallery() async {
    try {
      final picker = ImagePicker();
      final pickedFile = await picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1000,
        maxHeight: 1000,
        imageQuality: 80,
      );
      if (pickedFile != null) {
        setState(() {
          _checkinImagePath = pickedFile.path;
        });
      }
    } catch (e) {
      debugPrint('Lỗi chọn ảnh: $e');
    }
  }

  Future<void> _submitCameraCheckin() async {
    setState(() {
      _isSendingCheckin = true;
    });

    final isGuest = !ApiService.isAuthenticated;
    final res = await ApiService.storeCheckin(
      eateryId: _selectedEateryId,
      rating: _rating,
      comment: _commentController.text,
      guestName: isGuest ? _guestNameController.text : null,
      imagePath: _checkinImagePath,
    );

    if (mounted) {
      setState(() {
        _isSendingCheckin = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Thành công'),
          backgroundColor: res['success'] == true ? Colors.green : Colors.red,
        ),
      );
      if (res['success'] == true) {
        _commentController.clear();
        _guestNameController.clear();
        setState(() {
          _checkinImagePath = null;
          _rating = 5;
        });
        _loadFeed();
      }
    }
  }

  void _openLocationPickerModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: const Color(0xFF1E293B),
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (modalCtx) {
        String searchQuery = '';
        return StatefulBuilder(
          builder: (ctx, setModalState) {
            final filteredList = _eateries.where((eatery) {
              final name = (eatery['name'] ?? '').toString().toLowerCase();
              final commune = (eatery['commune']?['name'] ?? '').toString().toLowerCase();
              return name.contains(searchQuery.toLowerCase()) || commune.contains(searchQuery.toLowerCase());
            }).toList();

            return Container(
              height: MediaQuery.of(context).size.height * 0.7,
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        '📍 Chọn địa điểm check-in',
                        style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close, color: Colors.white70),
                        onPressed: () => Navigator.pop(modalCtx),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  TextField(
                    style: const TextStyle(color: Colors.white, fontSize: 13),
                    decoration: InputDecoration(
                      hintText: '🔍 Tìm tên địa điểm, cơ sở...',
                      hintStyle: const TextStyle(color: Colors.white54, fontSize: 13),
                      prefixIcon: const Icon(Icons.search, color: Color(0xFF0EA5E9)),
                      filled: true,
                      fillColor: const Color(0xFF334155),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide.none,
                      ),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    ),
                    onChanged: (val) {
                      setModalState(() {
                        searchQuery = val;
                      });
                    },
                  ),
                  const SizedBox(height: 12),
                  Expanded(
                    child: filteredList.isEmpty
                        ? const Center(
                            child: Text(
                              'Không tìm thấy địa điểm phù hợp',
                              style: TextStyle(color: Colors.white54, fontSize: 13),
                            ),
                          )
                        : ListView.separated(
                            itemCount: filteredList.length,
                            separatorBuilder: (_, __) => const Divider(color: Color(0xFF334155), height: 1),
                            itemBuilder: (context, idx) {
                              final item = filteredList[idx];
                              final isSelected = _selectedEateryId == item['id'];
                              return ListTile(
                                leading: const CircleAvatar(
                                  backgroundColor: Color(0xFF334155),
                                  child: Icon(Icons.storefront_rounded, color: Color(0xFF0EA5E9), size: 20),
                                ),
                                title: Text(
                                  item['name'] ?? '',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                                    fontSize: 14,
                                  ),
                                ),
                                subtitle: Text(
                                  item['commune']?['name'] ?? 'Đông Anh',
                                  style: const TextStyle(color: Colors.white60, fontSize: 11),
                                ),
                                trailing: isSelected
                                    ? const Icon(Icons.check_circle, color: Color(0xFF0EA5E9))
                                    : null,
                                onTap: () {
                                  setState(() {
                                    _selectedEateryId = item['id'];
                                  });
                                  Navigator.pop(modalCtx);
                                },
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
    );
  }

  void _react(int id, String emoji, String type) async {
    final res = await ApiService.reactToCheckin(id, emoji, type);
    if (res['success'] == true && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Bạn đã thả $emoji'),
          duration: const Duration(seconds: 1),
        ),
      );
      _loadFeed();
    }
  }

  void _openCommentsBottomSheet(dynamic item) {
    final commentableId = item['id'];
    final comments = item['comments'] as List? ?? [];
    final textController = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom,
                left: 16,
                right: 16,
                top: 16,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Bình luận (${comments.length})',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(context),
                      ),
                    ],
                  ),
                  const Divider(),
                  Container(
                    constraints: BoxConstraints(
                      maxHeight: MediaQuery.of(context).size.height * 0.4,
                    ),
                    child: comments.isEmpty
                        ? const Center(
                            child: Padding(
                              padding: EdgeInsets.all(24.0),
                              child: Text('Chưa có bình luận nào. Hãy gửi bình luận đầu tiên!'),
                            ),
                          )
                        : ListView.builder(
                            shrinkWrap: true,
                            itemCount: comments.length,
                            itemBuilder: (context, idx) {
                              final comment = comments[idx];
                              return Padding(
                                padding: const EdgeInsets.symmetric(vertical: 8.0),
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    CircleAvatar(
                                      radius: 14,
                                      backgroundColor: const Color(0xFF0EA5E9).withValues(alpha: 0.1),
                                      child: Text(
                                        (comment['display_name'] ?? 'U').toString().substring(0, 1).toUpperCase(),
                                        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF0EA5E9)),
                                      ),
                                    ),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            comment['display_name'] ?? 'Ẩn danh',
                                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                                          ),
                                          const SizedBox(height: 2),
                                          Text(
                                            comment['content'] ?? '',
                                            style: const TextStyle(fontSize: 13, color: Colors.black87),
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
                  const Divider(),
                  Padding(
                    padding: const EdgeInsets.only(bottom: 16.0, top: 8),
                    child: Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: textController,
                            decoration: InputDecoration(
                              hintText: 'Viết bình luận...',
                              hintStyle: const TextStyle(fontSize: 13),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                              filled: true,
                              fillColor: Colors.grey[100],
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(24),
                                borderSide: BorderSide.none,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        IconButton(
                          icon: const Icon(Icons.send, color: Color(0xFF0EA5E9)),
                          onPressed: () async {
                            final text = textController.text.trim();
                            if (text.isEmpty) return;

                            final res = await ApiService.storeComment(
                              commentableId: commentableId,
                              commentableType: item['type'] == 'checkin'
                                  ? 'App\\Models\\Checkin'
                                  : 'App\\Models\\FoodTourDiary',
                              content: text,
                            );

                            if (res['success'] == true) {
                              textController.clear();
                              if (context.mounted) Navigator.pop(context);
                              _loadFeed();
                            }
                          },
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

  @override
  Widget build(BuildContext context) {
    const primaryColor = Color(0xFF0EA5E9);

    return Scaffold(
      appBar: null,
      backgroundColor: const Color(0xFF0F172A),
      body: SafeArea(
        child: _isLoading
            ? const CustomPulseLoader(
                message: 'Đang kết nối khoảnh khắc check-in...',
                icon: Icons.photo_camera_rounded,
                primaryColor: Color(0xFF0EA5E9),
              )
            : LayoutBuilder(
                builder: (context, constraints) {
                  final height = constraints.maxHeight;
                  return PageView.builder(
                    controller: PageController(keepPage: false),
                    scrollDirection: Axis.vertical,
                    itemCount: _feedItems.length + 1,
                    onPageChanged: (pageIndex) {
                      if (pageIndex == 0) {
                        resumeCamera();
                      } else {
                        pauseCamera();
                      }
                    },
                    itemBuilder: (context, index) {
                      if (index == 0) {
                        return _buildTikTokCameraPage(primaryColor, height);
                      }
                      final item = _feedItems[index - 1];
                      return _buildTikTokFeedCard(item, primaryColor, height);
                    },
                  );
                },
              ),
      ),
    );
  }

  Widget _buildTikTokCameraPage(Color primaryColor, double height) {
    final isGuest = !ApiService.isAuthenticated;

    return Container(
      height: height,
      padding: const EdgeInsets.all(10),
      child: Card(
        elevation: 6,
        shadowColor: const Color(0xFF0EA5E9).withValues(alpha: 0.25),
        shape: SquircleHelper.shape(
          radius: 22,
          side: BorderSide(color: const Color(0xFF0EA5E9).withValues(alpha: 0.2), width: 1.2),
        ),
        color: const Color(0xFF1E293B), // Dark card for camera page
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. Header Row
              Row(
                children: [
                  Container(
                    width: 8,
                    height: 8,
                    decoration: const BoxDecoration(
                      color: Color(0xFF10B981), // Emerald green
                      shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(width: 6),
                  const Text(
                    'LIVE CAMERA ACTIVE',
                    style: TextStyle(
                      color: Color(0xFF10B981),
                      fontWeight: FontWeight.bold,
                      fontSize: 10,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const Spacer(),
                  IconButton(
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                    icon: const Icon(Icons.refresh, color: Colors.white70, size: 18),
                    onPressed: _initializeCamera,
                    tooltip: 'Làm mới Camera',
                  ),
                ],
              ),
              const SizedBox(height: 8),

              // 2. Camera Viewport / Captured Photo Preview
              Expanded(
                flex: _checkinImagePath != null ? 3 : 5,
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.black,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFF334155), width: 1.5),
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        if (_checkinImagePath == null) ...[
                          // Real Camera Preview with Pinch-to-Zoom & Double Tap
                          if (_isCameraInitialized && _cameraController != null && _cameraController!.value.isInitialized)
                            GestureDetector(
                              onScaleStart: (details) {
                                _baseZoomLevel = _currentZoomLevel;
                              },
                              onScaleUpdate: (details) {
                                double newZoom = _baseZoomLevel * details.scale;
                                _setZoomLevel(newZoom);
                              },
                              onDoubleTap: () {
                                double target = (_currentZoomLevel > 1.5) ? _minZoomLevel : math.min(2.0, _maxZoomLevel);
                                _setZoomLevel(target);
                              },
                              child: FittedBox(
                                fit: BoxFit.cover,
                                child: SizedBox(
                                  width: _cameraController!.value.previewSize?.height ?? 720,
                                  height: _cameraController!.value.previewSize?.width ?? 1280,
                                  child: CameraPreview(_cameraController!),
                                ),
                              ),
                            )
                          else
                            InkWell(
                              onTap: _initializeCamera,
                              child: Center(
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    const CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                    const SizedBox(height: 10),
                                    const Text(
                                      'Đang kết nối Camera...',
                                      style: TextStyle(
                                        color: Colors.white,
                                        fontWeight: FontWeight.bold,
                                        fontSize: 12,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      'Chạm vào đây để tải lại',
                                      style: TextStyle(
                                        color: Colors.white.withValues(alpha: 0.6),
                                        fontSize: 10,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),

                          // Viewfinder corners
                          Positioned(
                            top: 10,
                            left: 10,
                            child: Container(
                              width: 14,
                              height: 14,
                              decoration: const BoxDecoration(
                                border: Border(
                                  top: BorderSide(color: Colors.white, width: 2),
                                  left: BorderSide(color: Colors.white, width: 2),
                                ),
                              ),
                            ),
                          ),
                          Positioned(
                            top: 10,
                            right: 10,
                            child: Container(
                              width: 14,
                              height: 14,
                              decoration: const BoxDecoration(
                                border: Border(
                                  top: BorderSide(color: Colors.white, width: 2),
                                  right: BorderSide(color: Colors.white, width: 2),
                                ),
                              ),
                            ),
                          ),
                          Positioned(
                            bottom: 10,
                            left: 10,
                            child: Container(
                              width: 14,
                              height: 14,
                              decoration: const BoxDecoration(
                                border: Border(
                                  bottom: BorderSide(color: Colors.white, width: 2),
                                  left: BorderSide(color: Colors.white, width: 2),
                                ),
                              ),
                            ),
                          ),
                          Positioned(
                            bottom: 10,
                            right: 10,
                            child: Container(
                              width: 14,
                              height: 14,
                              decoration: const BoxDecoration(
                                border: Border(
                                  bottom: BorderSide(color: Colors.white, width: 2),
                                  right: BorderSide(color: Colors.white, width: 2),
                                ),
                              ),
                            ),
                          ),
                          // Quick Zoom Buttons Overlay (1x, 2x, 3x)
                          if (_isCameraInitialized && _checkinImagePath == null && _maxZoomLevel > 1.0)
                            Positioned(
                              bottom: 12,
                              left: 0,
                              right: 0,
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [1.0, 2.0, 3.0, 5.0]
                                    .where((z) => z <= _maxZoomLevel || z == 1.0)
                                    .map((zoomVal) {
                                  final isSelected = (_currentZoomLevel - zoomVal).abs() < 0.3;
                                  return GestureDetector(
                                    onTap: () => _setZoomLevel(zoomVal),
                                    child: Container(
                                      margin: const EdgeInsets.symmetric(horizontal: 4),
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                      decoration: BoxDecoration(
                                        color: isSelected ? const Color(0xFF0EA5E9) : Colors.black.withValues(alpha: 0.6),
                                        borderRadius: BorderRadius.circular(20),
                                        border: Border.all(color: isSelected ? Colors.white : Colors.white30, width: 1.2),
                                      ),
                                      child: Text(
                                        '${zoomVal.toInt()}x',
                                        style: TextStyle(
                                          color: isSelected ? Colors.white : Colors.white70,
                                          fontWeight: FontWeight.w800,
                                          fontSize: 11,
                                        ),
                                      ),
                                    ),
                                  );
                                }).toList(),
                              ),
                            ),
                        ] else ...[
                          // Captured image preview
                          Image.file(
                            File(_checkinImagePath!),
                            fit: BoxFit.cover,
                          ),
                        ],
                      ],
                    ),
                  ),
                ),
              ),

              const SizedBox(height: 4),

              // 3. Controls & Check-in Form section
              if (_checkinImagePath == null) ...[
                // Shutter Controls in Camera Mode (FittedBox guarantees ZERO RenderFlex overflow!)
                Padding(
                  padding: const EdgeInsets.only(top: 2, bottom: 2),
                  child: FittedBox(
                    fit: BoxFit.scaleDown,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                      children: [
                        _circularControlButton(
                          icon: Icons.photo_library_outlined,
                          bgColor: const Color(0xFF334155),
                          onPressed: _pickCheckinImageFromGallery,
                        ),
                        const SizedBox(width: 24),
                        GestureDetector(
                          onTap: _takeCheckinPicture,
                          child: Container(
                            width: 52,
                            height: 52,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              border: Border.all(color: const Color(0xFF475569), width: 2.5),
                              color: Colors.white,
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.08),
                                  blurRadius: 5,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: Center(
                              child: Container(
                                width: 38,
                                height: 38,
                                decoration: const BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: Color(0xFF0EA5E9),
                                ),
                                child: const Icon(Icons.camera_alt, color: Colors.white, size: 18),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 24),
                        _circularControlButton(
                          icon: Icons.cached_outlined,
                          color: Colors.white,
                          bgColor: const Color(0xFF334155),
                          onPressed: _switchCamera,
                        ),
                      ],
                    ),
                  ),
                ),
              ] else ...[
                // Responsive Scrollable Check-in Form in Photo Mode (Prevents any screen overflow!)
                Expanded(
                  flex: 4,
                  child: SingleChildScrollView(
                    physics: const BouncingScrollPhysics(),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // Retake / Gallery Action Row
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            TextButton.icon(
                              onPressed: () {
                                setState(() {
                                  _checkinImagePath = null;
                                });
                              },
                              icon: const Icon(Icons.refresh, color: Colors.redAccent, size: 16),
                              label: const Text('Chụp lại', style: TextStyle(color: Colors.redAccent, fontSize: 12, fontWeight: FontWeight.bold)),
                              style: TextButton.styleFrom(padding: EdgeInsets.zero, minimumSize: Size.zero, tapTargetSize: MaterialTapTargetSize.shrinkWrap),
                            ),
                            TextButton.icon(
                              onPressed: _pickCheckinImageFromGallery,
                              icon: const Icon(Icons.photo_library_outlined, color: Colors.white70, size: 16),
                              label: const Text('Đổi từ Thư viện', style: TextStyle(color: Colors.white70, fontSize: 12)),
                              style: TextButton.styleFrom(padding: EdgeInsets.zero, minimumSize: Size.zero, tapTargetSize: MaterialTapTargetSize.shrinkWrap),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),

                        // Location selection button / selected location chip
                        _selectedEateryId == null
                            ? InkWell(
                                onTap: () => _openLocationPickerModal(context),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF334155),
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(color: const Color(0xFF475569)),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(Icons.add_location_alt_rounded, color: Color(0xFF0EA5E9), size: 18),
                                      const SizedBox(width: 8),
                                      const Expanded(
                                        child: Text(
                                          'Gắn địa điểm / Cơ sở (Tùy chọn)',
                                          style: TextStyle(color: Colors.white70, fontSize: 12),
                                        ),
                                      ),
                                      IconButton(
                                        onPressed: _autoDetectCurrentLocationAndSelectEatery,
                                        icon: const Icon(Icons.my_location, color: Color(0xFF0EA5E9), size: 16),
                                        tooltip: 'Định vị tự động',
                                        padding: EdgeInsets.zero,
                                        constraints: const BoxConstraints(),
                                      ),
                                    ],
                                  ),
                                ),
                              )
                            : Builder(
                                builder: (context) {
                                  final selectedItem = _eateries.firstWhere(
                                    (e) => e['id'] == _selectedEateryId,
                                    orElse: () => null,
                                  );
                                  return Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFF0EA5E9).withValues(alpha: 0.15),
                                      borderRadius: BorderRadius.circular(10),
                                      border: Border.all(color: const Color(0xFF0EA5E9)),
                                    ),
                                    child: Row(
                                      children: [
                                        const Icon(Icons.location_on, color: Color(0xFF0EA5E9), size: 18),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: Text(
                                            selectedItem != null ? '${selectedItem['name']}' : 'Địa điểm đã gắn',
                                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                        InkWell(
                                          onTap: () => setState(() => _selectedEateryId = null),
                                          child: const Padding(
                                            padding: EdgeInsets.all(4.0),
                                            child: Icon(Icons.close, color: Colors.white70, size: 18),
                                          ),
                                        ),
                                      ],
                                    ),
                                  );
                                },
                              ),
                        const SizedBox(height: 8),

                        // Stars Rating
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: List.generate(5, (index) {
                            return GestureDetector(
                              onTap: () {
                                setState(() {
                                  _rating = index + 1;
                                });
                              },
                              child: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 2.0),
                                child: Icon(
                                  index < _rating ? Icons.star : Icons.star_border,
                                  color: Colors.amber,
                                  size: 22,
                                ),
                              ),
                            );
                          }),
                        ),
                        const SizedBox(height: 8),

                        // Comment input
                        TextField(
                          controller: _commentController,
                          style: const TextStyle(color: Colors.white, fontSize: 12),
                          decoration: InputDecoration(
                            hintText: 'Nhập cảm nhận của bạn...',
                            hintStyle: const TextStyle(color: Colors.white54, fontSize: 12),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: BorderSide.none,
                            ),
                            filled: true,
                            fillColor: const Color(0xFF334155),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          ),
                        ),
                        const SizedBox(height: 6),

                        if (isGuest) ...[
                          TextField(
                            controller: _guestNameController,
                            style: const TextStyle(color: Colors.white, fontSize: 12),
                            decoration: InputDecoration(
                              hintText: 'Tên của bạn (Khách vãng lai)...',
                              hintStyle: const TextStyle(color: Colors.white54, fontSize: 12),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(8),
                                borderSide: BorderSide.none,
                              ),
                              filled: true,
                              fillColor: const Color(0xFF334155),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                            ),
                          ),
                          const SizedBox(height: 6),
                        ],

                        // Submit Button
                        ElevatedButton(
                          onPressed: _isSendingCheckin
                              ? null
                              : _submitCameraCheckin,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF0EA5E9),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          child: _isSendingCheckin
                              ? const SizedBox(
                                  width: 16,
                                  height: 16,
                                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                )
                              : const Text('Gửi Check-in', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTikTokFeedCard(dynamic item, Color primaryColor, double height) {
    final isCheckin = item['type'] == 'checkin';
    final commentableId = item['id'];

    return Container(
      height: height,
      padding: const EdgeInsets.all(16),
      child: Card(
        elevation: 4,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        clipBehavior: Clip.antiAlias,
        child: Stack(
          fit: StackFit.expand,
          children: [
            // Background Image (Memory & Painting Optimized with RepaintBoundary)
            RepaintBoundary(
              child: Image.network(
                (item['image_path'] != null && item['image_path'].toString().isNotEmpty)
                    ? (item['image_path'].toString().startsWith('http')
                        ? item['image_path'].toString()
                        : 'https://donganhdiscovery.xadonganh.com/${item['image_path'].toString().startsWith('/') ? item['image_path'].toString().substring(1) : item['image_path'].toString()}')
                    : 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=400&q=60',
                fit: BoxFit.cover,
                cacheWidth: 800,
                filterQuality: FilterQuality.medium,
                errorBuilder: (_, __, ___) => Container(
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      colors: [Color(0xFF334155), Color(0xFF0F172A)],
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                    ),
                  ),
                ),
              ),
            ),

            // Black overlay for text readability
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    Colors.black.withValues(alpha: 0.4),
                    Colors.transparent,
                    Colors.black.withValues(alpha: 0.7)
                  ],
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  stops: const [0.0, 0.5, 1.0],
                ),
              ),
            ),

            // Card Overlay details
            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  // Top Row: User Avatar & Stars
                  Row(
                    children: [
                      GestureDetector(
                        onTap: () {
                          final uId = item['user_id'] ?? item['user']?['id'];
                          if (uId != null) {
                            showPublicProfileModal(context, uId);
                          }
                        },
                        behavior: HitTestBehavior.opaque,
                        child: Row(
                          children: [
                            CircleAvatar(
                              backgroundColor: Colors.white.withValues(alpha: 0.9),
                              backgroundImage: ResizeImage(NetworkImage(ApiService.getAvatarUrl(item['avatar'] ?? item['author_avatar'] ?? item, item['display_name'] ?? 'User')), width: 120),
                            ),
                            const SizedBox(width: 12),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Text(
                                      item['display_name'] ?? 'Ẩn danh',
                                      style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 14,
                                        color: Colors.white,
                                      ),
                                    ),
                                    if ((item['role'] ?? '').toString().toLowerCase() == 'admin') ...[
                                      const SizedBox(width: 4),
                                      const Icon(Icons.star_rounded, color: Color(0xFFEF4444), size: 16),
                                    ] else if (item['is_verified'] == true || item['is_verified'] == 1) ...[
                                      const SizedBox(width: 4),
                                      const Icon(Icons.star_rounded, color: Color(0xFFF59E0B), size: 16),
                                    ],
                                  ],
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  'Công khai',
                                  style: TextStyle(color: Colors.white.withValues(alpha: 0.7), fontSize: 11),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const Spacer(),
                      // Rating stars
                      Row(
                        children: List.generate(5, (index) {
                          return Icon(
                            index < (item['rating'] ?? 5) ? Icons.star : Icons.star_border,
                            color: Colors.amber,
                            size: 16,
                          );
                        }),
                      ),
                    ],
                  ),

                  // Bottom Column: Eatery Tag & Comment & Buttons
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (isCheckin && item['eatery'] != null)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.5),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: Colors.white.withValues(alpha: 0.2), width: 1),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.restaurant, color: Color(0xFF0EA5E9), size: 16),
                              const SizedBox(width: 8),
                              Text(
                                item['eatery']['name'] ?? '',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.white),
                              ),
                              const SizedBox(width: 6),
                              Text(
                                '• ${item['eatery']['commune']}',
                                style: TextStyle(color: Colors.white.withValues(alpha: 0.7), fontSize: 11),
                              ),
                            ],
                          ),
                        ),

                      const SizedBox(height: 12),

                      // User comment text
                      if (item['comment'] != null && item['comment'].toString().trim().isNotEmpty)
                        Text(
                          item['comment'],
                          style: const TextStyle(fontSize: 14, height: 1.4, color: Colors.white, fontWeight: FontWeight.w500),
                          maxLines: 3,
                          overflow: TextOverflow.ellipsis,
                        ),

                      const SizedBox(height: 16),

                      // Interactions row
                      Row(
                        children: [
                          Expanded(
                            child: SingleChildScrollView(
                              scrollDirection: Axis.horizontal,
                              child: Row(
                                children: [
                                  _reactionButton(commentableId, '❤️', item['type'], item),
                                  _reactionButton(commentableId, '🔥', item['type'], item),
                                  _reactionButton(commentableId, '👍', item['type'], item),
                                  _reactionButton(commentableId, '😂', item['type'], item),
                                  _reactionButton(commentableId, '😍', item['type'], item),
                                  _reactionButton(commentableId, '🤤', item['type'], item),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          // Slide comments bottom drawer trigger
                          GestureDetector(
                            onTap: () => _openCommentsBottomSheet(item),
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                              decoration: BoxDecoration(
                                color: Colors.black.withValues(alpha: 0.5),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.comment_outlined, size: 16, color: Colors.white),
                                  const SizedBox(width: 6),
                                  Text(
                                    '${item['comments']?.length ?? 0} bình luận',
                                    style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          // Share Button (Zalo, FB, Link, System)
                          GestureDetector(
                            onTap: () => _showCheckinShareModal(context, item),
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                              decoration: BoxDecoration(
                                color: Colors.black.withValues(alpha: 0.5),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                              ),
                              child: const Icon(Icons.share_outlined, size: 16, color: Colors.white),
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
      ),
    );
  }

  Widget _circularControlButton({
    required IconData icon,
    required VoidCallback onPressed,
    Color color = Colors.white,
    Color bgColor = const Color(0xFF334155),
  }) {
    return Container(
      width: 46,
      height: 46,
      decoration: BoxDecoration(
        color: bgColor,
        shape: BoxShape.circle,
        border: Border.all(color: Colors.white.withValues(alpha: 0.1)),
      ),
      child: IconButton(
        icon: Icon(icon, color: color, size: 20),
        onPressed: onPressed,
      ),
    );
  }

  Widget _reactionButton(int id, String emoji, String type, Map item) {
    final counts = (item['reaction_counts'] is Map) ? item['reaction_counts'] as Map : {};
    final cnt = counts[emoji] ?? 0;

    return InkWell(
      onTap: () => _react(id, emoji, type),
      borderRadius: BorderRadius.circular(16),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
        margin: const EdgeInsets.only(right: 5),
        decoration: BoxDecoration(
          color: Colors.black.withValues(alpha: 0.4),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(emoji, style: const TextStyle(fontSize: 14)),
            const SizedBox(width: 4),
            Text(
              '$cnt',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.bold,
                color: cnt > 0 ? const Color(0xFFF97316) : Colors.white70,
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showCheckinShareModal(BuildContext context, dynamic item) {
    final commentText = (item['comment'] ?? '').toString().trim();
    final eateryName = item['eatery']?['name'] ?? 'Đông Anh Social';
    final title = commentText.isNotEmpty ? commentText : 'Check-in tại $eateryName';
    final shareUrl = 'https://donganhdiscovery.xadonganh.com/checkin/${item['id']}';

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (ctx) => Container(
        padding: const EdgeInsets.only(top: 16, left: 20, right: 20, bottom: 28),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 36,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const SizedBox(width: 36),
                const Expanded(
                  child: Text(
                    'Chia sẻ bài Check-in',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF0F172A),
                    ),
                  ),
                ),
                GestureDetector(
                  onTap: () => Navigator.pop(ctx),
                  child: Container(
                    padding: const EdgeInsets.all(6),
                    decoration: const BoxDecoration(
                      color: Color(0xFFF1F5F9),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.close_rounded, size: 20, color: Color(0xFF64748B)),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                GestureDetector(
                  onTap: () async {
                    Navigator.pop(ctx);
                    final zaloUrl = Uri.parse('https://sp.zalo.me/share_inline?link=${Uri.encodeComponent(shareUrl)}');
                    try {
                      if (await canLaunchUrl(zaloUrl)) {
                        await launchUrl(zaloUrl, mode: LaunchMode.externalApplication);
                        return;
                      }
                    } catch (_) {}
                    Share.share('📸 [Check-in Đông Anh] $title\n\n🔗 Xem chi tiết: $shareUrl');
                  },
                  child: SizedBox(
                    width: 72,
                    child: Column(
                      children: [
                        Container(
                          width: 62,
                          height: 62,
                          decoration: BoxDecoration(
                            color: const Color(0xFF0068FF),
                            borderRadius: BorderRadius.circular(22),
                          ),
                          child: const Center(
                            child: Text(
                              'Zalo',
                              style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 14),
                            ),
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Zalo',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
                        ),
                      ],
                    ),
                  ),
                ),
                GestureDetector(
                  onTap: () async {
                    Navigator.pop(ctx);
                    final fbUrl = Uri.parse('https://www.facebook.com/sharer/sharer.php?u=${Uri.encodeComponent(shareUrl)}');
                    try {
                      if (await canLaunchUrl(fbUrl)) {
                        await launchUrl(fbUrl, mode: LaunchMode.externalApplication);
                        return;
                      }
                    } catch (_) {}
                    Share.share('📸 [Check-in Đông Anh] $title\n\n🔗 Xem chi tiết: $shareUrl');
                  },
                  child: SizedBox(
                    width: 72,
                    child: Column(
                      children: [
                        Container(
                          width: 62,
                          height: 62,
                          decoration: BoxDecoration(
                            color: const Color(0xFF1877F2),
                            borderRadius: BorderRadius.circular(22),
                          ),
                          child: const Center(
                            child: Text(
                              'FB',
                              style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 16),
                            ),
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Facebook',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
                        ),
                      ],
                    ),
                  ),
                ),
                GestureDetector(
                  onTap: () {
                    Clipboard.setData(ClipboardData(text: '📸 [Check-in Đông Anh] $title\n🔗 Xem chi tiết: $shareUrl'));
                    Navigator.pop(ctx);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: const Row(
                          children: [
                            Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                            SizedBox(width: 8),
                            Text('Đã sao chép liên kết bài check-in!'),
                          ],
                        ),
                        backgroundColor: const Color(0xFF059669),
                        behavior: SnackBarBehavior.floating,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    );
                  },
                  child: SizedBox(
                    width: 72,
                    child: Column(
                      children: [
                        Container(
                          width: 62,
                          height: 62,
                          decoration: BoxDecoration(
                            color: const Color(0xFFECFDF5),
                            borderRadius: BorderRadius.circular(22),
                          ),
                          child: const Center(
                            child: Icon(Icons.link_rounded, color: Color(0xFF059669), size: 28),
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Sao chép',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
                        ),
                      ],
                    ),
                  ),
                ),
                GestureDetector(
                  onTap: () {
                    Navigator.pop(ctx);
                    Share.share('📸 [Check-in Đông Anh] $title\n\n🔗 Xem chi tiết: $shareUrl');
                  },
                  child: SizedBox(
                    width: 72,
                    child: Column(
                      children: [
                        Container(
                          width: 62,
                          height: 62,
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEF3C7),
                            borderRadius: BorderRadius.circular(22),
                          ),
                          child: const Center(
                            child: Icon(Icons.share_rounded, color: Color(0xFFD97706), size: 28),
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Ứng dụng khác',
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
