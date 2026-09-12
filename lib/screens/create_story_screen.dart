import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../services/api_service.dart';

/// Story Creation Mode descriptor model
class StoryCreationMode {
  final String title;
  final dynamic icon;
  final bool isText;
  final List<Color> gradient;
  final VoidCallback onTap;

  const StoryCreationMode({
    required this.title,
    required this.icon,
    this.isText = false,
    required this.gradient,
    required this.onTap,
  });
}

/// Facebook & Instagram Style Create Story Screen
class CreateStoryScreen extends StatefulWidget {
  const CreateStoryScreen({super.key});

  @override
  State<CreateStoryScreen> createState() => _CreateStoryScreenState();
}

class _CreateStoryScreenState extends State<CreateStoryScreen> {
  final ImagePicker _picker = ImagePicker();

  // --- STATE ---
  List<XFile> _selectedFiles = [];
  bool _isMultiSelect = false;
  String _galleryFilter = 'Tất cả'; // 'Tất cả', 'Hình ảnh', 'Video'

  // Uploading state
  bool _isUploading = false;
  String _uploadStatus = '';

  // Story Editor state
  XFile? _previewMedia;
  bool _isTextStoryMode = false;

  // Text Story Styling & Controller
  final TextEditingController _textStoryController = TextEditingController();
  int _selectedGradientIndex = 0;
  
  static const List<List<Color>> _storyGradients = [
    [Color(0xFF833AB4), Color(0xFFFD1D1D), Color(0xFFFCB045)], // Instagram classic
    [Color(0xFF00C6FF), Color(0xFF0072FF)],                   // Facebook blue
    [Color(0xFFF857A6), Color(0xFFFF5858)],                   // Sunset pink
    [Color(0xFF11998E), Color(0xFF38EF7D)],                   // Emerald
    [Color(0xFFFF8008), Color(0xFFFFC837)],                   // Warm amber
    [Color(0xFF0F2027), Color(0xFF203A43), Color(0xFF2C5364)], // Midnight dark
  ];

  // Story Privacy: 'public' | 'friends' | 'private'
  String _privacySetting = 'public';

  @override
  void initState() {
    super.initState();
    // Auto-prompt real device gallery picker on screen enter
    WidgetsBinding.instance.addPostFrameCallback((_) => _pickMediaFromGallery());
  }

  @override
  void dispose() {
    _textStoryController.dispose();
    super.dispose();
  }

  // --- MEDIA ACTIONS ---

  /// Pick real media files from device gallery
  Future<void> _pickMediaFromGallery({bool forceMulti = false}) async {
    try {
      if (_isMultiSelect || forceMulti) {
        final List<XFile> media = await _picker.pickMultipleMedia();
        if (media.isNotEmpty) {
          setState(() {
            _selectedFiles = media;
            _previewMedia = media.first;
          });
        }
      } else {
        final XFile? media = await _picker.pickMedia();
        if (media != null) {
          setState(() {
            if (!_selectedFiles.contains(media)) {
              _selectedFiles.insert(0, media);
            }
            _previewMedia = media;
          });
        }
      }
    } catch (e) {
      debugPrint('[CreateStoryScreen] pickMedia error: $e');
      try {
        final XFile? img = await _picker.pickImage(source: ImageSource.gallery);
        if (img != null) {
          setState(() {
            if (!_selectedFiles.contains(img)) {
              _selectedFiles.insert(0, img);
            }
            _previewMedia = img;
          });
        }
      } catch (_) {}
    }
  }

  /// Capture real photo using device camera
  Future<void> _openCamera() async {
    try {
      final XFile? photo = await _picker.pickImage(source: ImageSource.camera);
      if (photo != null) {
        setState(() {
          _selectedFiles.insert(0, photo);
          _previewMedia = photo;
        });
      }
    } catch (e) {
      debugPrint('[CreateStoryScreen] camera error: $e');
    }
  }

