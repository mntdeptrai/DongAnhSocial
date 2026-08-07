import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/squircle_helper.dart';

class PrincipalDashboardScreen extends StatefulWidget {
  final VoidCallback? onBack;

  const PrincipalDashboardScreen({super.key, this.onBack});

  @override
  State<PrincipalDashboardScreen> createState() => _PrincipalDashboardScreenState();
}

class _PrincipalDashboardScreenState extends State<PrincipalDashboardScreen> {
  bool _isLoading = true;
  Map<String, dynamic>? _schoolData;
  List<dynamic> _posts = [];
  List<dynamic> _programs = [];
  Map<String, dynamic> _stats = {};

  @override
  void initState() {
    super.initState();
    _fetchDashboardData();
  }

  Future<void> _fetchDashboardData() async {
    setState(() => _isLoading = true);
    try {
      await ApiService.graphqlQuery('''
        query {
          adminStats {
            total_users
            total_eateries
          }
        }
      ''');
      // Thử gọi REST API hoặc mock data nếu endpoint chưa kết nối
      setState(() {
        _stats = {
          'total_posts': 12,
          'total_programs': 4,
          'total_likes': 348,
          'total_shares': 89,
        };
        _schoolData = {
          'name': 'Trường Tiểu Học An Dương Vương',
          'address': 'Xã An Dương Vương, Đông Anh, Hà Nội',
          'phone': '024 3883 xxxx',
          'level': 'Tiểu học',
          'components': ['Trường TH An Dương Vương cũ', 'Điểm trường Mầm Nông Phục Lộc']
        };
        _posts = [
          {
            'id': 1,
            'title': 'Lễ Khai Giảng Năm Học Mới & Đạt Chuẩn Quốc Gia Mức Độ 2',
            'date': '2026-09-05',
            'likes': 142,
            'status': 'Đã xuất bản'
          },
          {
            'id': 2,
            'title': 'Hội Thảo Chuyên Đề: Giáo Dục Số & Bản Đồ Thông Minh Đông Anh',
            'date': '2026-08-01',
            'likes': 98,
            'status': 'Đã xuất bản'
          }
        ];
        _programs = [
          {'id': 1, 'name': 'Chương Trình STEM & Robotics Học Đường', 'fee': 'Theo quy định'},
          {'id': 2, 'name': 'Bán Trú Chất Lượng Cao & ATTP Chuẩn', 'fee': 'Bình dân'},
        ];
        _isLoading = false;
      });
    } catch (_) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: Color(0xFF0F172A)),
          onPressed: widget.onBack ?? () => Navigator.pop(context),
        ),
        title: const Text(
          'Kênh Ban Giám Hiệu Trường Học',
          style: TextStyle(color: Color(0xFF0F172A), fontSize: 18, fontWeight: FontWeight.bold),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Color(0xFF0EA5E9)),
            onPressed: _fetchDashboardData,
          )
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // School Info Banner Card
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: SquircleHelper.decoration(
                      radius: 20,
                      color: const Color(0xFF0284C7),
                      boxShadow: [
                        BoxShadow(color: const Color(0xFF0284C7).withOpacity(0.25), blurRadius: 10, offset: const Offset(0, 4))
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const CircleAvatar(
                              backgroundColor: Colors.white24,
                              child: Icon(Icons.school_rounded, color: Colors.white),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    _schoolData?['name'] ?? 'Trường Học Đông Anh',
                                    style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                                  ),
                                  Text(
                                    _schoolData?['address'] ?? 'Đông Anh, Hà Nội',
                                    style: const TextStyle(color: Colors.white70, fontSize: 12),
                                  ),
                                ],
                              ),
                            )
                          ],
                        ),
                        const Divider(color: Colors.white24, height: 24),
                        const Text('CƠ SỞ SÁP NHẬP / ĐIỂM TRƯỜNG THÀNH PHẦN:', style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 6),
                        Wrap(
                          spacing: 8,
                          runSpacing: 6,
                          children: ((_schoolData?['components'] as List?) ?? []).map((comp) {
                            return Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(10)),
                              child: Text(comp.toString(), style: const TextStyle(color: Colors.white, fontSize: 11)),
                            );
                          }).toList(),
                        )
                      ],
                    ),
                  ),

                  const SizedBox(height: 20),

                  // Stats Overview
                  const Text('THỐNG KÊ TRUYỀN THÔNG NHÀ TRƯỜNG', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF64748B))),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Expanded(child: _buildStatCard('Bài Viết', '${_stats['total_posts'] ?? 0}', Icons.article_rounded, const Color(0xFF0EA5E9))),
                      const SizedBox(width: 10),
                      Expanded(child: _buildStatCard('Chương Trình', '${_stats['total_programs'] ?? 0}', Icons.workspace_premium_rounded, const Color(0xFF10B981))),
                      const SizedBox(width: 10),
                      Expanded(child: _buildStatCard('Lượt Yêu Thích', '${_stats['total_likes'] ?? 0}', Icons.favorite_rounded, const Color(0xFFF43F5E))),
                    ],
                  ),

                  const SizedBox(height: 24),

                  // School Posts Management
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('TIN BÀI TRUYỀN THÔNG TRƯỜNG HỌC', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF64748B))),
                      ElevatedButton.icon(
                        onPressed: () {
                          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Chức năng đăng bài tin tức trường học')));
                        },
                        icon: const Icon(Icons.add_rounded, size: 16),
                        label: const Text('Đăng Bài', style: TextStyle(fontSize: 12)),
                        style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF0284C7), foregroundColor: Colors.white),
                      )
                    ],
                  ),
                  const SizedBox(height: 10),

                  ..._posts.map((post) {
                    return Container(
                      margin: const EdgeInsets.only(bottom: 10),
                      padding: const EdgeInsets.all(12),
                      decoration: SquircleHelper.decoration(radius: 14, color: Colors.white, borderSide: BorderSide(color: Colors.grey.shade200)),
                      child: ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: const CircleAvatar(backgroundColor: Color(0xFFE0F2FE), child: Icon(Icons.newspaper_rounded, color: Color(0xFF0284C7))),
                        title: Text(post['title'].toString(), style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                        subtitle: Text('Ngày đăng: ${post['date']} • ${post['likes']} Lượt thích', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                        trailing: IconButton(
                          icon: const Icon(Icons.delete_outline_rounded, color: Colors.redAccent),
                          onPressed: () {
                            setState(() {
                              _posts.removeWhere((p) => p['id'] == post['id']);
                            });
                          },
                        ),
                      ),
                    );
                  }),

                  const SizedBox(height: 20),
                  const Text('CHƯƠNG TRÌNH GIÁO DỤC NỔI BẬT', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF64748B))),
                  const SizedBox(height: 10),

                  ..._programs.map((program) {
                    return Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.all(12),
                      decoration: SquircleHelper.decoration(radius: 14, color: Colors.white, borderSide: BorderSide(color: Colors.grey.shade200)),
                      child: ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: const CircleAvatar(backgroundColor: Color(0xFFDCFCE7), child: Icon(Icons.workspace_premium_rounded, color: Color(0xFF10B981))),
                        title: Text(program['name'].toString(), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                        subtitle: Text('Học phí: ${program['fee']}', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                      ),
                    );
                  }),
                ],
              ),
            ),
    );
  }

  Widget _buildStatCard(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: SquircleHelper.decoration(radius: 16, color: color.withValues(alpha: 0.08), borderSide: BorderSide(color: color.withValues(alpha: 0.3))),
      child: Column(
        children: [
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 6),
          Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color)),
          Text(label, style: TextStyle(fontSize: 10, color: Colors.grey.shade700), textAlign: TextAlign.center),
        ],
      ),
    );
  }
}
