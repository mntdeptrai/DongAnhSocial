import 'dart:ui';
import 'package:flutter/material.dart';
import '../services/cart_service.dart';
import '../services/notification_state_service.dart';

class FloatingIslandHeader extends StatelessWidget implements PreferredSizeWidget {
  final int currentIndex;
  final ValueChanged<int> onTabSelected;
  final VoidCallback? onSearchTap;
  final VoidCallback? onNotificationsTap;
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
    this.onNotificationsTap,
    this.onMessengerTap,
    this.onCartTap,
    this.onMenuTap,
    this.onRoleDashboardTap,
    this.unreadMessagesCount = 0,
    this.unreadNotifsCount = 0,
    this.cartCount = 0,
  });

  @override
  Size get preferredSize => const Size.fromHeight(64.0);

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(24),
          child: BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 16, sigmaY: 16),
            child: Container(
              height: 54,
              padding: const EdgeInsets.symmetric(horizontal: 10),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.94),
                borderRadius: BorderRadius.circular(24),
                border: Border.all(
                  color: Colors.black.withValues(alpha: 0.06),
                  width: 1.0,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.05),
                    blurRadius: 14,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Row(
                children: [
                  // Menu drawer trigger
                  InkWell(
                    onTap: onMenuTap ?? () => Scaffold.of(context).openDrawer(),
                    borderRadius: BorderRadius.circular(14),
                    child: Container(
                      padding: const EdgeInsets.all(7.0),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.menu_rounded, color: Color(0xFF1E293B), size: 20),
                    ),
                  ),
                  const SizedBox(width: 8),

                  // Brand Logo Title
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 30,
                        height: 30,
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF0EA5E9), Color(0xFF0284C7)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Center(
                          child: Icon(Icons.explore_rounded, color: Colors.white, size: 17),
                        ),
                      ),
                      const SizedBox(width: 7),
                      const Text(
                        'Đông Anh',
                        style: TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF0F172A),
                          letterSpacing: -0.4,
                        ),
                      ),
                    ],
                  ),

                  const Spacer(),

                  // Action Buttons Group
                  // 1. Search
                  _buildHeaderButton(
                    icon: Icons.search_rounded,
                    onTap: onSearchTap,
                  ),
                  const SizedBox(width: 4),

                  // 2. Notifications
                  ValueListenableBuilder<int>(
                    valueListenable: NotificationStateService.unreadCountNotifier,
                    builder: (context, liveNotifCount, _) {
                      final int count = liveNotifCount > 0 ? liveNotifCount : unreadNotifsCount;
                      return _buildHeaderButton(
                        icon: Icons.notifications_none_rounded,
                        badgeCount: count,
                        onTap: onNotificationsTap,
                      );
                    },
                  ),
                  const SizedBox(width: 4),

                  // 3. Cart
                  ValueListenableBuilder<int>(
                    valueListenable: CartService.cartCountNotifier,
                    builder: (context, liveCartCount, _) {
                      return _buildHeaderButton(
                        icon: Icons.shopping_bag_outlined,
                        badgeCount: liveCartCount > 0 ? liveCartCount : cartCount,
                        onTap: onCartTap,
                      );
                    },
                  ),
                  const SizedBox(width: 4),

                  // 4. Messenger / Chat
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

  Widget _buildHeaderButton({
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
            width: 34,
            height: 34,
            decoration: BoxDecoration(
              color: const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Center(
              child: Icon(icon, color: const Color(0xFF334155), size: 18),
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
                constraints: const BoxConstraints(minWidth: 15, minHeight: 15),
                child: Center(
                  child: Text(
                    badgeCount > 99 ? '99+' : '$badgeCount',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 8,
                      fontWeight: FontWeight.w800,
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
