import 'dart:async';
import 'dart:io';
import 'dart:ui' as ui;
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:image_picker/image_picker.dart';
import 'package:photo_manager/photo_manager.dart';
import 'package:audioplayers/audioplayers.dart';
import 'package:video_player/video_player.dart';
import '../services/api_service.dart';
import '../services/music_api_service.dart';

// ==========================================
// MODELS & DATA STRUCTURES
// ==========================================

enum _StoryFontFamily { modern, script, serif, neon, typewriter }
enum _StoryTextBg { none, pill, solid, outline }

class _StoryTextItem {
  final String id;
  String text;
  Offset position;
  Color color;
  Color bgColor;
  _StoryFontFamily fontFamily;
  _StoryTextBg textBg;
  double fontSize;
  TextAlign align;

  _StoryTextItem({
    required this.id,
    required this.text,
    required this.position,
    required this.color,
    required this.bgColor,
    required this.fontFamily,
    required this.textBg,
    required this.fontSize,
    required this.align,
  });
}

enum _StickerKind { dongAnhBadge, timeClock, weather, trending, emoji }

class _StoryStickerItem {
  final String id;
  final _StickerKind kind;
  final String label;
  final String? icon;
  Offset position;

  _StoryStickerItem({
    required this.id,
    required this.kind,
    required this.label,
    this.icon,
    required this.position,
  });
}

class _DoodleStroke {
  final List<Offset> points;
  final Color color;
  final double strokeWidth;
  final bool isNeon;

  _DoodleStroke({
    required this.points,
    required this.color,
    required this.strokeWidth,
    this.isNeon = false,
  });
}

class _StoryFilter {
  final String name;
  final String icon;
  final List<double> matrix;

  const _StoryFilter({
    required this.name,
    required this.icon,
    required this.matrix,
  });
}


// ==========================================
// CUSTOM PAINTER FOR DOODLE DRAWING
// ==========================================

class _DoodlePainter extends CustomPainter {
  final List<_DoodleStroke> strokes;
  final _DoodleStroke? activeStroke;

  _DoodlePainter({required this.strokes, this.activeStroke});

  @override
  void paint(Canvas canvas, Size size) {
    final allStrokes = [...strokes, if (activeStroke != null) activeStroke!];

    for (final stroke in allStrokes) {
      if (stroke.points.isEmpty) continue;

      final paint = Paint()
        ..color = stroke.color
        ..strokeWidth = stroke.strokeWidth
        ..strokeCap = StrokeCap.round
        ..strokeJoin = StrokeJoin.round
        ..style = PaintingStyle.stroke;

      if (stroke.isNeon) {
        final glowPaint = Paint()
          ..color = stroke.color.withValues(alpha: 0.4)
          ..strokeWidth = stroke.strokeWidth + 8
          ..strokeCap = StrokeCap.round
          ..strokeJoin = StrokeJoin.round
          ..style = PaintingStyle.stroke
          ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 6);

        final path = Path()..moveTo(stroke.points.first.dx, stroke.points.first.dy);
        for (int i = 1; i < stroke.points.length; i++) {
          path.lineTo(stroke.points[i].dx, stroke.points[i].dy);
        }
        canvas.drawPath(path, glowPaint);
      }

      if (stroke.points.length == 1) {
        canvas.drawCircle(stroke.points.first, stroke.strokeWidth / 2, paint..style = PaintingStyle.fill);
      } else {
        final path = Path()..moveTo(stroke.points.first.dx, stroke.points.first.dy);
        for (int i = 1; i < stroke.points.length; i++) {
          path.lineTo(stroke.points[i].dx, stroke.points[i].dy);
        }
        canvas.drawPath(path, paint);
      }
    }
  }

  @override
  bool shouldRepaint(covariant _DoodlePainter oldDelegate) => true;
}

// ==========================================
// MAIN SCREEN WIDGET
// ==========================================

class CreateStoryScreen extends StatefulWidget {
  const CreateStoryScreen({super.key});

  @override
  State<CreateStoryScreen> createState() => _CreateStoryScreenState();
}

class _CreateStoryScreenState extends State<CreateStoryScreen> {
  final ImagePicker _picker = ImagePicker();
  final GlobalKey _storyCanvasKey = GlobalKey();

  // --- MEDIA & CREATION STATE ---
  XFile? _previewMedia;
  bool _isTextStoryMode = false;
  int _rotationQuarterTurns = 0;
  bool _isVideoStory = false;
  String? _originalVideoPath;
  VideoPlayerController? _videoPlayerController;
  bool _isVideoInitialized = false;
  bool _isVideoPlaying = true;
  bool _isVideoMuted = false;

  bool _isPathVideo(String? path) {
    if (path == null || path.isEmpty) return false;
    final ext = path.split('.').last.toLowerCase();
    return ['mp4', 'mov', 'avi', 'mkv', 'webm', '3gp', 'm4v'].contains(ext);
  }

  Future<void> _initVideoPreview(String videoPath) async {
    await _disposeVideoController();
    try {
      final controller = VideoPlayerController.file(File(videoPath));
      await controller.initialize();
      await controller.setLooping(true);
      await controller.setVolume(1.0);
      await controller.play();
      if (mounted) {
        setState(() {
          _videoPlayerController = controller;
          _isVideoInitialized = true;
          _isVideoPlaying = true;
          _isVideoMuted = false;
        });
      }
    } catch (e) {
      debugPrint('[CreateStoryScreen] Video init error: $e');
      if (mounted) {
        setState(() {
          _isVideoInitialized = false;
        });
      }
    }
  }

  Future<void> _disposeVideoController() async {
    if (_videoPlayerController != null) {
      final old = _videoPlayerController;
      _videoPlayerController = null;
      _isVideoInitialized = false;
      try {
        await old?.pause();
        await old?.dispose();
      } catch (_) {}
    }
  }

  void _toggleVideoPlayPause() {
    if (_videoPlayerController == null || !_isVideoInitialized) return;
    setState(() {
      if (_videoPlayerController!.value.isPlaying) {
        _videoPlayerController!.pause();
        _isVideoPlaying = false;
      } else {
        _videoPlayerController!.play();
        _isVideoPlaying = true;
      }
    });
  }

  void _toggleVideoMute() {
    if (_videoPlayerController == null || !_isVideoInitialized) return;
    setState(() {
      _isVideoMuted = !_isVideoMuted;
      _videoPlayerController!.setVolume(_isVideoMuted ? 0.0 : 1.0);
    });
  }

  // --- AUDIO PLAYER STATE ---
  final AudioPlayer _audioPlayer = AudioPlayer();
  String? _activePlayingTrackId;
  bool _isPlayingAudio = false;
  bool _isLoadingAudio = false;
  StoryMusicTrack? _selectedTrackObj;
  double _musicStartSeconds = 0.0;
  double _musicSegmentDuration = 15.0;

  // --- DEVICE ASSETS STATE ---
  List<AssetEntity> _recentAssets = [];
  bool _isLoadingAssets = true;
  bool _isLimitedAccess = false;
  bool _isPermissionDenied = false;
  String? _loadingAssetId;

  @override
  void initState() {
    super.initState();
    _initAudioPlayer();
    PhotoManager.addChangeCallback(_onPhotosChanged);
    _loadDevicePhotos();
  }

  Future<void> _initAudioPlayer() async {
    try {
      await _audioPlayer.setAudioContext(AudioContext(
        iOS: AudioContextIOS(
          category: AVAudioSessionCategory.playback,
          options: const {},
        ),
        android: const AudioContextAndroid(
          isSpeakerphoneOn: true,
          stayAwake: true,
          contentType: AndroidContentType.music,
          usageType: AndroidUsageType.media,
          audioFocus: AndroidAudioFocus.gain,
        ),
      ));

      _audioPlayer.onPlayerStateChanged.listen((state) {
        if (mounted) {
          setState(() {
            _isPlayingAudio = (state == PlayerState.playing);
            if (state == PlayerState.completed || state == PlayerState.stopped) {
              _activePlayingTrackId = null;
              _isPlayingAudio = false;
              _isLoadingAudio = false;
            }
          });
        }
      });
    } catch (e) {
      debugPrint('[CreateStoryScreen] AudioPlayer init error: $e');
    }
  }

  String _formatTime(int totalSec) {
    final m = totalSec ~/ 60;
    final s = totalSec % 60;
    return '$m:${s.toString().padLeft(2, '0')}';
  }

  Future<void> _togglePlayTrack(StoryMusicTrack track, {VoidCallback? onStateUpdated}) async {
    if (_activePlayingTrackId == track.id) {
      if (_isPlayingAudio) {
        await _audioPlayer.pause();
        setState(() {
          _isPlayingAudio = false;
        });
        onStateUpdated?.call();
      } else {
        if (_audioPlayer.state == PlayerState.paused) {
          await _audioPlayer.resume();
        } else {
          await _audioPlayer.play(UrlSource(track.audioUrl));
        }
        setState(() {
          _isPlayingAudio = true;
        });
        onStateUpdated?.call();
      }
    } else {
      setState(() {
        _activePlayingTrackId = track.id;
        _isLoadingAudio = true;
        _isPlayingAudio = false;
      });
      onStateUpdated?.call();
      try {
        await _audioPlayer.stop();
        await _audioPlayer.play(UrlSource(track.audioUrl));
        setState(() {
          _isLoadingAudio = false;
          _isPlayingAudio = true;
        });
        onStateUpdated?.call();
      } catch (e) {
        debugPrint('[CreateStoryScreen] Audio play error: $e');
        setState(() {
          _isLoadingAudio = false;
          _isPlayingAudio = false;
          _activePlayingTrackId = null;
        });
        onStateUpdated?.call();
        _showToast('Không thể phát bài hát này', isError: true);
      }
    }
  }

  void _onPhotosChanged(MethodCall call) {
    _loadDevicePhotos();
  }

  Future<void> _loadDevicePhotos() async {
    try {
      PhotoManager.setIgnorePermissionCheck(true);
      final PermissionState ps = await PhotoManager.requestPermissionExtend();
      
      _isLimitedAccess = (ps == PermissionState.limited);
      _isPermissionDenied = (ps == PermissionState.denied || ps == PermissionState.restricted);

      List<AssetEntity> entities = [];

      // 1. First priority: Direct getAssetListRange (works across all authorized/limited photos on iOS)
      try {
        entities = await PhotoManager.getAssetListRange(
          start: 0,
          end: 150,
          type: RequestType.common,
        );
      } catch (e) {
        debugPrint('[CreateStoryScreen] Direct common range error: $e');
      }

      // 2. Direct getAssetListRange for images
      if (entities.isEmpty) {
        try {
          entities = await PhotoManager.getAssetListRange(
            start: 0,
            end: 150,
            type: RequestType.image,
          );
        } catch (e) {
          debugPrint('[CreateStoryScreen] Direct image range error: $e');
        }
      }

      // 3. Fallback: Albums with hasAll: true, onlyAll: false
      if (entities.isEmpty) {
        try {
          final List<AssetPathEntity> paths = await PhotoManager.getAssetPathList(
            type: RequestType.common,
            hasAll: true,
            onlyAll: false,
          );
          for (final path in paths) {
            final list = await path.getAssetListRange(start: 0, end: 150);
            if (list.isNotEmpty) {
              entities = list;
              break;
            }
          }
        } catch (e) {
          debugPrint('[CreateStoryScreen] getAssetPathList error: $e');
        }
      }

      // 4. Fallback: image albums
      if (entities.isEmpty) {
        try {
          final List<AssetPathEntity> paths = await PhotoManager.getAssetPathList(
            type: RequestType.image,
            hasAll: true,
            onlyAll: false,
          );
          for (final path in paths) {
            final list = await path.getAssetListRange(start: 0, end: 150);
            if (list.isNotEmpty) {
              entities = list;
              break;
            }
          }
        } catch (e) {
          debugPrint('[CreateStoryScreen] getAssetPathList image error: $e');
        }
      }

      if (mounted) {
        setState(() {
          _recentAssets = entities;
          _isLoadingAssets = false;
        });
        return;
      }
    } catch (e) {
      debugPrint('[CreateStoryScreen] Lỗi đọc thư viện ảnh: $e');
    }
    if (mounted) {
      setState(() => _isLoadingAssets = false);
    }
  }

