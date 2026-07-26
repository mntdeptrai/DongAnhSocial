import 'package:flutter/material.dart';
import '../services/api_service.dart';

class RoleMenuDrawer extends StatelessWidget {
  final String activeRole;
  final Function(String newRole) onRoleChanged;
  final Function(int tabIndex)? onNavigateTab;
  final VoidCallback? onLogout;

  const RoleMenuDrawer({
    super.key,
    required this.activeRole,
    required this.onRoleChanged,
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

    // Build available roles based on user permissions
    final List<Map<String, dynamic>> availableRoles = [
      {
        'id': 'user',
        'label': 'Chế độ Người Tiêu Dùng (Khám phá)',
        'description': 'Bản đồ ẩm thực, Chợ số OCOP & Check-in',
        'icon': Icons.explore_rounded,
        'color': const Color(0xFF0EA5E9),
      },
    ];

    if (userRole == 'seller' || userRole == 'admin' || userRole == 'manager') {
      availableRoles.add({
        'id': 'seller',
        'label': 'Chế độ Chủ Gian Hàng (Seller Portal)',
        'description': 'Quản lý đơn hàng, thực đơn & tiêu chuẩn ATTP',
        'icon': Icons.storefront_rounded,
        'color': const Color(0xFF059669),
      });
    }

    if (userRole == 'manager' || userRole == 'admin') {
      availableRoles.add({
        'id': 'manager',
        'label': 'Chế độ Ban Quản Lý Chợ (Manager)',
        'description': 'Giám sát chợ, duyệt gian hàng & cảnh báo ATTP',
        'icon': Icons.admin_panel_settings_rounded,
        'color': const Color(0xFF4F46E5),
      });
    }

    if (userRole == 'admin') {
      availableRoles.add({
        'id': 'admin',
        'label': 'Chế độ Quản Trị Viên (System Admin)',
        'description': 'Phân quyền người dùng & quản trị toàn hệ thống',
        'icon': Icons.shield_rounded,
        'color': const Color(0xFFDC2626),
      });
    }

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
                            'Quyền Hạn: ${userRole.toUpperCase()}',
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
                  // Role Switcher Section Header
                  const Text(
                    'CHUYỂN ĐỔI CHẾ ĐỘ VAI TRÒ',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8),
                  ),
                  const SizedBox(height: 10),

                  // Role Cards List according to permissions
                  ...availableRoles.map((r) {
                    final isSelected = activeRole == r['id'];
                    final Color color = r['color'];

                    return Container(
                      margin: const EdgeInsets.only(bottom: 10),
                      decoration: BoxDecoration(
                        color: isSelected ? color.withValues(alpha: 0.08) : Colors.grey.shade50,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isSelected ? color : Colors.grey.shade200,
                          width: isSelected ? 2 : 1,
                        ),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: isSelected ? color : Colors.grey.shade200,
                          child: Icon(r['icon'], color: isSelected ? Colors.white : Colors.grey.shade700, size: 20),
                        ),
                        title: Text(
                          r['label'],
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: isSelected ? color : const Color(0xFF0F172A),
                          ),
                        ),
                        subtitle: Text(
                          r['description'],
                          style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                        ),
                        trailing: isSelected ? Icon(Icons.check_circle_rounded, color: color, size: 20) : null,
                        onTap: () {
                          Navigator.pop(context); // Close Drawer
                          onRoleChanged(r['id']);
                        },
                      ),
                    );
                  }).toList(),

                  const Divider(height: 24),

                  // Quick Shortcuts Section Header
                  const Text(
                    'DỊCH VỤ & LỐI TẮC',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B), letterSpacing: 0.8),
                  ),
                  const SizedBox(height: 10),

                  _buildDrawerShortcut(
                    icon: Icons.map_rounded,
                    title: 'Bản Đồ Ẩm Thực & Di Sản Cổ Loa',
                    color: const Color(0xFF0EA5E9),
                    onTap: () {
                      Navigator.pop(context);
                      onNavigateTab?.call(2); // Map Tab
                    },
                  ),
                  _buildDrawerShortcut(
                    icon: Icons.storefront_rounded,
                    title: 'Chợ Số & Nông Sản OCOP',
                    color: const Color(0xFF059669),
                    onTap: () {
                      Navigator.pop(context);
                      onNavigateTab?.call(3); // Market Tab
                    },
                  ),
                  _buildDrawerShortcut(
                    icon: Icons.photo_camera_rounded,
                    title: 'Check-in Locket Cổ Loa',
                    color: const Color(0xFFF59E0B),
                    onTap: () {
                      Navigator.pop(context);
                      onNavigateTab?.call(0); // Feed Tab
                    },
                  ),
                  _buildDrawerShortcut(
                    icon: Icons.notifications_active_rounded,
                    title: 'Thông Báo Hệ Thống',
                    color: const Color(0xFF6366F1),
                    onTap: () {
                      Navigator.pop(context);
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

  Widget _buildDrawerShortcut({
    required IconData icon,
    required String title,
    required Color color,
    required VoidCallback onTap,
  }) {
    return ListTile(
      dense: true,
      leading: Icon(icon, color: color, size: 22),
      title: Text(
        title,
        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
      ),
      trailing: const Icon(Icons.chevron_right_rounded, size: 18, color: Colors.grey),
      onTap: onTap,
    );
  }
}
