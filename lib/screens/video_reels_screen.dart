import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/squircle_helper.dart';

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
          _videos = res;
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
                        ],
                      ),
                    ),
                  ],
                );
              },
            ),
    );
  }
}