  /// Publish created story to Backend
  Future<void> _publishStory() async {
    if (_previewMedia == null && !_isTextStoryMode) {
      _showSnackBar('Vui lòng chọn ảnh, video hoặc nhập văn bản để tạo tin!');
      return;
    }

    if (_isTextStoryMode && _textStoryController.text.trim().isEmpty) {
      _showSnackBar('Vui lòng nhập nội dung cho tin văn bản!');
      return;
    }

    setState(() {
      _isUploading = true;
      _uploadStatus = 'Đang tải tin lên...';
    });

    try {
      List<String> uploadedImageUrls = [];
      List<String> uploadedVideoUrls = [];

      if (!_isTextStoryMode && _previewMedia != null) {
        final List<String> pathsToUpload = _selectedFiles.isNotEmpty
            ? _selectedFiles.map((f) => f.path).toList()
            : [_previewMedia!.path];

        _uploadStatus = 'Đang tải tệp truyền thông lên Cloud...';
        final uploadedItems = await ApiService.uploadFilesToR2(pathsToUpload, folder: 'stories');

        for (var item in uploadedItems) {
          final url = item['url'] ?? '';
          final type = item['type'] ?? 'image';
          if (url.isNotEmpty) {
            if (type == 'video' || url.endsWith('.mp4') || url.endsWith('.mov') || url.endsWith('.webm')) {
              uploadedVideoUrls.add(url);
            } else {
              uploadedImageUrls.add(url);
            }
          }
        }
      }

      _uploadStatus = 'Đang đăng tin...';
      final String storyDescription = _isTextStoryMode
          ? _textStoryController.text.trim()
          : (_textStoryController.text.trim().isNotEmpty
              ? _textStoryController.text.trim()
              : 'Tin mới từ ứng dụng');

      final result = await ApiService.createPost(
        description: storyDescription,
        images: uploadedImageUrls.isNotEmpty ? uploadedImageUrls : null,
        videos: uploadedVideoUrls.isNotEmpty ? uploadedVideoUrls : null,
      );

      if (result['success'] == true || result['id'] != null) {
        if (mounted) {
          _showSnackBar('Đã chia sẻ lên tin của bạn!', isSuccess: true);
          Navigator.of(context).pop(true);
        }
      } else {
        throw Exception(result['message'] ?? 'Không thể lưu tin');
      }
    } catch (e) {
      if (mounted) {
        _showSnackBar('Lỗi khi chia sẻ tin: $e', isError: true);
      }
    } finally {
      if (mounted) {
        setState(() {
          _isUploading = false;
          _uploadStatus = '';
        });
      }
    }
  }

