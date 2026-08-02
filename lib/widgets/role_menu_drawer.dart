import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../screens/my_orders_screen.dart';
import '../screens/admin_dashboard_screen.dart';
import 'squircle_helper.dart';

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
    final userName = user?['name'] ?? 'Khách vãng lai';
    final userEmail = user?['email'] ?? 'Chưa đăng nhập';
    final userAvatar = user?['avatar'] ?? 'https://i.pravatar.cc/150?img=12';

    return Drawer(
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.horizontal(right: Radius.circular(24)),
      ),
      child: SafeArea(
        child: Column(
          children: [
            // Drawer User Profile Header Card
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
                    radius: 28,
                    backgroundImage: NetworkImage(userAvatar),
                    backgroundColor: Colors.white24,
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          userName,
                          style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          userEmail,
                          style: const TextStyle(color: Colors.white70, fontSize: 12),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.white24,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            'QUYỀN HẠN: ${userRole.toUpperCase()}',
                            style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // Scrollable Menu List
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                children: [
                  // Dedicated Management Portal Action Card for Privilege Roles
                  if (userRole == 'seller') ...[
                    const Text(
                      'TRUNG TÂM DÀNH CHO CHỦ GIAN HÀNG',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8),
                    ),
                    const SizedBox(height: 10),
                    _buildPortalCard(
                      icon: Icons.storefront_rounded,
                      title: 'Kênh Điều Hành Cửa Hàng',
                      subtitle: 'Quản lý món ăn, thực đơn & đơn hàng cửa hàng',
                      color: const Color(0xFF059669),
                      onTap: () {
                        Navigator.pop(context);
                        onRoleChanged?.call('seller');
                      },
                    ),
                    const Divider(height: 24),
                  ] else if (userRole == 'manager') ...[
                    const Text(
                      'TRUNG TÂM BAN QUẢN LÝ CHỢ',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8),
                    ),
                    const SizedBox(height: 10),
                    _buildPortalCard(
                      icon: Icons.admin_panel_settings_rounded,
                      title: 'Ban Quản Lý Chợ & ATTP',
                      subtitle: 'Giám sát gian hàng, kiểm tra ATTP & duyệt hồ sơ',
                      color: const Color(0xFF4F46E5),
                      onTap: () {
                        Navigator.pop(context);
                        onRoleChanged?.call('manager');
                      },
                    ),
                    const Divider(height: 24),
                  ] else if (userRole == 'admin') ...[
                    const Text(
                      'TỔNG QUAN',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8),
                    ),
                    const SizedBox(height: 8),
                    _buildDrawerShortcut(
                      icon: Icons.dashboard_rounded,
                      title: 'Dashboard Thống Kê',
                      subtitle: 'Xem tổng quan KPI, lượt truy cập & báo cáo',
                      color: const Color(0xFF6366F1),
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const AdminDashboardScreen(initialTabIndex: 0)),
                        );
                      },
                    ),
                    const SizedBox(height: 12),
                    const Text(
                      'QUẢN TRỊ HỆ THỐNG',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8),
                    ),
                    const SizedBox(height: 8),
                    _buildDrawerShortcut(
                      icon: Icons.location_on_rounded,
                      title: 'Quản Lý Địa Điểm & Cơ Sở',
                      subtitle: 'Đăng ký cơ sở, ghim nổi bật & duyệt bản đồ số',
                      color: const Color(0xFFDC2626),
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const AdminDashboardScreen(initialTabIndex: 1)),
                        );
                      },
                    ),
                    _buildDrawerShortcut(
                      icon: Icons.school_rounded,
                      title: 'Quản Lý Trường Học & Sáp Nhập',
                      subtitle: 'Trường học, bản đồ giáo dục & tuyến tuyển sinh',
                      color: const Color(0xFF0284C7),
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const AdminDashboardScreen(initialTabIndex: 2)),
                        );
                      },
                    ),
                    _buildDrawerShortcut(
                      icon: Icons.shopping_bag_rounded,
                      title: 'Quản Lý Gian Hàng',
                      subtitle: 'Duyệt gian hàng OCOP, đặc sản & người bán',
                      color: const Color(0xFF059669),
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const AdminDashboardScreen(initialTabIndex: 3)),
                        );
                      },
                    ),
                    _buildDrawerShortcut(
                      icon: Icons.people_alt_rounded,
                      title: 'Quản Lý Tài Khoản',
                      subtitle: 'Phân quyền Admin/Seller/User, khóa & tạo user mới',
                      color: const Color(0xFF8B5CF6),
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const AdminDashboardScreen(initialTabIndex: 4)),
                        );
                      },
                    ),
                    const Divider(height: 24),
                  ],

                  // Standard Consumer Services Section
                  const Text(
                    'DỊCH VỤ & LỐI TẮC NGƯỜI DÙNG',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8),
                  ),
                  const SizedBox(height: 10),

                  _buildDrawerShortcut(
                    icon: Icons.map_rounded,
                    title: 'Bản Đồ Ẩm Thực & Di Sản Cổ Loa',
                    subtitle: 'Khám phá địa điểm & quán ăn Đông Anh',
                    color: const Color(0xFF0EA5E9),
                    onTap: () {
                      Navigator.pop(context);
                      onRoleChanged?.call('user');
                      onNavigateTab?.call(2); // Map Tab
                    },
                  ),
                  _buildDrawerShortcut(
                    icon: Icons.shopping_bag_rounded,
                    title: 'Chợ Số & Nông Sản OCOP',
                    subtitle: 'Mua sắm đặc sản Đông Anh online',
                    color: const Color(0xFF059669),
                    onTap: () {
                      Navigator.pop(context);
                      onRoleChanged?.call('user');
                      onNavigateTab?.call(3); // Market Tab
                    },
                  ),
                  _buildDrawerShortcut(
                    icon: Icons.receipt_long_rounded,
                    title: '📦 Lịch Sử & Quản Lý Đơn Hàng',
                    subtitle: 'Trạng thái, đơn đã nhận, hủy đơn & hoàn hàng',
                    color: const Color(0xFFEA580C),
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (context) => const MyOrdersScreen()),
                      );
                    },
                  ),
                  _buildDrawerShortcut(
                    icon: Icons.add_location_alt_rounded,
                    title: '📸 Góc Trải Nghiệm Thực Tế',
                    subtitle: 'Lưu giữ & chia sẻ bài check-in của bạn',
                    color: const Color(0xFF0284C7),
                    onTap: () {
                      Navigator.pop(context);
                      onRoleChanged?.call('user');
                      onNavigateTab?.call(1); // Real Experience Tab
                    },
                  ),
                  _buildDrawerShortcut(
                    icon: Icons.photo_camera_rounded,
                    title: 'Bảng Tin Check-in Locket',
                    subtitle: 'Khám phá khoảnh khắc ẩm thực cộng đồng',
                    color: const Color(0xFFF59E0B),
                    onTap: () {
                      Navigator.pop(context);
                      onRoleChanged?.call('user');
                      onNavigateTab?.call(0); // Feed Tab
                    },
                  ),
                  _buildDrawerShortcut(
                    icon: Icons.notifications_active_rounded,
                    title: 'Thông Báo Hệ Thống',
                    subtitle: 'Cập nhật tin tức & đơn hàng',
                    color: const Color(0xFF6366F1),
                    onTap: () {
                      Navigator.pop(context);
                      onRoleChanged?.call('user');
                      onNavigateTab?.call(4); // Notifs Tab
                    },
                  ),
                ],
              ),
            ),

            // Logout Footer Button
            if (ApiService.isAuthenticated && onLogout != null)
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
                    label: const Text('Đăng Xuất Tài Khoản', style: TextStyle(color: Color(0xFFEF4444), fontWeight: FontWeight.bold)),
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

  Widget _buildPortalCard({
    required IconData icon,
    required String title,
    required String subtitle,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Container(
      decoration: SquircleHelper.decoration(
        radius: 16,
        color: color.withValues(alpha: 0.08),
        borderSide: BorderSide(color: color, width: 1.5),
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        leading: CircleAvatar(
          backgroundColor: color,
          radius: 22,
          child: Icon(icon, color: Colors.white, size: 22),
        ),
        title: Text(
          title,
          style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: color),
        ),
        subtitle: Text(
          subtitle,
          style: TextStyle(fontSize: 11, color: Colors.grey.shade700),
        ),
        trailing: Icon(Icons.arrow_forward_ios_rounded, size: 16, color: color),
        onTap: onTap,
      ),
    );
  }

  Widget _buildDrawerShortcut({
    required IconData icon,
    required String title,
    String? subtitle,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: SquircleHelper.decoration(
        radius: 14,
        color: Colors.grey.shade50,
        borderSide: BorderSide(color: Colors.grey.shade200),
      ),
      child: ListTile(
        dense: true,
        leading: CircleAvatar(
          backgroundColor: color.withValues(alpha: 0.12),
          child: Icon(icon, color: color, size: 20),
        ),
        title: Text(
          title,
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
        ),
        subtitle: subtitle != null
            ? Text(
                subtitle,
                style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
              )
            : null,
        trailing: const Icon(Icons.chevron_right_rounded, size: 18, color: Colors.grey),
        onTap: onTap,
      ),
    );
  }
}
