import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../models/post_model.dart';
import '../services/api_service.dart';
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
                      const Text(
                        'Công khai',
                        style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.more_horiz, color: Color(0xFF64748B)),
                  onPressed: widget.onShare,
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
}
