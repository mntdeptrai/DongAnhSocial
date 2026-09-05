import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';

import '../models/post_model.dart';
import '../services/api_service.dart';
import '../widgets/category_filter_bar.dart';
import '../widgets/create_post_modal.dart';
import '../widgets/custom_loader.dart';
import '../widgets/post_card.dart';
import '../widgets/squircle_helper.dart';
import '../widgets/story_carousel.dart';

class NewsBulletinScreen extends StatefulWidget {
  final dynamic targetPostId;
  final String? targetTitle;

  const NewsBulletinScreen({
    super.key,
    this.targetPostId,
    this.targetTitle,
  });

  @override
  State<NewsBulletinScreen> createState() => _NewsBulletinScreenState();
}

class _NewsBulletinScreenState extends State<NewsBulletinScreen> {
  List<PostModel> _posts = [];
  bool _isLoading = true;

  final Set<String> _likedPosts = {};
  final Map<String, int> _likesCounts = {};
  final Map<String, List<Map<String, dynamic>>> _postComments = {};
  final Set<String> _expandedPosts = {};

  String _selectedCategory = 'all';

  final List<Map<String, String>> _categories = const [
    {'id': 'all', 'label': 'Tất cả', 'icon': '🔥'},
    {'id': 'food_tour', 'label': 'Food Tour', 'icon': '🍲'},
    {'id': 'school', 'label': 'Trường học', 'icon': '🏫'},
    {'id': 'media', 'label': 'Media', 'icon': '📸'},
    {'id': 'checkin', 'label': 'Check-in', 'icon': '🎈'},
  ];

  List<PostModel> get _filteredPosts {
    if (_selectedCategory == 'all') return _posts;
    return _posts.where((post) {
      if (_selectedCategory == 'food_tour') return post.isFoodTour;
      if (_selectedCategory == 'checkin') return post.isCheckin;
      if (_selectedCategory == 'school') return post.isSchool;
      if (_selectedCategory == 'media') return post.images.isNotEmpty;
      return true;
    }).toList();
  }

  @override
  void initState() {
    super.initState();
    _fetchNewsfeed();
  }

