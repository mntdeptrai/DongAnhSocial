import 'package:flutter/material.dart';

class TopNavBar extends StatelessWidget implements PreferredSizeWidget {
  final int currentIndex;
  final ValueChanged<int> onTabSelected;
  final VoidCallback? onAddPostTap;
  final VoidCallback? onSearchTap;
  final VoidCallback? onMessengerTap;
  final VoidCallback? onMenuTap;
  final int unreadMessagesCount;
  final int unreadNotifsCount;

  const TopNavBar({
    super.key,
    required this.currentIndex,
    required this.onTabSelected,
    this.onAddPostTap,
    this.onSearchTap,
    this.onMessengerTap,
    this.onMenuTap,
    this.unreadMessagesCount = 1,
    this.unreadNotifsCount = 2,
  });

  @override
  Size get preferredSize => const Size.fromHeight(106.0); // 54px header + 52px tab bar

  @override
  Widget build(BuildContext context) {
    return Container(
      color: const Color(0xFF1C1E21), // Facebook Dark Header Theme
      child: SafeArea(
        bottom: false,
        child: Column(
          children: [
            // Top Row: Brand & Action Buttons
            Container(
              height: 52,
              padding: const EdgeInsets.symmetric(horizontal: 14),
              child: Row(
                children: [
                  // Menu drawer button
                  InkWell(
                    onTap: onMenuTap,
                    borderRadius: BorderRadius.circular(20),
                    child: const Padding(
                      padding: EdgeInsets.all(6.0),
                      child: Icon(Icons.menu, color: Colors.white, size: 26),
                    ),
                  ),
                  const SizedBox(width: 8),

                  // Brand Text (Facebook style)
                  ShaderMask(
                    shaderCallback: (bounds) => const LinearGradient(
                      colors: [Color(0xFF38BDF8), Color(0xFF00A8EE), Color(0xFFFFB800)],
                    ).createShader(bounds),
                    child: const Text(
                      'DongAnh',
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                        letterSpacing: -0.5,
                      ),
                    ),
                  ),

                  const Spacer(),

                  // Action Button 1: Add Post (+)
                  _buildHeaderActionButton(
                    icon: Icons.add,
                    onTap: onAddPostTap,
                  ),
                  const SizedBox(width: 8),

                  // Action Button 2: Search (🔍)
                  _buildHeaderActionButton(
                    icon: Icons.search,
                    onTap: onSearchTap,
                  ),
                  const SizedBox(width: 8),

                  // Action Button 3: Messenger (💬) with Red Badge
                  _buildHeaderActionButton(
                    icon: Icons.chat_bubble,
                    badgeCount: unreadMessagesCount,
                    onTap: onMessengerTap,
                  ),
                ],
              ),
            ),

            // Bottom Row: Navigation Tabs (Facebook Style 6-Icon Bar)
            Expanded(
              child: Container(
                decoration: const BoxDecoration(
                  border: Border(
                    bottom: BorderSide(color: Color(0xFF323436), width: 1.0),
                  ),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    // Tab 0: Home - Lướt tin check-in
                    _buildTabItem(
                      index: 0,
                      icon: Icons.home_rounded,
                    ),

                    // Tab 1: Check-in - Đăng & Quản lý check-in
                    _buildTabItem(
                      index: 1,
                      icon: Icons.add_location_alt_rounded,
                    ),

                    // Tab 2: Map - Bản đồ địa điểm
                    _buildTabItem(
                      index: 2,
                      icon: Icons.map_rounded,
                    ),

                    // Tab 3: Marketplace / Chợ OCOP
                    _buildTabItem(
                      index: 3,
                      icon: Icons.storefront_rounded,
                    ),

                    // Tab 4: Notifications (Thông báo)
                    _buildTabItem(
                      index: 4,
                      icon: Icons.notifications_rounded,
                      badgeCount: unreadNotifsCount,
                    ),

                    // Tab 5: Profile / Cá nhân
                    _buildTabItem(
                      index: 5,
                      icon: Icons.account_circle_rounded,
                    ),
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
            decoration: const BoxDecoration(
              color: Color(0xFF3A3B3C), // Facebook circle action button bg
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Icon(icon, color: Colors.white, size: 20),
            ),
          ),
          if (badgeCount > 0)
            Positioned(
              top: -2,
              right: -2,
              child: IgnorePointer(
                child: Container(
                  padding: const EdgeInsets.all(4),
                  decoration: const BoxDecoration(
                    color: Color(0xFFE41E3F), // Facebook notification red badge
                    shape: BoxShape.circle,
                  ),
                  constraints: const BoxConstraints(minWidth: 18, minHeight: 18),
                  child: Center(
                    child: Text(
                      '$badgeCount',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
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
        child: Container(
          decoration: BoxDecoration(
            border: Border(
              bottom: BorderSide(
                color: isSelected ? const Color(0xFF00A8EE) : Colors.transparent,
                width: 3.0,
              ),
            ),
          ),
          child: Center(
            child: Stack(
              clipBehavior: Clip.none,
              children: [
                Icon(
                  icon,
                  size: 26,
                  color: isSelected ? const Color(0xFF00A8EE) : const Color(0xFFB0B3B8),
                ),
                if (badgeCount > 0)
                  Positioned(
                    top: -4,
                    right: -6,
                    child: Container(
                      padding: const EdgeInsets.all(3),
                      decoration: const BoxDecoration(
                        color: Color(0xFFE41E3F),
                        shape: BoxShape.circle,
                      ),
                      constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                      child: Center(
                        child: Text(
                          '$badgeCount',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 9,
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
}
