import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';

import 'package:video_player/video_player.dart';

import '../models/post_model.dart';
import '../services/api_service.dart';
import '../services/moderation_service.dart';
import '../widgets/category_filter_bar.dart';
import '../widgets/create_post_modal.dart';
import '../widgets/custom_loader.dart';
import '../widgets/post_card.dart';
import '../widgets/story_carousel.dart';
import '../core/youtube_helper.dart';
import 'create_story_screen.dart';

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
    return _posts.where((post) {
      if (ModerationService.isUserBlocked(post.author.id.toString())) return false;
      if (ModerationService.isPostHidden(post.id)) return false;
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
    ModerationService.init().then((_) {
      if (mounted) setState(() {});
    });
    _fetchNewsfeed();
  }

  Future<void> _fetchNewsfeed() async {
    setState(() {
      _isLoading = true;
    });
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

  Future<void> _showCreateStoryScreen() async {
    if (!ApiService.isAuthenticated) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Vui lòng đăng nhập để tạo tin!'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }
    final result = await Navigator.push<bool>(
      context,
      MaterialPageRoute(builder: (_) => const CreateStoryScreen()),
    );
    if (result == true) {
      _fetchNewsfeed();
    }
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
                  return _GalleryMediaItem(mediaUrl: images[index]);
                },
              ),
            );
          },
        );
      },
    );
  }


  void _showCommentOptions(BuildContext context, Map<String, dynamic> comment, VoidCallback onRefresh) {
    final author = (comment['author'] ?? 'Người dùng').toString();
    final authorId = (comment['author_id'] ?? comment['user_id'] ?? '').toString();

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        padding: const EdgeInsets.only(top: 12, bottom: 28, left: 16, right: 16),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 36,
              height: 4,
              decoration: BoxDecoration(color: const Color(0xFFE2E8F0), borderRadius: BorderRadius.circular(2)),
            ),
            const SizedBox(height: 16),
            ListTile(
              leading: const Icon(Icons.flag_outlined, color: Color(0xFFDC2626)),
              title: const Text('Báo cáo bình luận vi phạm', style: TextStyle(fontWeight: FontWeight.w600, color: Color(0xFFDC2626))),
              subtitle: const Text('Báo cáo nội dung xấu độc, quấy rối (Xử lý trong 24h)', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
              onTap: () async {
                Navigator.pop(ctx);
                await ModerationService.reportContent(
                  contentId: (comment['id'] ?? DateTime.now().millisecondsSinceEpoch).toString(),
                  contentType: 'comment',
                  reason: 'Bình luận vi phạm tiêu chuẩn cộng đồng',
                  title: 'Bình luận của $author',
                  authorName: author,
                  authorId: authorId,
                  snippet: (comment['text'] ?? '').toString(),
                );
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Báo cáo bình luận đã được ghi nhận và sẽ được xử lý trong 24 giờ.')),
                  );
                }
              },
            ),
            if (authorId.isNotEmpty)
              ListTile(
                leading: const Icon(Icons.block_rounded, color: Color(0xFFDC2626)),
                title: Text('Chặn $author', style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFFDC2626))),
                subtitle: const Text('Ẩn tất cả bài viết và bình luận của người này', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                onTap: () async {
                  Navigator.pop(ctx);
                  await ModerationService.blockUser(authorId);
                  onRefresh();
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Đã chặn $author. Toàn bộ nội dung đã được ẩn.')),
                    );
                  }
                },
              ),
          ],
        ),
      ),
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
          final allComments = _postComments[postId] ?? rawComments.map((c) => Map<String, dynamic>.from(c as Map)).toList();
          final comments = allComments.where((c) {
            final aId = (c['author_id'] ?? c['user_id'] ?? '').toString();
            return aId.isEmpty || !ModerationService.isUserBlocked(aId);
          }).toList();

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
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text(
                                              c['author'] ?? 'Người dùng',
                                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                                            ),
                                            GestureDetector(
                                              onTap: () => _showCommentOptions(context, c, () {
                                                setModalState(() {});
                                                setState(() {});
                                              }),
                                              child: const Icon(Icons.more_horiz, size: 16, color: Color(0xFF94A3B8)),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 2),
                                        Text(c['text'] ?? '', style: const TextStyle(fontSize: 13, color: Color(0xFF334155))),
                                        const SizedBox(height: 4),
                                        Text(c['time'] ?? '', style: const TextStyle(fontSize: 10, color: Color(0xFF94A3B8))),
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
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Colors.black.withValues(alpha: 0.05)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 19,
            backgroundImage: ResizeImage(
              NetworkImage(ApiService.getAvatarUrl(user, user?['name'])),
              width: 90,
            ),
            backgroundColor: const Color(0xFFE2E8F0),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: GestureDetector(
              onTap: _showCreatePostModal,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Text(
                  ApiService.isAuthenticated
                      ? '${user?['name'] ?? 'Bạn'} ơi, bạn đang nghĩ gì thế?'
                      : 'Đăng nhập để chia sẻ thông tin...',
                  style: const TextStyle(
                    color: Color(0xFF64748B),
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ),
          ),
          const SizedBox(width: 8),
          GestureDetector(
            onTap: _showCreatePostModal,
            child: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: const Color(0xFF10B981).withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.photo_library_rounded,
                color: Color(0xFF10B981),
                size: 20,
              ),
            ),
          ),
        ],
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
                padding: const EdgeInsets.fromLTRB(14, 10, 14, 90),
                itemCount: filtered.isEmpty ? 7 : 6 + filtered.length,
                itemBuilder: (context, index) {
                  switch (index) {
                    case 0:
                      return StoryCarousel(
                        posts: _posts,
                        onCreateStory: _showCreateStoryScreen,
                        onStoryTap: (post) {
                          showDialog(
                            context: context,
                            useSafeArea: false,
                            barrierDismissible: true,
                            builder: (_) => _StoryViewerDialog(post: post),
                          );
                        },
                        onDeleteStory: (post) async {
                          final ok = await ApiService.deleteStory(post.numericId ?? post.id);
                          if (ok) {
                            setState(() {
                              _posts.removeWhere((p) => p.id == post.id);
                            });
                            if (context.mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('🗑️ Đã xóa story thành công!')),
                              );
                            }
                          } else if (context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Không thể xóa story. Vui lòng thử lại.')),
                            );
                          }
                        },
                      );
                    case 1:
                      return const SizedBox(height: 10);
                    case 2:
                      return _buildPostComposer(user);
                    case 3:
                      return const SizedBox(height: 10);
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
                      return const SizedBox(height: 10);
                    default:
                      if (filtered.isEmpty) {
                        return Container(
                          padding: const EdgeInsets.all(36),
                          alignment: Alignment.center,
                          child: const Column(
                            children: [
                              Icon(
                                Icons.article_outlined,
                                size: 48,
                                color: Color(0xFF94A3B8),
                              ),
                              SizedBox(height: 10),
                              Text(
                                'Chưa có bài viết nào trong mục này.',
                                textAlign: TextAlign.center,
                                style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.bold, fontSize: 13.5),
                              ),
                            ],
                          ),
                        );
                      }
                      final postIndex = index - 6;
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
                        onHidePost: () => setState(() {}),
                        onBlockAuthor: () => setState(() {}),
                        onDeletePost: () async {
                          final ok = await ApiService.deletePost(post.numericId ?? post.id);
                          if (ok) {
                            setState(() {
                              _posts.removeWhere((p) => p.id == post.id);
                            });
                            if (context.mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('🗑️ Đã xóa bài viết thành công!')),
                              );
                            }
                          } else if (context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Không thể xóa bài viết. Vui lòng thử lại.')),
                            );
                          }
                        },
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