  Future<void> _fetchNewsfeed() async {
    setState(() => _isLoading = true);
    try {
      final feed = await ApiService.getNewsfeed();
      if (!mounted) return;

      final parsedPosts = feed.map((item) => PostModel.fromJson(Map<String, dynamic>.from(item))).toList();

      setState(() {
        _posts = parsedPosts;

        // Target post matching logic
        if (widget.targetPostId != null || (widget.targetTitle != null && widget.targetTitle!.isNotEmpty)) {
          final targetIdStr = widget.targetPostId?.toString();
          final targetTitleClean = widget.targetTitle?.toLowerCase().trim();

          int targetIdx = -1;
          for (int i = 0; i < _posts.length; i++) {
            final p = _posts[i];
            final pId = p.id;
            final pHash = p.hashId;
            final pTitle = p.title.toLowerCase().trim();

            if ((targetIdStr != null && (pId == targetIdStr || pHash == targetIdStr)) ||
                (targetTitleClean != null && targetTitleClean.isNotEmpty && pTitle.contains(targetTitleClean))) {
              targetIdx = i;
              break;
            }
          }

          if (targetIdx > 0) {
            final targetPost = _posts.removeAt(targetIdx);
            _posts.insert(0, targetPost);
          }

          if (_posts.isNotEmpty) {
            _expandedPosts.add(_posts.first.id);
          }
        }

        _isLoading = false;

        for (var post in _posts) {
          if (post.initialIsLiked) {
            _likedPosts.add(post.id);
          }
          _likesCounts[post.id] = post.initialLikesCount;
        }
      });

      if (widget.targetPostId != null || (widget.targetTitle != null && widget.targetTitle!.isNotEmpty)) {
        if (_posts.isNotEmpty) {
          WidgetsBinding.instance.addPostFrameCallback((_) {
            if (mounted) {
              _showCommentsBottomSheet(context, _posts.first);
            }
          });
        }
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _toggleLike(PostModel post) async {
    final postId = post.id;
    final currentLikes = _likesCounts[postId] ?? post.initialLikesCount;
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
      postId: post.numericId,
      type: post.type,
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

  void _showCreatePostModal() {
    if (!ApiService.isAuthenticated) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Vui lòng đăng nhập để chia sẻ bài viết lên Bản tin!'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }
    showCreatePostModal(context, onPostSuccess: _fetchNewsfeed);
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
                title: Text(
                  '${currentIndex + 1} / ${images.length}',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
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

  void _showCommentsBottomSheet(BuildContext context, PostModel post) {
    final postId = post.id;
    final commentsController = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) {
          final rawComments = (post.rawJson['comments'] is List) ? (post.rawJson['comments'] as List) : [];
          final comments = _postComments[postId] ?? rawComments.map((c) => Map<String, dynamic>.from(c as Map)).toList();

          return Container(
            height: MediaQuery.of(context).size.height * 0.72,
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
            ),
            child: Column(
              children: [
                Container(
                  margin: const EdgeInsets.only(top: 10, bottom: 6),
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2)),
                ),
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
                Expanded(
                  child: comments.isEmpty
                      ? const Center(
                          child: Text(
                            'Chưa có bình luận nào. Hãy là người đầu tiên!',
                            style: TextStyle(color: Colors.grey),
                          ),
                        )
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
                                        Text(
                                          c['author']!,
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                                        ),
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

  void _showShareBottomSheet(BuildContext context, PostModel post) {
    final title = post.title.isNotEmpty ? post.title : 'Bài viết trên Bản tin Đông Anh';
    const shareUrl = 'https://donganhdiscovery.xadonganh.com/ban-tin';

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
                decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2)),
              ),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const SizedBox(width: 36),
                const Expanded(
                  child: Text(
                    'Chia sẻ bài viết',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close_rounded, color: Color(0xFF64748B)),
                  onPressed: () => Navigator.pop(context),
                ),
              ],
            ),
            const SizedBox(height: 12),
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
                    _showRepostConfirmModal(context, post);
                  },
                ),
                _buildShareCustomItem(
                  label: 'Zalo',
                  customIcon: Container(
                    width: 62,
                    height: 62,
                    decoration: BoxDecoration(
                      color: const Color(0xFF0068FF),
                      borderRadius: BorderRadius.circular(22),
                    ),
                    child: const Center(
                      child: Text('Zalo', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 14)),
                    ),
                  ),
                  onTap: () {
                    Navigator.pop(context);
                    final zaloUrl = Uri.parse('https://sp.zalo.me/share_inline?link=${Uri.encodeComponent(shareUrl)}');
                    _showExternalAppShareConfirmModal(context, 'Zalo', post, zaloUrl, shareUrl);
                  },
                ),
                _buildShareCustomItem(
                  label: 'Facebook',
                  customIcon: Container(
                    width: 62,
                    height: 62,
                    decoration: BoxDecoration(
                      color: const Color(0xFF1877F2),
                      borderRadius: BorderRadius.circular(22),
                    ),
                    child: const Center(
                      child: Text('FB', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 16)),
                    ),
                  ),
                  onTap: () {
                    Navigator.pop(context);
                    final fbUrl = Uri.parse('https://www.facebook.com/sharer/sharer.php?u=${Uri.encodeComponent(shareUrl)}');
                    _showExternalAppShareConfirmModal(context, 'Facebook', post, fbUrl, shareUrl);
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
                  icon: Icons.share_rounded,
                  label: 'Ứng dụng\nkhác',
                  bgColor: const Color(0xFFFEF3C7),
                  iconColor: const Color(0xFFD97706),
                  onTap: () {
                    Navigator.pop(context);
                    Share.share('$title\n\n🔗 Xem bài viết tại Đông Anh Social:\n$shareUrl', subject: title);
                  },
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildShareCustomItem({required Widget customIcon, required String label, required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: SizedBox(
        width: 72,
        child: Column(
          children: [
            customIcon,
            const SizedBox(height: 8),
            Text(
              label,
              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF334155), height: 1.2),
              textAlign: TextAlign.center,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
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
              decoration: BoxDecoration(color: bgColor, borderRadius: BorderRadius.circular(22)),
              child: Center(child: Icon(icon, color: iconColor, size: 28)),
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF334155), height: 1.2),
              textAlign: TextAlign.center,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  void _showRepostConfirmModal(BuildContext context, PostModel post) {
    final title = post.title.isNotEmpty ? post.title : post.content;
    final author = post.author.name;
    final TextEditingController captionController = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalCtx) {
        return Padding(
          padding: EdgeInsets.only(bottom: MediaQuery.of(modalCtx).viewInsets.bottom),
          child: Container(
            padding: const EdgeInsets.all(20),
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
                    decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2)),
                  ),
                ),
                const SizedBox(height: 14),
                Row(
                  children: [
                    const Icon(Icons.repeat_rounded, color: Color(0xFF0EA5E9), size: 24),
                    const SizedBox(width: 8),
                    const Expanded(
                      child: Text(
                        'Chia sẻ bài viết lên Bảng Tin',
                        style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, color: Colors.grey),
                      onPressed: () => Navigator.pop(modalCtx),
                    ),
                  ],
                ),
                const Divider(height: 20),
                const Text(
                  'Lời nhắn của bạn (Tùy chọn):',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF475569)),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: captionController,
                  maxLines: 3,
                  decoration: InputDecoration(
                    hintText: 'Nhập suy nghĩ hoặc lời nhắn của bạn về bài viết này...',
                    hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFF0EA5E9))),
                    contentPadding: const EdgeInsets.all(12),
                  ),
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(Icons.format_quote_rounded, color: Color(0xFF0EA5E9), size: 18),
                          const SizedBox(width: 4),
                          Text(
                            'Bài viết gốc của $author',
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF0EA5E9)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        title,
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF1E293B)),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () => Navigator.pop(modalCtx),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: const Text('Hủy'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      flex: 2,
                      child: ElevatedButton.icon(
                        onPressed: () async {
                          final userCaption = captionController.text.trim();
                          final description = userCaption.isNotEmpty
                              ? '$userCaption\n\n🔄 [Chia sẻ bài viết từ $author]: $title'
                              : '🔄 [Chia sẻ bài viết từ $author]: $title';

                          Navigator.pop(modalCtx);
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('⏳ Đang chia sẻ bài viết lên Bảng tin...'),
                              duration: Duration(seconds: 1),
                            ),
                          );

                          final res = await ApiService.createPost(
                            description: description,
                            name: title,
                          );

                          if (context.mounted) {
                            if (res['success'] != false) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Row(
                                    children: [
                                      Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                                      SizedBox(width: 8),
                                      Text('🎉 Đã đăng chia sẻ bài viết thành công lên Bảng tin!'),
                                    ],
                                  ),
                                  backgroundColor: Color(0xFF059669),
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                              _fetchNewsfeed();
                            } else {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(res['message'] ?? 'Không thể đăng bài viết'),
                                  backgroundColor: Colors.red,
                                ),
                              );
                            }
                          }
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0EA5E9),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        icon: const Icon(Icons.send_rounded, size: 18),
                        label: const Text('Xác nhận Đăng bài', style: TextStyle(fontWeight: FontWeight.bold)),
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
  }

  void _showExternalAppShareConfirmModal(
    BuildContext context,
    String appName,
    PostModel post,
    Uri targetUri,
    String shareUrl,
  ) {
    final title = post.title.isNotEmpty ? post.title : post.content;
    final shareContent = '$title\n\n🔗 Xem chi tiết tại Đông Anh Social:\n$shareUrl';

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (modalCtx) {
        return Container(
          padding: const EdgeInsets.all(20),
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
                  decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2)),
                ),
              ),
              const SizedBox(height: 14),
              Row(
                children: [
                  CircleAvatar(
                    backgroundColor: appName == 'Zalo' ? const Color(0xFF0068FF) : const Color(0xFF1877F2),
                    radius: 16,
                    child: Text(
                      appName == 'Zalo' ? 'Zalo' : 'FB',
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 11),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Chia sẻ bài viết qua $appName',
                      style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Colors.grey),
                    onPressed: () => Navigator.pop(modalCtx),
                  ),
                ],
              ),
              const Divider(height: 20),
              const Text(
                'Nội dung chia sẻ sẽ được tạo sẵn:',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
              ),
              const SizedBox(height: 8),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Text(
                  shareContent,
                  style: const TextStyle(fontSize: 13, height: 1.4, color: Color(0xFF1E293B)),
                ),
              ),
              const SizedBox(height: 20),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => Navigator.pop(modalCtx),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      child: const Text('Hủy'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    flex: 2,
                    child: ElevatedButton.icon(
                      onPressed: () async {
                        Navigator.pop(modalCtx);
                        try {
                          if (await canLaunchUrl(targetUri)) {
                            await launchUrl(targetUri, mode: LaunchMode.externalApplication);
                            return;
                          }
                        } catch (_) {}
                        Share.share(shareContent, subject: title);
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: appName == 'Zalo' ? const Color(0xFF0068FF) : const Color(0xFF1877F2),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      icon: const Icon(Icons.open_in_new_rounded, size: 18),
                      label: Text('Mở $appName & Chia sẻ', style: const TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildPostComposer(dynamic user) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: SquircleHelper.decoration(
        radius: 20,
        color: Colors.white,
        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 12, offset: const Offset(0, 3)),
        ],
      ),
      child: Column(
        children: [
          GestureDetector(
            onTap: _showCreatePostModal,
            child: Row(
              children: [
                CircleAvatar(
                  radius: 21,
                  backgroundImage: ResizeImage(NetworkImage(ApiService.getAvatarUrl(user, user?['name'])), width: 90),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 11),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(100),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Text(
                      ApiService.isAuthenticated
                          ? '${user?['name'] ?? 'Bạn'} ơi, bạn đang nghĩ gì thế?'
                          : 'Đăng nhập để chia sẻ thông tin...',
                      style: const TextStyle(color: Color(0xFF64748B), fontSize: 13, fontWeight: FontWeight.w500),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildComposerShortcut(
                icon: Icons.photo_library_rounded,
                label: 'Ảnh & Video',
                color: const Color(0xFF10B981),
                onTap: _showCreatePostModal,
              ),
              _buildComposerShortcut(
                icon: Icons.sentiment_satisfied_alt_rounded,
                label: 'Cảm xúc',
                color: const Color(0xFFF59E0B),
                onTap: _showCreatePostModal,
              ),
              _buildComposerShortcut(
                icon: Icons.location_on_rounded,
                label: 'Check-in',
                color: const Color(0xFFEF4444),
                onTap: _showCreatePostModal,
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildComposerShortcut({
    required IconData icon,
    required String label,
    required Color color,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: color, size: 18),
            const SizedBox(width: 5),
            Text(
              label,
              style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.bold),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = ApiService.currentUser;
    final filtered = _filteredPosts;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: RefreshIndicator(
        onRefresh: _fetchNewsfeed,
        color: const Color(0xFF0EA5E9),
        child: _isLoading
            ? const CustomPulseLoader(
                message: 'Đang tải bản tin & dữ liệu khám phá Đông Anh...',
                primaryColor: Color(0xFF0EA5E9),
              )
            : ListView.builder(
                padding: const EdgeInsets.fromLTRB(14, 12, 14, 90),
                itemCount: filtered.isEmpty ? 9 : 8 + filtered.length,
                itemBuilder: (context, index) {
                  switch (index) {
                    case 0:
                      return StoryCarousel(
                        posts: _posts,
                        onCreateStory: _showCreatePostModal,
                        onStoryTap: (post) {
                          if (post.images.isNotEmpty) {
                            _openFullscreenGallery(context, post.images, 0);
                          }
                        },
                      );
                    case 1:
                      return const SizedBox(height: 14);
                    case 2:
                      return _buildPostComposer(user);
                    case 3:
                      return const SizedBox(height: 14);
                    case 4:
                      return CategoryFilterBar(
                        categories: _categories,
                        selectedCategory: _selectedCategory,
                        onCategorySelected: (catId) {
                          setState(() {
                            _selectedCategory = catId;
                          });
                        },
                      );
                    case 5:
                      return const SizedBox(height: 14);
                    case 6:
                      return Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              'BÀI VIẾT BẢN TIN',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w800,
                                color: Color(0xFF64748B),
                                letterSpacing: 0.8,
                              ),
                            ),
                            Text(
                              '${filtered.length} bài viết',
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: Color(0xFF94A3B8),
                              ),
                            ),
                          ],
                        ),
                      );
                    case 7:
                      return const SizedBox(height: 8);
                    default:
                      if (filtered.isEmpty) {
                        return Container(
                          padding: const EdgeInsets.all(36),
                          alignment: Alignment.center,
                          child: const Column(
                            children: [
                              Icon(Icons.article_outlined, size: 48, color: Color(0xFF94A3B8)),
                              SizedBox(height: 10),
                              Text(
                                'Chưa có bài viết nào trong mục này.',
                                style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.bold, fontSize: 13.5),
                              ),
                            ],
                          ),
                        );
                      }
                      final postIndex = index - 8;
                      final post = filtered[postIndex];
                      final postId = post.id;
                      final isLiked = _likedPosts.contains(postId);
                      final likesCount = _likesCounts[postId] ?? post.initialLikesCount;

                      final commentsList = _postComments[postId] ??
                          ((post.rawJson['comments'] is List) ? (post.rawJson['comments'] as List) : []);
                      final commentsCount = commentsList.length > post.commentsCount
                          ? commentsList.length
                          : post.commentsCount;

                      final isExpanded = _expandedPosts.contains(postId);

                      return PostCard(
                        key: ValueKey(postId),
                        post: post,
                        isLiked: isLiked,
                        likesCount: likesCount,
                        commentsCount: commentsCount,
                        isExpanded: isExpanded,
                        onLike: () => _toggleLike(post),
                        onComment: () => _showCommentsBottomSheet(context, post),
                        onShare: () => _showShareBottomSheet(context, post),
                        onToggleExpand: () {
                          setState(() {
                            if (isExpanded) {
                              _expandedPosts.remove(postId);
                            } else {
                              _expandedPosts.add(postId);
                            }
                          });
                        },
                        onOpenGallery: (images, initialIndex) {
                          _openFullscreenGallery(context, images, initialIndex);
                        },
                      );
                  }
                },
              ),
      ),
    );
  }
}
