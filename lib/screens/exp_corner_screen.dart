import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/squircle_helper.dart';

class ExpCornerScreen extends StatefulWidget {
  const ExpCornerScreen({super.key});

  @override
  State<ExpCornerScreen> createState() => _ExpCornerScreenState();
}

class _ExpCornerScreenState extends State<ExpCornerScreen> {
  Map<String, dynamic> _data = {};
  List<dynamic> _activities = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchExpCorner();
  }

  Future<void> _fetchExpCorner() async {
    setState(() => _isLoading = true);
    try {
      final res = await ApiService.getExpCorner();
      if (mounted) {
        setState(() {
          _data = res;
          _activities = res['activities'] ?? [];
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Widget _buildStatCard(String value, String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
      decoration: SquircleHelper.decoration(
        radius: 14,
        color: color.withValues(alpha: 0.08),
        borderSide: BorderSide(color: color.withValues(alpha: 0.2), width: 1),
      ),
      child: Column(
        children: [
          Text(
            value,
            style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16, color: color),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 10, color: Color(0xFF64748B), fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final stats = _data['stats'] ?? {};

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        title: const Row(
          children: [
            Icon(Icons.rowing_rounded, color: Color(0xFF059669)),
            SizedBox(width: 8),
            Text(
              '🎪 Góc Trải Nghiệm Thực Tế',
              style: TextStyle(color: Color(0xFF0F172A), fontSize: 16, fontWeight: FontWeight.bold),
            ),
          ],
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _fetchExpCorner,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator(color: Color(0xFF059669)))
            : ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // 1. Hero Header Banner (Cinematic Style)
                  Container(
                    padding: const EdgeInsets.all(18),
                    decoration: SquircleHelper.decoration(
                      radius: 22,
                      color: const Color(0xFF059669),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF059669).withValues(alpha: 0.25),
                          blurRadius: 12,
                          offset: const Offset(0, 5),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Text(
                            '🍃 HÀNH TRÌNH VĂN HÓA & LÀNG NGHỀ',
                            style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 1),
                          ),
                        ),
                        const SizedBox(height: 10),
                        const Text(
                          'Góc Trải Nghiệm Thực Tế\nLàng Nghề & Vui Chơi Bản Địa',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w900, height: 1.3),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          _data['subtitle'] ??
                              'Không chỉ là ăn uống, đây là hành trình nhập vai thực tế! Tự tay học nghề làm bún, đan lát, gốm sứ & tham gia trò chơi dân gian Cổ Loa.',
                          textAlign: TextAlign.center,
                          style: const TextStyle(color: Colors.white70, fontSize: 12, height: 1.4),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // 2. Stats Row Grid (12+ Làng nghề, 500+ Khách, 4.9⭐, 100%)
                  Row(
                    children: [
                      Expanded(child: _buildStatCard(stats['villages'] ?? '12+', 'LÀNG NGHỀ', const Color(0xFF059669))),
                      const SizedBox(width: 8),
                      Expanded(child: _buildStatCard(stats['visitors'] ?? '500+', 'KHÁCH HÀNG', const Color(0xFF0284C7))),
                      const SizedBox(width: 8),
                      Expanded(child: _buildStatCard(stats['rating'] ?? '4.9 ⭐', 'ĐÁNH GIÁ', const Color(0xFFD97706))),
                      const SizedBox(width: 8),
                      Expanded(child: _buildStatCard(stats['experience'] ?? '100%', 'THỰC TẾ', const Color(0xFF8B5CF6))),
                    ],
                  ),

                  const SizedBox(height: 22),

                  const Text(
                    '🗺️ CÁC LỘ TRÌNH & HOẠT ĐỘNG TRẢI NGHIỆM THỰC TẾ',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8),
                  ),
                  const SizedBox(height: 12),

                  // 3. Activities List
                  ..._activities.map((act) {
                    final name = act['name'] ?? '';
                    final desc = act['description'] ?? '';
                    final location = act['location'] ?? 'Khu Di Tích Cổ Loa';
                    final price = act['price'] ?? 'Giá niêm yết';
                    final tag = act['tag'] ?? 'Trải nghiệm';
                    final imgPath = act['image_path'];

                    return Container(
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: SquircleHelper.decoration(
                        radius: 20,
                        color: Colors.white,
                        borderSide: BorderSide(color: Colors.grey.shade200),
                        boxShadow: [
                          BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 10, offset: const Offset(0, 3)),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Cover Image with Tag Badges
                          Stack(
                            children: [
                              ClipRRect(
                                borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
                                child: Image.network(
                                  imgPath != null && imgPath.toString().isNotEmpty
                                      ? (imgPath.toString().startsWith('http')
                                          ? imgPath.toString()
                                          : 'https://donganhdiscovery.xadonganh.com/' + (imgPath.toString().startsWith('/') ? imgPath.toString().substring(1) : imgPath.toString()))
                                      : 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=400&q=60',
                                  width: double.infinity,
                                  height: 180,
                                  fit: BoxFit.cover,
                                  cacheWidth: 400,
                                  filterQuality: FilterQuality.low,
                                  errorBuilder: (_, __, ___) => Container(
                                    height: 180,
                                    color: const Color(0xFFE2E8F0),
                                    child: const Center(child: Icon(Icons.image_not_supported_rounded, color: Colors.grey)),
                                  ),
                                ),
                              ),
                              Positioned(
                                top: 12,
                                left: 12,
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF0284C7),
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(Icons.place_rounded, color: Colors.white, size: 12),
                                      const SizedBox(width: 4),
                                      Text(location, style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                                    ],
                                  ),
                                ),
                              ),
                              Positioned(
                                top: 12,
                                right: 12,
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF059669),
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: Text(tag, style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                                ),
                              ),
                            ],
                          ),

                          // Activity Content
                          Padding(
                            padding: const EdgeInsets.all(14),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  name,
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A), height: 1.3),
                                ),
                                const SizedBox(height: 6),
                                Text(
                                  desc,
                                  maxLines: 3,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B), height: 1.4),
                                ),
                                const SizedBox(height: 12),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Row(
                                      children: [
                                        const Icon(Icons.sell_rounded, color: Color(0xFFD97706), size: 16),
                                        const SizedBox(width: 4),
                                        Text(price, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 14, color: Color(0xFFD97706))),
                                      ],
                                    ),
                                    ElevatedButton.icon(
                                      onPressed: () {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          SnackBar(
                                            content: Text('🎉 Đã chọn trải nghiệm: $name'),
                                            backgroundColor: const Color(0xFF059669),
                                          ),
                                        );
                                      },
                                      icon: const Icon(Icons.explore_rounded, size: 16),
                                      label: const Text('Tham gia ngay', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: const Color(0xFF059669),
                                        foregroundColor: Colors.white,
                                        elevation: 0,
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
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