class _GalleryMediaItem extends StatefulWidget {
  final String mediaUrl;
  const _GalleryMediaItem({required this.mediaUrl});

  @override
  State<_GalleryMediaItem> createState() => _GalleryMediaItemState();
}

class _GalleryMediaItemState extends State<_GalleryMediaItem> {
  VideoPlayerController? _controller;
  bool _isVideo = false;
  bool _isYouTube = false;
  bool _isInitialized = false;
  bool _isPlaying = true;
  bool _isMuted = false;
  bool _isCoverFit = true;

  @override
  void initState() {
    super.initState();
    _checkAndInit();
  }

  bool _isPathVideo(String path) {
    if (YouTubeHelper.isYouTubeUrl(path)) return true;
    final lower = path.toLowerCase();
    return lower.endsWith('.mp4') ||
        lower.endsWith('.mov') ||
        lower.endsWith('.m4v') ||
        lower.endsWith('.webm') ||
        lower.endsWith('.avi') ||
        lower.contains('.mp4?') ||
        lower.contains('.mov?');
  }

  void _checkAndInit() async {
    final url = widget.mediaUrl.trim();
    if (YouTubeHelper.isYouTubeUrl(url)) {
      _isYouTube = true;
      _isVideo = true;
      _isInitialized = true;
      if (mounted) setState(() {});
      return;
    }
    if (_isPathVideo(url)) {
      _isVideo = true;
      try {
        final fullUrl = url.startsWith('http')
            ? url
            : 'https://donganhdiscovery.xadonganh.com/${url.startsWith('/') ? url.substring(1) : url}';
        final controller = VideoPlayerController.networkUrl(Uri.parse(fullUrl));
        await controller.initialize();
        await controller.setLooping(true);
        await controller.setVolume(1.0);
        await controller.play();
        if (mounted) {
          setState(() {
            _controller = controller;
            _isInitialized = true;
            _isPlaying = true;
            _isMuted = false;
          });
        }
      } catch (e) {
        debugPrint('[GalleryVideo] Init error: $e');
        if (mounted) setState(() => _isInitialized = true);
      }
    }
  }

