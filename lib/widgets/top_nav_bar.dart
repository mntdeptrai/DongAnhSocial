import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/cart_service.dart';
import '../widgets/squircle_helper.dart';

class TopNavBar extends StatelessWidget implements PreferredSizeWidget {
  final int currentIndex;
  final ValueChanged<int> onTabSelected;
  final VoidCallback? onAddPostTap;
  final VoidCallback? onSearchTap;
  final VoidCallback? onMessengerTap;
  final VoidCallback? onCartTap;
  final VoidCallback? onMenuTap;
  final Function(String role)? onRoleDashboardTap;
  final int unreadMessagesCount;
  final int unreadNotifsCount;
  final int cartCount;

  const TopNavBar({
    super.key,
    required this.currentIndex,
    required this.onTabSelected,
    this.onAddPostTap,
    this.onSearchTap,
    this.onMessengerTap,
    this.onCartTap,
    this.onMenuTap,
    this.onRoleDashboardTap,
    this.unreadMessagesCount = 1,
    this.unreadNotifsCount = 2,
    this.cartCount = 0,
  });

  @override
  Size get preferredSize => const Size.fromHeight(108.0);

  @override
  Widget build(BuildContext context) {
    final user = ApiService.currentUser;
    final userRole = user?['role'] ?? 'user';

    return Container(
      decoration: const BoxDecoration(
        color: Color(0xFFF0FDFA),
        border: Border(
          bottom: BorderSide(color: Color(0x1F0EA5E9), width: 1.0),
        ),
      ),
      child: SafeArea(
        bottom: false,
        child: Column(
          children: [
            // Top Row: Brand & Light Double-Bezel Action Buttons
            Container(
              height: 54,
              padding: const EdgeInsets.symmetric(horizontal: 14),
              child: Row(
                children: [
                  // Menu drawer button with Squircle smooth corners
                  InkWell(
                    onTap: onMenuTap ?? () => Scaffold.of(context).openDrawer(),
                    borderRadius: SquircleHelper.radius(12),
                    child: Container(
                      padding: const EdgeInsets.all(8.0),
                      decoration: SquircleHelper.decoration(
                        radius: 12,
                        color: Colors.white,
                        borderSide: BorderSide(color: const Color(0xFF0EA5E9).withValues(alpha: 0.18)),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.04),
                            blurRadius: 4,
                            offset: const Offset(0, 1),
                          ),
                        ],
                      ),
                      child: const Icon(Icons.menu_rounded, color: Color(0xFF0F172A), size: 22),
                    ),
                  ),
                  const SizedBox(width: 8),

                  // Brand Title with Vibrant Gradient
                  ShaderMask(
                    shaderCallback: (bounds) => const LinearGradient(
                      colors: [Color(0xFF0EA5E9), Color(0xFF06B6D4), Color(0xFF10B981)],
                    ).createShader(bounds),
                    child: const Text(
                      'DongAnh',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                        letterSpacing: -0.8,
                      ),
                    ),
                  ),

                  const Spacer(),

                  // Nút chuyển nhanh vào Trang Dashboard Theo Role (Role Dashboard Button)
                  if (userRole != 'user') ...[
                    _buildRoleDashboardButton(context, userRole),
                    const SizedBox(width: 6),
                  ],

                  // Clean Action Buttons (Search, Cart, Messenger)
                  _buildHeaderActionButton(
                    icon: Icons.search_rounded,
                    onTap: onSearchTap,
                  ),
                  const SizedBox(width: 6),

                  ValueListenableBuilder<int>(
                    valueListenable: CartService.cartCountNotifier,
                    builder: (context, liveCartCount, _) {
                      return _buildHeaderActionButton(
                        icon: Icons.shopping_bag_outlined,
                        badgeCount: liveCartCount > 0 ? liveCartCount : cartCount,
                        onTap: onCartTap,
                      );
                    },
                  ),
                  const SizedBox(width: 6),

                  _buildHeaderActionButton(
                    icon: Icons.chat_bubble_outline_rounded,
                    badgeCount: unreadMessagesCount,
                    onTap: onMessengerTap,
                  ),
                ],
              ),
            ),

            // Bottom Row: Navigation Tabs with Light Double-Bezel Pill Indicators
            Expanded(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _buildTabItem(index: 0, icon: Icons.home_rounded),
                    _buildTabItem(index: 1, icon: Icons.add_location_alt_rounded),
                    _buildTabItem(index: 2, icon: Icons.map_rounded),
                    _buildTabItem(index: 3, icon: Icons.storefront_rounded),
                    _buildTabItem(index: 4, icon: Icons.notifications_rounded, badgeCount: unreadNotifsCount),
                    _buildTabItem(index: 5, icon: Icons.account_circle_rounded),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeaderActionButton({
    required IconData icon,
    int badgeCount = 0,
    VoidCallback? onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      behavior: HitTestBehavior.opaque,
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          Container(
            width: 38,
            height: 38,
            decoration: SquircleHelper.decoration(
              radius: 12,
              color: Colors.white,
              borderSide: BorderSide(color: const Color(0xFF0EA5E9).withValues(alpha: 0.18)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.04),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Center(
              child: Icon(icon, color: const Color(0xFF0F172A), size: 20),
            ),
          ),
          if (badgeCount > 0)
            Positioned(
              top: -3,
              right: -3,
              child: IgnorePointer(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                  decoration: SquircleHelper.decoration(
                    radius: 10,
                    color: const Color(0xFFEF4444),
                    borderSide: const BorderSide(color: Color(0xFFF0FDFA), width: 1.5),
                  ),
                  constraints: const BoxConstraints(minWidth: 18, minHeight: 18),
                  child: Center(
                    child: Text(
                      '$badgeCount',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 9,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildTabItem({
    required int index,
    required IconData icon,
    int badgeCount = 0,
  }) {
    final bool isSelected = currentIndex == index;

    return Expanded(
      child: InkWell(
        onTap: () => onTabSelected(index),
        borderRadius: SquircleHelper.radius(12),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 250),
          curve: Curves.fastOutSlowIn,
          margin: const EdgeInsets.symmetric(horizontal: 2),
          decoration: SquircleHelper.decoration(
            radius: 12,
            color: isSelected ? const Color(0xFF0EA5E9).withValues(alpha: 0.14) : Colors.transparent,
            borderSide: BorderSide(
              color: isSelected ? const Color(0xFF0EA5E9).withValues(alpha: 0.35) : Colors.transparent,
              width: 1.0,
            ),
            boxShadow: isSelected
                ? [
                    BoxShadow(
                      color: const Color(0xFF0EA5E9).withValues(alpha: 0.1),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ]
                : [],
          ),
          child: Center(
            child: Stack(
              clipBehavior: Clip.none,
              children: [
                Icon(
                  icon,
                  size: 24,
                  color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFF64748B),
                ),
                if (badgeCount > 0)
                  Positioned(
                    top: -4,
                    right: -6,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEF4444),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      constraints: const BoxConstraints(minWidth: 15, minHeight: 15),
                      child: Center(
                        child: Text(
                          '$badgeCount',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 8,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildRoleDashboardButton(BuildContext context, String role) {
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
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
        decoration: SquircleHelper.decoration(
          radius: 12,
          color: color.withOpacity(0.12),
          borderSide: BorderSide(color: color.withOpacity(0.4), width: 1),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 16, color: color),
            const SizedBox(width: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w800,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }
}


