import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/squircle_helper.dart';

class RoleSwitchBanner extends StatelessWidget {
  final String activeRole;
  final Function(String newRole) onRoleChanged;

  const RoleSwitchBanner({
    super.key,
    required this.activeRole,
    required this.onRoleChanged,
  });

  @override
  Widget build(BuildContext context) {
    final userRole = ApiService.currentUser?['role'] ?? 'user';

    final List<Map<String, dynamic>> availableRoles = [
      {
        'id': 'user',
        'label': 'Mua sắm & Khám phá',
        'icon': Icons.explore_rounded,
        'color': const Color(0xFF0EA5E9),
      },
    ];

    if (userRole == 'seller' || userRole == 'admin' || userRole == 'manager') {
      availableRoles.add({
        'id': 'seller',
        'label': 'Chủ gian hàng (Seller)',
        'icon': Icons.storefront_rounded,
        'color': const Color(0xFF059669),
      });
    }

    if (userRole == 'manager' || userRole == 'admin') {
      availableRoles.add({
        'id': 'manager',
        'label': 'Quản lý Chợ (Manager)',
        'icon': Icons.admin_panel_settings_rounded,
        'color': const Color(0xFF4F46E5),
      });
    }

    if (userRole == 'admin') {
      availableRoles.add({
        'id': 'admin',
        'label': 'Quản trị viên (Admin)',
        'icon': Icons.shield_rounded,
        'color': const Color(0xFFDC2626),
      });
    }

    if (availableRoles.length <= 1) return const SizedBox.shrink();

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      padding: const EdgeInsets.all(4),
      decoration: SquircleHelper.decoration(
        radius: 16,
        color: Colors.grey.shade100,
        borderSide: BorderSide(color: Colors.grey.shade200),
      ),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: availableRoles.map((r) {
            final isSelected = activeRole == r['id'];
            final Color roleColor = r['color'];

            return Padding(
              padding: const EdgeInsets.symmetric(horizontal: 2),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 250),
                child: Material(
                  color: Colors.transparent,
                  child: InkWell(
                    onTap: () => onRoleChanged(r['id']),
                    borderRadius: SquircleHelper.radius(12),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                      decoration: SquircleHelper.decoration(
                        radius: 12,
                        color: isSelected ? roleColor : Colors.transparent,
                        boxShadow: isSelected
                            ? [
                                BoxShadow(
                                  color: roleColor.withValues(alpha: 0.3),
                                  blurRadius: 6,
                                  offset: const Offset(0, 2),
                                )
                              ]
                            : [],
                      ),
                      child: Row(
                        children: [
                          Icon(
                            r['icon'],
                            size: 18,
                            color: isSelected ? Colors.white : Colors.grey.shade700,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            r['label'],
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                              color: isSelected ? Colors.white : Colors.grey.shade800,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }
}
