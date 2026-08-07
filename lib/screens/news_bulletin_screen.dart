import 'package:flutter/material.dart';
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
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showCreatePostModal() {
    final user = ApiService.currentUser;
    if (!ApiService.isAuthenticated) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Vui lòng đăng nhập để chia sẻ bài viết lên Bản tin !'),
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
                        IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () => Navigator.pop(context),
                        ),
                      ],
                    ),
                    const Divider(height: 24),
                    TextField(
                      controller: _titleController,
                      decoration: InputDecoration(
                        hintText: 'Tiêu đề bài viết (Không bắt buộc)...',
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide.none,
                        ),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _postController,
                      maxLines: 5,
                      decoration: InputDecoration(
                        hintText: 'Chia sẻ thông tin, sự kiện, cập nhật mới lên Bản tin Đông Anh...',
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide.none,
                        ),
                        contentPadding: const EdgeInsets.all(14),
                      ),
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: _isPublishing
                          ? null
                          : () async {
                              final text = _postController.text.trim();
                              if (text.isEmpty) return;
                              setModalState(() => _isPublishing = true);
                              final res = await ApiService.createPost(
                                description: text,
                                name: _titleController.text.trim().isNotEmpty ? _titleController.text.trim() : null,
                              );
                              setModalState(() => _isPublishing = false);
                              if (res['success'] == true) {
                                _postController.clear();
                                _titleController.clear();
                                Navigator.pop(context);
                                _fetchNewsfeed();
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                    content: Text('🎉 Đã đăng bài viết mới thành công lên Bản tin!'),
                                    backgroundColor: Color(0xFF10B981),
                                  ),
                                );
                              }
                            },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0284C7),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      child: _isPublishing
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                            )
                          : const Text(
                              'ĐĂNG BÀI VIẾT NAY',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
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

  Widget _buildRoleBadge(String? role) {
    switch (role) {
      case 'principal':
        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
          decoration: BoxDecoration(
            color: const Color(0xFFFEF3C7),
            borderRadius: BorderRadius.circular(6),
          ),
          child: const Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.star_rounded, size: 12, color: Color(0xFFD97706)),
              SizedBox(width: 2),
              Text('Trường học', style: TextStyle(color: Color(0xFFD97706), fontSize: 10, fontWeight: FontWeight.bold)),
            ],
          ),
        );
      case 'admin':
        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
          decoration: BoxDecoration(
            color: const Color(0xFFF3E8FF),
            borderRadius: BorderRadius.circular(6),
          ),
          child: const Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.verified_rounded, size: 12, color: Color(0xFF9333EA)),
              SizedBox(width: 2),
              Text('Admin', style: TextStyle(color: Color(0xFF9333EA), fontSize: 10, fontWeight: FontWeight.bold)),
            ],
          ),
        );
      case 'seller':
        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
          decoration: BoxDecoration(
            color: const Color(0xFFE0F2FE),
            borderRadius: BorderRadius.circular(6),
          ),
          child: const Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.storefront_rounded, size: 12, color: Color(0xFF0284C7)),
              SizedBox(width: 2),
              Text('Gian hàng', style: TextStyle(color: Color(0xFF0284C7), fontSize: 10, fontWeight: FontWeight.bold)),
            ],
          ),
        );
      default:
        return const SizedBox.shrink();
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = ApiService.currentUser;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        title: const Row(
          children: [
            Icon(Icons.newspaper_rounded, color: Color(0xFF0284C7)),
            SizedBox(width: 8),
            Text(
              '📰 Bản tin ',
              style: TextStyle(color: Color(0xFF0F172A), fontSize: 16, fontWeight: FontWeight.bold),
            ),
          ],
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _fetchNewsfeed,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : ListView(
                padding: const EdgeInsets.all(14),
                children: [
                  // 1. Post Creation Input Bar (Tương thích chuẩn Web Bản tin)
                  GestureDetector(
                    onTap: _showCreatePostModal,
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: SquircleHelper.decoration(
                        radius: 18,
                        color: Colors.white,
                        borderSide: BorderSide(color: Colors.grey.shade200),
                        boxShadow: [
                          BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2)),
                        ],
                      ),
                      child: Row(
                        children: [
                          CircleAvatar(
                            radius: 18,
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
                                style: const TextStyle(color: Color(0xFF64748B), fontSize: 13),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          const Icon(Icons.photo_library_rounded, color: Color(0xFF10B981), size: 22),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 16),

                  // 2. Top Banner Announcement Card
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: SquircleHelper.decoration(
                      radius: 20,
                      color: const Color(0xFF0284C7),
                      boxShadow: [
                        BoxShadow(color: const Color(0xFF0284C7).withValues(alpha: 0.2), blurRadius: 10, offset: const Offset(0, 4)),
                      ],
                    ),
                    child: const Row(
                      children: [
                        CircleAvatar(backgroundColor: Colors.white24, radius: 22, child: Icon(Icons.campaign_rounded, color: Colors.white)),
                        SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('TIN TỨC BẢN TIN ĐÔNG ANH 2026', style: TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.bold)),
                              Text('Thông Báo Đa Phân Quyền Huyện', style: TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold)),
                              Text('Tổng hợp bài đăng từ Trường học, Gian hàng & Căn hộ', style: TextStyle(color: Colors.white70, fontSize: 11)),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),
                  const Text('BÀI VIẾT MỚI NHẤT BẢN TIN', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8)),
                  const SizedBox(height: 10),

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
                      final title = item['title'] ?? '';
                      final desc = item['description'] ?? '';
                      final authorName = item['author_name'] ?? 'Thành viên Đông Anh';
                      final role = item['author_role'] ?? 'user';
                      final timeStr = item['created_at_human'] ?? 'Vừa xong';
                      final imagePath = item['image_path'];
                      final likesCount = item['likes_count'] ?? 0;
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
                                      : 'https://donganhdiscovery.xadonganh.com/' + (firstImg.startsWith('/') ? firstImg.substring(1) : firstImg);

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
                                      : 'https://donganhdiscovery.xadonganh.com/' + (imagePath.toString().startsWith('/') ? imagePath.toString().substring(1) : imagePath.toString()),
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
                                    onPressed: () {},
                                    icon: const Icon(Icons.thumb_up_alt_outlined, size: 18, color: Color(0xFF64748B)),
                                    label: const Text('Thích', style: TextStyle(color: Color(0xFF64748B), fontSize: 13)),
                                  ),
                                ),
                                Expanded(
                                  child: TextButton.icon(
                                    onPressed: () {},
                                    icon: const Icon(Icons.chat_bubble_outline_rounded, size: 18, color: Color(0xFF64748B)),
                                    label: const Text('Bình luận', style: TextStyle(color: Color(0xFF64748B), fontSize: 13)),
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
