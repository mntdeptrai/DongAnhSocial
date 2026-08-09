import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/gestures.dart';
import 'package:flutter/services.dart';
import 'package:image_picker/image_picker.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/api_service.dart';
import '../widgets/squircle_helper.dart';

class NewsBulletinScreen extends StatefulWidget {
  const NewsBulletinScreen({super.key});

  @override
  State<NewsBulletinScreen> createState() => _NewsBulletinScreenState();
}

class _NewsBulletinScreenState extends State<NewsBulletinScreen> {
  List<dynamic> _posts = [];
  bool _isLoading = true;
  final TextEditingController _postController = TextEditingController();
  final TextEditingController _titleController = TextEditingController();
  bool _isPublishing = false;

  final Set<String> _likedPosts = {};
  final Map<String, int> _likesCounts = {};
  final Map<String, List<Map<String, dynamic>>> _postComments = {};
  final Set<String> _expandedPosts = {};

  @override
  void initState() {
    super.initState();
    _fetchNewsfeed();
  }

  @override
  void dispose() {
    _postController.dispose();
    _titleController.dispose();
    super.dispose();
  }

  Future<void> _fetchNewsfeed() async {
    setState(() => _isLoading = true);
    try {
      final feed = await ApiService.getNewsfeed();
      if (mounted) {
        setState(() {
          _posts = feed;
          _isLoading = false;

          for (var item in feed) {
            final postId = item['id'].toString();
            if (item['is_liked'] == true) {
              _likedPosts.add(postId);
            }
            if (item['likes_count'] != null) {
              _likesCounts[postId] = item['likes_count'] as int;
            }
          }
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _toggleLike(dynamic item) async {
    final postId = item['id'].toString();
    final currentLikes = _likesCounts[postId] ?? (item['likes_count'] ?? 0);
    final isLikedNow = _likedPosts.contains(postId);

    setState(() {
      if (isLikedNow) {
        _likedPosts.remove(postId);
        _likesCounts[postId] = (currentLikes > 0) ? currentLikes - 1 : 0;
      } else {
        _likedPosts.add(postId);
        _likesCounts[postId] = currentLikes + 1;
      }
    });

    final res = await ApiService.toggleReaction(
      postId: item['numeric_id'] ?? item['id'],
      type: item['type'] ?? 'post',
    );

    if (mounted && res['success'] == true) {
      setState(() {
        final serverLikes = res['likes_count'] as int?;
        if (serverLikes != null) {
          _likesCounts[postId] = serverLikes;
        }
        if (res['liked'] == true) {
          _likedPosts.add(postId);
        } else if (res['liked'] == false) {
          _likedPosts.remove(postId);
        }
      });
    }
  }

  void _sharePost(dynamic item) {
    _showShareBottomSheet(context, item);
  }

  List<String> _getPostImages(dynamic item) {
    final List<String> urls = [];
    void addUrl(dynamic raw) {
      if (raw == null) return;
      final s = raw.toString().trim();
      if (s.isEmpty) return;
      final full = s.startsWith('http')
          ? s
          : 'https://donganhdiscovery.xadonganh.com/${s.startsWith('/') ? s.substring(1) : s}';
      if (!urls.contains(full)) urls.add(full);
    }

    if (item['images'] is List) {
      for (var img in item['images']) {
        addUrl(img);
      }
    } else if (item['images'] is String && item['images'].toString().isNotEmpty) {
      try {
        final decoded = item['images'].toString().startsWith('[') ? (item['images'] as String) : null;
        if (decoded != null) {
          final List list = (item['images'] as String).replaceAll('[', '').replaceAll(']', '').replaceAll('"', '').split(',');
          for (var img in list) {
            addUrl(img);
          }
        } else {
          addUrl(item['images']);
        }
      } catch (_) {
        addUrl(item['images']);
      }
    }

    if (item['image_paths'] is List) {
      for (var img in item['image_paths']) {
        addUrl(img);
      }
    }

    if (item['image_path'] != null && item['image_path'].toString().isNotEmpty) {
      addUrl(item['image_path']);
    }

    return urls;
  }

  void _openFullscreenGallery(BuildContext context, List<String> images, int initialIndex) {
    showDialog(
      context: context,
      barrierColor: Colors.black,
      builder: (ctx) {
        final PageController pageController = PageController(initialPage: initialIndex);
        int currentIndex = initialIndex;

        return StatefulBuilder(
          builder: (context, setDialogState) {
            return Scaffold(
              backgroundColor: Colors.black,
              appBar: AppBar(
                backgroundColor: Colors.black,
                foregroundColor: Colors.white,
                title: Text('${currentIndex + 1} / ${images.length}', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                leading: IconButton(
                  icon: const Icon(Icons.close_rounded, color: Colors.white),
                  onPressed: () => Navigator.pop(ctx),
                ),
              ),
              body: PageView.builder(
                controller: pageController,
                itemCount: images.length,
                onPageChanged: (idx) {
                  setDialogState(() {
                    currentIndex = idx;
                  });
                },
                itemBuilder: (context, index) {
                  return InteractiveViewer(
                    minScale: 0.8,
                    maxScale: 4.0,
                    child: Center(
                      child: Image.network(
                        images[index],
                        fit: BoxFit.contain,
                        errorBuilder: (_, __, ___) => const Icon(Icons.broken_image, color: Colors.white54, size: 64),
                      ),
                    ),
                  );
                },
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildMultiImageGrid(List<String> images) {
    if (images.isEmpty) return const SizedBox.shrink();

    void openGallery(int initialIndex) {
      _openFullscreenGallery(context, images, initialIndex);
    }

    if (images.length == 1) {
      return GestureDetector(
        onTap: () => openGallery(0),
        child: ClipRRect(
          child: Image.network(
            images[0],
            width: double.infinity,
            height: 250,
            fit: BoxFit.cover,
            errorBuilder: (_, __, ___) => const SizedBox.shrink(),
          ),
        ),
      );
    }

    if (images.length == 2) {
      return SizedBox(
        height: 200,
        child: Row(
          children: [
            Expanded(
              child: GestureDetector(
                onTap: () => openGallery(0),
                child: Image.network(images[0], height: 200, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const SizedBox.shrink()),
              ),
            ),
            const SizedBox(width: 2),
            Expanded(
              child: GestureDetector(
                onTap: () => openGallery(1),
                child: Image.network(images[1], height: 200, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const SizedBox.shrink()),
              ),
            ),
          ],
        ),
      );
    }

    if (images.length == 3) {
      return SizedBox(
        height: 240,
        child: Row(
          children: [
            Expanded(
              flex: 2,
              child: GestureDetector(
                onTap: () => openGallery(0),
                child: Image.network(images[0], height: 240, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const SizedBox.shrink()),
              ),
            ),
            const SizedBox(width: 2),
            Expanded(
              flex: 1,
              child: Column(
                children: [
                  Expanded(
                    child: GestureDetector(
                      onTap: () => openGallery(1),
                      child: Image.network(images[1], width: double.infinity, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const SizedBox.shrink()),
                    ),
                  ),
                  const SizedBox(height: 2),
                  Expanded(
                    child: GestureDetector(
                      onTap: () => openGallery(2),
                      child: Image.network(images[2], width: double.infinity, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const SizedBox.shrink()),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    // 4 or more images: 2x2 grid with +N on 4th image (Facebook / Web style)
    final remainingCount = images.length - 4;
    return SizedBox(
      height: 260,
      child: Column(
        children: [
          Expanded(
            child: Row(
              children: [
                Expanded(
                  child: GestureDetector(
                    onTap: () => openGallery(0),
                    child: Image.network(images[0], height: double.infinity, width: double.infinity, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const SizedBox.shrink()),
                  ),
                ),
                const SizedBox(width: 2),
                Expanded(
                  child: GestureDetector(
                    onTap: () => openGallery(1),
                    child: Image.network(images[1], height: double.infinity, width: double.infinity, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const SizedBox.shrink()),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 2),
          Expanded(
            child: Row(
              children: [
                Expanded(
                  child: GestureDetector(
                    onTap: () => openGallery(2),
                    child: Image.network(images[2], height: double.infinity, width: double.infinity, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const SizedBox.shrink()),
                  ),
                ),
                const SizedBox(width: 2),
                Expanded(
                  child: GestureDetector(
                    onTap: () => openGallery(3),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        Image.network(images[3], height: double.infinity, width: double.infinity, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const SizedBox.shrink()),
                        if (remainingCount > 0)
                          Container(
                            color: Colors.black.withValues(alpha: 0.55),
                            alignment: Alignment.center,
                            child: Text(
                              '+$remainingCount',
                              style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold),
                            ),
                          ),
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

  void _showShareBottomSheet(BuildContext context, dynamic item) {
    final title = item['title'] ?? 'Bài viết trên Bản tin Đông Anh';
    const shareUrl = 'https://donganhdiscovery.xadonganh.com/ban-tin';

    final friendsList = [
      {'name': 'Thành viên...', 'avatar': '👧'},
      {'name': 'Trường M...', 'avatar': '👦'},
      {'name': 'Trường T...', 'avatar': '🧑'},
      {'name': 'Nguyễn Tr...', 'avatar': '👨'},
      {'name': 'Cổ Loa Club', 'avatar': '👧'},
    ];

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (context) => Container(
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
                    'Gửi hoặc Chia sẻ bài viết',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF0F172A),
                    ),
                  ),
                ),
                GestureDetector(
                  onTap: () => Navigator.pop(context),
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
            const SizedBox(height: 20),

            const Text(
              'GỬI TRỰC TIẾP CHO BẠN BÈ',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: Color(0xFF64748B),
                letterSpacing: 0.5,
              ),
            ),
            const SizedBox(height: 14),

            SizedBox(
              height: 98,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: friendsList.length,
                separatorBuilder: (_, __) => const SizedBox(width: 14),
                itemBuilder: (context, idx) {
                  final friend = friendsList[idx];
                  return GestureDetector(
                    onTap: () {
                      Navigator.pop(context);
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Row(
                            children: [
                              const Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                              const SizedBox(width: 8),
                              Text('Đã chia sẻ trực tiếp tới ${friend['name']}!'),
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
                              color: const Color(0xFFF8FAFC),
                              borderRadius: BorderRadius.circular(22),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: Center(
                              child: Text(
                                friend['avatar']!,
                                style: const TextStyle(fontSize: 28),
                              ),
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            friend['name']!,
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF334155),
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            textAlign: TextAlign.center,
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),

            const Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: Divider(color: Color(0xFFF1F5F9), height: 1, thickness: 1),
            ),

            const Text(
              'CHIA SẺ LÊN HỆ SINH THÁI',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: Color(0xFF64748B),
                letterSpacing: 0.5,
              ),
            ),
            const SizedBox(height: 16),

            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildShareActionItem(
                  icon: Icons.newspaper_rounded,
                  label: 'Bảng tin cá\nnhân',
                  bgColor: const Color(0xFFEEF2FF),
                  iconColor: const Color(0xFF6366F1),
                  onTap: () {
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: const Row(
                          children: [
                            Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                            SizedBox(width: 8),
                            Text('Đã đăng bài viết lên Bảng tin cá nhân!'),
                          ],
                        ),
                        backgroundColor: const Color(0xFF059669),
                        behavior: SnackBarBehavior.floating,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    );
                  },
                ),

                _buildShareActionItem(
                  icon: Icons.link_rounded,
                  label: 'Sao chép\nliên kết',
                  bgColor: const Color(0xFFECFDF5),
                  iconColor: const Color(0xFF059669),
                  onTap: () {
                    Clipboard.setData(ClipboardData(text: '$title\nXem thêm tại: $shareUrl'));
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: const Row(
                          children: [
                            Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                            SizedBox(width: 8),
                            Text('Đã sao chép liên kết chia sẻ bài viết!'),
                          ],
                        ),
                        backgroundColor: const Color(0xFF059669),
                        behavior: SnackBarBehavior.floating,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    );
                  },
                ),

                _buildShareActionItem(
                  icon: Icons.language_rounded,
                  label: 'Ứng dụng\nkhác',
                  bgColor: const Color(0xFFFEF3C7),
                  iconColor: const Color(0xFFD97706),
                  onTap: () {
                    Clipboard.setData(ClipboardData(text: '$title\n$shareUrl'));
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: const Row(
                          children: [
                            Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                            SizedBox(width: 8),
                            Text('Đã copy liên kết để chia sẻ qua ứng dụng khác!'),
                          ],
                        ),
                        backgroundColor: const Color(0xFF059669),
                        behavior: SnackBarBehavior.floating,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    );
                  },
                ),

                _buildShareActionItem(
                  icon: Icons.chat_bubble_outline_rounded,
                  label: 'Đông Anh\nChat',
                  bgColor: const Color(0xFFE0F2FE),
                  iconColor: const Color(0xFF0EA5E9),
                  onTap: () {
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: const Row(
                          children: [
                            Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                            SizedBox(width: 8),
                            Text('Đã gửi bài viết vào Đông Anh Chat!'),
                          ],
                        ),
                        backgroundColor: const Color(0xFF059669),
                        behavior: SnackBarBehavior.floating,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    );
                  },
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildShareActionItem({
    required IconData icon,
    required String label,
    required Color bgColor,
    required Color iconColor,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: SizedBox(
        width: 72,
        child: Column(
          children: [
            Container(
              width: 62,
              height: 62,
              decoration: BoxDecoration(
                color: bgColor,
                borderRadius: BorderRadius.circular(22),
              ),
              child: Center(
                child: Icon(icon, color: iconColor, size: 28),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: const TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: Color(0xFF334155),
                height: 1.2,
              ),
              textAlign: TextAlign.center,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  void _showCommentsBottomSheet(BuildContext context, dynamic item) {
    final postId = item['id'].toString();
    final commentsController = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) {
          final rawComments = (item['comments'] is List) ? (item['comments'] as List) : [];
          final comments = _postComments[postId] ?? rawComments.map((c) => Map<String, dynamic>.from(c as Map)).toList();

          return Container(
            height: MediaQuery.of(context).size.height * 0.72,
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
            ),
            child: Column(
              children: [
                // Handle Bar
                Container(
                  margin: const EdgeInsets.only(top: 10, bottom: 6),
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2)),
                ),

                // Header
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Bình luận (${comments.length})',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A)),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close_rounded, color: Color(0xFF64748B)),
                        onPressed: () => Navigator.pop(context),
                      ),
                    ],
                  ),
                ),
                const Divider(height: 1),

                // Comments List
                Expanded(
                  child: comments.isEmpty
                      ? const Center(child: Text('Chưa có bình luận nào. Hãy là người đầu tiên!', style: TextStyle(color: Colors.grey)))
                      : ListView.separated(
                          padding: const EdgeInsets.all(16),
                          itemCount: comments.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 12),
                          itemBuilder: (context, index) {
                            final c = comments[index];
                            return Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                CircleAvatar(
                                  radius: 18,
                                  backgroundColor: const Color(0xFF0EA5E9).withValues(alpha: 0.15),
                                  child: Text(
                                    (c['author'] ?? 'U')[0].toUpperCase(),
                                    style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0EA5E9)),
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFF8FAFC),
                                      borderRadius: BorderRadius.circular(14),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(c['author']!, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
                                        const SizedBox(height: 2),
                                        Text(c['text']!, style: const TextStyle(fontSize: 13, color: Color(0xFF334155))),
                                        const SizedBox(height: 4),
                                        Text(c['time']!, style: const TextStyle(fontSize: 10, color: Color(0xFF94A3B8))),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            );
                          },
                        ),
                ),

                // Input Bar
                SafeArea(
                  child: Container(
                    padding: EdgeInsets.only(
                      left: 16,
                      right: 16,
                      top: 8,
                      bottom: MediaQuery.of(context).viewInsets.bottom + 8,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      border: Border(top: BorderSide(color: Colors.grey.shade200)),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: commentsController,
                            decoration: InputDecoration(
                              hintText: 'Viết bình luận...',
                              hintStyle: const TextStyle(fontSize: 13.5, color: Color(0xFF94A3B8)),
                              filled: true,
                              fillColor: const Color(0xFFF1F5F9),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(20), borderSide: BorderSide.none),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        IconButton(
                          style: IconButton.styleFrom(
                            backgroundColor: const Color(0xFF0EA5E9),
                            foregroundColor: Colors.white,
                          ),
                          icon: const Icon(Icons.send_rounded, size: 18),
                          onPressed: () {
                            final text = commentsController.text.trim();
                            if (text.isNotEmpty) {
                              final user = ApiService.currentUser;
                              final userName = user?['name'] ?? 'Thành viên Đông Anh';
                              final newC = {'author': userName, 'text': text, 'time': 'Vừa xong'};

                              setModalState(() {
                                comments.add(newC);
                              });

                              setState(() {
                                _postComments[postId] = comments;
                                item['comments_count'] = (item['comments_count'] ?? 0) + 1;
                              });

                              commentsController.clear();
                            }
                          },
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  void _showCreatePostModal() {
    final user = ApiService.currentUser;
    if (!ApiService.isAuthenticated) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Vui lòng đăng nhập để chia sẻ bài viết lên Bản tin!'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    final userName = user?['name'] ?? 'Thành viên Đông Anh';
    String? selectedImagePath;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom + 20,
                top: 16,
                left: 20,
                right: 20,
              ),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
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
                            'Tạo bài viết',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF0F172A),
                            ),
                          ),
                        ),
                        GestureDetector(
                          onTap: () => Navigator.pop(context),
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
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 12),
                      child: Divider(color: Color(0xFFF1F5F9), height: 1, thickness: 1),
                    ),

                    Row(
                      children: [
                        CircleAvatar(
                          radius: 22,
                          backgroundColor: const Color(0xFF0EA5E9),
                          backgroundImage: NetworkImage(ApiService.getAvatarUrl(user, userName)),
                        ),
                        const SizedBox(width: 12),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              userName,
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 15,
                                color: Color(0xFF0F172A),
                              ),
                            ),
                            const SizedBox(height: 3),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: const Color(0xFFEFF6FF),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.public, size: 12, color: Color(0xFF0EA5E9)),
                                  SizedBox(width: 4),
                                  Text(
                                    'Công khai',
                                    style: TextStyle(
                                      color: Color(0xFF0EA5E9),
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    TextField(
                      controller: _titleController,
                      decoration: InputDecoration(
                        hintText: 'Tiêu đề bài viết...',
                        hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 14),
                        filled: true,
                        fillColor: const Color(0xFFFFFFFF),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide(color: Colors.grey.shade300),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide(color: Colors.grey.shade200),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: Color(0xFF0EA5E9), width: 1.5),
                        ),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 12),

                    TextField(
                      controller: _postController,
                      maxLines: 4,
                      decoration: InputDecoration(
                        hintText: '$userName ơi, bạn đang nghĩ gì thế?',
                        hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 14),
                        filled: true,
                        fillColor: const Color(0xFFFFFFFF),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide(color: Colors.grey.shade300),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide(color: Colors.grey.shade200),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(color: Color(0xFF0EA5E9), width: 1.5),
                        ),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 14),

                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFFFFF),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Thêm vào bài viết của bạn',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF334155),
                            ),
                          ),
                          Row(
                            children: [
                              GestureDetector(
                                onTap: () async {
                                  try {
                                    final picked = await ImagePicker().pickImage(source: ImageSource.gallery);
                                    if (picked != null) {
                                      setModalState(() {
                                        selectedImagePath = picked.path;
                                      });
                                    }
                                  } catch (_) {}
                                },
                                child: Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFECFDF5),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: const Icon(Icons.photo_library_rounded, color: Color(0xFF10B981), size: 22),
                                ),
                              ),
                              const SizedBox(width: 8),
                              GestureDetector(
                                onTap: () async {
                                  try {
                                    final picked = await ImagePicker().pickVideo(source: ImageSource.gallery);
                                    if (picked != null) {
                                      setModalState(() {
                                        selectedImagePath = picked.path;
                                      });
                                    }
                                  } catch (_) {}
                                },
                                child: Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFFEF2F2),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: const Icon(Icons.videocam_rounded, color: Color(0xFFEF4444), size: 22),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    if (selectedImagePath != null) ...[
                      const SizedBox(height: 10),
                      Builder(
                        builder: (context) {
                          final ext = selectedImagePath!.split('.').last.toLowerCase();
                          final isVideo = (ext == 'mp4' || ext == 'mov' || ext == 'avi');

                          return Stack(
                            alignment: Alignment.center,
                            children: [
                              ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: isVideo
                                    ? Container(
                                        height: 120,
                                        width: double.infinity,
                                        color: const Color(0xFF1E293B),
                                        child: const Center(
                                          child: Icon(Icons.movie_creation_rounded, color: Colors.white54, size: 40),
                                        ),
                                      )
                                    : Image.file(
                                        File(selectedImagePath!),
                                        height: 120,
                                        width: double.infinity,
                                        fit: BoxFit.cover,
                                      ),
                              ),
                              if (isVideo)
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                  decoration: BoxDecoration(
                                    color: Colors.black.withValues(alpha: 0.7),
                                    borderRadius: BorderRadius.circular(16),
                                  ),
                                  child: const Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Icon(Icons.play_circle_fill_rounded, color: Colors.white, size: 18),
                                      SizedBox(width: 4),
                                      Text('Video đính kèm', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                                    ],
                                  ),
                                ),
                              Positioned(
                                top: 6,
                                right: 6,
                                child: GestureDetector(
                                  onTap: () => setModalState(() => selectedImagePath = null),
                                  child: Container(
                                    padding: const EdgeInsets.all(4),
                                    decoration: const BoxDecoration(
                                      color: Colors.black54,
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(Icons.close, color: Colors.white, size: 16),
                                  ),
                                ),
                              ),
                            ],
                          );
                        },
                      ),
                    ],

                    const SizedBox(height: 16),

                    SizedBox(
                      height: 48,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF2563EB),
                          foregroundColor: Colors.white,
                          elevation: 0,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: _isPublishing
                            ? null
                            : () async {
                                final text = _postController.text.trim();
                                final title = _titleController.text.trim();
                                if (text.isEmpty) return;

                                setModalState(() => _isPublishing = true);
                                final res = await ApiService.createPost(
                                  description: text,
                                  name: title.isNotEmpty ? title : null,
                                  imagePath: selectedImagePath,
                                );
                                setModalState(() => _isPublishing = false);

                                if (mounted) {
                                  if (res['success'] == true) {
                                    _postController.clear();
                                    _titleController.clear();
                                    Navigator.pop(context);
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(
                                        content: Text(res['message'] ?? 'Đã đăng bài viết!'),
                                        backgroundColor: const Color(0xFF059669),
                                      ),
                                    );
                                    _fetchNewsfeed();
                                  } else {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(
                                        content: Text(res['message'] ?? 'Đăng bài thất bại!'),
                                        backgroundColor: const Color(0xFFEF4444),
                                      ),
                                    );
                                  }
                                }
                              },
                        child: _isPublishing
                            ? const SizedBox(
                                width: 22,
                                height: 22,
                                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5),
                              )
                            : const Text(
                                'Đăng',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                              ),
                      ),
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



  Widget _buildParsedRichText(String text, {TextStyle? style}) {
    final urlRegex = RegExp(
      r'(https?:\/\/[^\s]+|www\.[^\s]+)',
      caseSensitive: false,
    );

    final matches = urlRegex.allMatches(text);
    if (matches.isEmpty) {
      return Text(
        text,
        style: style ?? const TextStyle(fontSize: 13.5, color: Color(0xFF334155), height: 1.4),
      );
    }

    final spans = <InlineSpan>[];
    int lastMatchEnd = 0;

    for (final match in matches) {
      if (match.start > lastMatchEnd) {
        spans.add(TextSpan(
          text: text.substring(lastMatchEnd, match.start),
          style: style ?? const TextStyle(fontSize: 13.5, color: Color(0xFF334155), height: 1.4),
        ));
      }

      final rawUrl = match.group(0)!;
      final validUrl = rawUrl.startsWith('http') ? rawUrl : 'https://$rawUrl';

      spans.add(
        TextSpan(
          text: rawUrl,
          style: (style ?? const TextStyle(fontSize: 13.5, height: 1.4)).copyWith(
            color: const Color(0xFF0EA5E9),
            fontWeight: FontWeight.bold,
            decoration: TextDecoration.underline,
            decorationColor: const Color(0xFF0EA5E9).withValues(alpha: 0.5),
          ),
          recognizer: TapGestureRecognizer()
            ..onTap = () async {
              try {
                final uri = Uri.parse(validUrl);
                if (await canLaunchUrl(uri)) {
                  await launchUrl(uri, mode: LaunchMode.externalApplication);
                }
              } catch (_) {}
            },
        ),
      );

      lastMatchEnd = match.end;
    }

    if (lastMatchEnd < text.length) {
      spans.add(TextSpan(
        text: text.substring(lastMatchEnd),
        style: style ?? const TextStyle(fontSize: 13.5, color: Color(0xFF334155), height: 1.4),
      ));
    }

    return RichText(
      text: TextSpan(children: spans),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = ApiService.currentUser;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: RefreshIndicator(
        onRefresh: _fetchNewsfeed,
        color: const Color(0xFF0EA5E9),
        child: _isLoading
            ? const Center(child: CircularProgressIndicator(color: Color(0xFF0EA5E9)))
            : ListView(
                padding: const EdgeInsets.fromLTRB(14, 12, 14, 90),
                children: [
                  // 1. Post Creation Input Bar
                  GestureDetector(
                    onTap: _showCreatePostModal,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      decoration: SquircleHelper.decoration(
                        radius: 18,
                        color: Colors.white,
                        borderSide: BorderSide(color: Colors.grey.shade200),
                        boxShadow: [
                          BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 10, offset: const Offset(0, 2)),
                        ],
                      ),
                      child: Row(
                        children: [
                          CircleAvatar(
                            radius: 20,
                            backgroundImage: ResizeImage(NetworkImage(ApiService.getAvatarUrl(user, user?['name'])), width: 90),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                ApiService.isAuthenticated
                                    ? 'Đăng bài viết mới lên Bản tin...'
                                    : 'Đăng nhập để chia sẻ bài viết lên Bản tin...',
                                style: const TextStyle(color: Color(0xFF64748B), fontSize: 13.5),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          const Icon(Icons.image_rounded, color: Color(0xFF10B981), size: 24),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 14),

                  const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 4, vertical: 4),
                    child: Text(
                      'BÀI VIẾT MỚI NHẤT BẢN TIN',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF64748B),
                        letterSpacing: 0.8,
                      ),
                    ),
                  ),

                  const SizedBox(height: 8),

                  // 3. Posts List
                  if (_posts.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(30),
                      alignment: Alignment.center,
                      child: const Column(
                        children: [
                          Icon(Icons.article_outlined, size: 48, color: Colors.grey),
                          SizedBox(height: 10),
                          Text('Chưa có bài viết nào trên Bản tin.', style: TextStyle(color: Colors.grey, fontWeight: FontWeight.bold)),
                        ],
                      ),
                    )
                  else
                    ..._posts.map((item) {
                      final postId = item['id'].toString();
                      final title = item['title'] ?? '';
                      final desc = item['description'] ?? '';
                      final authorName = item['author_name'] ?? 'Thành viên Đông Anh';
                      final role = item['author_role'] ?? 'user';
                      final timeStr = item['created_at_human'] ?? 'Vừa xong';

                      final isLiked = _likedPosts.contains(postId);
                      final likesCount = _likesCounts[postId] ?? (item['likes_count'] ?? 0);
                      final commentsCount = item['comments_count'] ?? 0;

                      return Container(
                        margin: const EdgeInsets.only(bottom: 14),
                        decoration: SquircleHelper.decoration(
                          radius: 18,
                          color: Colors.white,
                          borderSide: BorderSide(color: Colors.grey.shade200),
                          boxShadow: [
                            BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 8, offset: const Offset(0, 2)),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Author Header
                            Padding(
                              padding: const EdgeInsets.all(12),
                              child: Row(
                                children: [
                                  CircleAvatar(
                                    radius: 20,
                                    backgroundImage: ResizeImage(NetworkImage(ApiService.getAvatarUrl(item['author_avatar'] ?? item, authorName)), width: 100),
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          children: [
                                            Flexible(
                                              child: Text(
                                                authorName,
                                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                            if (role == 'principal' || role == 'admin' || role == 'manager' || item['is_verified'] == true) ...[
                                              const SizedBox(width: 4),
                                              const Icon(Icons.star_rounded, color: Colors.amber, size: 16),
                                            ],
                                          ],
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          '$timeStr • Công khai',
                                          style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                                        ),
                                      ],
                                    ),
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.more_horiz, color: Color(0xFF64748B)),
                                    onPressed: () {},
                                  ),
                                ],
                              ),
                            ),

                            // Post Title
                            if (title.toString().isNotEmpty)
                              Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 2),
                                child: Text(
                                  title,
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A)),
                                ),
                              ),

                            // Post Content Description (Collapsible with "... Xem thêm" / "Thu gọn")
                            if (desc.toString().isNotEmpty)
                              Builder(
                                builder: (context) {
                                  final fullText = desc.toString().trim();
                                  final isExpanded = _expandedPosts.contains(postId);
                                  final isLongText = fullText.length > 160;
                                  final displayText = (!isExpanded && isLongText)
                                      ? '${fullText.substring(0, 160)}...'
                                      : fullText;

                                  return Padding(
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        _buildParsedRichText(
                                          displayText,
                                          style: const TextStyle(fontSize: 13.5, color: Color(0xFF334155), height: 1.4),
                                        ),
                                        if (isLongText)
                                          GestureDetector(
                                            onTap: () {
                                              setState(() {
                                                if (isExpanded) {
                                                  _expandedPosts.remove(postId);
                                                } else {
                                                  _expandedPosts.add(postId);
                                                }
                                              });
                                            },
                                            behavior: HitTestBehavior.opaque,
                                            child: Padding(
                                              padding: const EdgeInsets.only(top: 4, bottom: 2),
                                              child: Text(
                                                isExpanded ? 'Thu gọn' : '... Xem thêm',
                                                style: const TextStyle(
                                                  color: Color(0xFF0EA5E9),
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 13.5,
                                                ),
                                              ),
                                            ),
                                          ),
                                      ],
                                    ),
                                  );
                                },
                              ),

                            const SizedBox(height: 8),

                            // Post Image / Gallery (Multi-photo Web/Facebook grid layout)
                            Builder(
                              builder: (context) {
                                final postImages = _getPostImages(item);
                                if (postImages.isEmpty) return const SizedBox.shrink();
                                return Padding(
                                  padding: const EdgeInsets.only(top: 8.0),
                                  child: _buildMultiImageGrid(postImages),
                                );
                              },
                            ),

                            // Footer Actions Row (Like & Comment)
                            Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Row(
                                    children: [
                                      const Icon(Icons.favorite_rounded, color: Colors.redAccent, size: 18),
                                      const SizedBox(width: 4),
                                      Text('$likesCount thích', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontWeight: FontWeight.bold)),
                                    ],
                                  ),
                                  Text('$commentsCount bình luận', style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                                ],
                              ),
                            ),

                            const Divider(height: 1),

                            Row(
                              children: [
                                Expanded(
                                  child: TextButton.icon(
                                    onPressed: () => _toggleLike(item),
                                    icon: Icon(
                                      isLiked ? Icons.thumb_up_alt_rounded : Icons.thumb_up_alt_outlined,
                                      size: 18,
                                      color: isLiked ? const Color(0xFF0EA5E9) : const Color(0xFF64748B),
                                    ),
                                    label: Text(
                                      'Thích',
                                      style: TextStyle(
                                        color: isLiked ? const Color(0xFF0EA5E9) : const Color(0xFF64748B),
                                        fontWeight: isLiked ? FontWeight.bold : FontWeight.normal,
                                        fontSize: 13,
                                      ),
                                    ),
                                  ),
                                ),
                                Expanded(
                                  child: TextButton.icon(
                                    onPressed: () => _showCommentsBottomSheet(context, item),
                                    icon: const Icon(Icons.chat_bubble_outline_rounded, size: 18, color: Color(0xFF64748B)),
                                    label: const Text('Bình luận', style: TextStyle(color: Color(0xFF64748B), fontSize: 13)),
                                  ),
                                ),
                                Expanded(
                                  child: TextButton.icon(
                                    onPressed: () => _sharePost(item),
                                    icon: const Icon(Icons.share_outlined, size: 18, color: Color(0xFF64748B)),
                                    label: const Text('Chia sẻ', style: TextStyle(color: Color(0xFF64748B), fontSize: 13)),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      );
                    }),
                ],
              ),
      ),
    );
  }
}
