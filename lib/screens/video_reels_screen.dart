import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/moderation_service.dart';

class VideoReelsScreen extends StatefulWidget {
  const VideoReelsScreen({super.key});

  @override
  State<VideoReelsScreen> createState() => _VideoReelsScreenState();
}

class _VideoReelsScreenState extends State<VideoReelsScreen> {
  List<dynamic> _videos = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchVideos();
  }

  Future<void> _fetchVideos() async {
    setState(() => _isLoading = true);
    try {
      final res = await ApiService.getVideos();
      if (mounted) {
        setState(() {
          _videos = res.where((v) => !ModerationService.isPostHidden('video_${v['id']}')).toList();
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _handleLike(int id, int index) async {
    final res = await ApiService.likeVideo(id);
    if (res['success'] == true && mounted) {
      setState(() {
        _videos[index]['likes_count'] = res['likes_count'];
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.white),
        title: const Text('Góc Trải Nghiệm Thực Tế (Video Reels)', style: TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.bold)),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF38BDF8)))
          : PageView.builder(
              scrollDirection: Axis.vertical,
              itemCount: _videos.isEmpty ? 1 : _videos.length,
              itemBuilder: (context, index) {
                if (_videos.isEmpty) {
                  return const Center(child: Text('Chưa có video trải nghiệm thực tế nào', style: TextStyle(color: Colors.white70)));
                }

                final item = _videos[index];
                return Stack(
                  children: [
                    // Video Background Placeholder / Thumbnail
                    Container(
                      width: double.infinity,
                      height: double.infinity,
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Colors.black, Colors.grey.shade900],
                          begin: Alignment.topCenter,
                          end: Alignment.bottomCenter,
                        ),
                      ),
                      child: Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.play_circle_fill_rounded, color: Color(0xFF38BDF8), size: 72),
                            const SizedBox(height: 12),
                            Text(item['title'] ?? 'Review Ẩm Thực Đông Anh', style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                          ],
                        ),
                      ),
                    ),

                    // Overlay Info & Action Buttons
                    Positioned(
                      bottom: 40,
                      left: 16,
                      right: 70,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(color: const Color(0xFF0284C7), borderRadius: BorderRadius.circular(10)),
                            child: Text(item['eatery_name'] ?? 'Địa điểm', style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                          ),
                          const SizedBox(height: 8),
                          Text(item['title'] ?? '', style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 4),
                          Text(item['description'] ?? 'Góc quay thực tế chân thực từ reviewer bản địa', style: const TextStyle(color: Colors.white70, fontSize: 12)),
                        ],
                      ),
                    ),

                    // Right Side Floating Actions (Like, Comment, Share)
                    Positioned(
                      bottom: 40,
                      right: 16,
                      child: Column(
                        children: [
                          GestureDetector(
                            onTap: () => _handleLike(item['id'], index),
                            child: CircleAvatar(
                              radius: 24,
                              backgroundColor: Colors.white24,
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const Icon(Icons.favorite_rounded, color: Colors.redAccent, size: 20),
                                  Text('${item['likes_count'] ?? 0}', style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold)),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(height: 16),
                          const CircleAvatar(
                            radius: 24,
                            backgroundColor: Colors.white24,
                            child: Icon(Icons.chat_bubble_rounded, color: Colors.white, size: 20),
                          ),
                          const SizedBox(height: 16),
                          const CircleAvatar(
                            radius: 24,
                            backgroundColor: Colors.white24,
                            child: Icon(Icons.share_rounded, color: Colors.white, size: 20),
                          ),
                          const SizedBox(height: 16),
                          GestureDetector(
                            onTap: () => _showVideoModerationSheet(context, item, index),
                            child: const CircleAvatar(
                              radius: 24,
                              backgroundColor: Colors.white24,
                              child: Icon(Icons.more_horiz_rounded, color: Colors.white, size: 20),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                );
              },
            ),
    );
  }

  void _showVideoModerationSheet(BuildContext context, Map<String, dynamic> item, int index) {
    final videoId = (item['id'] ?? '').toString();

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
              leading: const Icon(Icons.visibility_off_outlined, color: Color(0xFF475569)),
              title: const Text('Ẩn video này', style: TextStyle(fontWeight: FontWeight.w600)),
              onTap: () async {
                Navigator.pop(ctx);
                await ModerationService.hidePost('video_$videoId');
                setState(() {
                  _videos.removeAt(index);
                });
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Đã ẩn video khỏi danh sách.')),
                  );
                }
              },
            ),
            ListTile(
              leading: const Icon(Icons.flag_outlined, color: Color(0xFFDC2626)),
              title: const Text('Báo cáo video vi phạm', style: TextStyle(fontWeight: FontWeight.w600, color: Color(0xFFDC2626))),
              subtitle: const Text('Báo cáo nội dung xấu độc, phản cảm (Xử lý trong 24h)', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
              onTap: () async {
                Navigator.pop(ctx);
                await ModerationService.reportContent(
                  contentId: videoId,
                  contentType: 'video',
                  reason: 'Video vi phạm tiêu chuẩn cộng đồng',
                );
                await ModerationService.hidePost('video_$videoId');
                setState(() {
                  _videos.removeAt(index);
                });
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Báo cáo đã được ghi nhận. Video đã được ẩn và sẽ được xử lý trong 24 giờ.')),
                  );
                }
              },
            ),
          ],
        ),
      ),
    );
  }
}
