import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../screens/my_orders_screen.dart';
import '../screens/admin_dashboard_screen.dart';
import '../screens/food_tour_screen.dart';
import '../screens/video_reels_screen.dart';
import '../screens/exp_corner_screen.dart';
import '../screens/about_guide_screen.dart';
import '../screens/privacy_policy_screen.dart';
import '../screens/chat_screen.dart';

class RoleMenuDrawer extends StatelessWidget {
  final String activeRole;
  final Function(String newRole)? onRoleChanged;
  final Function(int tabIndex)? onNavigateTab;
  final VoidCallback? onLogout;

  const RoleMenuDrawer({
    super.key,
    required this.activeRole,
    this.onRoleChanged,
    this.onNavigateTab,
    this.onLogout,
  });

  @override
  Widget build(BuildContext context) {
    final user = ApiService.currentUser;
    final userRole = user?['role'] ?? 'user';
    final userName = user?['name'] ?? 'Khách khám phá';
    final userEmail = user?['email'] ?? 'Chưa đăng nhập';
    final userAvatar = ApiService.getAvatarUrl(user, userName);
    final isAuthenticated = ApiService.isAuthenticated;

    return Drawer(
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.horizontal(right: Radius.circular(24)),
      ),
      child: SafeArea(
        child: Column(
          children: [
            // User Profile Header
            Container(
              padding: const EdgeInsets.all(20),
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF0EA5E9), Color(0xFF0284C7)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 26,
                    backgroundImage: ResizeImage(NetworkImage(userAvatar), width: 120),
                    backgroundColor: Colors.white24,
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          userName,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 3),
                        Text(
                          isAuthenticated ? 'Thành viên Đông Anh' : userEmail,
                          style: const TextStyle(color: Colors.white70, fontSize: 12),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // Navigation Menu List
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                children: [
                  // SECTION 1: KHÁM PHÁ & TRẢI NGHIỆM
                  _buildSectionHeader('KHÁM PHÁ & TRẢI NGHIỆM'),
                  _buildMenuItem(
                    icon: Icons.directions_bike_rounded,
                    iconColor: const Color(0xFF0284C7),
                    bgColor: const Color(0xFFE0F2FE),
                    title: 'Food Tour AI',
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const FoodTourScreen()),
                      );
                    },
                  ),
                  _buildMenuItem(
                    icon: Icons.palette_rounded,
                    iconColor: const Color(0xFF059669),
                    bgColor: const Color(0xFFD1FAE5),
                    title: 'Làng nghề & Trải nghiệm thực tế',
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const ExpCornerScreen()),
                      );
                    },
                  ),
                  _buildMenuItem(
                    icon: Icons.play_circle_fill_rounded,
                    iconColor: const Color(0xFFEA580C),
                    bgColor: const Color(0xFFFFEDD5),
                    title: 'Video Shorts & Review',
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const VideoReelsScreen()),
                      );
                    },
                  ),

                  const SizedBox(height: 14),

                  // SECTION 2: TIỆN ÍCH CỦA TÔI
                  _buildSectionHeader('TIỆN ÍCH CỦA TÔI'),
                  _buildMenuItem(
                    icon: Icons.chat_bubble_outline_rounded,
                    iconColor: const Color(0xFF0284C7),
                    bgColor: const Color(0xFFE0F2FE),
                    title: 'Tin nhắn & Gọi điện (WebRTC)',
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const ChatScreen()),
                      );
                    },
                  ),
                  _buildMenuItem(
                    icon: Icons.receipt_long_rounded,
                    iconColor: const Color(0xFF6366F1),
                    bgColor: const Color(0xFFEEF2FF),
                    title: 'Đơn hàng của tôi',
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const MyOrdersScreen()),
                      );
                    },
                  ),
                  _buildMenuItem(
                    icon: Icons.info_outline_rounded,
                    iconColor: const Color(0xFF8B5CF6),
                    bgColor: const Color(0xFFF3E8FF),
                    title: 'Cẩm nang & Hướng dẫn du khách',
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const AboutGuideScreen()),
                      );
                    },
                  ),
                  _buildMenuItem(
                    icon: Icons.shield_outlined,
                    iconColor: const Color(0xFF0EA5E9),
                    bgColor: const Color(0xFFE0F2FE),
                    title: 'Chính sách bảo mật & Điều khoản (EULA)',
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const PrivacyPolicyScreen()),
                      );
                    },
                  ),

                  // SECTION 3: KÊNH ĐỐI TÁC & QUẢN TRỊ (Chỉ hiển thị khi có quyền)
                  if (userRole == 'seller' || userRole == 'principal' || userRole == 'manager' || userRole == 'admin') ...[
                    const SizedBox(height: 14),
                    _buildSectionHeader('KÊNH ĐIỀU HÀNH & QUẢN TRỊ'),
                    if (userRole == 'seller' || userRole == 'admin')
                      _buildMenuItem(
                        icon: Icons.storefront_rounded,
                        iconColor: const Color(0xFF059669),
                        bgColor: const Color(0xFFD1FAE5),
                        title: 'Kênh Quản lý Cửa hàng',
                        onTap: () {
                          Navigator.pop(context);
                          onRoleChanged?.call('seller');
                        },
                      ),
                    if (userRole == 'principal' || userRole == 'admin')
                      _buildMenuItem(
                        icon: Icons.school_rounded,
                        iconColor: const Color(0xFF0284C7),
                        bgColor: const Color(0xFFE0F2FE),
                        title: 'Kênh Quản lý Trường học',
                        onTap: () {
                          Navigator.pop(context);
                          onRoleChanged?.call('principal');
                        },
                      ),
                    if (userRole == 'manager' || userRole == 'admin')
                      _buildMenuItem(
                        icon: Icons.admin_panel_settings_rounded,
                        iconColor: const Color(0xFF4F46E5),
                        bgColor: const Color(0xFFEEF2FF),
                        title: 'Ban Quản lý Chợ & ATTP',
                        onTap: () {
                          Navigator.pop(context);
                          onRoleChanged?.call('manager');
                        },
                      ),
                    if (userRole == 'admin')
                      _buildMenuItem(
                        icon: Icons.dashboard_rounded,
                        iconColor: const Color(0xFF6366F1),
                        bgColor: const Color(0xFFEEF2FF),
                        title: 'Dashboard Thống kê Hệ thống',
                        onTap: () {
                          Navigator.pop(context);
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => const AdminDashboardScreen(initialTabIndex: 0),
                            ),
                          );
                        },
                      ),
                  ],
                ],
              ),
            ),

            // Logout Button
            if (isAuthenticated && onLogout != null)
              Padding(
                padding: const EdgeInsets.all(16),
                child: SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: () {
                      Navigator.pop(context);
                      onLogout?.call();
                    },
                    icon: const Icon(Icons.logout_rounded, size: 18, color: Color(0xFFEF4444)),
                    label: const Text(
                      'Đăng xuất tài khoản',
                      style: TextStyle(color: Color(0xFFEF4444), fontWeight: FontWeight.bold),
                    ),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      side: const BorderSide(color: Color(0xFFFCA5A5)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 8, top: 4),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w700,
          color: Color(0xFF94A3B8),
          letterSpacing: 0.6,
        ),
      ),
    );
  }

  Widget _buildMenuItem({
    required IconData icon,
    required Color iconColor,
    required Color bgColor,
    required String title,
    required VoidCallback onTap,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 6),
      child: Material(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(14),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            child: Row(
              children: [
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    color: bgColor,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(icon, color: iconColor, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    title,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF0F172A),
                    ),
                  ),
                ),
                const Icon(
                  Icons.chevron_right_rounded,
                  size: 18,
                  color: Color(0xFF94A3B8),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