  @override
  void dispose() {
    _controller?.pause();
    _controller?.dispose();
    super.dispose();
  }

  void _togglePlayPause() {
    if (_controller == null || !_isInitialized) return;
    setState(() {
      if (_controller!.value.isPlaying) {
        _controller!.pause();
        _isPlaying = false;
      } else {
        _controller!.play();
        _isPlaying = true;
      }
    });
  }

  void _toggleMute() {
    if (_controller == null || !_isInitialized) return;
    setState(() {
      _isMuted = !_isMuted;
      _controller!.setVolume(_isMuted ? 0.0 : 1.0);
    });
  }

  @override
  Widget build(BuildContext context) {
    final url = widget.mediaUrl;
    final fullUrl = url.startsWith('http')
        ? url
        : 'https://donganhdiscovery.xadonganh.com/${url.startsWith('/') ? url.substring(1) : url}';

    if (_isYouTube) {
      final thumbUrl = YouTubeHelper.getThumbnailUrl(url, highRes: true);
      return Stack(
        fit: StackFit.expand,
        alignment: Alignment.center,
        children: [
          Image.network(
            thumbUrl,
            fit: BoxFit.contain,
            errorBuilder: (_, __, ___) => const Center(
              child: Icon(Icons.broken_image_rounded, color: Colors.white54, size: 64),
            ),
          ),
          Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  Colors.black.withValues(alpha: 0.3),
                  Colors.transparent,
                  Colors.black.withValues(alpha: 0.6),
                ],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
            ),
          ),
          Center(
            child: GestureDetector(
              onTap: () => YouTubeHelper.launchYouTube(url),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 14),
                decoration: BoxDecoration(
                  color: const Color(0xFFFF0000),
                  borderRadius: BorderRadius.circular(30),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFFFF0000).withValues(alpha: 0.5),
                      blurRadius: 18,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.play_arrow_rounded, color: Colors.white, size: 30),
                    SizedBox(width: 8),
                    Text(
                      'Phát trên YouTube',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      );
    }