  void _showSnackBar(String message, {bool isSuccess = false, bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            if (isSuccess) const Icon(Icons.check_circle, color: Colors.white),
            if (isSuccess) const SizedBox(width: 8),
            Expanded(child: Text(message)),
          ],
        ),
        backgroundColor: isError ? Colors.red : (isSuccess ? Colors.green : Colors.orange),
      ),
    );
  }

  // --- BUILD UI ---

  @override
  Widget build(BuildContext context) {
    if (_previewMedia != null || _isTextStoryMode) {
      return _buildStoryEditorView();
    }

    return Scaffold(
      backgroundColor: Colors.black,
      body: SafeArea(
        child: Column(
          children: [
            _buildTopHeaderBar(),
            const SizedBox(height: 12),
            _buildActionCardsRow(),
            const SizedBox(height: 16),
            _buildGalleryToolbar(),
            const SizedBox(height: 8),
            Expanded(child: _buildGalleryMediaGrid()),
          ],
        ),
      ),
    );
  }

  // --- SUB-WIDGET BUILDERS ---

  /// Header Bar with Close, Title, Camera & Settings
  Widget _buildTopHeaderBar() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          IconButton(
            icon: const Icon(Icons.close, color: Colors.white, size: 28),
            onPressed: () => Navigator.of(context).pop(),
          ),
          const Text(
            'Tạo tin',
            style: TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          Row(
            children: [
              IconButton(
                icon: const Icon(Icons.camera_alt_outlined, color: Colors.white, size: 26),
                onPressed: _openCamera,
                tooltip: 'Mở máy ảnh',
              ),
              IconButton(
                icon: const Icon(Icons.settings_outlined, color: Colors.white, size: 26),
                onPressed: _showPrivacySettingsModal,
                tooltip: 'Cài đặt tin',
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// Creation Mode Quick-Select Cards
  Widget _buildActionCardsRow() {
    final List<StoryCreationMode> creationModes = [
      StoryCreationMode(
        title: 'Văn bản',
        icon: 'Aa',
        isText: true,
        gradient: const [Color(0xFF833AB4), Color(0xFFFD1D1D)],
        onTap: () => setState(() => _isTextStoryMode = true),
      ),
      StoryCreationMode(
        title: 'Nhạc',
        icon: Icons.music_note_rounded,
        gradient: const [Color(0xFF00C6FF), Color(0xFF0072FF)],
        onTap: () => _pickMediaFromGallery(),
      ),
      StoryCreationMode(
        title: 'Mẫu',
        icon: Icons.space_dashboard_rounded,
        gradient: const [Color(0xFFF857A6), Color(0xFFFF5858)],
        onTap: () => _pickMediaFromGallery(forceMulti: true),
      ),
      StoryCreationMode(
        title: 'Boomerang',
        icon: Icons.all_inclusive_rounded,
        gradient: const [Color(0xFFFF8008), Color(0xFFFFC837)],
        onTap: _openCamera,
      ),
    ];

    return SizedBox(
      height: 110,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: creationModes.length,
        itemBuilder: (context, index) {
          final mode = creationModes[index];
          return _buildCreationModeCard(mode);
        },
      ),
    );
  }

  Widget _buildCreationModeCard(StoryCreationMode mode) {
    return GestureDetector(
      onTap: mode.onTap,
      child: Container(
        width: 90,
        margin: const EdgeInsets.only(right: 12),
        decoration: BoxDecoration(
          color: const Color(0xFF1E293B),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.white.withValues(alpha: 0.15), width: 1),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: LinearGradient(
                  colors: mode.gradient,
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                boxShadow: [
                  BoxShadow(
                    color: mode.gradient.first.withValues(alpha: 0.4),
                    blurRadius: 8,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Center(
                child: mode.isText
                    ? const Text(
                        'Aa',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          fontFamily: 'Serif',
                        ),
                      )
                    : Icon(
                        mode.icon as IconData,
                        color: Colors.white,
                        size: 24,
                      ),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              mode.title,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 13,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// Gallery Header Toolbar (Category Dropdown, Search, Multi-select pill)
  Widget _buildGalleryToolbar() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Theme(
            data: Theme.of(context).copyWith(cardColor: const Color(0xFF1E293B)),
            child: PopupMenuButton<String>(
              initialValue: _galleryFilter,
              onSelected: (String value) {
                setState(() => _galleryFilter = value);
                _pickMediaFromGallery();
              },
              child: const Row(
                children: [
                  Text(
                    'Thư viện ảnh',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 17,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  SizedBox(width: 4),
                  Icon(Icons.keyboard_arrow_down_rounded, color: Colors.white, size: 24),
                ],
              ),
              itemBuilder: (BuildContext context) => const <PopupMenuEntry<String>>[
                PopupMenuItem<String>(
                  value: 'Tất cả',
                  child: Text('Tất cả phương tiện', style: TextStyle(color: Colors.white)),
                ),
                PopupMenuItem<String>(
                  value: 'Hình ảnh',
                  child: Text('Chỉ hình ảnh', style: TextStyle(color: Colors.white)),
                ),
                PopupMenuItem<String>(
                  value: 'Video',
                  child: Text('Chỉ Video', style: TextStyle(color: Colors.white)),
                ),
              ],
            ),
          ),
          Row(
            children: [
              Container(
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                ),
                child: IconButton(
                  icon: const Icon(Icons.search, color: Colors.white, size: 20),
                  onPressed: () => _pickMediaFromGallery(),
                  constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
                  padding: EdgeInsets.zero,
                ),
              ),
              const SizedBox(width: 8),
              GestureDetector(
                onTap: () {
                  setState(() => _isMultiSelect = !_isMultiSelect);
                  _pickMediaFromGallery(forceMulti: _isMultiSelect);
                },
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: _isMultiSelect ? const Color(0xFF0EA5E9) : Colors.white.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.collections_rounded,
                        color: Colors.white,
                        size: 16,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        'Chọn nhiều file',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 13,
                          fontWeight: _isMultiSelect ? FontWeight.bold : FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// 3-Column Gallery Media Grid (Camera Tile + Gallery Tile + Picked Media Files)
  Widget _buildGalleryMediaGrid() {
    final int totalCount = 2 + _selectedFiles.length;

    return GridView.builder(
      padding: const EdgeInsets.all(2),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 3,
        crossAxisSpacing: 2,
        mainAxisSpacing: 2,
        childAspectRatio: 0.75,
      ),
      itemCount: totalCount,
      itemBuilder: (context, index) {
        if (index == 0) return _buildCameraTile();
        if (index == 1) return _buildGalleryPickerTile();
        return _buildMediaTile(_selectedFiles[index - 2]);
      },
    );
  }

  Widget _buildCameraTile() {
    return GestureDetector(
      onTap: _openCamera,
      child: Container(
        color: const Color(0xFF1E293B),
        child: const Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.camera_alt, color: Colors.white, size: 36),
            SizedBox(height: 6),
            Text(
              'Máy ảnh',
              style: TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.w600),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildGalleryPickerTile() {
    return GestureDetector(
      onTap: () => _pickMediaFromGallery(forceMulti: true),
      child: Container(
        color: const Color(0xFF0EA5E9).withValues(alpha: 0.2),
        child: const Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.photo_library_rounded, color: Color(0xFF0EA5E9), size: 36),
            SizedBox(height: 6),
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 4),
              child: Text(
                'Thư viện máy',
                textAlign: TextAlign.center,
                style: TextStyle(color: Color(0xFF0EA5E9), fontSize: 13, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMediaTile(XFile file) {
    final isVideo = file.path.endsWith('.mp4') || file.path.endsWith('.mov') || file.path.endsWith('.webm') || file.path.endsWith('.avi');

    return GestureDetector(
      onTap: () => setState(() => _previewMedia = file),
      child: Stack(
        fit: StackFit.expand,
        children: [
          Image.file(File(file.path), fit: BoxFit.cover),
          if (isVideo)
            Positioned(
              bottom: 6,
              right: 6,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: Colors.black.withValues(alpha: 0.7),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.play_arrow, color: Colors.white, size: 12),
                    SizedBox(width: 2),
                    Text(
                      'VIDEO',
                      style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }

  /// Fullscreen Story Preview & Publishing Editor View
  Widget _buildStoryEditorView() {
    final currentGradient = _storyGradients[_selectedGradientIndex % _storyGradients.length];

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // Background Canvas (Image/Video File or Text Story Gradient)
          Positioned.fill(
            child: _isTextStoryMode || _previewMedia == null
                ? Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: currentGradient,
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                    ),
                    child: Center(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 24),
                        child: TextField(
                          controller: _textStoryController,
                          maxLines: null,
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 28,
                            fontWeight: FontWeight.bold,
                            shadows: [
                              Shadow(blurRadius: 10, color: Colors.black45, offset: Offset(0, 2)),
                            ],
                          ),
                          decoration: const InputDecoration(
                            hintText: 'Bắt đầu nhập văn bản...',
                            hintStyle: TextStyle(color: Colors.white60, fontSize: 24),
                            border: InputBorder.none,
                          ),
                        ),
                      ),
                    ),
                  )
                : Image.file(
                    File(_previewMedia!.path),
                    fit: BoxFit.cover,
                  ),
          ),

          // Caption Text Overlay for Media Story
          if (!_isTextStoryMode && _previewMedia != null)
            Positioned(
              bottom: 120,
              left: 20,
              right: 20,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.black.withValues(alpha: 0.4),
                  borderRadius: BorderRadius.circular(24),
                ),
                child: TextField(
                  controller: _textStoryController,
                  style: const TextStyle(color: Colors.white, fontSize: 16),
                  decoration: const InputDecoration(
                    hintText: 'Thêm chú thích cho tin...',
                    hintStyle: TextStyle(color: Colors.white70, fontSize: 15),
                    border: InputBorder.none,
                    isDense: true,
                  ),
                ),
              ),
            ),

          // Editor Top Controls (Back, Gradient Palette, Stickers, Settings)
          Positioned(
            top: 50,
            left: 16,
            right: 16,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                IconButton(
                  icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 24),
                  onPressed: () {
                    setState(() {
                      _previewMedia = null;
                      _isTextStoryMode = false;
                      _textStoryController.clear();
                    });
                  },
                ),
                Row(
                  children: [
                    if (_isTextStoryMode)
                      GestureDetector(
                        onTap: () {
                          setState(() {
                            _selectedGradientIndex = (_selectedGradientIndex + 1) % _storyGradients.length;
                          });
                        },
                        child: Container(
                          width: 34,
                          height: 34,
                          margin: const EdgeInsets.only(right: 12),
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: 2),
                            gradient: LinearGradient(colors: currentGradient),
                          ),
                        ),
                      ),
                    IconButton(
                      icon: const Icon(Icons.text_fields_rounded, color: Colors.white, size: 26),
                      onPressed: () {},
                    ),
                    IconButton(
                      icon: const Icon(Icons.sentiment_satisfied_alt_rounded, color: Colors.white, size: 26),
                      onPressed: () {},
                    ),
                    IconButton(
                      icon: const Icon(Icons.tune_rounded, color: Colors.white, size: 26),
                      onPressed: _showPrivacySettingsModal,
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Uploading Indicator Overlay
          if (_isUploading)
            Positioned.fill(
              child: Container(
                color: Colors.black87,
                child: Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const CircularProgressIndicator(color: Color(0xFF0EA5E9)),
                      const SizedBox(height: 16),
                      Text(
                        _uploadStatus,
                        style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
              ),
            ),

          // Bottom Action Bar ("Chia sẻ lên tin")
          Positioned(
            bottom: 30,
            left: 20,
            right: 20,
            child: Row(
              children: [
                GestureDetector(
                  onTap: _showPrivacySettingsModal,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.25),
                      borderRadius: BorderRadius.circular(30),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          _privacySetting == 'public'
                              ? Icons.public
                              : (_privacySetting == 'friends' ? Icons.people : Icons.lock),
                          color: Colors.white,
                          size: 18,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          _privacySetting == 'public'
                              ? 'Công khai'
                              : (_privacySetting == 'friends' ? 'Bạn bè' : 'Chỉ mình tôi'),
                          style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _isUploading ? null : _publishStory,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0EA5E9),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(30),
                      ),
                      elevation: 4,
                    ),
                    child: const Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          'Chia sẻ lên tin',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        SizedBox(width: 8),
                        Icon(Icons.send_rounded, size: 18),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  /// Story Privacy Selection Modal Sheet
  void _showPrivacySettingsModal() {
    showModalBottomSheet(
      context: context,
      backgroundColor: const Color(0xFF1E293B),
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Quyền riêng tư của tin',
                    style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 16),
                  ListTile(
                    leading: const Icon(Icons.public, color: Color(0xFF0EA5E9)),
                    title: const Text('Công khai', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                    subtitle: const Text('Bất kỳ ai trên ứng dụng cũng có thể xem tin này', style: TextStyle(color: Colors.white70)),
                    trailing: _privacySetting == 'public' ? const Icon(Icons.check_circle, color: Color(0xFF0EA5E9)) : null,
                    onTap: () {
                      setModalState(() => _privacySetting = 'public');
                      setState(() => _privacySetting = 'public');
                      Navigator.pop(context);
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.people, color: Color(0xFF0EA5E9)),
                    title: const Text('Bạn bè', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                    subtitle: const Text('Chỉ bạn bè của bạn mới có thể xem tin này', style: TextStyle(color: Colors.white70)),
                    trailing: _privacySetting == 'friends' ? const Icon(Icons.check_circle, color: Color(0xFF0EA5E9)) : null,
                    onTap: () {
                      setModalState(() => _privacySetting = 'friends');
                      setState(() => _privacySetting = 'friends');
                      Navigator.pop(context);
                    },
                  ),
                  ListTile(
                    leading: const Icon(Icons.lock, color: Color(0xFF0EA5E9)),
                    title: const Text('Chỉ mình tôi', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                    subtitle: const Text('Chỉ một mình bạn có quyền xem tin', style: TextStyle(color: Colors.white70)),
                    trailing: _privacySetting == 'private' ? const Icon(Icons.check_circle, color: Color(0xFF0EA5E9)) : null,
                    onTap: () {
                      setModalState(() => _privacySetting = 'private');
                      setState(() => _privacySetting = 'private');
                      Navigator.pop(context);
                    },
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }
}