  Future<void> _manageLimitedPhotos() async {
    try {
      await PhotoManager.presentLimited();
      await _loadDevicePhotos();
    } catch (e) {
      debugPrint('[CreateStoryScreen] Error presentLimited: $e');
    }
  }

  // --- TEXT STORY STATE ---
  final TextEditingController _textStoryController = TextEditingController();
  int _selectedGradientIndex = 0;

  static const List<List<Color>> _storyGradients = [
    [Color(0xFFFF5E62), Color(0xFFFF9966)],                   // Coral Sunrise
    [Color(0xFF00C6FF), Color(0xFF0072FF)],                   // Cyber Ocean
    [Color(0xFFF857A6), Color(0xFFFF5858)],                   // Neon Sunset
    [Color(0xFF10B981), Color(0xFF34D399)],                   // Spring Emerald
    [Color(0xFFFF8008), Color(0xFFFFC837)],                   // Solar Amber
    [Color(0xFF833AB4), Color(0xFFFD1D1D), Color(0xFFFCB045)], // Instagram Flame
    [Color(0xFFA855F7), Color(0xFFEC4899)],                   // Lavender Rose
    [Color(0xFF0284C7), Color(0xFF38BDF8)],                   // Sky Blue
  ];

  // --- REAL-TIME FILTERS ---
  int _selectedFilterIndex = 0;
  static const List<_StoryFilter> _filters = [
    _StoryFilter(
      name: 'Gốc',
      icon: '✨',
      matrix: [
        1.0, 0.0, 0.0, 0.0, 0.0,
        0.0, 1.0, 0.0, 0.0, 0.0,
        0.0, 0.0, 1.0, 0.0, 0.0,
        0.0, 0.0, 0.0, 1.0, 0.0,
      ],
    ),
    _StoryFilter(
      name: 'Hoàng Hôn',
      icon: '🌅',
      matrix: [
        1.15, 0.0, 0.0, 0.0, 15.0,
        0.0, 1.05, 0.0, 0.0, 10.0,
        0.0, 0.0, 0.85, 0.0, -10.0,
        0.0, 0.0, 0.0, 1.0, 0.0,
      ],
    ),
    _StoryFilter(
      name: 'Điện Ảnh',
      icon: '🎬',
      matrix: [
        1.2, 0.0, 0.0, 0.0, 10.0,
        0.0, 1.0, 0.0, 0.0, 0.0,
        0.0, 0.0, 1.25, 0.0, 20.0,
        0.0, 0.0, 0.0, 1.0, 0.0,
      ],
    ),
    _StoryFilter(
      name: 'Cổ Điển',
      icon: '🎞️',
      matrix: [
        0.9, 0.1, 0.1, 0.0, 20.0,
        0.0, 0.85, 0.1, 0.0, 15.0,
        0.0, 0.1, 0.75, 0.0, 10.0,
        0.0, 0.0, 0.0, 1.0, 0.0,
      ],
    ),
    _StoryFilter(
      name: 'Trắng Đen',
      icon: '🖤',
      matrix: [
        0.299, 0.587, 0.114, 0.0, 0.0,
        0.299, 0.587, 0.114, 0.0, 0.0,
        0.299, 0.587, 0.114, 0.0, 0.0,
        0.0, 0.0, 0.0, 1.0, 0.0,
      ],
    ),
    _StoryFilter(
      name: 'Tươi Tắn',
      icon: '🌸',
      matrix: [
        1.3, -0.15, -0.15, 0.0, 0.0,
        -0.15, 1.3, -0.15, 0.0, 0.0,
        -0.15, -0.15, 1.3, 0.0, 0.0,
        0.0, 0.0, 0.0, 1.0, 0.0,
      ],
    ),
    _StoryFilter(
      name: 'Đêm Đông Anh',
      icon: '🌃',
      matrix: [
        0.8, 0.0, 0.0, 0.0, 0.0,
        0.0, 0.9, 0.0, 0.0, 5.0,
        0.0, 0.0, 1.3, 0.0, 25.0,
        0.0, 0.0, 0.0, 1.0, 0.0,
      ],
    ),
    _StoryFilter(
      name: 'Nắng Sớm',
      icon: '☕',
      matrix: [
        0.393, 0.769, 0.189, 0.0, 0.0,
        0.349, 0.686, 0.168, 0.0, 0.0,
        0.272, 0.534, 0.131, 0.0, 0.0,
        0.0, 0.0, 0.0, 1.0, 0.0,
      ],
    ),
  ];

  // --- OVERLAYS: TEXT & STICKERS ---
  final List<_StoryTextItem> _textItems = [];
  final List<_StoryStickerItem> _stickerItems = [];

  // --- DOODLE & DRAWING STATE ---
  bool _isDoodleMode = false;
  final List<_DoodleStroke> _strokes = [];
  _DoodleStroke? _activeStroke;
  Color _doodleColor = const Color(0xFF0EA5E9);
  final double _doodleWidth = 4.0;
  bool _doodleNeon = false;

  // --- MUSIC & LOCATION TAGS ---
  String? _selectedMusicName;
  String? _selectedLocationTag;

  // --- PRIVACY & UPLOAD ---
  String _privacySetting = 'public';
  bool _isUploading = false;
  String _uploadStatus = '';
  bool _isHoveringDelete = false;

  final List<String> _dongAnhLocations = const [
    'Huyện Đông Anh',
    'Khu Di Tích Cổ Loa',
    'Thị trấn Đông Anh',
    'Cầu Nhật Tân',
    'Đền Sái - Thụy Lâm',
    'Xã Tiên Dương',
    'Xã Uy Nỗ',
    'Xã Kim Chung',
    'Xã Nam Hồng',
    'Bờ Kênh Sông Thiếp',
  ];



  @override
  void dispose() {
    _disposeVideoController();
    _audioPlayer.stop();
    _audioPlayer.dispose();
    PhotoManager.removeChangeCallback(_onPhotosChanged);
    _textStoryController.dispose();
    super.dispose();
  }

  // ==========================================
  // MEDIA ACTIONS
  // ==========================================

  Future<void> _pickFromGallery() async {
    try {
      final XFile? file = await _picker.pickMedia();
      if (file != null) {
        final isVideo = _isPathVideo(file.path);
        setState(() {
          _previewMedia = file;
          _isTextStoryMode = false;
          _isVideoStory = isVideo;
          _originalVideoPath = isVideo ? file.path : null;
          _rotationQuarterTurns = 0;
        });
        if (isVideo) {
          await _initVideoPreview(file.path);
        } else {
          await _disposeVideoController();
        }
      }
    } catch (e) {
      _showToast('Không thể mở thư viện ảnh: $e', isError: true);
    }
  }

  Future<void> _openCamera() async {
    try {
      final XFile? photo = await _picker.pickImage(source: ImageSource.camera, imageQuality: 90);
      if (photo != null) {
        await _disposeVideoController();
        setState(() {
          _previewMedia = photo;
          _isTextStoryMode = false;
          _isVideoStory = false;
          _originalVideoPath = null;
          _rotationQuarterTurns = 0;
        });
      }
    } catch (e) {
      _showToast('Không thể mở máy ảnh: $e', isError: true);
    }
  }

  // ==========================================
  // STORY COMPOSITE & PUBLISH
  // ==========================================

  Future<String?> _captureStoryImage() async {
    try {
      final boundary = _storyCanvasKey.currentContext?.findRenderObject() as RenderRepaintBoundary?;
      if (boundary == null) return null;
      final image = await boundary.toImage(pixelRatio: 2.0);
      final byteData = await image.toByteData(format: ui.ImageByteFormat.png);
      if (byteData == null) return null;
      final buffer = byteData.buffer.asUint8List();

      final tempDir = Directory.systemTemp;
      final filePath = '${tempDir.path}/story_${DateTime.now().millisecondsSinceEpoch}.png';
      final file = File(filePath);
      await file.writeAsBytes(buffer);
      return file.path;
    } catch (e) {
      debugPrint('[CreateStoryScreen] Capture error: $e');
      return null;
    }
  }