    if (_isVideo) {
      if (!_isInitialized) {
        return const Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircularProgressIndicator(color: Color(0xFF0EA5E9), strokeWidth: 3),
              SizedBox(height: 12),
              Text('Đang tải video & âm thanh...', style: TextStyle(color: Colors.white70, fontSize: 13)),
            ],
          ),
        );
      }

      if (_controller != null && _controller!.value.isInitialized) {
        final val = _controller!.value;
        final isRotated = val.rotationCorrection == 90 || val.rotationCorrection == 270;
        final double vidW = isRotated ? val.size.height : val.size.width;
        final double vidH = isRotated ? val.size.width : val.size.height;

        return GestureDetector(
          onTap: _togglePlayPause,
          onDoubleTap: () => setState(() => _isCoverFit = !_isCoverFit),
          behavior: HitTestBehavior.opaque,
          child: Stack(
            fit: StackFit.expand,
            alignment: Alignment.center,
            children: [
              SizedBox.expand(
                child: FittedBox(
                  fit: _isCoverFit ? BoxFit.cover : BoxFit.contain,
                  clipBehavior: Clip.hardEdge,
                  child: SizedBox(
                    width: vidW > 0 ? vidW : 1,
                    height: vidH > 0 ? vidH : 1,
                    child: VideoPlayer(_controller!),
                  ),
                ),
              ),
              if (!_isPlaying)
                Center(
                  child: Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.black.withValues(alpha: 0.65),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.play_arrow_rounded, color: Colors.white, size: 48),
                  ),
                ),
              Positioned(
                bottom: 24,
                left: 16,
                right: 16,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.6),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    children: [
                      IconButton(
                        icon: Icon(_isPlaying ? Icons.pause_rounded : Icons.play_arrow_rounded, color: Colors.white, size: 24),
                        onPressed: _togglePlayPause,
                      ),
                      Expanded(
                        child: VideoProgressIndicator(
                          _controller!,
                          allowScrubbing: true,
                          colors: const VideoProgressColors(
                            playedColor: Color(0xFF0EA5E9),
                            bufferedColor: Colors.white38,
                            backgroundColor: Colors.white12,
                          ),
                        ),
                      ),
                      IconButton(
                        icon: Icon(_isMuted ? Icons.volume_off_rounded : Icons.volume_up_rounded, color: Colors.white, size: 22),
                        onPressed: _toggleMute,
                      ),
                      IconButton(
                        icon: Icon(
                          _isCoverFit ? Icons.fullscreen_exit_rounded : Icons.fullscreen_rounded,
                          color: Colors.white,
                          size: 24,
                        ),
                        tooltip: _isCoverFit ? 'Thu nhỏ vừa khung' : 'Toàn màn hình tràn viền',
                        onPressed: () => setState(() => _isCoverFit = !_isCoverFit),
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

    return InteractiveViewer(
      minScale: 0.8,
      maxScale: 4.0,
      child: Center(
        child: Image.network(
          fullUrl,
          fit: BoxFit.contain,
          errorBuilder: (_, __, ___) => const Icon(Icons.broken_image, color: Colors.white54, size: 64),
        ),
      ),
    );
  }
}

class _StoryViewerDialog extends StatefulWidget {
  final PostModel post;
  const _StoryViewerDialog({required this.post});


  @override
  State<_StoryViewerDialog> createState() => _StoryViewerDialogState();
}

class _StoryViewerDialogState extends State<_StoryViewerDialog> with SingleTickerProviderStateMixin {
  VideoPlayerController? _videoController;
  AnimationController? _progressController;
  bool _isVideo = false;
  bool _isYouTube = false;
  bool _isInitialized = false;
  bool _isMuted = false;

  @override
  void initState() {
    super.initState();
    _initMedia();
  }

  void _initMedia() async {
    final post = widget.post;
    final mediaUrl = post.images.isNotEmpty
        ? post.images.first
        : (post.rawJson['image_path'] ?? post.rawJson['media_url'] ?? '').toString();
    final rawType = (post.rawJson['story_type'] ?? post.rawJson['type'] ?? post.type).toString().toLowerCase();

    _isYouTube = YouTubeHelper.isYouTubeUrl(mediaUrl);

    if (_isYouTube) {
      _isVideo = true;
      _progressController = AnimationController(vsync: this, duration: const Duration(seconds: 10))
        ..addStatusListener((status) {
          if (status == AnimationStatus.completed && mounted) {
            Navigator.of(context).pop();
          }
        })
        ..forward();
      if (mounted) setState(() => _isInitialized = true);
      return;
    }

    final isVideoExt = mediaUrl.endsWith('.mp4') ||
        mediaUrl.endsWith('.mov') ||
        mediaUrl.endsWith('.webm') ||
        mediaUrl.contains('.mp4?') ||
        mediaUrl.contains('.mov?');

    _isVideo = rawType == 'video' || isVideoExt;

    if (_isVideo && mediaUrl.isNotEmpty) {
      try {
        final fullUrl = mediaUrl.startsWith('http')
            ? mediaUrl
            : 'https://donganhdiscovery.xadonganh.com/${mediaUrl.startsWith('/') ? mediaUrl.substring(1) : mediaUrl}';
        final uri = Uri.parse(fullUrl);
        final controller = VideoPlayerController.networkUrl(uri);
        await controller.initialize();
        await controller.setLooping(false);
        await controller.setVolume(1.0);
        await controller.play();

        controller.addListener(() {
          if (mounted &&
              controller.value.isInitialized &&
              controller.value.duration > Duration.zero &&
              controller.value.position >= controller.value.duration) {
            Navigator.of(context).pop();
          }
        });

        if (mounted) {
          setState(() {
            _videoController = controller;
            _isInitialized = true;
          });
        }
      } catch (e) {
        debugPrint('[StoryViewer] Video error: $e');
        if (mounted) setState(() => _isInitialized = true);
      }
    } else {
      _progressController = AnimationController(vsync: this, duration: const Duration(seconds: 5))
        ..addStatusListener((status) {
          if (status == AnimationStatus.completed && mounted) {
            Navigator.of(context).pop();
          }
        })
        ..forward();
      if (mounted) setState(() => _isInitialized = true);
    }
  }

  @override
  void dispose() {
    _videoController?.pause();
    _videoController?.dispose();
    _progressController?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final post = widget.post;
    final authorName = post.author.name;
    final authorAvatar = post.author.avatarUrl;
    final caption = post.title.isNotEmpty ? post.title : post.content;
    final mediaUrl = post.images.isNotEmpty
        ? post.images.first
        : (post.rawJson['image_path'] ?? post.rawJson['media_url'] ?? '').toString();

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        fit: StackFit.expand,
        children: [
          // 1. Background Content
          if (!_isInitialized)
            const Center(
              child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
            )
          else if (_isYouTube)
            GestureDetector(
              onTap: () {
                _progressController?.stop();
                YouTubeHelper.launchYouTube(mediaUrl);
              },
              child: Stack(
                fit: StackFit.expand,
                children: [
                  Image.network(
                    YouTubeHelper.getThumbnailUrl(mediaUrl, highRes: true),
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => const Center(
                      child: Icon(Icons.broken_image_rounded, color: Colors.white54, size: 64),
                    ),
                  ),
                  Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          Colors.black.withValues(alpha: 0.5),
                          Colors.black.withValues(alpha: 0.2),
                          Colors.black.withValues(alpha: 0.8),
                        ],
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                      ),
                    ),
                  ),
                  Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          padding: const EdgeInsets.all(18),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFF0000),
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFFFF0000).withValues(alpha: 0.5),
                                blurRadius: 24,
                                offset: const Offset(0, 8),
                              ),
                            ],
                          ),
                          child: const Icon(Icons.play_arrow_rounded, color: Colors.white, size: 48),
                        ),
                        const SizedBox(height: 16),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.7),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: Colors.white24, width: 1),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.smart_display_rounded, color: Color(0xFFFF0000), size: 18),
                              SizedBox(width: 6),
                              Text(
                                'Xem Video trên YouTube',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                  letterSpacing: 0.3,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            )
          else if (_isVideo && _videoController != null && _videoController!.value.isInitialized)
            GestureDetector(
              onTap: () {
                setState(() {
                  if (_videoController!.value.isPlaying) {
                    _videoController!.pause();
                  } else {
                    _videoController!.play();
                  }
                });
              },
              child: SizedBox.expand(
                child: FittedBox(
                  fit: BoxFit.cover,
                  clipBehavior: Clip.hardEdge,
                  child: Builder(
                    builder: (context) {
                      final val = _videoController!.value;
                      final isRotated = val.rotationCorrection == 90 || val.rotationCorrection == 270;
                      final double vidW = isRotated ? val.size.height : val.size.width;
                      final double vidH = isRotated ? val.size.width : val.size.height;

                      return SizedBox(
                        width: vidW > 0 ? vidW : 1,
                        height: vidH > 0 ? vidH : 1,
                        child: VideoPlayer(_videoController!),
                      );
                    },
                  ),
                ),
              ),
            )
          else if (!_isVideo && mediaUrl.isNotEmpty)
            SizedBox.expand(
              child: Image.network(
                mediaUrl.startsWith('http')
                    ? mediaUrl
                    : 'https://donganhdiscovery.xadonganh.com/${mediaUrl.startsWith('/') ? mediaUrl.substring(1) : mediaUrl}',
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => const Center(
                  child: Icon(Icons.broken_image_rounded, color: Colors.white54, size: 64),
                ),
              ),
            )
          else
            Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF0EA5E9), Color(0xFF6366F1)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
              ),
              child: Center(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Text(
                    caption,
                    textAlign: TextAlign.center,
                    style: const TextStyle(color: Colors.white, fontSize: 26, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ),

          // 2. Top Progress Bar & Header
          Positioned(
            top: MediaQuery.of(context).padding.top + 8,
            left: 12,
            right: 12,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(2),
                  child: Container(
                    height: 3,
                    color: Colors.white24,
                    child: _isVideo && _videoController != null && _videoController!.value.isInitialized
                        ? ValueListenableBuilder<VideoPlayerValue>(
                            valueListenable: _videoController!,
                            builder: (context, value, _) {
                              final progress = value.duration.inMilliseconds > 0
                                  ? value.position.inMilliseconds / value.duration.inMilliseconds
                                  : 0.0;
                              return LinearProgressIndicator(
                                value: progress.clamp(0.0, 1.0),
                                backgroundColor: Colors.transparent,
                                valueColor: const AlwaysStoppedAnimation(Colors.white),
                              );
                            },
                          )
                        : (_progressController != null
                            ? AnimatedBuilder(
                                animation: _progressController!,
                                builder: (context, _) => LinearProgressIndicator(
                                  value: _progressController!.value,
                                  backgroundColor: Colors.transparent,
                                  valueColor: const AlwaysStoppedAnimation(Colors.white),
                                ),
                              )
                            : const SizedBox.shrink()),
                  ),
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    CircleAvatar(
                      radius: 18,
                      backgroundColor: Colors.white24,
                      backgroundImage: (authorAvatar != null && authorAvatar.isNotEmpty)
                          ? NetworkImage(authorAvatar.startsWith('http')
                              ? authorAvatar
                              : 'https://donganhdiscovery.xadonganh.com/${authorAvatar.startsWith('/') ? authorAvatar.substring(1) : authorAvatar}')
                          : null,
                      child: (authorAvatar == null || authorAvatar.isEmpty)
                          ? const Icon(Icons.person, color: Colors.white, size: 20)
                          : null,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            authorName,
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13.5),
                          ),
                          Text(
                            post.createdAt ?? 'Tin 24h',
                            style: const TextStyle(color: Colors.white70, fontSize: 11),
                          ),
                        ],
                      ),
                    ),
                    if (_isVideo && _videoController != null)
                      IconButton(
                        icon: Icon(_isMuted ? Icons.volume_off_rounded : Icons.volume_up_rounded, color: Colors.white),
                        onPressed: () {
                          setState(() {
                            _isMuted = !_isMuted;
                            _videoController!.setVolume(_isMuted ? 0.0 : 1.0);
                          });
                        },
                      ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, color: Colors.white, size: 26),
                      onPressed: () => Navigator.of(context).pop(),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // 3. Caption overlay at bottom
          if (caption.isNotEmpty && (mediaUrl.isNotEmpty || _isVideo))
            Positioned(
              bottom: MediaQuery.of(context).padding.bottom + 20,
              left: 16,
              right: 16,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.black.withValues(alpha: 0.65),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  caption,
                  style: const TextStyle(color: Colors.white, fontSize: 14),
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ),
        ],
      ),
    );
  }
}
