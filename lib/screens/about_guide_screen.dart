import 'package:flutter/material.dart';
import '../widgets/squircle_helper.dart';

class AboutGuideScreen extends StatelessWidget {
  const AboutGuideScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        title: const Text('ℹ️ Giới Thiệu & Hướng Dẫn Sử Dụng', style: TextStyle(color: Color(0xFF0F172A), fontSize: 17, fontWeight: FontWeight.bold)),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Hero Card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: SquircleHelper.decoration(
              radius: 24,
              color: const Color(0xFF0F172A),
              borderSide: const BorderSide(color: Color(0xFF38BDF8), width: 1.5),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                ShaderMask(
                  shaderCallback: (bounds) => const LinearGradient(
                    colors: [Color(0xFF0284C7), Color(0xFF06B6D4), Color(0xFF10B981)],
                  ).createShader(bounds),
                  child: const Text('ĐÔNG ANH DISCOVERY 2026', style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w900)),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Nền tảng số hóa sinh thái kết nối Ẩm thực, Di sản Cổ Loa, Chợ OCOP, Bản đồ Giáo dục Smart & Chăm sóc Sức khỏe toàn diện tại Đông Anh, Hà Nội.',
                  style: TextStyle(color: Colors.white70, fontSize: 13, height: 1.4),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),

          const Text('HƯỚNG DẪN SỬ DỤNG CÁC CHỨC NĂNG', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8)),
          const SizedBox(height: 12),

          _buildGuideCard(
            icon: Icons.explore_rounded,
            title: '1. Bản Đồ & Tìm Kiếm Thông Minh',
            subtitle: 'Tra cứu hơn 1.000+ địa điểm ẩm thực, di sản, trường học và gian hàng OCOP được xác thực.',
            color: const Color(0xFF0284C7),
          ),
          _buildGuideCard(
            icon: Icons.directions_bike_rounded,
            title: '2. Food Tour AI Nối Đuôi Stream',
            subtitle: 'Nhập ngân sách & tâm trạng để Trợ lý Gemini AI tự động lên lịch trình du lịch ẩm thực tối ưu.',
            color: const Color(0xFF059669),
          ),
          _buildGuideCard(
            icon: Icons.camera_alt_rounded,
            title: '3. Góc Check-in Locket & Video Reels',
            subtitle: 'Chụp ảnh khoảnh khắc hoặc xem video trải nghiệm chân thực từ cộng đồng bản địa.',
            color: const Color(0xFFEA580C),
          ),
          _buildGuideCard(
            icon: Icons.admin_panel_settings_rounded,
            title: '4. Chuyển Đổi Nhanh Role Dashboard',
            subtitle: 'Dành cho Tiểu thương, Hiệu trưởng, BQL Chợ & Admin điều hành trực tiếp ngay trên app.',
            color: const Color(0xFF7C3AED),
          ),
        ],
      ),
    );
  }

  Widget _buildGuideCard({required IconData icon, required String title, required String subtitle, required Color color}) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: SquircleHelper.decoration(radius: 18, color: Colors.white, borderSide: BorderSide(color: Colors.grey.shade200)),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          CircleAvatar(backgroundColor: color.withOpacity(0.12), radius: 22, child: Icon(icon, color: color, size: 22)),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A))),
                const SizedBox(height: 4),
                Text(subtitle, style: const TextStyle(fontSize: 12, color: Colors.grey, height: 1.3)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