  Future<void> _publishStory() async {
    if (_previewMedia == null && !_isTextStoryMode && _textItems.isEmpty && _textStoryController.text.isEmpty) {
      _showToast('Vui lòng thêm hình ảnh hoặc nội dung cho Story', isError: true);
      return;
    }

    _stopEditorBackgroundMusic();

    setState(() {
      _isUploading = true;
      _uploadStatus = 'Đang xử lý và đăng tải tin...';
    });

    try {
      String? effectivePath;
      if (_isVideoStory && _originalVideoPath != null) {
        effectivePath = _originalVideoPath;
      } else {
        final capturedPath = await _captureStoryImage();
        effectivePath = capturedPath ?? _previewMedia?.path;
      }

      final textCaption = _textStoryController.text.trim().isNotEmpty
          ? _textStoryController.text.trim()
          : (_textItems.isNotEmpty
              ? _textItems.first.text
              : (_selectedLocationTag != null ? 'Check-in tại $_selectedLocationTag' : 'Tin mới'));

      var finalCaption = textCaption;
      if (_selectedMusicName != null && !finalCaption.contains('🎵')) {
        finalCaption = '$finalCaption\n🎵 $_selectedMusicName (${_musicStartSeconds.toInt()}s - ${(_musicStartSeconds + _musicSegmentDuration).toInt()}s)';
      }

      final result = await ApiService.createStory(
        caption: finalCaption,
        mediaPath: effectivePath,
      );

      if (mounted) {
        if (result['success'] == true) {
          _showToast('Đã chia sẻ tin thành công!', isSuccess: true);
          Navigator.of(context).pop(true);
        } else {
          _showToast(result['message'] ?? 'Đăng tin thất bại', isError: true);
        }
      }
    } catch (e) {
      if (mounted) {
        _showToast('Lỗi khi đăng tin: $e', isError: true);
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

  void _showToast(String message, {bool isSuccess = false, bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(
              isSuccess ? Icons.check_circle_rounded : (isError ? Icons.error_outline : Icons.info_outline),
              color: Colors.white,
              size: 20,
            ),
            const SizedBox(width: 8),
            Expanded(child: Text(message, style: const TextStyle(fontWeight: FontWeight.w600))),
          ],
        ),
        backgroundColor: isSuccess ? const Color(0xFF059669) : (isError ? const Color(0xFFE11D48) : const Color(0xFF0F172A)),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  // ==========================================
  // BUILD METHOD
  // ==========================================

  @override
  Widget build(BuildContext context) {
    if (_previewMedia != null || _isTextStoryMode) {
      return _buildStoryEditorView();
    }
    return _buildStoryHubView();
  }

  // ==========================================
  // 1. STORY HUB VIEW (STUDIO DARK MODE)
  // ==========================================

  Widget _buildStoryHubView() {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        shadowColor: Colors.black12,
        leading: IconButton(
          icon: const Icon(Icons.close_rounded, color: Color(0xFF0F172A), size: 26),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text(
          'Tạo Tin Mới',
          style: TextStyle(color: Color(0xFF0F172A), fontSize: 18, fontWeight: FontWeight.bold),
        ),
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.privacy_tip_outlined, color: Color(0xFF64748B), size: 22),
            onPressed: _showPrivacyModal,
            tooltip: 'Quyền riêng tư',
          ),
        ],
      ),
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 12),
            // Quick Creative Modes Bar
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  _buildCreativeModeTile(
                    title: 'Văn bản',
                    icon: Icons.title_rounded,
                    gradient: const [Color(0xFF833AB4), Color(0xFFFD1D1D)],
                    onTap: () => setState(() => _isTextStoryMode = true),
                  ),
                  const SizedBox(width: 10),
                  _buildCreativeModeTile(
                    title: 'Máy ảnh',
                    icon: Icons.camera_alt_rounded,
                    gradient: const [Color(0xFF0EA5E9), Color(0xFF2563EB)],
                    onTap: _openCamera,
                  ),
                  const SizedBox(width: 10),
                  _buildCreativeModeTile(
                    title: 'Âm nhạc',
                    icon: Icons.music_note_rounded,
                    gradient: const [Color(0xFFF59E0B), Color(0xFFD97706)],
                    onTap: _showMusicModal,
                  ),
                  const SizedBox(width: 10),
                  _buildCreativeModeTile(
                    title: 'Cổ Loa',
                    icon: Icons.account_balance_rounded,
                    gradient: const [Color(0xFF10B981), Color(0xFF059669)],
                    onTap: () {
                      setState(() {
                        _isTextStoryMode = true;
                        _selectedGradientIndex = 0;
                        _stickerItems.add(
                          _StoryStickerItem(
                            id: DateTime.now().millisecondsSinceEpoch.toString(),
                            kind: _StickerKind.dongAnhBadge,
                            label: 'Cổ Loa Thành',
                            icon: '🏛️',
                            position: const Offset(80, 200),
                          ),
                        );
                      });
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            // Cultural Dong Anh Templates Banner
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFFE0F2FE), Color(0xFFF0FDF4)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFBAE6FD)),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF0284C7).withValues(alpha: 0.06),
                      blurRadius: 10,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: const Color(0xFF0284C7).withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.explore_rounded, color: Color(0xFF0284C7), size: 28),
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Khám Phá & Check-in Đông Anh',
                            style: TextStyle(color: Color(0xFF0F172A), fontSize: 14, fontWeight: FontWeight.bold),
                          ),
                          SizedBox(height: 4),
                          Text(
                            'Chia sẻ khoảnh khắc di tích Cổ Loa, ẩm thực và văn hóa làng nghề.',
                            style: TextStyle(color: Color(0xFF475569), fontSize: 11.5),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 18),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      const Text(
                        'Thư viện thiết bị',
                        style: TextStyle(color: Color(0xFF0F172A), fontSize: 15, fontWeight: FontWeight.bold),
                      ),
                      if (_recentAssets.isNotEmpty) ...[
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: const Color(0xFFE0F2FE),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: const Color(0xFFBAE6FD)),
                          ),
                          child: Text(
                            '${_recentAssets.length}',
                            style: const TextStyle(color: Color(0xFF0284C7), fontSize: 11, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ],
                    ],
                  ),
                  if (_isLimitedAccess)
                    GestureDetector(
                      onTap: _manageLimitedPhotos,
                      child: const Row(
                        children: [
                          Text(
                            'Quản lý ảnh',
                            style: TextStyle(color: Color(0xFF0284C7), fontSize: 12.5, fontWeight: FontWeight.bold),
                          ),
                          SizedBox(width: 2),
                          Icon(Icons.tune_rounded, color: Color(0xFF0284C7), size: 16),
                        ],
                      ),
                    )
                  else
                    GestureDetector(
                      onTap: _pickFromGallery,
                      child: const Row(
                        children: [
                          Text(
                            'Chọn ảnh khác',
                            style: TextStyle(color: Color(0xFF64748B), fontSize: 12, fontWeight: FontWeight.w500),
                          ),
                          SizedBox(width: 2),
                          Icon(Icons.chevron_right_rounded, color: Color(0xFF64748B), size: 16),
                        ],
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            Expanded(
              child: _isLoadingAssets
                  ? _buildSkeletonGrid()
                  : _recentAssets.isEmpty
                      ? _buildEmptyGalleryPrompt()
                      : _buildLiveGalleryGrid(),
            ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

  Widget _buildCreativeModeTile({
    required String title,
    required IconData icon,
    required List<Color> gradient,
    required VoidCallback onTap,
  }) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: const Color(0xFFE2E8F0)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: LinearGradient(colors: gradient),
                  boxShadow: [
                    BoxShadow(
                      color: gradient.first.withValues(alpha: 0.3),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Icon(icon, color: Colors.white, size: 21),
              ),
              const SizedBox(height: 8),
              Text(
                title,
                style: const TextStyle(color: Color(0xFF1E293B), fontSize: 12, fontWeight: FontWeight.w600),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ==========================================
  // 2. FULL CREATIVE STORY EDITOR VIEW
  // ==========================================

  Widget _buildStoryEditorView() {
    final currentGradient = _storyGradients[_selectedGradientIndex % _storyGradients.length];
    final activeFilter = _filters[_selectedFilterIndex % _filters.length];

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // COMPOSITE CANVAS (WRAPPED IN REPAINT BOUNDARY)
          RepaintBoundary(
            key: _storyCanvasKey,
            child: RotatedBox(
              quarterTurns: _rotationQuarterTurns,
              child: Stack(
                fit: StackFit.expand,
                children: [
                  // 1. Background Media or Gradient
                  if (_isTextStoryMode || _previewMedia == null)
                    Container(
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
                              hintText: 'Chạm để nhập văn bản...',
                              hintStyle: TextStyle(color: Colors.white60, fontSize: 24),
                              border: InputBorder.none,
                            ),
                          ),
                        ),
                      ),
                    )
                  else
                    ColorFiltered(
                      colorFilter: ColorFilter.matrix(activeFilter.matrix),
                      child: Stack(
                        fit: StackFit.expand,
                        children: [
                          if (_isVideoStory && _videoPlayerController != null && _isVideoInitialized)
                            GestureDetector(
                              onTap: _toggleVideoPlayPause,
                              behavior: HitTestBehavior.opaque,
                              child: SizedBox.expand(
                                child: FittedBox(
                                  fit: BoxFit.cover,
                                  clipBehavior: Clip.hardEdge,
                                  child: SizedBox(
                                    width: _videoPlayerController!.value.size.width,
                                    height: _videoPlayerController!.value.size.height,
                                    child: VideoPlayer(_videoPlayerController!),
                                  ),
                                ),
                              ),
                            )
                          else if (_previewMedia != null && !_isPathVideo(_previewMedia!.path))
                            Image.file(
                              File(_previewMedia!.path),
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) => Container(
                                color: const Color(0xFF0F172A),
                                child: const Center(
                                  child: Icon(Icons.broken_image_rounded, color: Colors.white54, size: 48),
                                ),
                              ),
                            )
                          else
                            Container(
                              color: const Color(0xFF0F172A),
                              child: const Center(
                                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                              ),
                            ),
                          if (_isVideoStory) ...[
                            // Top overlay: Live Video Story badge + Mute/Unmute
                            Positioned(
                              top: 60,
                              left: 16,
                              right: 16,
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                    decoration: BoxDecoration(
                                      color: Colors.black.withValues(alpha: 0.75),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(color: const Color(0xFF0EA5E9), width: 1.2),
                                    ),
                                    child: Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        const Icon(Icons.play_circle_fill_rounded, color: Color(0xFF38BDF8), size: 16),
                                        const SizedBox(width: 5),
                                        Text(
                                          _videoPlayerController != null && _isVideoInitialized
                                              ? 'Video Story (${_videoPlayerController!.value.duration.inSeconds}s)'
                                              : 'Video Story 24h',
                                          style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                                        ),
                                      ],
                                    ),
                                  ),
                                  GestureDetector(
                                    onTap: _toggleVideoMute,
                                    child: Container(
                                      padding: const EdgeInsets.all(7),
                                      decoration: BoxDecoration(
                                        color: Colors.black.withValues(alpha: 0.75),
                                        shape: BoxShape.circle,
                                        border: Border.all(color: Colors.white38),
                                      ),
                                      child: Icon(
                                        _isVideoMuted ? Icons.volume_off_rounded : Icons.volume_up_rounded,
                                        color: Colors.white,
                                        size: 18,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            // Center Play/Pause icon when paused
                            if (!_isVideoPlaying)
                              Center(
                                child: GestureDetector(
                                  onTap: _toggleVideoPlayPause,
                                  child: Container(
                                    padding: const EdgeInsets.all(16),
                                    decoration: BoxDecoration(
                                      color: Colors.black.withValues(alpha: 0.6),
                                      shape: BoxShape.circle,
                                      border: Border.all(color: Colors.white30, width: 2),
                                    ),
                                    child: const Icon(
                                      Icons.play_arrow_rounded,
                                      color: Colors.white,
                                      size: 48,
                                    ),
                                  ),
                                ),
                              ),
                          ],
                        ],
                      ),
                    ),

                  // 2. Doodle Strokes Layer
                  CustomPaint(
                    painter: _DoodlePainter(
                      strokes: _strokes,
                      activeStroke: _activeStroke,
                    ),
                    size: Size.infinite,
                  ),

                  // 3. Location Badge Overlay
                  if (_selectedLocationTag != null)
                    Positioned(
                      top: 110,
                      left: 20,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: Colors.black.withValues(alpha: 0.65),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: const Color(0xFF0EA5E9), width: 1.2),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.location_on_rounded, color: Color(0xFF38BDF8), size: 16),
                            const SizedBox(width: 4),
                            Text(
                              _selectedLocationTag!,
                              style: const TextStyle(color: Colors.white, fontSize: 12.5, fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ),
                    ),

                  // 4. Music Indicator Overlay (Tap to adjust segment)
                  if (_selectedMusicName != null)
                    Positioned(
                      top: 110,
                      right: 20,
                      child: GestureDetector(
                        onTap: () {
                          if (_selectedTrackObj != null) {
                            _showMusicTrimmerModal(_selectedTrackObj!);
                          } else {
                            _showMusicModal();
                          }
                        },
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.75),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: const Color(0xFF0EA5E9), width: 1.2),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF0EA5E9).withValues(alpha: 0.3),
                                blurRadius: 8,
                              ),
                            ],
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.music_note_rounded, color: Color(0xFF38BDF8), size: 16),
                              const SizedBox(width: 5),
                              ConstrainedBox(
                                constraints: const BoxConstraints(maxWidth: 160),
                                child: Text(
                                  _selectedMusicName!,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                                ),
                              ),
                              const SizedBox(width: 4),
                              const Icon(Icons.tune_rounded, color: Colors.white70, size: 14),
                            ],
                          ),
                        ),
                      ),
                    ),

                  // 5. Draggable Stickers Layer
                  for (final sticker in _stickerItems) _buildDraggableSticker(sticker),

                  // 6. Draggable Text Layer
                  for (final textItem in _textItems) _buildDraggableText(textItem),
                ],
              ),
            ),
          ),

          // DOODLE GESTURE DETECTOR (Active only in Doodle Mode)
          if (_isDoodleMode)
            Positioned.fill(
              child: GestureDetector(
                onPanStart: (details) {
                  setState(() {
                    _activeStroke = _DoodleStroke(
                      points: [details.localPosition],
                      color: _doodleColor,
                      strokeWidth: _doodleWidth,
                      isNeon: _doodleNeon,
                    );
                  });
                },
                onPanUpdate: (details) {
                  setState(() {
                    _activeStroke?.points.add(details.localPosition);
                  });
                },
                onPanEnd: (_) {
                  setState(() {
                    if (_activeStroke != null) {
                      _strokes.add(_activeStroke!);
                      _activeStroke = null;
                    }
                  });
                },
              ),
            ),

          // TOP TOOLBAR
          Positioned(
            top: 45,
            left: 12,
            right: 12,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                // Back Button
                _buildCircleIconButton(
                  icon: Icons.arrow_back_ios_new_rounded,
                  onPressed: () {
                    _disposeVideoController();
                    setState(() {
                      _previewMedia = null;
                      _isTextStoryMode = false;
                      _isVideoStory = false;
                      _originalVideoPath = null;
                      _strokes.clear();
                      _textItems.clear();
                      _stickerItems.clear();
                      _textStoryController.clear();
                    });
                  },
                ),
                const SizedBox(width: 8),
                // Creative Editing Tools (Scrollable to prevent overflow on smaller screens)
                Expanded(
                  child: SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    reverse: true,
                    physics: const BouncingScrollPhysics(),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        if (_isTextStoryMode) ...[
                          _buildCircleIconButton(
                            icon: Icons.palette_outlined,
                            tooltip: 'Đổi dải màu nền',
                            onPressed: () {
                              setState(() {
                                _selectedGradientIndex = (_selectedGradientIndex + 1) % _storyGradients.length;
                              });
                            },
                          ),
                          const SizedBox(width: 8),
                        ],
                        _buildCircleIconButton(
                          icon: Icons.rotate_right_rounded,
                          tooltip: 'Xoay ảnh 90°',
                          onPressed: () {
                            setState(() {
                              _rotationQuarterTurns = (_rotationQuarterTurns + 1) % 4;
                            });
                          },
                        ),
                        const SizedBox(width: 8),
                        _buildCircleIconButton(
                          icon: _isDoodleMode ? Icons.check_rounded : Icons.gesture_rounded,
                          color: _isDoodleMode ? const Color(0xFF0EA5E9) : null,
                          tooltip: 'Bút vẽ cọ',
                          onPressed: () {
                            setState(() => _isDoodleMode = !_isDoodleMode);
                          },
                        ),
                        const SizedBox(width: 8),
                        _buildCircleIconButton(
                          icon: Icons.title_rounded,
                          tooltip: 'Thêm chữ nghệ thuật',
                          onPressed: _showAddTextModal,
                        ),
                        const SizedBox(width: 8),
                        _buildCircleIconButton(
                          icon: Icons.sentiment_satisfied_alt_rounded,
                          tooltip: 'Nhãn dán & Sticker',
                          onPressed: _showStickerModal,
                        ),
                        const SizedBox(width: 8),
                        _buildCircleIconButton(
                          icon: Icons.location_on_outlined,
                          tooltip: 'Gắn địa danh Đông Anh',
                          onPressed: _showLocationModal,
                        ),
                        const SizedBox(width: 8),
                        _buildCircleIconButton(
                          icon: Icons.music_note_rounded,
                          tooltip: 'Nhạc nền',
                          onPressed: _showMusicModal,
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),

          // DOODLE TOOLBAR (Visible when drawing)
          if (_isDoodleMode)
            Positioned(
              top: 105,
              left: 16,
              right: 16,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.black.withValues(alpha: 0.75),
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: Colors.white24),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        _buildColorDot(const Color(0xFF0EA5E9)),
                        _buildColorDot(const Color(0xFFEF4444)),
                        _buildColorDot(const Color(0xFFF59E0B)),
                        _buildColorDot(const Color(0xFF10B981)),
                        _buildColorDot(Colors.white),
                        _buildColorDot(const Color(0xFFA855F7)),
                      ],
                    ),
                    Row(
                      children: [
                        GestureDetector(
                          onTap: () => setState(() => _doodleNeon = !_doodleNeon),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: _doodleNeon ? const Color(0xFF0EA5E9) : Colors.transparent,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Text('Neon', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                          ),
                        ),
                        const SizedBox(width: 6),
                        IconButton(
                          icon: const Icon(Icons.undo_rounded, color: Colors.white, size: 20),
                          padding: EdgeInsets.zero,
                          constraints: const BoxConstraints(),
                          onPressed: () {
                            if (_strokes.isNotEmpty) {
                              setState(() => _strokes.removeLast());
                            }
                          },
                        ),
                        const SizedBox(width: 8),
                        IconButton(
                          icon: const Icon(Icons.delete_outline_rounded, color: Colors.white, size: 20),
                          padding: EdgeInsets.zero,
                          constraints: const BoxConstraints(),
                          onPressed: () => setState(() => _strokes.clear()),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),

          // BOTTOM FILTERS BAR (Only for Image Mode)
          if (!_isTextStoryMode && !_isDoodleMode)
            Positioned(
              bottom: 90,
              left: 0,
              right: 0,
              child: SizedBox(
                height: 74,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: _filters.length,
                  separatorBuilder: (_, __) => const SizedBox(width: 10),
                  itemBuilder: (context, index) {
                    final filter = _filters[index];
                    final isSelected = _selectedFilterIndex == index;
                    return GestureDetector(
                      onTap: () => setState(() => _selectedFilterIndex = index),
                      child: Container(
                        width: 66,
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        decoration: BoxDecoration(
                          color: isSelected ? const Color(0xFF0EA5E9).withValues(alpha: 0.3) : Colors.black45,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: isSelected ? const Color(0xFF0EA5E9) : Colors.white24,
                            width: isSelected ? 2 : 1,
                          ),
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(filter.icon, style: const TextStyle(fontSize: 18)),
                            const SizedBox(height: 4),
                            Text(
                              filter.name,
                              style: TextStyle(
                                color: isSelected ? const Color(0xFF38BDF8) : Colors.white,
                                fontSize: 10,
                                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ),
            ),

          // DELETE DROP ZONE (Appears when dragging items)
          if (_isHoveringDelete)
            Positioned(
              bottom: 180,
              left: 0,
              right: 0,
              child: Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  decoration: BoxDecoration(
                    color: Colors.red.withValues(alpha: 0.85),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.delete_rounded, color: Colors.white, size: 20),
                      SizedBox(width: 6),
                      Text('Thả vào đây để xóa', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
              ),
            ),

          // BOTTOM ACTION BAR: PRIVACY & PUBLISH
          Positioned(
            bottom: 24,
            left: 16,
            right: 16,
            child: Row(
              children: [
                GestureDetector(
                  onTap: _showPrivacyModal,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 13),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(28),
                      border: Border.all(color: Colors.white24),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          _privacySetting == 'public'
                              ? Icons.public
                              : (_privacySetting == 'friends' ? Icons.people : Icons.lock),
                          color: Colors.white,
                          size: 16,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          _privacySetting == 'public'
                              ? 'Công khai'
                              : (_privacySetting == 'friends' ? 'Bạn bè' : 'Chỉ mình tôi'),
                          style: const TextStyle(color: Colors.white, fontSize: 12.5, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _isUploading ? null : _publishStory,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0EA5E9),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          _isUploading ? 'Đang tải lên...' : 'Chia sẻ lên tin',
                          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(width: 8),
                        const Icon(Icons.send_rounded, size: 16),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),

          // UPLOAD PROGRESS MODAL
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
                        style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildColorDot(Color color) {
    final isSelected = _doodleColor == color;
    return GestureDetector(
      onTap: () => setState(() => _doodleColor = color),
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 4),
        width: 22,
        height: 22,
        decoration: BoxDecoration(
          color: color,
          shape: BoxShape.circle,
          border: Border.all(color: isSelected ? Colors.white : Colors.white38, width: isSelected ? 2.5 : 1),
          boxShadow: isSelected ? [BoxShadow(color: color.withValues(alpha: 0.6), blurRadius: 6)] : null,
        ),
      ),
    );
  }

  // ==========================================
  // DRAGGABLE TEXT & STICKER BUILDERS
  // ==========================================

  Widget _buildDraggableText(_StoryTextItem item) {
    TextStyle textStyle;
    switch (item.fontFamily) {
      case _StoryFontFamily.script:
        textStyle = TextStyle(color: item.color, fontSize: item.fontSize, fontStyle: FontStyle.italic, fontWeight: FontWeight.bold);
        break;
      case _StoryFontFamily.serif:
        textStyle = TextStyle(color: item.color, fontSize: item.fontSize, fontFamily: 'Serif', fontWeight: FontWeight.bold);
        break;
      case _StoryFontFamily.neon:
        textStyle = TextStyle(
          color: item.color,
          fontSize: item.fontSize,
          fontWeight: FontWeight.bold,
          shadows: [Shadow(color: item.color, blurRadius: 16), const Shadow(color: Colors.white, blurRadius: 4)],
        );
        break;
      case _StoryFontFamily.typewriter:
        textStyle = TextStyle(color: item.color, fontSize: item.fontSize, fontFamily: 'Courier', fontWeight: FontWeight.w700);
        break;
      case _StoryFontFamily.modern:
        textStyle = TextStyle(color: item.color, fontSize: item.fontSize, fontWeight: FontWeight.bold);
        break;
    }

    BoxDecoration? boxDeco;
    switch (item.textBg) {
      case _StoryTextBg.pill:
        boxDeco = BoxDecoration(color: item.bgColor.withValues(alpha: 0.7), borderRadius: BorderRadius.circular(16));
        break;
      case _StoryTextBg.solid:
        boxDeco = BoxDecoration(color: item.bgColor, borderRadius: BorderRadius.circular(8));
        break;
      case _StoryTextBg.outline:
        boxDeco = BoxDecoration(border: Border.all(color: item.color, width: 2), borderRadius: BorderRadius.circular(12));
        break;
      case _StoryTextBg.none:
        boxDeco = null;
        break;
    }

    return Positioned(
      left: item.position.dx,
      top: item.position.dy,
      child: GestureDetector(
        onPanUpdate: (details) {
          setState(() {
            item.position += details.delta;
            _isHoveringDelete = item.position.dy > 550;
          });
        },
        onPanEnd: (_) {
          if (_isHoveringDelete) {
            setState(() {
              _textItems.removeWhere((t) => t.id == item.id);
              _isHoveringDelete = false;
            });
            _showToast('Đã xóa chữ');
          }
        },
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
          decoration: boxDeco,
          child: Text(item.text, style: textStyle, textAlign: item.align),
        ),
      ),
    );
  }

  Widget _buildDraggableSticker(_StoryStickerItem item) {
    Widget stickerWidget;
    switch (item.kind) {
      case _StickerKind.timeClock:
        final now = DateTime.now();
        final timeStr = '${now.hour.toString().padLeft(2, '0')}:${now.minute.toString().padLeft(2, '0')}';
        stickerWidget = Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.3), blurRadius: 8, offset: const Offset(0, 3))],
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.access_time_filled_rounded, color: Color(0xFF0F172A), size: 18),
              const SizedBox(width: 6),
              Text(timeStr, style: const TextStyle(color: Color(0xFF0F172A), fontSize: 18, fontWeight: FontWeight.bold)),
            ],
          ),
        );
        break;
      case _StickerKind.weather:
        stickerWidget = Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          decoration: BoxDecoration(
            gradient: const LinearGradient(colors: [Color(0xFFF59E0B), Color(0xFFEF4444)]),
            borderRadius: BorderRadius.circular(12),
          ),
          child: const Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.wb_sunny_rounded, color: Colors.white, size: 18),
              SizedBox(width: 6),
              Text('28°C Đông Anh', style: TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold)),
            ],
          ),
        );
        break;
      case _StickerKind.dongAnhBadge:
        stickerWidget = Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          decoration: BoxDecoration(
            gradient: const LinearGradient(colors: [Color(0xFF0EA5E9), Color(0xFF6366F1)]),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: Colors.white38),
            boxShadow: const [BoxShadow(color: Colors.black38, blurRadius: 6, offset: Offset(0, 2))],
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(item.icon ?? '🏛️', style: const TextStyle(fontSize: 16)),
              const SizedBox(width: 6),
              Text(item.label, style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold)),
            ],
          ),
        );
        break;
      case _StickerKind.trending:
        stickerWidget = Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          decoration: BoxDecoration(
            color: const Color(0xFFE11D48),
            borderRadius: BorderRadius.circular(16),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('🔥', style: TextStyle(fontSize: 14)),
              const SizedBox(width: 4),
              Text(item.label, style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
            ],
          ),
        );
        break;
      case _StickerKind.emoji:
        stickerWidget = Text(item.label, style: const TextStyle(fontSize: 48));
        break;
    }

    return Positioned(
      left: item.position.dx,
      top: item.position.dy,
      child: GestureDetector(
        onPanUpdate: (details) {
          setState(() {
            item.position += details.delta;
            _isHoveringDelete = item.position.dy > 550;
          });
        },
        onPanEnd: (_) {
          if (_isHoveringDelete) {
            setState(() {
              _stickerItems.removeWhere((s) => s.id == item.id);
              _isHoveringDelete = false;
            });
            _showToast('Đã xóa nhãn dán');
          }
        },
        child: stickerWidget,
      ),
    );
  }

  // ==========================================
  // MODALS: TEXT EDITOR, STICKERS, LOCATION, MUSIC, PRIVACY
  // ==========================================

  void _showAddTextModal() {
    final textController = TextEditingController();
    _StoryFontFamily currentFont = _StoryFontFamily.modern;
    _StoryTextBg currentBg = _StoryTextBg.pill;
    Color currentColor = Colors.white;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            final bottomInset = MediaQuery.of(context).viewInsets.bottom;
            return Container(
              padding: EdgeInsets.fromLTRB(20, 16, 20, 20 + bottomInset),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: Colors.grey[300],
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Thêm Chữ Nghệ Thuật',
                        style: TextStyle(color: Color(0xFF0F172A), fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                      IconButton(
                        icon: const Icon(Icons.check_rounded, color: Color(0xFF0EA5E9), size: 26),
                        onPressed: () {
                          if (textController.text.trim().isNotEmpty) {
                            setState(() {
                              _textItems.add(
                                _StoryTextItem(
                                  id: DateTime.now().millisecondsSinceEpoch.toString(),
                                  text: textController.text.trim(),
                                  position: const Offset(80, 250),
                                  color: currentColor,
                                  bgColor: const Color(0xFF0F172A),
                                  fontFamily: currentFont,
                                  textBg: currentBg,
                                  fontSize: 24.0,
                                  align: TextAlign.center,
                                ),
                              );
                            });
                          }
                          Navigator.pop(ctx);
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  TextField(
                    controller: textController,
                    autofocus: true,
                    style: const TextStyle(color: Color(0xFF0F172A), fontSize: 18),
                    decoration: InputDecoration(
                      hintText: 'Nhập nội dung chữ...',
                      hintStyle: TextStyle(color: Colors.grey[400]),
                      filled: true,
                      fillColor: const Color(0xFFF8FAFC),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFF0EA5E9), width: 1.5),
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),
                  // Font selector
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: [
                        _buildFontOption('Hiện Đại', _StoryFontFamily.modern, currentFont, (f) => setModalState(() => currentFont = f)),
                        _buildFontOption('Neon Glow', _StoryFontFamily.neon, currentFont, (f) => setModalState(() => currentFont = f)),
                        _buildFontOption('Viết Tay', _StoryFontFamily.script, currentFont, (f) => setModalState(() => currentFont = f)),
                        _buildFontOption('Cổ Điển', _StoryFontFamily.serif, currentFont, (f) => setModalState(() => currentFont = f)),
                        _buildFontOption('Typewriter', _StoryFontFamily.typewriter, currentFont, (f) => setModalState(() => currentFont = f)),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                  // Color selector
                  Row(
                    children: [
                      _buildModalColorCircle(Colors.white, currentColor, (c) => setModalState(() => currentColor = c)),
                      _buildModalColorCircle(const Color(0xFF0F172A), currentColor, (c) => setModalState(() => currentColor = c)),
                      _buildModalColorCircle(const Color(0xFFFBBF24), currentColor, (c) => setModalState(() => currentColor = c)),
                      _buildModalColorCircle(const Color(0xFFEF4444), currentColor, (c) => setModalState(() => currentColor = c)),
                      _buildModalColorCircle(const Color(0xFF0EA5E9), currentColor, (c) => setModalState(() => currentColor = c)),
                      _buildModalColorCircle(const Color(0xFF10B981), currentColor, (c) => setModalState(() => currentColor = c)),
                      _buildModalColorCircle(const Color(0xFFA855F7), currentColor, (c) => setModalState(() => currentColor = c)),
                    ],
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildFontOption(String label, _StoryFontFamily font, _StoryFontFamily selected, ValueChanged<_StoryFontFamily> onSelect) {
    final isSelected = font == selected;
    return GestureDetector(
      onTap: () => onSelect(font),
      child: Container(
        margin: const EdgeInsets.only(right: 8),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFFE2E8F0)),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? Colors.white : const Color(0xFF475569),
            fontSize: 12,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }

  Widget _buildModalColorCircle(Color color, Color selectedColor, ValueChanged<Color> onSelect) {
    final isSelected = color == selectedColor;
    return GestureDetector(
      onTap: () => onSelect(color),
      child: Container(
        width: 28,
        height: 28,
        margin: const EdgeInsets.only(right: 10),
        decoration: BoxDecoration(
          color: color,
          shape: BoxShape.circle,
          border: Border.all(
            color: isSelected ? const Color(0xFF0F172A) : Colors.grey.shade300,
            width: isSelected ? 2.5 : 1,
          ),
          boxShadow: [
            if (isSelected)
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.15),
                blurRadius: 4,
                offset: const Offset(0, 2),
              ),
          ],
        ),
      ),
    );
  }

  void _showStickerModal() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) {
        return DefaultTabController(
          length: 3,
          child: Container(
            padding: const EdgeInsets.all(20),
            height: 380,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: Colors.grey[300],
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                const Text('Kho Nhãn Dán & Sticker', style: TextStyle(color: Color(0xFF0F172A), fontSize: 17, fontWeight: FontWeight.bold)),
                const SizedBox(height: 10),
                const TabBar(
                  indicatorColor: Color(0xFF0EA5E9),
                  labelColor: Color(0xFF0EA5E9),
                  unselectedLabelColor: Color(0xFF64748B),
                  tabs: [
                    Tab(text: 'Đông Anh'),
                    Tab(text: 'Huy hiệu'),
                    Tab(text: 'Emoji'),
                  ],
                ),
                const SizedBox(height: 14),
                Expanded(
                  child: TabBarView(
                    children: [
                      // Tab 1: Dong Anh Culture Badges
                      Wrap(
                        spacing: 10,
                        runSpacing: 10,
                        children: [
                          _buildStickerButton('🏛️', 'Check-in Cổ Loa', _StickerKind.dongAnhBadge),
                          _buildStickerButton('🏮', 'Lễ Hội Đền Sái', _StickerKind.dongAnhBadge),
                          _buildStickerButton('🍲', 'Ẩm Thực Đông Anh', _StickerKind.dongAnhBadge),
                          _buildStickerButton('🌉', 'Cầu Nhật Tân', _StickerKind.dongAnhBadge),
                          _buildStickerButton('📍', 'Đông Anh Hôm Nay', _StickerKind.dongAnhBadge),
                          _buildStickerButton('❤️', 'Yêu Đông Anh', _StickerKind.dongAnhBadge),
                        ],
                      ),
                      // Tab 2: Clock & Weather Widgets
                      Wrap(
                        spacing: 10,
                        runSpacing: 10,
                        children: [
                          _buildStickerButton('⏰', 'Đồng Hồ Thời Gian', _StickerKind.timeClock),
                          _buildStickerButton('🌤️', 'Thời Tiết 28°C', _StickerKind.weather),
                          _buildStickerButton('🔥', 'Siêu Hot Trending', _StickerKind.trending),
                          _buildStickerButton('☕', 'Chill Cuối Tuần', _StickerKind.trending),
                        ],
                      ),
                      // Tab 3: Emojis
                      GridView.count(
                        crossAxisCount: 6,
                        mainAxisSpacing: 10,
                        crossAxisSpacing: 10,
                        children: ['😍', '🔥', '🎉', '🌸', '☕', '🍜', '🚀', '💯', '✨', '💖', '🌿', '🏖️'].map((emoji) {
                          return GestureDetector(
                            onTap: () {
                              setState(() {
                                _stickerItems.add(
                                  _StoryStickerItem(
                                    id: DateTime.now().millisecondsSinceEpoch.toString(),
                                    kind: _StickerKind.emoji,
                                    label: emoji,
                                    position: const Offset(140, 300),
                                  ),
                                );
                              });
                              Navigator.pop(ctx);
                            },
                            child: Center(child: Text(emoji, style: const TextStyle(fontSize: 32))),
                          );
                        }).toList(),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildStickerButton(String icon, String label, _StickerKind kind) {
    return ActionChip(
      avatar: Text(icon),
      label: Text(label, style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 12)),
      backgroundColor: const Color(0xFFF8FAFC),
      side: const BorderSide(color: Color(0xFFE2E8F0)),
      onPressed: () {
        setState(() {
          _stickerItems.add(
            _StoryStickerItem(
              id: DateTime.now().millisecondsSinceEpoch.toString(),
              kind: kind,
              label: label,
              icon: icon,
              position: const Offset(80, 320),
            ),
          );
        });
        Navigator.pop(context);
      },
    );
  }

  void _showLocationModal() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) {
        return Container(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey[300],
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 14),
              const Row(
                children: [
                  Icon(Icons.location_on_rounded, color: Color(0xFF0EA5E9), size: 22),
                  SizedBox(width: 8),
                  Text('Gắn Địa Danh Đông Anh', style: TextStyle(color: Color(0xFF0F172A), fontSize: 17, fontWeight: FontWeight.bold)),
                ],
              ),
              const SizedBox(height: 16),
              Flexible(
                child: ListView.separated(
                  shrinkWrap: true,
                  itemCount: _dongAnhLocations.length,
                  separatorBuilder: (_, __) => const Divider(color: Color(0xFFF1F5F9), height: 1),
                  itemBuilder: (context, idx) {
                    final loc = _dongAnhLocations[idx];
                    final isSelected = _selectedLocationTag == loc;
                    return ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: Icon(
                        Icons.place_outlined,
                        color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFF64748B),
                      ),
                      title: Text(
                        loc,
                        style: TextStyle(
                          color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFF1E293B),
                          fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                        ),
                      ),
                      trailing: isSelected ? const Icon(Icons.check_circle_rounded, color: Color(0xFF0EA5E9)) : null,
                      onTap: () {
                        setState(() => _selectedLocationTag = loc);
                        Navigator.pop(ctx);
                        _showToast('Đã gắn địa danh: $loc');
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
  }

  void _showMusicModal() {
    String searchQuery = '';
    String selectedCategory = 'all';
    List<StoryMusicTrack> displayedTracks = List.from(MusicApiService.defaultCuratedTracks);
    bool isLoadingTracks = false;
    Timer? searchDebounceTimer;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              height: MediaQuery.of(context).size.height * 0.82,
              padding: const EdgeInsets.only(top: 12),
              child: Column(
                children: [
                  // Drag handle
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: Colors.grey[300],
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Header
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 18),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: const Color(0xFF0EA5E9).withValues(alpha: 0.15),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.music_note_rounded, color: Color(0xFF0284C7), size: 20),
                            ),
                            const SizedBox(width: 10),
                            const Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Âm nhạc cho Tin',
                                  style: TextStyle(
                                    color: Color(0xFF0F172A),
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                Text(
                                  'Apple Music Live • Phát nhạc thật',
                                  style: TextStyle(
                                    color: Color(0xFF64748B),
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                        if (_selectedMusicName != null)
                          TextButton(
                            onPressed: () {
                              _stopEditorBackgroundMusic();
                              setState(() {
                                _selectedMusicName = null;
                                _selectedTrackObj = null;
                                _activePlayingTrackId = null;
                                _isPlayingAudio = false;
                                _isLoadingAudio = false;
                              });
                              setModalState(() {});
                              Navigator.pop(ctx);
                              _showToast('Đã gỡ bài hát khỏi Tin');
                            },
                            child: const Text(
                              'Gỡ nhạc',
                              style: TextStyle(
                                color: Color(0xFFEF4444),
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                              ),
                            ),
                          )
                        else
                          IconButton(
                            icon: const Icon(Icons.close_rounded, color: Color(0xFF64748B)),
                            onPressed: () {
                              _audioPlayer.stop();
                              Navigator.pop(ctx);
                            },
                          ),
                      ],
                    ),
                  ),

                  // Search bar (Facebook style)
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 6),
                    child: Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: TextField(
                        onChanged: (val) {
                          searchQuery = val;
                          searchDebounceTimer?.cancel();
                          searchDebounceTimer = Timer(const Duration(milliseconds: 350), () async {
                            setModalState(() => isLoadingTracks = true);
                            final results = await MusicApiService.search(searchQuery);
                            if (ctx.mounted) {
                              setModalState(() {
                                displayedTracks = results;
                                isLoadingTracks = false;
                              });
                            }
                          });
                        },
                        style: const TextStyle(color: Color(0xFF0F172A), fontSize: 14),
                        decoration: InputDecoration(
                          hintText: 'Tìm kiếm bài hát, ca sĩ, giai điệu...',
                          hintStyle: TextStyle(color: Colors.grey[500], fontSize: 13.5),
                          prefixIcon: const Icon(Icons.search_rounded, color: Color(0xFF64748B), size: 20),
                          suffixIcon: searchQuery.isNotEmpty
                              ? IconButton(
                                  icon: const Icon(Icons.clear_rounded, size: 18, color: Color(0xFF64748B)),
                                  onPressed: () async {
                                    searchDebounceTimer?.cancel();
                                    setModalState(() {
                                      searchQuery = '';
                                      isLoadingTracks = true;
                                    });
                                    final res = await MusicApiService.fetchByCategory(selectedCategory);
                                    if (ctx.mounted) {
                                      setModalState(() {
                                        displayedTracks = res;
                                        isLoadingTracks = false;
                                      });
                                    }
                                  },
                                )
                              : null,
                          border: InputBorder.none,
                          contentPadding: const EdgeInsets.symmetric(vertical: 12),
                        ),
                      ),
                    ),
                  ),



                  // Category Chips (Horizontal scrollable)
                  SizedBox(
                    height: 38,
                    child: ListView(
                      scrollDirection: Axis.horizontal,
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      children: [
                        _buildMusicCategoryChip('Tất cả', 'all', selectedCategory, (cat) async {
                          setModalState(() {
                            selectedCategory = cat;
                            isLoadingTracks = true;
                          });
                          final res = await MusicApiService.fetchByCategory(cat);
                          if (ctx.mounted) {
                            setModalState(() {
                              displayedTracks = res;
                              isLoadingTracks = false;
                            });
                          }
                        }),
                        _buildMusicCategoryChip('Thịnh hành 🔥', 'trending', selectedCategory, (cat) async {
                          setModalState(() {
                            selectedCategory = cat;
                            isLoadingTracks = true;
                          });
                          final res = await MusicApiService.fetchByCategory(cat);
                          if (ctx.mounted) {
                            setModalState(() {
                              displayedTracks = res;
                              isLoadingTracks = false;
                            });
                          }
                        }),
                        _buildMusicCategoryChip('Đông Anh 🏛️', 'donganh', selectedCategory, (cat) async {
                          setModalState(() {
                            selectedCategory = cat;
                            isLoadingTracks = true;
                          });
                          final res = await MusicApiService.fetchByCategory(cat);
                          if (ctx.mounted) {
                            setModalState(() {
                              displayedTracks = res;
                              isLoadingTracks = false;
                            });
                          }
                        }),
                        _buildMusicCategoryChip('Thư giãn ☕', 'chill', selectedCategory, (cat) async {
                          setModalState(() {
                            selectedCategory = cat;
                            isLoadingTracks = true;
                          });
                          final res = await MusicApiService.fetchByCategory(cat);
                          if (ctx.mounted) {
                            setModalState(() {
                              displayedTracks = res;
                              isLoadingTracks = false;
                            });
                          }
                        }),
                        _buildMusicCategoryChip('Sôi động ⚡', 'remix', selectedCategory, (cat) async {
                          setModalState(() {
                            selectedCategory = cat;
                            isLoadingTracks = true;
                          });
                          final res = await MusicApiService.fetchByCategory(cat);
                          if (ctx.mounted) {
                            setModalState(() {
                              displayedTracks = res;
                              isLoadingTracks = false;
                            });
                          }
                        }),
                        _buildMusicCategoryChip('Tình ca ❤️', 'love', selectedCategory, (cat) async {
                          setModalState(() {
                            selectedCategory = cat;
                            isLoadingTracks = true;
                          });
                          final res = await MusicApiService.fetchByCategory(cat);
                          if (ctx.mounted) {
                            setModalState(() {
                              displayedTracks = res;
                              isLoadingTracks = false;
                            });
                          }
                        }),
                      ],
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Divider(height: 1, color: Color(0xFFF1F5F9)),

                  // Track List
                  Expanded(
                    child: isLoadingTracks
                        ? const Center(
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                SizedBox(
                                  width: 28,
                                  height: 28,
                                  child: CircularProgressIndicator(strokeWidth: 2.5, color: Color(0xFF0EA5E9)),
                                ),
                                SizedBox(height: 10),
                                Text(
                                  'Đang tìm kiếm bài hát trên Apple Music...',
                                  style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
                                ),
                              ],
                            ),
                          )
                        : displayedTracks.isEmpty
                            ? Center(
                                child: Column(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(Icons.music_off_rounded, size: 48, color: Colors.grey[350]),
                                    const SizedBox(height: 10),
                                    Text(
                                      'Không tìm thấy bài hát phù hợp',
                                      style: TextStyle(color: Colors.grey[600], fontSize: 14, fontWeight: FontWeight.w500),
                                    ),
                                  ],
                                ),
                              )
                            : ListView.separated(
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                                itemCount: displayedTracks.length,
                                separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFF1F5F9)),
                                itemBuilder: (context, idx) {
                                  final track = displayedTracks[idx];
                                  final isSelected = _selectedMusicName == track.name || _selectedMusicName == '${track.name} - ${track.artist}';
                                  final isThisPlaying = _activePlayingTrackId == track.id && _isPlayingAudio;
                                  final isThisLoading = _activePlayingTrackId == track.id && _isLoadingAudio;

                                  return InkWell(
                                    onTap: () {
                                      _showMusicTrimmerModal(track);
                                    },
                                    borderRadius: BorderRadius.circular(12),
                                    child: Padding(
                                      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
                                      child: Row(
                                        children: [
                                          // Cover Art with network image and fallback
                                          ClipRRect(
                                            borderRadius: BorderRadius.circular(10),
                                            child: track.artworkUrl != null
                                                ? Image.network(
                                                    track.artworkUrl!,
                                                    width: 48,
                                                    height: 48,
                                                    fit: BoxFit.cover,
                                                    errorBuilder: (_, __, ___) => _buildFallbackMusicCover(),
                                                  )
                                                : _buildFallbackMusicCover(),
                                          ),
                                          const SizedBox(width: 12),

                                          // Song Info
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(
                                                  track.name,
                                                  maxLines: 1,
                                                  overflow: TextOverflow.ellipsis,
                                                  style: TextStyle(
                                                    color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFF0F172A),
                                                    fontWeight: FontWeight.bold,
                                                    fontSize: 14.5,
                                                  ),
                                                ),
                                                const SizedBox(height: 3),
                                                Text(
                                                  '${track.artist} • ${track.duration}',
                                                  maxLines: 1,
                                                  overflow: TextOverflow.ellipsis,
                                                  style: TextStyle(
                                                    color: Colors.grey[600],
                                                    fontSize: 12,
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),

                                          // Real Audio Preview Play/Pause/Loading Button
                                          IconButton(
                                            icon: isThisLoading
                                                ? const SizedBox(
                                                    width: 22,
                                                    height: 22,
                                                    child: CircularProgressIndicator(
                                                      strokeWidth: 2,
                                                      color: Color(0xFF0EA5E9),
                                                    ),
                                                  )
                                                : Icon(
                                                    isThisPlaying
                                                        ? Icons.pause_circle_filled_rounded
                                                        : Icons.play_circle_filled_rounded,
                                                    color: isThisPlaying
                                                        ? const Color(0xFF0EA5E9)
                                                        : const Color(0xFF64748B),
                                                    size: 32,
                                                  ),
                                            onPressed: () => _togglePlayTrack(track, onStateUpdated: () {
                                              setModalState(() {});
                                            }),
                                          ),

                                          // Selected Checkmark
                                          if (isSelected)
                                            const Padding(
                                              padding: EdgeInsets.only(left: 4),
                                              child: Icon(Icons.check_circle_rounded, color: Color(0xFF0EA5E9), size: 22),
                                            ),
                                        ],
                                      ),
                                    ),
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
    ).whenComplete(() {
      _audioPlayer.stop();
      if (mounted) {
        setState(() {
          _activePlayingTrackId = null;
          _isPlayingAudio = false;
          _isLoadingAudio = false;
        });
        // Phát nhạc nền trong trình chỉnh sửa story nếu đã chọn bài
        _playEditorBackgroundMusic();
      }
    });
  }

  Widget _buildFallbackMusicCover() {
    return Container(
      width: 48,
      height: 48,
      decoration: BoxDecoration(
        color: const Color(0xFF0EA5E9).withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(10),
      ),
      child: const Center(
        child: Icon(Icons.music_note_rounded, color: Color(0xFF0EA5E9), size: 24),
      ),
    );
  }

  void _showMusicTrimmerModal(StoryMusicTrack track) {
    final double totalSongSeconds = (track.totalSeconds > 30 ? track.totalSeconds : 210).toDouble();
    double segmentDuration = (_selectedTrackObj?.id == track.id)
        ? _musicSegmentDuration
        : 15.0; // 15s, 30s or 60s
    double startSeconds = (_selectedTrackObj?.id == track.id)
        ? _musicStartSeconds.clamp(0.0, (totalSongSeconds - segmentDuration).clamp(0.0, totalSongSeconds))
        : 0.0;
    bool isPlayingThis = false;

    // Start playing track from startSeconds (mapped to preview buffer)
    _audioPlayer.stop();
    _activePlayingTrackId = track.id;
    _audioPlayer.play(UrlSource(track.audioUrl));
    final initialPreviewOffset = (startSeconds.toInt() % 30);
    _audioPlayer.seek(Duration(seconds: initialPreviewOffset));
    _isPlayingAudio = true;
    isPlayingThis = true;

    StreamSubscription<Duration>? posSub;
    StreamSubscription<void>? compSub;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (trimCtx) {
        return StatefulBuilder(
          builder: (context, setTrimState) {
            final maxStart = (totalSongSeconds - segmentDuration) > 0 ? (totalSongSeconds - segmentDuration) : 0.0;
            final endSeconds = (startSeconds + segmentDuration).clamp(0.0, totalSongSeconds);

            final previewOffset = (startSeconds.toInt() % 30);
            final previewWindowEnd = (previewOffset + segmentDuration.toInt()).clamp(1, 30);

            posSub ??= _audioPlayer.onPositionChanged.listen((pos) {
              if (isPlayingThis) {
                if (pos.inSeconds >= previewWindowEnd || pos.inSeconds < previewOffset) {
                  _audioPlayer.seek(Duration(seconds: previewOffset));
                }
              }
            });
            compSub ??= _audioPlayer.onPlayerComplete.listen((_) {
              if (trimCtx.mounted) {
                _audioPlayer.seek(Duration(seconds: previewOffset));
                _audioPlayer.resume();
              }
            });



            return Container(
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 28),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  // Handle
                  Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: Colors.grey[300],
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Header
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      TextButton(
                        onPressed: () {
                          _audioPlayer.stop();
                          Navigator.pop(trimCtx);
                        },
                        child: const Text('Hủy', style: TextStyle(color: Color(0xFF64748B), fontSize: 14)),
                      ),
                      Column(
                        children: [
                          const Text(
                            'Chọn đoạn trên toàn bài',
                            style: TextStyle(
                              color: Color(0xFF0F172A),
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            'Toàn bài: ${_formatTime(totalSongSeconds.toInt())}',
                            style: const TextStyle(
                              color: Color(0xFF0284C7),
                              fontSize: 11.5,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                      TextButton(
                        onPressed: () {
                          _audioPlayer.stop();
                          setState(() {
                            _selectedTrackObj = track;
                            _musicStartSeconds = startSeconds;
                            _musicSegmentDuration = segmentDuration;
                            _selectedMusicName = '${track.name} (${_formatTime(startSeconds.toInt())} - ${_formatTime(endSeconds.toInt())})';
                            _activePlayingTrackId = null;
                            _isPlayingAudio = false;
                            _isLoadingAudio = false;
                          });
                          Navigator.pop(trimCtx);
                          _showToast('Đã chọn: ${_formatTime(startSeconds.toInt())} - ${_formatTime(endSeconds.toInt())} / ${_formatTime(totalSongSeconds.toInt())}', isSuccess: true);
                        },
                        child: const Text(
                          'Xong',
                          style: TextStyle(
                            color: Color(0xFF0EA5E9),
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Artwork & Song Info
                  Row(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: track.artworkUrl != null
                            ? Image.network(
                                track.artworkUrl!,
                                width: 56,
                                height: 56,
                                fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => _buildFallbackMusicCover(),
                              )
                            : _buildFallbackMusicCover(),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              track.name,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                color: Color(0xFF0F172A),
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                              ),
                            ),
                            const SizedBox(height: 3),
                            Text(
                              '${track.artist} • Cả bài: ${_formatTime(totalSongSeconds.toInt())}',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(color: Colors.grey[600], fontSize: 13),
                            ),
                          ],
                        ),
                      ),
                      // Big play/pause toggle in trimmer
                      IconButton(
                        iconSize: 44,
                        icon: Icon(
                          isPlayingThis ? Icons.pause_circle_filled_rounded : Icons.play_circle_filled_rounded,
                          color: const Color(0xFF0EA5E9),
                        ),
                        onPressed: () async {
                          if (isPlayingThis) {
                            await _audioPlayer.pause();
                            setTrimState(() => isPlayingThis = false);
                            setState(() => _isPlayingAudio = false);
                          } else {
                            if (_audioPlayer.state == PlayerState.paused) {
                              await _audioPlayer.resume();
                            } else {
                              await _audioPlayer.play(UrlSource(track.audioUrl));
                              await _audioPlayer.seek(Duration(seconds: previewOffset));
                            }
                            setTrimState(() => isPlayingThis = true);
                            setState(() => _isPlayingAudio = true);
                          }
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Duration selector chips: 15s, 30s, 60s
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Text(
                        'Độ dài đoạn:',
                        style: TextStyle(color: Color(0xFF64748B), fontSize: 13, fontWeight: FontWeight.w500),
                      ),
                      const SizedBox(width: 10),
                      ChoiceChip(
                        label: const Text('15 giây', style: TextStyle(fontSize: 12)),
                        selected: segmentDuration == 15.0,
                        selectedColor: const Color(0xFF0EA5E9).withValues(alpha: 0.18),
                        labelStyle: TextStyle(
                          color: segmentDuration == 15.0 ? const Color(0xFF0284C7) : const Color(0xFF64748B),
                          fontWeight: FontWeight.bold,
                        ),
                        onSelected: (sel) {
                          if (sel) {
                            setTrimState(() {
                              segmentDuration = 15.0;
                              if (startSeconds > (totalSongSeconds - 15.0)) {
                                startSeconds = (totalSongSeconds - 15.0).clamp(0.0, totalSongSeconds);
                              }
                            });
                          }
                        },
                      ),
                      const SizedBox(width: 6),
                      ChoiceChip(
                        label: const Text('30 giây', style: TextStyle(fontSize: 12)),
                        selected: segmentDuration == 30.0,
                        selectedColor: const Color(0xFF0EA5E9).withValues(alpha: 0.18),
                        labelStyle: TextStyle(
                          color: segmentDuration == 30.0 ? const Color(0xFF0284C7) : const Color(0xFF64748B),
                          fontWeight: FontWeight.bold,
                        ),
                        onSelected: (sel) {
                          if (sel) {
                            setTrimState(() {
                              segmentDuration = 30.0;
                              if (startSeconds > (totalSongSeconds - 30.0)) {
                                startSeconds = (totalSongSeconds - 30.0).clamp(0.0, totalSongSeconds);
                              }
                            });
                          }
                        },
                      ),
                      const SizedBox(width: 6),
                      ChoiceChip(
                        label: const Text('60 giây', style: TextStyle(fontSize: 12)),
                        selected: segmentDuration == 60.0,
                        selectedColor: const Color(0xFF0EA5E9).withValues(alpha: 0.18),
                        labelStyle: TextStyle(
                          color: segmentDuration == 60.0 ? const Color(0xFF0284C7) : const Color(0xFF64748B),
                          fontWeight: FontWeight.bold,
                        ),
                        onSelected: (sel) {
                          if (sel) {
                            setTrimState(() {
                              segmentDuration = 60.0;
                              if (startSeconds > (totalSongSeconds - 60.0)) {
                                startSeconds = (totalSongSeconds - 60.0).clamp(0.0, totalSongSeconds);
                              }
                            });
                          }
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Sóng nhạc trực quan trên toàn bài (Waveform Visualizer across full song)
                  Container(
                    height: 52,
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: List.generate(36, (i) {
                        final waveHeights = [14.0, 22.0, 32.0, 18.0, 28.0, 36.0, 20.0, 34.0, 24.0, 38.0, 16.0, 30.0, 26.0, 35.0, 19.0, 33.0, 25.0, 36.0, 22.0, 30.0, 17.0, 32.0, 28.0, 34.0, 20.0, 26.0, 18.0, 24.0, 30.0, 36.0, 22.0, 28.0, 32.0, 20.0, 16.0, 12.0];
                        final h = waveHeights[i % waveHeights.length];
                        final ratio = i / 36.0;
                        final barSec = ratio * totalSongSeconds;
                        final isInSegment = barSec >= startSeconds && barSec <= endSeconds;

                        return Container(
                          width: 4,
                          height: isPlayingThis ? h : (h * 0.75).clamp(8.0, 36.0),
                          decoration: BoxDecoration(
                            color: isInSegment
                                ? const Color(0xFF0EA5E9)
                                : const Color(0xFFCBD5E1),
                            borderRadius: BorderRadius.circular(2),
                          ),
                        );
                      }),
                    ),
                  ),
                  const SizedBox(height: 6),

                  // Slider kéo chọn đoạn bắt đầu trên TOÀN BỘ BÀI HÁT
                  SliderTheme(
                    data: SliderTheme.of(context).copyWith(
                      activeTrackColor: const Color(0xFF0EA5E9),
                      inactiveTrackColor: const Color(0xFFE2E8F0),
                      thumbColor: const Color(0xFF0284C7),
                      overlayColor: const Color(0xFF0EA5E9).withValues(alpha: 0.2),
                      trackHeight: 4,
                    ),
                    child: Slider(
                      value: startSeconds.clamp(0.0, maxStart),
                      min: 0.0,
                      max: maxStart > 0 ? maxStart : 0.001,
                      divisions: maxStart > 0 ? maxStart.toInt() : null,
                      onChanged: (val) {
                        setTrimState(() {
                          startSeconds = val;
                        });
                      },
                      onChangeEnd: (val) async {
                        final off = (val.toInt() % 30);
                        await _audioPlayer.seek(Duration(seconds: off));
                        if (!isPlayingThis) {
                          await _audioPlayer.resume();
                          setTrimState(() => isPlayingThis = true);
                          setState(() => _isPlayingAudio = true);
                        }
                      },
                    ),
                  ),

                  // Time indicators: 0:00 --- Đoạn đang chọn --- Toàn bài
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 6),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          '0:00 (Đầu)',
                          style: TextStyle(color: Color(0xFF94A3B8), fontSize: 11, fontWeight: FontWeight.w500),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: const Color(0xFF0EA5E9).withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            'Đoạn phát: ${_formatTime(startSeconds.toInt())} - ${_formatTime(endSeconds.toInt())} (${segmentDuration.toInt()}s)',
                            style: const TextStyle(
                              color: Color(0xFF0284C7),
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        Text(
                          '${_formatTime(totalSongSeconds.toInt())} (Hết)',
                          style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11, fontWeight: FontWeight.w500),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Action Button
                  SizedBox(
                    width: double.infinity,
                    height: 46,
                    child: ElevatedButton(
                      onPressed: () {
                        _audioPlayer.stop();
                        setState(() {
                          _selectedTrackObj = track;
                          _musicStartSeconds = startSeconds;
                          _musicSegmentDuration = segmentDuration;
                          _selectedMusicName = '${track.name} (${_formatTime(startSeconds.toInt())} - ${_formatTime(endSeconds.toInt())})';
                          _activePlayingTrackId = null;
                          _isPlayingAudio = false;
                          _isLoadingAudio = false;
                        });
                        Navigator.pop(trimCtx);
                        _showToast('Đã gắn: ${track.name} (${_formatTime(startSeconds.toInt())} - ${_formatTime(endSeconds.toInt())})', isSuccess: true);
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0EA5E9),
                        foregroundColor: Colors.white,
                        elevation: 0,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.check_circle_rounded, size: 18),
                          SizedBox(width: 8),
                          Text(
                            'Áp dụng đoạn nhạc này',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14.5),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    ).whenComplete(() {
      posSub?.cancel();
      compSub?.cancel();
      _audioPlayer.stop();
      if (mounted) {
        setState(() {
          _activePlayingTrackId = null;
          _isPlayingAudio = false;
          _isLoadingAudio = false;
        });
      }
    });
  }

  /// Phát nhạc nền trong trình chỉnh sửa story (loop đoạn đã chọn)
  void _playEditorBackgroundMusic() {
    if (_selectedTrackObj == null) return;
    final track = _selectedTrackObj!;
    final previewOffset = (_musicStartSeconds.toInt() % 30);
    _audioPlayer.stop();
    _audioPlayer.play(UrlSource(track.audioUrl));
    _audioPlayer.seek(Duration(seconds: previewOffset));
    _audioPlayer.setVolume(0.5);
    setState(() {
      _isPlayingAudio = true;
      _activePlayingTrackId = track.id;
    });

    // Loop within the selected segment
    _audioPlayer.onPositionChanged.listen((pos) {
      final segEnd = (previewOffset + _musicSegmentDuration.toInt()).clamp(1, 30);
      if (pos.inSeconds >= segEnd) {
        _audioPlayer.seek(Duration(seconds: previewOffset));
      }
    });
    _audioPlayer.onPlayerComplete.listen((_) {
      if (mounted && _selectedTrackObj != null) {
        _audioPlayer.seek(Duration(seconds: previewOffset));
        _audioPlayer.resume();
      }
    });
  }

  /// Dừng nhạc nền khi đóng trình chỉnh sửa hoặc gỡ nhạc
  void _stopEditorBackgroundMusic() {
    _audioPlayer.stop();
    _audioPlayer.setVolume(1.0);
    setState(() {
      _isPlayingAudio = false;
      _activePlayingTrackId = null;
    });
  }



  Widget _buildMusicCategoryChip(
    String label,
    String value,
    String selectedValue,
    ValueChanged<String> onSelected,
  ) {
    final isSelected = value == selectedValue;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ChoiceChip(
        label: Text(
          label,
          style: TextStyle(
            color: isSelected ? Colors.white : const Color(0xFF475569),
            fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
            fontSize: 12.5,
          ),
        ),
        selected: isSelected,
        selectedColor: const Color(0xFF0EA5E9),
        backgroundColor: const Color(0xFFF1F5F9),
        side: BorderSide(
          color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFFE2E8F0),
        ),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        showCheckmark: false,
        onSelected: (_) => onSelected(value),
      ),
    );
  }

  void _showPrivacyModal() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: Colors.grey[300],
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),
                  const Text('Quyền Riêng Tư Của Tin', style: TextStyle(color: Color(0xFF0F172A), fontSize: 17, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 16),
                  _buildPrivacyTile('Công khai', 'Mọi người trên ứng dụng đều có thể xem', Icons.public, 'public', setModalState),
                  _buildPrivacyTile('Bạn bè', 'Chỉ những người theo dõi bạn mới xem được', Icons.people, 'friends', setModalState),
                  _buildPrivacyTile('Chỉ mình tôi', 'Chỉ bạn mới có quyền xem tin này', Icons.lock, 'private', setModalState),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildPrivacyTile(String title, String subtitle, IconData icon, String value, StateSetter setModalState) {
    final isSelected = _privacySetting == value;
    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: Icon(icon, color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFF64748B)),
      title: Text(title, style: TextStyle(color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFF0F172A), fontWeight: FontWeight.bold)),
      subtitle: Text(subtitle, style: const TextStyle(color: Color(0xFF64748B), fontSize: 12)),
      trailing: isSelected ? const Icon(Icons.check_circle_rounded, color: Color(0xFF0EA5E9)) : null,
      onTap: () {
        setModalState(() => _privacySetting = value);
        setState(() => _privacySetting = value);
        Navigator.pop(context);
      },
    );
  }

  // ==========================================
  // UTILITY HELPER WIDGETS
  // ==========================================

  Widget _buildCircleIconButton({
    required IconData icon,
    required VoidCallback onPressed,
    Color? color,
    String? tooltip,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: color ?? Colors.black.withValues(alpha: 0.55),
        shape: BoxShape.circle,
        border: Border.all(color: Colors.white24),
      ),
      child: IconButton(
        icon: Icon(icon, color: Colors.white, size: 20),
        onPressed: onPressed,
        tooltip: tooltip,
        constraints: const BoxConstraints(minWidth: 38, minHeight: 38),
        padding: EdgeInsets.zero,
      ),
    );
  }

  Widget _buildSkeletonGrid() {
    return GridView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 3,
        crossAxisSpacing: 6,
        mainAxisSpacing: 6,
        childAspectRatio: 0.75,
      ),
      itemCount: 9,
      itemBuilder: (context, index) {
        if (index == 0) {
          return GestureDetector(
            onTap: _openCamera,
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFF0EA5E9), width: 1.5),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0EA5E9).withValues(alpha: 0.08),
                    blurRadius: 6,
                  ),
                ],
              ),
              child: const Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.camera_alt_rounded, color: Color(0xFF0EA5E9), size: 30),
                  SizedBox(height: 6),
                  Text(
                    'Chụp ảnh',
                    style: TextStyle(color: Color(0xFF0F172A), fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
            ),
          );
        }
        return Container(
          decoration: BoxDecoration(
            color: const Color(0xFFF1F5F9),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFE2E8F0)),
          ),
          child: const Center(
            child: SizedBox(
              width: 16,
              height: 16,
              child: CircularProgressIndicator(strokeWidth: 1.5, color: Color(0xFF94A3B8)),
            ),
          ),
        );
      },
    );
  }

  Widget _buildEmptyGalleryPrompt() {
    if (_isPermissionDenied) {
      return Container(
        margin: const EdgeInsets.symmetric(horizontal: 16),
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFE2E8F0)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.04),
              blurRadius: 10,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 60,
                height: 60,
                decoration: BoxDecoration(
                  color: const Color(0xFFEF4444).withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.no_photography_rounded, color: Color(0xFFEF4444), size: 30),
              ),
              const SizedBox(height: 12),
              const Text(
                'Cần cấp quyền truy cập Ảnh',
                style: TextStyle(color: Color(0xFF0F172A), fontSize: 15, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 6),
              Text(
                'Cho phép Đông Anh Social truy cập thư viện ảnh để hiển thị ảnh của bạn tại đây.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey[600], fontSize: 12),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: () => PhotoManager.openSetting(),
                icon: const Icon(Icons.settings_rounded, size: 16),
                label: const Text('Cài đặt ứng dụng', style: TextStyle(fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0EA5E9),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                  elevation: 0,
                ),
              ),
            ],
          ),
        ),
      );
    }

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                color: const Color(0xFF0EA5E9).withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.camera_enhance_rounded, color: Color(0xFF0EA5E9), size: 30),
            ),
            const SizedBox(height: 12),
            const Text(
              'Chưa có ảnh trong thư viện',
              style: TextStyle(color: Color(0xFF0F172A), fontSize: 15, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            Text(
              'Bạn có thể chụp ảnh mới hoặc chọn ảnh từ album hệ thống.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey[600], fontSize: 12),
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                ElevatedButton.icon(
                  onPressed: _openCamera,
                  icon: const Icon(Icons.camera_alt_rounded, size: 16),
                  label: const Text('Chụp ảnh ngay', style: TextStyle(fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0EA5E9),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                    elevation: 0,
                  ),
                ),
                const SizedBox(width: 10),
                OutlinedButton.icon(
                  onPressed: _pickFromGallery,
                  icon: const Icon(Icons.photo_library_rounded, size: 16, color: Color(0xFF0EA5E9)),
                  label: const Text('Mở Album', style: TextStyle(color: Color(0xFF0EA5E9), fontWeight: FontWeight.bold)),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Color(0xFF0EA5E9)),
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLiveGalleryGrid() {
    final totalCount = 1 + _recentAssets.length;
    return GridView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 3,
        crossAxisSpacing: 6,
        mainAxisSpacing: 6,
        childAspectRatio: 0.75,
      ),
      itemCount: totalCount,
      itemBuilder: (context, index) {
        if (index == 0) {
          return GestureDetector(
            onTap: _openCamera,
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFF0EA5E9), width: 1.5),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0EA5E9).withValues(alpha: 0.08),
                    blurRadius: 6,
                  ),
                ],
              ),
              child: const Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.camera_alt_rounded, color: Color(0xFF0EA5E9), size: 30),
                  SizedBox(height: 6),
                  Text(
                    'Chụp ảnh',
                    style: TextStyle(color: Color(0xFF0F172A), fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
            ),
          );
        }

        final asset = _recentAssets[index - 1];
        final isVideo = asset.type == AssetType.video;
        final isCurrentLoading = _loadingAssetId == asset.id;

        return GestureDetector(
          onTap: () async {
            if (_loadingAssetId != null) return;
            setState(() => _loadingAssetId = asset.id);
            try {
              File? file = await asset.file;
              file ??= await asset.originFile;
              if (file != null && mounted) {
                final loadedFile = file;
                if (isVideo) {
                  Uint8List? thumbBytes;
                  try {
                    thumbBytes = await asset.thumbnailDataWithSize(
                      const ThumbnailSize(1080, 1920),
                      quality: 90,
                    );
                    thumbBytes ??= await asset.thumbnailData;
                  } catch (te) {
                    debugPrint('[CreateStoryScreen] Video thumb error: $te');
                  }

                  String? thumbPath;
                  if (thumbBytes != null) {
                    final tempDir = Directory.systemTemp;
                    final safeId = asset.id.replaceAll(RegExp(r'[^a-zA-Z0-9]'), '_');
                    final tempThumb = File('${tempDir.path}/video_thumb_$safeId.jpg');
                    await tempThumb.writeAsBytes(thumbBytes);
                    thumbPath = tempThumb.path;
                  }

                  setState(() {
                    _previewMedia = XFile(thumbPath ?? loadedFile.path);
                    _originalVideoPath = loadedFile.path;
                    _isVideoStory = true;
                    _isTextStoryMode = false;
                    _rotationQuarterTurns = 0;
                  });

                  await _initVideoPreview(loadedFile.path);
                } else {
                  await _disposeVideoController();
                  setState(() {
                    _previewMedia = XFile(loadedFile.path);
                    _originalVideoPath = null;
                    _isVideoStory = false;
                    _isTextStoryMode = false;
                    _rotationQuarterTurns = 0;
                  });
                }
              } else if (mounted) {
                _showToast('Không thể tải tệp từ thiết bị', isError: true);
              }
            } catch (e) {
              if (mounted) {
                _showToast('Lỗi khi mở tệp: $e', isError: true);
              }
            } finally {
              if (mounted) {
                setState(() => _loadingAssetId = null);
              }
            }
          },
          child: ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: Stack(
              fit: StackFit.expand,
              children: [
                _AssetThumbnailWidget(
                  key: ValueKey(asset.id),
                  entity: asset,
                ),
                if (isVideo)
                  Positioned(
                    bottom: 6,
                    right: 6,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.75),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.play_arrow_rounded, color: Colors.white, size: 12),
                          const SizedBox(width: 2),
                          Text(
                            '${(asset.duration ~/ 60).toString().padLeft(2, '0')}:${(asset.duration % 60).toString().padLeft(2, '0')}',
                            style: const TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ),
                  ),
                if (isCurrentLoading)
                  Container(
                    color: Colors.black45,
                    child: const Center(
                      child: SizedBox(
                        width: 22,
                        height: 22,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
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
}

class _AssetThumbnailWidget extends StatefulWidget {
  final AssetEntity entity;
  const _AssetThumbnailWidget({super.key, required this.entity});

  @override
  State<_AssetThumbnailWidget> createState() => _AssetThumbnailWidgetState();
}

class _AssetThumbnailWidgetState extends State<_AssetThumbnailWidget> {
  static final Map<String, Uint8List> _thumbCache = {};
  Uint8List? _bytes;

  @override
  void initState() {
    super.initState();
    _loadThumbnail();
  }

  @override
  void didUpdateWidget(covariant _AssetThumbnailWidget oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.entity.id != widget.entity.id) {
      _loadThumbnail();
    }
  }

  Future<void> _loadThumbnail() async {
    if (_thumbCache.containsKey(widget.entity.id)) {
      if (mounted) {
        setState(() => _bytes = _thumbCache[widget.entity.id]);
      }
      return;
    }

    if (mounted) setState(() => _bytes = null);

    try {
      final bytes = await widget.entity.thumbnailDataWithSize(
        const ThumbnailSize.square(300),
        quality: 85,
      );
      if (bytes != null) {
        _thumbCache[widget.entity.id] = bytes;
        if (mounted) {
          setState(() => _bytes = bytes);
        }
      }
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    if (_bytes != null) {
      return Image.memory(
        _bytes!,
        fit: BoxFit.cover,
        gaplessPlayback: true,
      );
    }
    return Container(
      color: const Color(0xFFF1F5F9),
      child: const Center(
        child: SizedBox(
          width: 16,
          height: 16,
          child: CircularProgressIndicator(
            strokeWidth: 1.5,
            color: Color(0xFF94A3B8),
          ),
        ),
      ),
    );
  }
}
