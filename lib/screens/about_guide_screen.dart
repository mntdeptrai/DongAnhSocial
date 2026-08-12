import 'package:flutter/material.dart';

class AboutGuideScreen extends StatelessWidget {
  const AboutGuideScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Color(0xFF1E293B), size: 20),
          onPressed: () => Navigator.of(context).maybePop(),
        ),
        title: const Text(
          'ℹ️ Giới Thiệu & Hướng Dẫn Sử Dụng',
          style: TextStyle(color: Color(0xFF0F172A), fontSize: 16, fontWeight: FontWeight.bold),
        ),
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Hero Card
            Container(
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: const Color(0xFF38BDF8).withValues(alpha: 0.4), width: 1.5),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F172A).withValues(alpha: 0.25),
                    blurRadius: 16,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFF0284C7).withValues(alpha: 0.25),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: const Color(0xFF38BDF8).withValues(alpha: 0.4)),
                          ),
                          child: const Row(
                            children: [
                              Icon(Icons.verified_rounded, color: Color(0xFF38BDF8), size: 14),
                              SizedBox(width: 4),
                              Text('ĐÔNG ANH DISCOVERY 2026', style: TextStyle(color: Color(0xFF38BDF8), fontSize: 11, fontWeight: FontWeight.w800, letterSpacing: 0.8)),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    const Text(
                      'Hệ Sinh Thái Số Hóa Đông Anh',
                      style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Nền tảng số hóa sinh thái kết nối Ẩm thực bản địa, Di sản Cổ Loa, Chợ Truyền thống OCOP, Bản đồ Giáo dục Smart & Chăm sóc Sức khỏe toàn diện tại Đông Anh, Hà Nội.',
                      style: TextStyle(color: Colors.white70, fontSize: 13, height: 1.4),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 24),

            const Text(
              'CẨM NANG HƯỚNG DẪN CÁC TÍNH NĂNG',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF475569), letterSpacing: 0.8),
            ),
            const SizedBox(height: 12),

            _buildGuideCard(
              icon: Icons.map_rounded,
              title: '1. Bản Đồ & Tìm Kiếm Thông Minh',
              subtitle: 'Tra cứu hơn 1.000+ địa điểm ẩm thực, di sản, trường học và gian hàng OCOP đã qua kiểm định.',
              color: const Color(0xFF0284C7),
            ),
            _buildGuideCard(
              icon: Icons.directions_bike_rounded,
              title: '2. Food Tour AI Nối Đuôi Stream',
              subtitle: 'Nhập ngân sách & tâm trạng để Trợ lý Gemini AI lập lộ trình du lịch ẩm thực tối ưu theo thời gian thực.',
              color: const Color(0xFF059669),
            ),
            _buildGuideCard(
              icon: Icons.photo_camera_rounded,
              title: '3. Góc Check-in Khoảnh Khắc & Short Reviews',
              subtitle: 'Chụp ảnh lưu giữ kỷ niệm thực tế và xem video review trải nghiệm sinh động từ cộng đồng.',
              color: const Color(0xFFEA580C),
            ),
            _buildGuideCard(
              icon: Icons.shopping_bag_rounded,
              title: '4. Chợ Số & Nông Sản OCOP',
              subtitle: 'Mua sắm đặc sản Đông Anh online trực tiếp từ tiểu thương với thanh toán mã VietQR tiện lợi.',
              color: const Color(0xFF10B981),
            ),
            _buildGuideCard(
              icon: Icons.admin_panel_settings_rounded,
              title: '5. Phân Quyền & Điều Hành Trực Tiếp',
              subtitle: 'Chuyển đổi linh hoạt giữa Tiểu thương, Ban Giám hiệu, BQL Chợ & Admin để quản lý ngay trên app.',
              color: const Color(0xFF7C3AED),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildGuideCard({required IconData icon, required String title, required String subtitle, required Color color}) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF64748B).withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: color.withValues(alpha: 0.25)),
            ),
            child: Icon(icon, color: color, size: 22),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A))),
                const SizedBox(height: 4),
                Text(subtitle, style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), height: 1.35)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
