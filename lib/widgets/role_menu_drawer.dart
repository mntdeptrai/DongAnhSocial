import 'package:flutter/material.dart';
import '../services/api_service.dart';

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

            // Scrollable Menu List based strictly on assigned account role
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                children: [
                  Text(
                    _getRoleSectionTitle(userRole),
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8),
                  ),
                  const SizedBox(height: 12),

                  // Role-specific navigation options
                  if (userRole == 'admin') ...[
                    _buildDrawerShortcut(
                      icon: Icons.shield_rounded,
                      title: 'Trung Tâm Quản Trị Admin',
                      subtitle: 'Phân quyền người dùng & Thống kê CSDL',
                      color: const Color(0xFFDC2626),
                      onTap: () {
                        Navigator.pop(context);
                        onRoleChanged?.call('admin');
                      },
                    ),
                  ] else if (userRole == 'manager') ...[
                    _buildDrawerShortcut(
                      icon: Icons.admin_panel_settings_rounded,
                      title: 'Ban Quản Lý Chợ & ATTP',
                      subtitle: 'Giám sát chợ, duyệt gian hàng & báo cáo',
                      color: const Color(0xFF4F46E5),
                      onTap: () {
                        Navigator.pop(context);
                        onRoleChanged?.call('manager');
                      },
                    ),
                  ] else if (userRole == 'seller') ...[
                    _buildDrawerShortcut(
                      icon: Icons.storefront_rounded,
                      title: 'Kênh Điều Hành Gian Hàng',
                      subtitle: 'Quản lý thực đơn & Đơn hàng cửa hàng',
                      color: const Color(0xFF059669),
                      onTap: () {
                        Navigator.pop(context);
                        onRoleChanged?.call('seller');
                      },
                    ),
                  ],

                  if (userRole != 'user') const Divider(height: 24),

                  // Standard Consumer Services
                  const Text(
                    'DỊCH VỤ & KHÁM PHÁ CÔNG KHAI',
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
                    icon: Icons.photo_camera_rounded,
                    title: 'Check-in Locket Cổ Loa',
                    subtitle: 'Bảng tin chia sẻ khoảnh khắc ẩm thực',
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

  String _getRoleSectionTitle(String role) {
    switch (role) {
      case 'admin':
        return 'CHỨC NĂNG QUẢN TRỊ VIÊN';
      case 'manager':
        return 'CHỨC NĂNG BAN QUẢN LÝ CHỢ';
      case 'seller':
        return 'CHỨC NĂNG CHỦ GIAN HÀNG';
      default:
        return 'TÍNH NĂNG NGƯỜI DÙNG';
    }
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
      decoration: BoxDecoration(
        color: Colors.grey.shade50,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.grey.shade200),
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
