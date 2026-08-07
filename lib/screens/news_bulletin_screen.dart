import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
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
    final title = item['title'] ?? 'Bài viết trên Bản tin Đông Anh';
    const shareUrl = 'https://donganhdiscovery.xadonganh.com/ban-tin';
    Clipboard.setData(ClipboardData(text: '$title\nXem thêm tại: $shareUrl'));

    if (!mounted) return;
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
        duration: const Duration(seconds: 2),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
                top: 20,
                left: 20,
                right: 20,
              ),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 20,
                          backgroundImage: ResizeImage(NetworkImage(ApiService.getAvatarUrl(user, user?['name'])), width: 100),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                user?['name'] ?? 'Thành viên Đông Anh',
                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                              ),
                              const Text(
                                'Công khai • Đăng bài lên Bản tin',
                                style: TextStyle(color: Color(0xFF64748B), fontSize: 12),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _titleController,
                      decoration: InputDecoration(
                        hintText: 'Tiêu đề bài viết (tùy chọn)',
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide(color: Colors.grey.shade300),
                        ),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _postController,
                      maxLines: 4,
                      decoration: InputDecoration(
                        hintText: 'Nội dung chia sẻ...',
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide(color: Colors.grey.shade300),
                        ),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0EA5E9),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
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
                          ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                          : const Text('Đăng Bài', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
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

  Widget _buildRoleBadge(String role) {
    Color bg;
    String label;

    switch (role) {
      case 'admin':
        bg = const Color(0xFFEF4444);
        label = 'Admin';
        break;
      case 'principal':
        bg = const Color(0xFFF59E0B);
        label = 'Trường học';
        break;
      case 'seller':
        bg = const Color(0xFF10B981);
        label = 'Gian hàng';
        break;
      case 'manager':
        bg = const Color(0xFF8B5CF6);
        label = 'BQL Chợ';
        break;
      default:
        bg = const Color(0xFF0EA5E9);
        label = 'Thành viên';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: bg.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: bg.withValues(alpha: 0.4)),
      ),
      child: Text(
        label,
        style: TextStyle(color: bg, fontSize: 10, fontWeight: FontWeight.bold),
      ),
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

                  // 2. Banner Announcement
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: SquircleHelper.decoration(
                      radius: 18,
                      gradient: const LinearGradient(
                        colors: [Color(0xFF0284C7), Color(0xFF0EA5E9)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      boxShadow: [
                        BoxShadow(color: const Color(0xFF0EA5E9).withValues(alpha: 0.25), blurRadius: 12, offset: const Offset(0, 4)),
                      ],
                    ),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.campaign_rounded, color: Colors.white, size: 24),
                        ),
                        const SizedBox(width: 12),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'TIN TỨC BẢN TIN ĐÔNG ANH 2026',
                                style: TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 0.8),
                              ),
                              SizedBox(height: 2),
                              Text(
                                'Thông Báo Đa Phân Quyền Huyện',
                                style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 15),
                              ),
                              SizedBox(height: 2),
                              Text(
                                'Tổng hợp bài đăng từ Trường học, Gian hàng & Cán bộ',
                                style: TextStyle(color: Colors.white70, fontSize: 11),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

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
                      final imagePath = item['image_path'];

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
                                            const SizedBox(width: 6),
                                            _buildRoleBadge(role),
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

                            // Post Content Description
                            if (desc.toString().isNotEmpty)
                              Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                child: Text(
                                  desc,
                                  style: const TextStyle(fontSize: 13.5, color: Color(0xFF334155), height: 1.4),
                                ),
                              ),

                            const SizedBox(height: 8),

                            // Post Image / Gallery (if any)
                            if (item['images'] is List && (item['images'] as List).isNotEmpty) ...[
                              Builder(
                                builder: (context) {
                                  final imgList = item['images'] as List;
                                  final firstImg = imgList[0].toString();
                                  final fullUrl = firstImg.startsWith('http')
                                      ? firstImg
                                      : 'https://donganhdiscovery.xadonganh.com/${firstImg.startsWith('/') ? firstImg.substring(1) : firstImg}';

                                  return Stack(
                                    children: [
                                      ClipRRect(
                                        child: Image.network(
                                          fullUrl,
                                          width: double.infinity,
                                          height: 230,
                                          fit: BoxFit.cover,
                                          cacheWidth: 400,
                                          filterQuality: FilterQuality.low,
                                          errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                                        ),
                                      ),
                                      if (imgList.length > 1)
                                        Positioned(
                                          right: 12,
                                          bottom: 12,
                                          child: Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                            decoration: BoxDecoration(
                                              color: Colors.black.withValues(alpha: 0.7),
                                              borderRadius: BorderRadius.circular(12),
                                            ),
                                            child: Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                const Icon(Icons.collections_rounded, color: Colors.white, size: 14),
                                                const SizedBox(width: 4),
                                                Text(
                                                  '+${imgList.length - 1} ảnh',
                                                  style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ),
                                    ],
                                  );
                                },
                              ),
                            ] else if (imagePath != null && imagePath.toString().isNotEmpty) ...[
                              ClipRRect(
                                child: Image.network(
                                  imagePath.toString().startsWith('http')
                                      ? imagePath.toString()
                                      : 'https://donganhdiscovery.xadonganh.com/${imagePath.toString().startsWith('/') ? imagePath.toString().substring(1) : imagePath.toString()}',
                                  width: double.infinity,
                                  height: 230,
                                  fit: BoxFit.cover,
                                  cacheWidth: 400,
                                  filterQuality: FilterQuality.low,
                                  errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                                ),
                              ),
                            ],

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
