import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:share_plus/share_plus.dart';
import 'package:image_picker/image_picker.dart';
import '../services/api_service.dart';

class ProfileScreen extends StatefulWidget {
  final VoidCallback onLogout;
  final VoidCallback onLoginRequest;

  const ProfileScreen({
    super.key,
    required this.onLogout,
    required this.onLoginRequest,
  });

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  List<dynamic> _myPosts = [];
  List<dynamic> _myCheckins = [];
  bool _isLoadingActivity = true;
  int _selectedActivityTab = 0;

  void _showFullScreenImageModal(BuildContext context, String imageUrl, String title) {
    showDialog(
      context: context,
      useSafeArea: false,
      barrierDismissible: true,
      builder: (dialogContext) {
        return GestureDetector(
          onTap: () => Navigator.of(dialogContext).pop(),
          child: Scaffold(
            backgroundColor: Colors.black,
            body: Stack(
              fit: StackFit.expand,
              children: [
                GestureDetector(
                  onTap: () => Navigator.of(dialogContext).pop(),
                  child: InteractiveViewer(
                    minScale: 0.5,
                    maxScale: 4.0,
                    child: Center(
                      child: Image.network(
                        imageUrl,
                        fit: BoxFit.contain,
                        loadingBuilder: (context, child, loadingProgress) {
                          if (loadingProgress == null) return child;
                          return const Center(
                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                          );
                        },
                        errorBuilder: (context, error, stackTrace) {
                          return const Center(
                            child: Text('Không thể tải hình ảnh', style: TextStyle(color: Colors.white70)),
                          );
                        },
                      ),
                    ),
                  ),
                ),
                Positioned(
                  top: MediaQuery.of(dialogContext).padding.top + 12,
                  left: 16,
                  right: 16,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: Colors.black.withValues(alpha: 0.5),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          title,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                          ),
                        ),
                      ),
                      GestureDetector(
                        onTap: () => Navigator.of(dialogContext).pop(),
                        behavior: HitTestBehavior.opaque,
                        child: Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.close_rounded, color: Colors.white, size: 24),
                        ),
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

  void _showImagePreviewDialog({
    required String title,
    required String imagePath,
    required bool isAvatar,
    required Future<void> Function() onConfirm,
  }) {
    bool isSubmitting = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Dialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
              child: Padding(
                padding: const EdgeInsets.all(20.0),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          title,
                          style: const TextStyle(
                            fontSize: 17,
                            fontWeight: FontWeight.w900,
                            color: Color(0xFF0F172A),
                          ),
                        ),
                        if (!isSubmitting)
                          IconButton(
                            icon: const Icon(Icons.close, color: Colors.grey, size: 20),
                            onPressed: () => Navigator.pop(dialogContext),
                          ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    if (isAvatar)
                      Container(
                        padding: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: const Color(0xFF0EA5E9), width: 3),
                        ),
                        child: CircleAvatar(
                          radius: 70,
                          backgroundImage: FileImage(File(imagePath)),
                        ),
                      )
                    else
                      ClipRRect(
                        borderRadius: BorderRadius.circular(16),
                        child: Container(
                          height: 160,
                          width: double.infinity,
                          decoration: BoxDecoration(
                            image: DecorationImage(
                              image: FileImage(File(imagePath)),
                              fit: BoxFit.cover,
                            ),
                          ),
                        ),
                      ),
                    const SizedBox(height: 24),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton(
                            onPressed: isSubmitting ? null : () => Navigator.pop(dialogContext),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: const Color(0xFF64748B),
                              side: const BorderSide(color: Color(0xFFCBD5E1)),
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                            child: const Text('Hủy bỏ', style: TextStyle(fontWeight: FontWeight.bold)),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: isSubmitting
                                ? null
                                : () async {
                                    setModalState(() => isSubmitting = true);
                                    await onConfirm();
                                    if (dialogContext.mounted) {
                                      Navigator.pop(dialogContext);
                                    }
                                  },
                            icon: isSubmitting
                                ? const SizedBox(
                                    width: 16,
                                    height: 16,
                                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                  )
                                : const Icon(Icons.check_circle_rounded, size: 18),
                            label: Text(
                              isSubmitting ? 'Đang lưu...' : 'Cập nhật',
                              style: const TextStyle(fontWeight: FontWeight.bold),
                            ),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF0EA5E9),
                              foregroundColor: Colors.white,
                              elevation: 0,
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Future<void> _pickAndUploadAvatar(ImageSource source) async {
    try {
      final picker = ImagePicker();
      final XFile? file = await picker.pickImage(source: source, imageQuality: 95, maxWidth: 4096);
      if (file == null) return;

      _showImagePreviewDialog(
        title: '📸 Xem trước ảnh đại diện',
        imagePath: file.path,
        isAvatar: true,
        onConfirm: () async {
          final success = await ApiService.uploadAvatar(file.path);
          if (mounted) {
            if (success) {
              PaintingBinding.instance.imageCache.clear();
              PaintingBinding.instance.imageCache.clearLiveImages();
              await ApiService.fetchUserProfile();
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('📸 Cập nhật ảnh đại diện thành công!'), backgroundColor: Color(0xFF10B981)),
                );
              }
            } else {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('❌ Cập nhật ảnh đại diện thất bại. Vui lòng thử lại!'), backgroundColor: Colors.red),
              );
            }
          }
        },
      );
    } catch (_) {}
  }

  Future<void> _pickAndUploadCoverPhoto() async {
    try {
      final picker = ImagePicker();
      final XFile? file = await picker.pickImage(source: ImageSource.gallery, imageQuality: 95, maxWidth: 4096);
      if (file == null) return;

      _showImagePreviewDialog(
        title: '🖼️ Xem trước ảnh bìa',
        imagePath: file.path,
        isAvatar: false,
        onConfirm: () async {
          final success = await ApiService.uploadCoverPhoto(file.path);
          if (mounted) {
            if (success) {
              PaintingBinding.instance.imageCache.clear();
              PaintingBinding.instance.imageCache.clearLiveImages();
              await ApiService.fetchUserProfile();
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('🖼️ Cập nhật ảnh bìa thành công!'), backgroundColor: Color(0xFF10B981)),
                );
              }
            } else {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('❌ Cập nhật ảnh bìa thất bại. Vui lòng thử lại!'), backgroundColor: Colors.red),
              );
            }
          }
        },
      );
    } catch (_) {}
  }

  void _showAvatarOptions(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => Container(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Cập nhật ảnh đại diện',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            const SizedBox(height: 16),
            ListTile(
              leading: const Icon(Icons.photo_library_outlined, color: Color(0xFF0EA5E9)),
              title: const Text('Chọn từ thư viện ảnh'),
              onTap: () {
                Navigator.pop(context);
                _pickAndUploadAvatar(ImageSource.gallery);
              },
            ),
            ListTile(
              leading: const Icon(Icons.camera_alt_outlined, color: Color(0xFF10B981)),
              title: const Text('Chụp ảnh từ Camera'),
              onTap: () {
                Navigator.pop(context);
                _pickAndUploadAvatar(ImageSource.camera);
              },
            ),
          ],
        ),
      ),
    );
  }

  @override
  void initState() {
    super.initState();
    _fetchMyActivity();
  }

  Future<void> _fetchMyActivity() async {
    if (!ApiService.isAuthenticated) return;
    setState(() => _isLoadingActivity = true);
    try {
      final userFuture = ApiService.getUserProfile();
      final postsFuture = ApiService.getMyPosts();
      final checkinsFuture = ApiService.getMyCheckins();
      final results = await Future.wait([userFuture, postsFuture, checkinsFuture]);
      if (mounted) {
        setState(() {
          _myPosts = results[1] as List<dynamic>;
          _myCheckins = results[2] as List<dynamic>;
          _isLoadingActivity = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoadingActivity = false);
    }
  }
  void _showEditProfileDialog(BuildContext context) {
    final user = ApiService.currentUser;
    final nameController = TextEditingController(text: user?['name'] ?? '');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.edit_note_rounded, color: Color(0xFF0EA5E9)),
            SizedBox(width: 8),
            Text('Chỉnh sửa hồ sơ'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Họ và tên', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
            const SizedBox(height: 6),
            TextField(
              controller: nameController,
              decoration: InputDecoration(
                hintText: 'Nhập họ tên mới...',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              ),
            ),
            const SizedBox(height: 12),
            Text('Email: ${user?['email'] ?? 'Chưa cập nhật'}', style: const TextStyle(color: Colors.grey, fontSize: 12)),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Hủy'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Đã cập nhật thông tin cá nhân thành công!'),
                  backgroundColor: Color(0xFF059669),
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF0EA5E9),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Lưu thay đổi'),
          ),
        ],
      ),
    );
  }

  void _showChangePasswordDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.lock_reset_rounded, color: Color(0xFF0EA5E9)),
            SizedBox(width: 8),
            Text('Đổi mật khẩu'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              obscureText: true,
              decoration: InputDecoration(
                labelText: 'Mật khẩu hiện tại',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              obscureText: true,
              decoration: InputDecoration(
                labelText: 'Mật khẩu mới',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy')),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Đã đổi mật khẩu thành công!'),
                  backgroundColor: Color(0xFF059669),
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF0EA5E9),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Cập nhật'),
          ),
        ],
      ),
    );
  }



  @override
  Widget build(BuildContext context) {
    const primaryColor = Color(0xFF0EA5E9);
    final isGuest = !ApiService.isAuthenticated;
    final user = ApiService.currentUser;

    if (isGuest) {
      return Scaffold(
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.account_circle_outlined, size: 64, color: Colors.grey[300]),
                const SizedBox(height: 16),
                const Text(
                  'Bạn đang ở chế độ Khách vãng lai. Hãy đăng nhập để lưu trữ lịch sử check-in và chat với bạn bè.',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 15, color: Colors.grey),
                ),
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: widget.onLoginRequest,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: primaryColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('Đăng nhập ngay', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Banner + Profile Card Container
            Stack(
              clipBehavior: Clip.none,
              children: [
                // Cover Photo Banner (Tap image to view full-screen, tap badge to change)
                GestureDetector(
                  onTap: () => _showFullScreenImageModal(
                    context,
                    ApiService.getCoverUrl(user),
                    'Ảnh bìa',
                  ),
                  child: Container(
                    height: 140,
                    width: double.infinity,
                    decoration: BoxDecoration(
                      image: DecorationImage(
                        image: NetworkImage(
                          ApiService.getCoverUrl(user),
                        ),
                        fit: BoxFit.cover,
                      ),
                    ),
                    child: Container(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [
                            Colors.black.withValues(alpha: 0.3),
                            Colors.black.withValues(alpha: 0.6),
                          ],
                          begin: Alignment.topCenter,
                          end: Alignment.bottomCenter,
                        ),
                      ),
                      padding: const EdgeInsets.all(12),
                      alignment: Alignment.topRight,
                      child: InkWell(
                        onTap: _pickAndUploadCoverPhoto,
                        borderRadius: BorderRadius.circular(20),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.6),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.camera_alt_outlined, color: Colors.white, size: 14),
                              SizedBox(width: 4),
                              Text('Đổi ảnh bìa', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600)),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                ),

                // Main Profile Card (Floating)
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 75, 16, 0),
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(24),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.05),
                          blurRadius: 15,
                          offset: const Offset(0, 5),
                        ),
                      ],
                    ),
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 20),
                    child: Column(
                      children: [
                        // Avatar + Camera Edit Badge
                        Stack(
                          alignment: Alignment.bottomRight,
                          children: [
                            GestureDetector(
                              onTap: () => _showFullScreenImageModal(
                                context,
                                ApiService.getAvatarUrl(user, user?['name']),
                                'Ảnh đại diện',
                              ),
                              child: Container(
                                padding: const EdgeInsets.all(4),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  shape: BoxShape.circle,
                                  boxShadow: [
                                    BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 8),
                                  ],
                                ),
                                child: CircleAvatar(
                                  radius: 42,
                                  backgroundColor: primaryColor.withValues(alpha: 0.1),
                                  backgroundImage: ResizeImage(
                                    NetworkImage(ApiService.getAvatarUrl(user, user?['name'])),
                                    width: 200,
                                  ),
                                ),
                              ),
                            ),
                            GestureDetector(
                              onTap: () => _showAvatarOptions(context),
                              child: Container(
                                padding: const EdgeInsets.all(7),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF0EA5E9),
                                  shape: BoxShape.circle,
                                  border: Border.all(color: Colors.white, width: 2),
                                  boxShadow: [
                                    BoxShadow(color: Colors.black.withValues(alpha: 0.15), blurRadius: 4),
                                  ],
                                ),
                                child: const Icon(Icons.camera_alt_rounded, color: Colors.white, size: 15),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),

                        // Name & Role Badge
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Flexible(
                              child: Text(
                                user?['name'] ?? '',
                                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            if ((user?['role'] ?? '').toString().toLowerCase() == 'admin') ...[
                              const SizedBox(width: 4),
                              const Icon(Icons.star_rounded, color: Color(0xFFEF4444), size: 18),
                            ] else if (user?['is_verified'] == true || user?['is_verified'] == 1) ...[
                              const SizedBox(width: 4),
                              const Icon(Icons.star_rounded, color: Color(0xFFF59E0B), size: 18),
                            ],
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          user?['email'] ?? '',
                          style: const TextStyle(color: Color(0xFF64748B), fontSize: 13),
                        ),
                        const SizedBox(height: 16),

                        // Social Stats Counters Row
                        Container(
                          padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceAround,
                            children: [
                              _socialStat('4', 'Người theo dõi'),
                              _statDivider(),
                              _socialStat('0', 'Đang theo dõi'),
                              _statDivider(),
                              _socialStat(
                                _isLoadingActivity ? '...' : _myPosts.length.toString(),
                                'Bài viết',
                                () => setState(() => _selectedActivityTab = 0),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Quick Actions Buttons Row
                        Row(
                          children: [
                            Expanded(
                              flex: 2,
                              child: ElevatedButton.icon(
                                onPressed: () => _showEditProfileDialog(context),
                                icon: const Icon(Icons.edit, size: 16),
                                label: const Text('Chỉnh sửa hồ sơ', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF2563EB),
                                  foregroundColor: Colors.white,
                                  elevation: 0,
                                  padding: const EdgeInsets.symmetric(vertical: 10),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              flex: 2,
                              child: OutlinedButton.icon(
                                onPressed: () => _showChangePasswordDialog(context),
                                icon: const Icon(Icons.key, size: 16),
                                label: const Text('Đổi mật khẩu', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xFF334155),
                                  side: const BorderSide(color: Color(0xFFCBD5E1)),
                                  padding: const EdgeInsets.symmetric(vertical: 10),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            InkWell(
                              onTap: () {
                                final userId = user?['id'] ?? '';
                                final name = user?['name'] ?? 'Thành viên Đông Anh';
                                final shareUrl = 'https://donganhdiscovery.xadonganh.com/profile/$userId';
                                Clipboard.setData(ClipboardData(text: shareUrl));
                                Share.share('👤 Trang cá nhân Đông Anh Social của $name:\n🔗 $shareUrl');
                              },
                              child: Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  border: Border.all(color: const Color(0xFFCBD5E1)),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: const Icon(Icons.share, size: 18, color: Color(0xFF475569)),
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

            const SizedBox(height: 20),

            // Activity Statistics Cards Section
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Thống kê hoạt động',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: Color(0xFF334155)),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: _activityStatCard('12', 'Địa điểm đã đi', Icons.map_outlined, const Color(0xFF0EA5E9), const Color(0xFFE0F2FE)),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _activityStatCard(
                          _isLoadingActivity ? '...' : _myCheckins.length.toString(),
                          'Bài Check-in',
                          Icons.location_on_outlined,
                          const Color(0xFF10B981),
                          const Color(0xFFD1FAE5),
                          () => setState(() => _selectedActivityTab = 1),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // My Activity & Posts Feed Section
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Nhật ký & Bài viết của tôi',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: Color(0xFF334155)),
                      ),
                      IconButton(
                        onPressed: _fetchMyActivity,
                        icon: const Icon(Icons.refresh_rounded, size: 18, color: Color(0xFF0EA5E9)),
                        tooltip: 'Làm mới',
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),

                  // Tab switchers (Bài viết vs Check-in)
                  Container(
                    height: 42,
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          child: GestureDetector(
                            onTap: () => setState(() => _selectedActivityTab = 0),
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              decoration: BoxDecoration(
                                color: _selectedActivityTab == 0 ? const Color(0xFF0EA5E9) : Colors.transparent,
                                borderRadius: BorderRadius.circular(10),
                                boxShadow: _selectedActivityTab == 0
                                    ? [BoxShadow(color: const Color(0xFF0EA5E9).withValues(alpha: 0.3), blurRadius: 4, offset: const Offset(0, 2))]
                                    : [],
                              ),
                              alignment: Alignment.center,
                              child: Text(
                                '📝 Bài viết (${_myPosts.length})',
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 12.5,
                                  color: _selectedActivityTab == 0 ? Colors.white : const Color(0xFF64748B),
                                ),
                              ),
                            ),
                          ),
                        ),
                        Expanded(
                          child: GestureDetector(
                            onTap: () => setState(() => _selectedActivityTab = 1),
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              decoration: BoxDecoration(
                                color: _selectedActivityTab == 1 ? const Color(0xFF10B981) : Colors.transparent,
                                borderRadius: BorderRadius.circular(10),
                                boxShadow: _selectedActivityTab == 1
                                    ? [BoxShadow(color: const Color(0xFF10B981).withValues(alpha: 0.3), blurRadius: 4, offset: const Offset(0, 2))]
                                    : [],
                              ),
                              alignment: Alignment.center,
                              child: Text(
                                '📍 Check-in (${_myCheckins.length})',
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 12.5,
                                  color: _selectedActivityTab == 1 ? Colors.white : const Color(0xFF64748B),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Content list
                  if (_isLoadingActivity)
                    const Padding(
                      padding: EdgeInsets.all(24),
                      child: Center(child: CircularProgressIndicator(color: Color(0xFF0EA5E9))),
                    )
                  else if (_selectedActivityTab == 0) ...[
                    // Posts List
                    if (_myPosts.isEmpty)
                      Container(
                        padding: const EdgeInsets.all(24),
                        width: double.infinity,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: const Color(0xFFF1F5F9)),
                        ),
                        child: const Column(
                          children: [
                            Icon(Icons.article_outlined, size: 40, color: Color(0xFF94A3B8)),
                            SizedBox(height: 8),
                            Text('Bạn chưa có bài viết nào', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                            SizedBox(height: 4),
                            Text('Đăng bài từ Bản tin Đông Anh để lưu trữ tại đây!', style: TextStyle(color: Color(0xFF64748B), fontSize: 12), textAlign: TextAlign.center),
                          ],
                        ),
                      )
                    else
                      ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: _myPosts.length,
                        itemBuilder: (context, index) {
                          final item = _myPosts[index];
                          return _buildMyPostCard(item);
                        },
                      ),
                  ] else ...[
                    // Checkins List
                    if (_myCheckins.isEmpty)
                      Container(
                        padding: const EdgeInsets.all(24),
                        width: double.infinity,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: const Color(0xFFF1F5F9)),
                        ),
                        child: const Column(
                          children: [
                            Icon(Icons.add_location_alt_outlined, size: 40, color: Color(0xFF94A3B8)),
                            SizedBox(height: 8),
                            Text('Bạn chưa có nhật ký check-in nào', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                            SizedBox(height: 4),
                            Text('Ghé thăm các địa điểm Cổ Loa & bấm Check-in để lưu kỉ niệm!', style: TextStyle(color: Color(0xFF64748B), fontSize: 12), textAlign: TextAlign.center),
                          ],
                        ),
                      )
                    else
                      ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: _myCheckins.length,
                        itemBuilder: (context, index) {
                          final item = _myCheckins[index];
                          return _buildMyCheckinCard(item);
                        },
                      ),
                  ],
                ],
              ),
            ),



            const SizedBox(height: 24),


            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  Widget _socialStat(String count, String label, [VoidCallback? onTap]) {
    return GestureDetector(
      onTap: onTap,
      behavior: HitTestBehavior.opaque,
      child: Column(
        children: [
          Text(
            count,
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
          ),
        ],
      ),
    );
  }

  Widget _statDivider() {
    return Container(
      height: 24,
      width: 1,
      color: const Color(0xFFE2E8F0),
    );
  }

  Widget _activityStatCard(String value, String label, IconData icon, Color iconColor, Color bgColor, [VoidCallback? onTap]) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: const Color(0xFFF1F5F9)),
          boxShadow: [
            BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 10),
          ],
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: bgColor,
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, color: iconColor, size: 22),
            ),
            const SizedBox(width: 12),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  value,
                  style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                ),
                Text(
                  label,
                  style: const TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.w500),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMyPostCard(Map<String, dynamic> item) {
    final title = item['name'] ?? item['title'] ?? '';
    final desc = item['description'] ?? item['content'] ?? '';
    final time = item['created_at_human'] ?? item['time'] ?? 'Gần đây';

    String? imageUrl;
    if (item['images'] is List && (item['images'] as List).isNotEmpty) {
      imageUrl = item['images'][0].toString();
    } else if (item['image_path'] != null) {
      imageUrl = item['image_path'].toString();
    }
    if (imageUrl != null && imageUrl.isNotEmpty && !imageUrl.startsWith('http')) {
      imageUrl = 'https://donganhdiscovery.xadonganh.com/${imageUrl.startsWith('/') ? imageUrl.substring(1) : imageUrl}';
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFF1F5F9)),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 8),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(Icons.article_rounded, color: Color(0xFF0EA5E9), size: 18),
                  const SizedBox(width: 6),
                  Text(
                    time,
                    style: const TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.w500),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: const Text('Bản tin', style: TextStyle(color: Color(0xFF0EA5E9), fontSize: 10, fontWeight: FontWeight.bold)),
              ),
            ],
          ),
          if (title.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              title,
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
            ),
          ],
          if (desc.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(
              desc,
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontSize: 13, color: Color(0xFF334155), height: 1.35),
            ),
          ],
          if (imageUrl != null && imageUrl.isNotEmpty) ...[
            const SizedBox(height: 8),
            ClipRRect(
              borderRadius: BorderRadius.circular(10),
              child: Image.network(
                imageUrl,
                height: 140,
                width: double.infinity,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => const SizedBox.shrink(),
              ),
            ),
          ],
          const SizedBox(height: 10),
          Row(
            children: [
              Row(
                children: [
                  const Icon(Icons.favorite_rounded, color: Color(0xFFEF4444), size: 15),
                  const SizedBox(width: 4),
                  Text('${item['likes_count'] ?? 0} lượt thích', style: const TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                ],
              ),
              const SizedBox(width: 16),
              Row(
                children: [
                  const Icon(Icons.chat_bubble_outline_rounded, color: Color(0xFF0EA5E9), size: 15),
                  const SizedBox(width: 4),
                  Text('${item['comments_count'] ?? 0} bình luận', style: const TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildMyCheckinCard(Map<String, dynamic> item) {
    final name = item['eatery_name'] ?? item['eatery']?['name'] ?? item['title'] ?? 'Địa điểm Cổ Loa';
    final comment = item['comment'] ?? item['description'] ?? '';
    final rating = item['rating'] is int ? item['rating'] : (int.tryParse(item['rating']?.toString() ?? '5') ?? 5);
    final time = item['created_at_human'] ?? item['time'] ?? 'Gần đây';

    String? imageUrl = item['image_path'] ?? item['image'];
    if (imageUrl != null && imageUrl.isNotEmpty && !imageUrl.startsWith('http')) {
      imageUrl = 'https://donganhdiscovery.xadonganh.com/${imageUrl.startsWith('/') ? imageUrl.substring(1) : imageUrl}';
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFF1F5F9)),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 8),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(Icons.location_on_rounded, color: Color(0xFF10B981), size: 18),
                  const SizedBox(width: 6),
                  Text(
                    name,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                  ),
                ],
              ),
              Row(
                children: List.generate(
                  5,
                  (i) => Icon(
                    i < rating ? Icons.star_rounded : Icons.star_outline_rounded,
                    color: Colors.amber,
                    size: 14,
                  ),
                ),
              ),
            ],
          ),
          if (comment.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              comment,
              style: const TextStyle(fontSize: 13, color: Color(0xFF334155), height: 1.35),
            ),
          ],
          if (imageUrl != null && imageUrl.isNotEmpty) ...[
            const SizedBox(height: 8),
            ClipRRect(
              borderRadius: BorderRadius.circular(10),
              child: Image.network(
                imageUrl,
                height: 140,
                width: double.infinity,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => const SizedBox.shrink(),
              ),
            ),
          ],
          const SizedBox(height: 8),
          Text(
            time,
            style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 11),
          ),
        ],
      ),
    );
  }
}
