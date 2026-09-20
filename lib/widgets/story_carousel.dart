import 'package:flutter/material.dart';
import '../models/post_model.dart';
import '../services/api_service.dart';

class StoryCarousel extends StatelessWidget {
  final List<PostModel> posts;
  final VoidCallback onCreateStory;
  final ValueChanged<PostModel> onStoryTap;
  final ValueChanged<PostModel>? onDeleteStory;

  const StoryCarousel({
    super.key,
    required this.posts,
    required this.onCreateStory,
    required this.onStoryTap,
    this.onDeleteStory,
  });

  @override
  Widget build(BuildContext context) {
    final storyPosts = posts.where((p) => p.type == 'story' || p.rawJson['is_story'] == true).take(6).toList();
    final currentUserId = ApiService.currentUser?['id']?.toString();

    return SizedBox(
      height: 136,
      child: ListView(
        scrollDirection: Axis.horizontal,
        physics: const BouncingScrollPhysics(),
        children: [
          // Create Story Tile
          GestureDetector(
            onTap: onCreateStory,
            child: Container(
              width: 92,
              margin: const EdgeInsets.only(right: 10),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(16),
                color: const Color(0xFF0F172A),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.1),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Stack(
                children: [
                  Positioned.fill(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(16),
                      child: Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [
                              const Color(0xFF0EA5E9).withValues(alpha: 0.4),
                              const Color(0xFF6366F1).withValues(alpha: 0.7),
                            ],
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                          ),
                        ),
                      ),
                    ),
                  ),
                  Positioned(
                    bottom: 10,
                    left: 6,
                    right: 6,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Container(
                          width: 32,
                          height: 32,
                          decoration: const BoxDecoration(
                            shape: BoxShape.circle,
                            color: Color(0xFF0EA5E9),
                          ),
                          child: const Icon(Icons.add, color: Colors.white, size: 20),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'Tạo tin mới',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                          textAlign: TextAlign.center,
                          maxLines: 1,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // User Stories Highlights
          ...storyPosts.map((post) {
            final authorName = post.author.name;
            final bgUrl = post.images.isNotEmpty ? post.images.first : null;
            final isMyStory = currentUserId != null && post.author.id.toString() == currentUserId;

            return GestureDetector(
              onTap: () => onStoryTap(post),
              onLongPress: isMyStory && onDeleteStory != null
                  ? () => _confirmDeleteStory(context, post)
                  : null,
              child: Container(
                width: 92,
                margin: const EdgeInsets.only(right: 10),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(16),
                  color: const Color(0xFF1E293B),
                  image: bgUrl != null
                      ? DecorationImage(
                          image: NetworkImage(bgUrl),
                          fit: BoxFit.cover,
                        )
                      : null,
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.12),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Stack(
                  children: [
                    Container(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(16),
                        gradient: LinearGradient(
                          colors: [
                            Colors.black.withValues(alpha: 0.1),
                            Colors.black.withValues(alpha: 0.75),
                          ],
                          begin: Alignment.topCenter,
                          end: Alignment.bottomCenter,
                        ),
                      ),
                    ),
                    Positioned(
                      top: 8,
                      left: 8,
                      child: Container(
                        padding: const EdgeInsets.all(1.5),
                        decoration: const BoxDecoration(
                          shape: BoxShape.circle,
                          gradient: LinearGradient(
                            colors: [Color(0xFF0EA5E9), Color(0xFFEC4899)],
                          ),
                        ),
                        child: CircleAvatar(
                          radius: 13,
                          backgroundImage: NetworkImage(
                            ApiService.getAvatarUrl(post.author.avatarUrl, authorName),
                          ),
                        ),
                      ),
                    ),
                    Positioned(
                      bottom: 8,
                      left: 6,
                      right: 6,
                      child: Text(
                        authorName,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 10.5,
                          fontWeight: FontWeight.bold,
                          shadows: [Shadow(color: Colors.black54, blurRadius: 4)],
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        textAlign: TextAlign.center,
                      ),
                    ),
                  ],
                ),
              ),
            );
          }),
        ],
      ),
    );
  }

  void _confirmDeleteStory(BuildContext context, PostModel post) {
    showDialog(
      context: context,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Xóa story?'),
        content: const Text(
          'Story này sẽ bị xóa hoàn toàn. Hành động này không thể hoàn tác.',
          style: TextStyle(fontSize: 14, color: Color(0xFF475569)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext),
            child: const Text('Hủy', style: TextStyle(color: Color(0xFF64748B))),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(dialogContext);
              onDeleteStory?.call(post);
            },
            child: const Text('Xóa', style: TextStyle(color: Color(0xFFDC2626), fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }
}
