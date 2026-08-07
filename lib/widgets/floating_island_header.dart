import 'dart:ui';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'squircle_helper.dart';

class FloatingIslandHeader extends StatelessWidget implements PreferredSizeWidget {
  final int currentIndex;
  final ValueChanged<int> onTabSelected;
  final VoidCallback? onSearchTap;
  final VoidCallback? onMessengerTap;
  final VoidCallback? onCartTap;
  final VoidCallback? onMenuTap;
  final Function(String role)? onRoleDashboardTap;
  final int unreadMessagesCount;
  final int unreadNotifsCount;
  final int cartCount;

  const FloatingIslandHeader({
    super.key,
    required this.currentIndex,
    required this.onTabSelected,
    this.onSearchTap,
    this.onMessengerTap,
    this.onCartTap,
    this.onMenuTap,
    this.onRoleDashboardTap,
    this.unreadMessagesCount = 0,
    this.unreadNotifsCount = 0,
    this.cartCount = 0,
  });

  @override
  Size get preferredSize => const Size.fromHeight(68.0);

  @override
  Widget build(BuildContext context) {
    final user = ApiService.currentUser;
    final userRole = user?['role'] ?? 'user';

    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(24),
          child: BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 16, sigmaY: 16),
            child: Container(
              height: 56,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.85),
                borderRadius: BorderRadius.circular(24),
                border: Border.all(
                  color: const Color(0xFF0EA5E9).withValues(alpha: 0.25),
                  width: 1.2,
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0EA5E9).withValues(alpha: 0.12),
                    blurRadius: 16,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Row(
                children: [
                  // Menu drawer trigger
                  InkWell(
                    onTap: onMenuTap ?? () => Scaffold.of(context).openDrawer(),
                    borderRadius: BorderRadius.circular(16),
                    child: Container(
                      padding: const EdgeInsets.all(7.0),
                      decoration: BoxDecoration(
                        color: const Color(0xFF0EA5E9).withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: const Icon(Icons.menu_rounded, color: Color(0xFF0F172A), size: 20),
                    ),
                  ),
                  const SizedBox(width: 8),

                  // Brand Logo Title
                  ShaderMask(
                    shaderCallback: (bounds) => const LinearGradient(
                      colors: [Color(0xFF0284C7), Color(0xFF06B6D4), Color(0xFF10B981)],
                    ).createShader(bounds),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.explore_rounded, color: Colors.white, size: 22),
                        SizedBox(width: 4),
                        Text(
                          'DongAnh',
                          style: TextStyle(
                            fontSize: 19,
                            fontWeight: FontWeight.w900,
                            color: Colors.white,
                            letterSpacing: -0.6,
                          ),
                        ),
                      ],
                    ),
                  ),

                  const Spacer(),

                  // Dynamic Role Switcher Island Pill
                  if (userRole != 'user') ...[
                    _buildRoleIslandBadge(context, userRole),
                    const SizedBox(width: 6),
                  ],

                  // Action Buttons
                  _buildHeaderButton(
                    icon: Icons.search_rounded,
                    onTap: onSearchTap,
                  ),
                  const SizedBox(width: 5),

                  _buildHeaderButton(
                    icon: Icons.shopping_bag_outlined,
                    badgeCount: cartCount,
                    onTap: onCartTap,
                  ),
                  const SizedBox(width: 5),

                  _buildHeaderButton(
                    icon: Icons.chat_bubble_outline_rounded,
                    badgeCount: unreadMessagesCount,
                    onTap: onMessengerTap,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildRoleIslandBadge(BuildContext context, String role) {
    IconData icon = Icons.space_dashboard_rounded;
    String label = 'Dashboard';
    Color color = const Color(0xFF0EA5E9);

    if (role == 'seller') {
      icon = Icons.storefront_rounded;
      label = 'Gian Hàng';
      color = const Color(0xFF059669);
    } else if (role == 'principal') {
      icon = Icons.school_rounded;
      label = 'Trường Học';
      color = const Color(0xFF0284C7);
    } else if (role == 'manager') {
      icon = Icons.admin_panel_settings_rounded;
      label = 'BQL Chợ';
      color = const Color(0xFF4F46E5);
    } else if (role == 'admin') {
      icon = Icons.dashboard_customize_rounded;
      label = 'Admin';
      color = const Color(0xFF8B5CF6);
    }

    return GestureDetector(
      onTap: () {
        if (onRoleDashboardTap != null) {
          onRoleDashboardTap!(role);
        } else {
          Scaffold.of(context).openDrawer();
        }
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [color.withValues(alpha: 0.15), color.withValues(alpha: 0.25)],
          ),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: color.withValues(alpha: 0.5), width: 1.2),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 15, color: color),
            const SizedBox(width: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w900,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeaderButton({
    required IconData icon,
    int badgeCount = 0,
    VoidCallback? onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFF0EA5E9).withValues(alpha: 0.15)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.03),
                  blurRadius: 4,
                  offset: const Offset(0, 1),
                ),
              ],
            ),
            child: Center(
              child: Icon(icon, color: const Color(0xFF0F172A), size: 18),
            ),
          ),
          if (badgeCount > 0)
            Positioned(
              top: -3,
              right: -3,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                decoration: BoxDecoration(
                  color: const Color(0xFFEF4444),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: Colors.white, width: 1.5),
                ),
                constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                child: Center(
                  child: Text(
                    '$badgeCount',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 8,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
