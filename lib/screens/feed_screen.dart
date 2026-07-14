import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:camera/camera.dart';
import '../services/api_service.dart';

class FeedScreen extends StatefulWidget {
  const FeedScreen({super.key});

  @override
  State<FeedScreen> createState() => _FeedScreenState();
}

class _FeedScreenState extends State<FeedScreen> {
  List<dynamic> _feedItems = [];
  bool _isLoading = false;

  // Locket-style Camera Check-in fields
  CameraController? _cameraController;
  List<CameraDescription>? _cameras;
  bool _isCameraInitialized = false;
  int _selectedCameraIndex = 0;

  List<dynamic> _eateries = [];
  int? _selectedEateryId;
  int _rating = 5;
  final _commentController = TextEditingController();
  final _guestNameController = TextEditingController();
  bool _isLoadingEateries = true;
  bool _isSendingCheckin = false;
  String? _checkinImagePath;

  @override
  void initState() {
    super.initState();
    _loadFeed();
    _loadEateries();
    _initializeCamera();
  }

  @override
  void dispose() {
    _cameraController?.dispose();
    _commentController.dispose();
    _guestNameController.dispose();
    super.dispose();
  }

  Future<void> _initializeCamera() async {
    try {
      _cameras = await availableCameras();
      if (_cameras != null && _cameras!.isNotEmpty) {
        _cameraController = CameraController(
          _cameras![_selectedCameraIndex],
          ResolutionPreset.medium,
          enableAudio: false,
        );

        await _cameraController!.initialize();
        if (mounted) {
          setState(() {
            _isCameraInitialized = true;
          });
        }
      }
    } catch (e) {
      debugPrint('Lỗi khởi tạo Camera: $e');
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
      ResolutionPreset.medium,
      enableAudio: false,
    );

    try {
      await _cameraController!.initialize();
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
    final items = await ApiService.getFeed();
    if (mounted) {
      setState(() {
        _feedItems = items;
        _isLoading = false;
      });
    }
  }

  Future<void> _loadEateries() async {
    final eateries = await ApiService.getEateries('am-thuc-dong-anh');
    if (mounted) {
      setState(() {
        _eateries = eateries;
        _isLoadingEateries = false;
        if (_eateries.isNotEmpty) {
          _selectedEateryId = _eateries[0]['id'];
        }
      });
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
    if (_selectedEateryId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Vui lòng chọn địa điểm quán ăn!'), backgroundColor: Colors.red),
      );
      return;
    }
    setState(() {
      _isSendingCheckin = true;
    });

    final isGuest = !ApiService.isAuthenticated;
    final res = await ApiService.storeCheckin(
      eateryId: _selectedEateryId!,
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

  void _submitComment(int commentableId, String type, String content) async {
    if (content.trim().isEmpty) return;
    final res = await ApiService.storeComment(
      commentableId: commentableId,
      commentableType: type == 'checkin' ? 'App\\Models\\Checkin' : 'App\\Models\\FoodTourDiary',
      content: content,
    );
    if (res['success'] == true) {
      _loadFeed();
    }
  }

  void _react(int id, String emoji, String type) async {
    final success = await ApiService.reactToCheckin(id, emoji, type);
    if (success && mounted) {
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
                                      backgroundColor: const Color(0xFF0EA5E9).withOpacity(0.1),
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
                              Navigator.pop(context);
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
    final primaryColor = const Color(0xFF0EA5E9);

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Check-in Cộng đồng',
          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 20, letterSpacing: -0.5),
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.grey[800],
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadFeed,
          ),
        ],
      ),
      backgroundColor: const Color(0xFF0F172A), // Dark mode background for TikTok feel
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF0EA5E9)))
          : LayoutBuilder(
              builder: (context, constraints) {
                final height = constraints.maxHeight;
                return PageView.builder(
                  scrollDirection: Axis.vertical,
                  itemCount: _feedItems.length + 1,
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
    );
  }

  Widget _buildTikTokCameraPage(Color primaryColor, double height) {
    final isGuest = !ApiService.isAuthenticated;

    return Container(
      height: height,
      padding: const EdgeInsets.all(16),
      child: Card(
        elevation: 4,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        color: const Color(0xFF1E293B), // Dark card for camera page
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Header Row
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
                  const SizedBox(width: 8),
                  const Text(
                    'LIVE CAMERA ACTIVE',
                    style: TextStyle(
                      color: Color(0xFF10B981),
                      fontWeight: FontWeight.bold,
                      fontSize: 11,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const Spacer(),
                  const Text(
                    '📸 DAD CHECK-IN',
                    style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w900,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // Camera Viewport (Locket-style inline preview)
              Expanded(
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.black,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFF334155), width: 2),
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(14),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        if (_checkinImagePath == null) ...[
                          // Real Camera Preview (Anti-distortion fitting)
                          if (_isCameraInitialized && _cameraController != null)
                            FittedBox(
                              fit: BoxFit.cover,
                              child: SizedBox(
                                width: _cameraController!.value.previewSize?.height ?? 720,
                                height: _cameraController!.value.previewSize?.width ?? 1280,
                                child: CameraPreview(_cameraController!),
                              ),
                            )
                          else
                            Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const CircularProgressIndicator(color: Colors.white),
                                  const SizedBox(height: 12),
                                  Text(
                                    'Đang khởi động camera...',
                                    style: TextStyle(
                                      color: Colors.white.withOpacity(0.7),
                                      fontSize: 12,
                                    ),
                                  ),
                                ],
                              ),
                            ),

                          // Viewfinder corners
                          // Top-Left corner
                          Positioned(
                            top: 12,
                            left: 12,
                            child: Container(
                              width: 16,
                              height: 16,
                              decoration: const BoxDecoration(
                                border: Border(
                                  top: BorderSide(color: Colors.white, width: 2.5),
                                  left: BorderSide(color: Colors.white, width: 2.5),
                                ),
                              ),
                            ),
                          ),
                          // Top-Right corner
                          Positioned(
                            top: 12,
                            right: 12,
                            child: Container(
                              width: 16,
                              height: 16,
                              decoration: const BoxDecoration(
                                border: Border(
                                  top: BorderSide(color: Colors.white, width: 2.5),
                                  right: BorderSide(color: Colors.white, width: 2.5),
                                ),
                              ),
                            ),
                          ),
                          // Bottom-Left corner
                          Positioned(
                            bottom: 12,
                            left: 12,
                            child: Container(
                              width: 16,
                              height: 16,
                              decoration: const BoxDecoration(
                                border: Border(
                                  bottom: BorderSide(color: Colors.white, width: 2.5),
                                  left: BorderSide(color: Colors.white, width: 2.5),
                                ),
                              ),
                            ),
                          ),
                          // Bottom-Right corner
                          Positioned(
                            bottom: 12,
                            right: 12,
                            child: Container(
                              width: 16,
                              height: 16,
                              decoration: const BoxDecoration(
                                border: Border(
                                  bottom: BorderSide(color: Colors.white, width: 2.5),
                                  right: BorderSide(color: Colors.white, width: 2.5),
                                ),
                              ),
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
              const SizedBox(height: 16),

              // Locket-style Camera Control Buttons
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  // Gallery selection button
                  _circularControlButton(
                    icon: Icons.photo_library_outlined,
                    bgColor: const Color(0xFF334155),
                    onPressed: _pickCheckinImageFromGallery,
                  ),

                  // Large white shutter button (captures photo instantly inline)
                  GestureDetector(
                    onTap: _checkinImagePath == null ? _takeCheckinPicture : null,
                    child: Container(
                      width: 68,
                      height: 68,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(color: const Color(0xFF475569), width: 4),
                        color: Colors.white,
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.08),
                            blurRadius: 8,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Center(
                        child: Container(
                          width: 50,
                          height: 50,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: _checkinImagePath == null ? const Color(0xFF0EA5E9) : Colors.grey[400],
                          ),
                          child: const Icon(Icons.camera_alt, color: Colors.white, size: 20),
                        ),
                      ),
                    ),
                  ),

                  // Right control button: Camera switch (if no photo) / Reset button (if photo captured)
                  _circularControlButton(
                    icon: _checkinImagePath != null ? Icons.refresh : Icons.cached_outlined,
                    color: _checkinImagePath != null ? Colors.red : Colors.white,
                    bgColor: const Color(0xFF334155),
                    onPressed: () {
                      if (_checkinImagePath != null) {
                        setState(() {
                          _checkinImagePath = null;
                        });
                      } else {
                        _switchCamera();
                      }
                    },
                  ),
                ],
              ),

              // Check-in input form (Slide-up style inline form)
              if (_checkinImagePath != null) ...[
                const SizedBox(height: 12),
                
                // Choose eatery
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  decoration: BoxDecoration(
                    color: const Color(0xFF334155),
                    border: Border.all(color: const Color(0xFF475569)),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<int>(
                      dropdownColor: const Color(0xFF334155),
                      value: _selectedEateryId,
                      isExpanded: true,
                      style: const TextStyle(color: Colors.white, fontSize: 13),
                      hint: const Text('Chọn địa điểm quán ăn...', style: TextStyle(color: Colors.white70)),
                      items: _eateries.map<DropdownMenuItem<int>>((eatery) {
                        return DropdownMenuItem<int>(
                          value: eatery['id'],
                          child: Text(
                            '${eatery['name']} (${eatery['commune']?['name'] ?? 'Đông Anh'})',
                            style: const TextStyle(color: Colors.white),
                          ),
                        );
                      }).toList(),
                      onChanged: (val) {
                        setState(() {
                          _selectedEateryId = val;
                        });
                      },
                    ),
                  ),
                ),
                const SizedBox(height: 8),

                Row(
                  children: [
                    // Stars selector
                    Expanded(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: List.generate(5, (index) {
                          return GestureDetector(
                            onTap: () {
                              setState(() {
                                _rating = index + 1;
                              });
                            },
                            child: Icon(
                              index < _rating ? Icons.star : Icons.star_border,
                              color: Colors.amber,
                              size: 24,
                            ),
                          );
                        }),
                      ),
                    ),

                    const SizedBox(width: 8),

                    // Comment input
                    Expanded(
                      flex: 2,
                      child: TextField(
                        controller: _commentController,
                        style: const TextStyle(color: Colors.white, fontSize: 13),
                        decoration: InputDecoration(
                          hintText: 'Cảm nhận...',
                          hintStyle: const TextStyle(color: Colors.white54),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                            borderSide: BorderSide.none,
                          ),
                          filled: true,
                          fillColor: const Color(0xFF334155),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),

                if (isGuest) ...[
                  TextField(
                    controller: _guestNameController,
                    style: const TextStyle(color: Colors.white, fontSize: 13),
                    decoration: InputDecoration(
                      hintText: 'Tên của bạn (Khách vãng lai)...',
                      hintStyle: const TextStyle(color: Colors.white54),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: BorderSide.none,
                      ),
                      filled: true,
                      fillColor: const Color(0xFF334155),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    ),
                  ),
                  const SizedBox(height: 8),
                ],

                ElevatedButton(
                  onPressed: _isSendingCheckin || _selectedEateryId == null
                      ? null
                      : _submitCameraCheckin,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0EA5E9),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
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
            // Background Image
            if (item['image_path'] != null)
              Image.network(
                item['image_path'].toString().startsWith('http')
                    ? item['image_path']
                    : 'https://donganhdiscovery.xadonganh.com/' + item['image_path'],
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => Container(color: const Color(0xFF1E293B)),
              )
            else
              Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Color(0xFF334155), Color(0xFF0F172A)],
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                  ),
                ),
              ),

            // Black overlay for text readability
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    Colors.black.withOpacity(0.4),
                    Colors.transparent,
                    Colors.black.withOpacity(0.7)
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
                      CircleAvatar(
                        backgroundColor: Colors.white.withOpacity(0.9),
                        child: Text(
                          item['avatar_char'] ?? '👤',
                          style: TextStyle(color: primaryColor, fontWeight: FontWeight.bold),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
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
                                if (item['role'] == 'admin') ...[
                                  const SizedBox(width: 6),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: Colors.red,
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: const Text(
                                      'ADMIN',
                                      style: TextStyle(color: Colors.white, fontSize: 8, fontWeight: FontWeight.bold),
                                    ),
                                  ),
                                ],
                              ],
                            ),
                            const SizedBox(height: 2),
                            Text(
                              item['created_at_human'] ?? 'Vừa xong',
                              style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11),
                            ),
                          ],
                        ),
                      ),
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
                            color: Colors.black.withOpacity(0.5),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.white.withOpacity(0.2), width: 1),
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
                                style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 11),
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
                          _reactionButton(commentableId, '👍', item['type']),
                          _reactionButton(commentableId, '❤️', item['type']),
                          _reactionButton(commentableId, '😋', item['type']),
                          _reactionButton(commentableId, '🔥', item['type']),
                          const Spacer(),
                          // Slide comments bottom drawer trigger
                          GestureDetector(
                            onTap: () => _openCommentsBottomSheet(item),
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                              decoration: BoxDecoration(
                                color: Colors.black.withOpacity(0.5),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: Colors.white.withOpacity(0.2)),
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
        border: Border.all(color: Colors.white.withOpacity(0.1)),
      ),
      child: IconButton(
        icon: Icon(icon, color: color, size: 20),
        onPressed: onPressed,
      ),
    );
  }

  Widget _reactionButton(int id, String emoji, String type) {
    return GestureDetector(
      onTap: () => _react(id, emoji, type),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        margin: const EdgeInsets.only(right: 6),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.15),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Text(emoji, style: const TextStyle(fontSize: 14)),
      ),
    );
  }
}
