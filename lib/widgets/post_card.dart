import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../models/post_model.dart';
import '../services/api_service.dart';
import '../services/moderation_service.dart';
import 'optimized_image.dart';
import 'squircle_helper.dart';

class PostCard extends StatefulWidget {
  final PostModel post;
  final bool isLiked;
  final int likesCount;
  final int commentsCount;
  final bool isExpanded;
  final VoidCallback onLike;
  final VoidCallback onComment;
  final VoidCallback onShare;
  final VoidCallback onToggleExpand;
  final Function(List<String> images, int initialIndex) onOpenGallery;
  final VoidCallback? onHidePost;
  final VoidCallback? onBlockAuthor;

  const PostCard({
    super.key,
    required this.post,
    required this.isLiked,
    required this.likesCount,
    required this.commentsCount,
    required this.isExpanded,
    required this.onLike,
    required this.onComment,
    required this.onShare,
    required this.onToggleExpand,
    required this.onOpenGallery,
    this.onHidePost,
    this.onBlockAuthor,
  });

  @override
  State<PostCard> createState() => _PostCardState();
}

class _PostCardState extends State<PostCard> {
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

    return RichText(text: TextSpan(children: spans));
  }

  Widget _buildMultiImageGrid(List<String> images) {
    if (images.isEmpty) return const SizedBox.shrink();

    void openGallery(int initialIndex) {
      widget.onOpenGallery(images, initialIndex);
    }

    if (images.length == 1) {
      return GestureDetector(
        onTap: () => openGallery(0),
        child: ClipRRect(
          child: OptimizedNetworkImage(
            imageUrl: images[0],
            width: double.infinity,
            height: 250,
            fit: BoxFit.cover,
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
                child: OptimizedNetworkImage(imageUrl: images[0], height: 200, fit: BoxFit.cover),
              ),
            ),
            const SizedBox(width: 2),
            Expanded(
              child: GestureDetector(
                onTap: () => openGallery(1),
                child: OptimizedNetworkImage(imageUrl: images[1], height: 200, fit: BoxFit.cover),
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
                child: OptimizedNetworkImage(imageUrl: images[0], height: 240, fit: BoxFit.cover),
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
                      child: OptimizedNetworkImage(imageUrl: images[1], width: double.infinity, fit: BoxFit.cover),
                    ),
                  ),
                  const SizedBox(height: 2),
                  Expanded(
                    child: GestureDetector(
                      onTap: () => openGallery(2),
                      child: OptimizedNetworkImage(imageUrl: images[2], width: double.infinity, fit: BoxFit.cover),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    // 4 or more images: 2x2 grid with +N overlay on 4th image
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
                    child: OptimizedNetworkImage(imageUrl: images[0], height: double.infinity, width: double.infinity, fit: BoxFit.cover),
                  ),
                ),
                const SizedBox(width: 2),
                Expanded(
                  child: GestureDetector(
                    onTap: () => openGallery(1),
                    child: OptimizedNetworkImage(imageUrl: images[1], height: double.infinity, width: double.infinity, fit: BoxFit.cover),
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
                    child: OptimizedNetworkImage(imageUrl: images[2], height: double.infinity, width: double.infinity, fit: BoxFit.cover),
                  ),
                ),
                const SizedBox(width: 2),
                Expanded(
                  child: GestureDetector(
                    onTap: () => openGallery(3),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        OptimizedNetworkImage(imageUrl: images[3], height: double.infinity, width: double.infinity, fit: BoxFit.cover),
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

  @override
  Widget build(BuildContext context) {
    final post = widget.post;
    final authorName = post.author.name;
    final desc = post.content.trim();
    final isLongText = desc.length > 160;
    final displayText = (!widget.isExpanded && isLongText) ? '${desc.substring(0, 160)}...' : desc;

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: SquircleHelper.decoration(
        radius: 18,
        color: Colors.white,
        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
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
                  backgroundImage: ResizeImage(
                    NetworkImage(ApiService.getAvatarUrl(post.author.avatarUrl, authorName)),
                    width: 100,
                  ),
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
                          if (post.author.isAdmin) ...[
                            const SizedBox(width: 4),
                            const Icon(Icons.star_rounded, color: Color(0xFFEF4444), size: 16),
                          ] else if (post.author.isVerified) ...[
                            const SizedBox(width: 4),
                            const Icon(Icons.star_rounded, color: Color(0xFFF59E0B), size: 16),
                          ],
                        ],
                      ),
                      const SizedBox(height: 2),
                      Row(
                        children: [
                          const Text(
                            'Công khai',
                            style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                          ),
                          if (post.personalTag != null && post.personalTag!.isNotEmpty) ...[
                            const SizedBox(width: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                              decoration: BoxDecoration(
                                color: const Color(0xFF0EA5E9).withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(
                                  color: const Color(0xFF0EA5E9).withValues(alpha: 0.3),
                                  width: 0.8,
                                ),
                              ),
                              child: Text(
                                post.personalTag!,
                                style: const TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w600,
                                  color: Color(0xFF0284C7),
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.more_horiz, color: Color(0xFF64748B)),
                  onPressed: () => _showPostActionsSheet(context),
                ),
              ],
            ),
          ),

          // Title
          if (post.title.isNotEmpty && post.title != post.content)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 2),
              child: Text(
                post.title,
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A)),
              ),
            ),

          // Description
          if (desc.isNotEmpty)
            Padding(
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
                      onTap: widget.onToggleExpand,
                      behavior: HitTestBehavior.opaque,
                      child: Padding(
                        padding: const EdgeInsets.only(top: 4, bottom: 2),
                        child: Text(
                          widget.isExpanded ? 'Thu gọn' : '... Xem thêm',
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
            ),

          const SizedBox(height: 8),

          // Image Grid
          if (post.images.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(top: 8.0),
              child: _buildMultiImageGrid(post.images),
            ),

          // Likes & Comments Count Header
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.favorite_rounded, color: Colors.redAccent, size: 18),
                    const SizedBox(width: 4),
                    Text(
                      '${widget.likesCount} thích',
                      style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
                Text(
                  '${widget.commentsCount} bình luận',
                  style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                ),
              ],
            ),
          ),

          const Divider(height: 1, color: Color(0xFFF1F5F9)),

          // Reaction Action Buttons
          Row(
            children: [
              Expanded(
                child: TextButton.icon(
                  onPressed: widget.onLike,
                  icon: Icon(
                    widget.isLiked ? Icons.thumb_up_alt_rounded : Icons.thumb_up_alt_outlined,
                    size: 18,
                    color: widget.isLiked ? const Color(0xFF0EA5E9) : const Color(0xFF64748B),
                  ),
                  label: Text(
                    'Thích',
                    style: TextStyle(
                      color: widget.isLiked ? const Color(0xFF0EA5E9) : const Color(0xFF64748B),
                      fontWeight: widget.isLiked ? FontWeight.bold : FontWeight.normal,
                      fontSize: 13,
                    ),
                  ),
                ),
              ),
              Expanded(
                child: TextButton.icon(
                  onPressed: widget.onComment,
                  icon: const Icon(Icons.chat_bubble_outline_rounded, size: 18, color: Color(0xFF64748B)),
                  label: const Text('Bình luận', style: TextStyle(color: Color(0xFF64748B), fontSize: 13)),
                ),
              ),
              Expanded(
                child: TextButton.icon(
                  onPressed: widget.onShare,
                  icon: const Icon(Icons.share_outlined, size: 18, color: Color(0xFF64748B)),
                  label: const Text('Chia sẻ', style: TextStyle(color: Color(0xFF64748B), fontSize: 13)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _showPostActionsSheet(BuildContext context) {
    final post = widget.post;
    final authorName = post.author.name;
    final authorId = post.author.id.toString();

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (sheetContext) => Container(
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
              decoration: BoxDecoration(
                color: const Color(0xFFE2E8F0),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(height: 16),
            ListTile(
              leading: const Icon(Icons.share_outlined, color: Color(0xFF0F172A)),
              title: const Text('Chia sẻ bài viết', style: TextStyle(fontWeight: FontWeight.w600)),
              onTap: () {
                Navigator.pop(sheetContext);
                widget.onShare();
              },
            ),
            ListTile(
              leading: const Icon(Icons.visibility_off_outlined, color: Color(0xFF475569)),
              title: const Text('Ẩn bài viết này', style: TextStyle(fontWeight: FontWeight.w600)),
              subtitle: const Text('Không hiển thị bài viết này trên bảng tin của bạn', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
              onTap: () async {
                Navigator.pop(sheetContext);
                await ModerationService.hidePost(post.id);
                widget.onHidePost?.call();
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Đã ẩn bài viết khỏi bảng tin.')),
                  );
                }
              },
            ),
            ListTile(
              leading: const Icon(Icons.block_rounded, color: Color(0xFFDC2626)),
              title: Text('Chặn người dùng $authorName', style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFFDC2626))),
              subtitle: const Text('Ẩn tất cả bài viết và bình luận của người này', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
              onTap: () {
                Navigator.pop(sheetContext);
                _confirmBlockAuthor(context, authorId, authorName);
              },
            ),
            ListTile(
              leading: const Icon(Icons.flag_outlined, color: Color(0xFFDC2626)),
              title: const Text('Báo cáo nội dung vi phạm', style: TextStyle(fontWeight: FontWeight.w600, color: Color(0xFFDC2626))),
              subtitle: const Text('Báo cáo vi phạm tiêu chuẩn cộng đồng (Xử lý trong 24h)', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
              onTap: () {
                Navigator.pop(sheetContext);
                _showReportModal(context);
              },
            ),
          ],
        ),
      ),
    );
  }

  void _confirmBlockAuthor(BuildContext context, String authorId, String authorName) {
    showDialog(
      context: context,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text('Chặn $authorName?'),
        content: const Text(
          'Bạn sẽ không nhìn thấy bất kỳ bài viết hay bình luận nào từ người này nữa. Toàn bộ nội dung của họ sẽ được ẩn ngay lập tức.',
          style: TextStyle(fontSize: 14, color: Color(0xFF475569)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext),
            child: const Text('Hủy'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFDC2626),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () async {
              Navigator.pop(dialogContext);
              await ModerationService.blockUser(authorId);
              widget.onBlockAuthor?.call();
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('Đã chặn $authorName. Nội dung đã được ẩn.')),
                );
              }
            },
            child: const Text('Chặn'),
          ),
        ],
      ),
    );
  }

  void _showReportModal(BuildContext context) {
    String selectedReason = 'Spam hoặc thông tin sai lệch';
    final detailsController = TextEditingController();
    final reasons = [
      'Spam hoặc thông tin sai lệch',
      'Nội dung khiêu dâm, đồi trụy',
      'Bạo lực, đe dọa hoặc quấy rối',
      'Nội dung chống phá hoặc thù địch',
      'Vi phạm bản quyền sở hữu trí tuệ',
      'Lý do khác',
    ];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalCtx) => StatefulBuilder(
        builder: (context, setModalState) => Container(
          padding: EdgeInsets.only(
            top: 16,
            left: 20,
            right: 20,
            bottom: MediaQuery.of(context).viewInsets.bottom + 24,
          ),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
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
                    color: const Color(0xFFE2E8F0),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEE2E2),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.flag_rounded, color: Color(0xFFDC2626), size: 22),
                  ),
                  const SizedBox(width: 10),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Báo cáo nội dung vi phạm',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A)),
                        ),
                        Text(
                          'Cam kết xem xét & xử lý trong 24 giờ',
                          style: TextStyle(fontSize: 12, color: Color(0xFF16A34A), fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded, color: Color(0xFF64748B)),
                    onPressed: () => Navigator.pop(modalCtx),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              const Text('Chọn lý do báo cáo:', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Color(0xFF334155))),
              const SizedBox(height: 8),
              ...reasons.map((r) {
                final isSelected = r == selectedReason;
                return InkWell(
                  onTap: () => setModalState(() => selectedReason = r),
                  borderRadius: BorderRadius.circular(8),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
                    child: Row(
                      children: [
                        Icon(
                          isSelected ? Icons.radio_button_checked_rounded : Icons.radio_button_unchecked_rounded,
                          color: isSelected ? const Color(0xFFDC2626) : const Color(0xFF94A3B8),
                          size: 20,
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            r,
                            style: TextStyle(
                              fontSize: 13.5,
                              fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                              color: isSelected ? const Color(0xFF0F172A) : const Color(0xFF475569),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }),
              const SizedBox(height: 8),
              TextField(
                controller: detailsController,
                maxLines: 2,
                decoration: InputDecoration(
                  hintText: 'Mô tả chi tiết vi phạm (không bắt buộc)...',
                  hintStyle: const TextStyle(fontSize: 12.5, color: Color(0xFF94A3B8)),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
                  focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFF0EA5E9))),
                  contentPadding: const EdgeInsets.all(12),
                ),
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFDC2626),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () async {
                    Navigator.pop(modalCtx);
                    final post = widget.post;
                    await ModerationService.reportContent(
                      contentId: post.id,
                      contentType: 'post',
                      reason: selectedReason,
                      details: detailsController.text.trim(),
                      title: post.title.isNotEmpty ? post.title : 'Bài viết trên Bảng tin',
                      authorName: post.author.name,
                      authorId: post.author.id.toString(),
                      snippet: post.content.isNotEmpty ? post.content : post.title,
                      imageUrl: post.images.isNotEmpty ? post.images.first : null,
                    );
                    await ModerationService.hidePost(post.id);
                    widget.onHidePost?.call();
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Báo cáo đã được ghi nhận. Nội dung đã được ẩn và sẽ được xử lý trong 24 giờ.'),
                          duration: Duration(seconds: 3),
                        ),
                      );
                    }
                  },
                  child: const Text('Gửi báo cáo vi phạm', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
