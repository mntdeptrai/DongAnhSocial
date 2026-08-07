import 'dart:ui';
import 'package:flutter/material.dart';

class FloatingDockNavBar extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTabSelected;
  final int unreadNotifsCount;

  const FloatingDockNavBar({
    super.key,
    required this.currentIndex,
    required this.onTabSelected,
    this.unreadNotifsCount = 0,
  });

  @override
  Widget build(BuildContext context) {
    final navItems = [
      {'icon': Icons.grid_view_rounded, 'label': 'Feed'},
      {'icon': Icons.camera_alt_rounded, 'label': 'Check-in'},
      {'icon': Icons.map_rounded, 'label': 'Bản đồ'},
      {'icon': Icons.storefront_rounded, 'label': 'Chợ OCOP'},
      {'icon': Icons.notifications_rounded, 'label': 'Thông báo', 'badge': unreadNotifsCount},
      {'icon': Icons.person_rounded, 'label': 'Cá nhân'},
    ];

    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(30),
          child: BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
            child: Container(
              height: 64,
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFF0F172A).withValues(alpha: 0.88),
                borderRadius: BorderRadius.circular(30),
                border: Border.all(
                  color: Colors.white.withValues(alpha: 0.15),
                  width: 1.2,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.25),
                    blurRadius: 24,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: List.generate(navItems.length, (index) {
                  final isSelected = currentIndex == index;
                  final item = navItems[index];
                  final IconData iconData = item['icon'] as IconData;
                  final int badge = (item['badge'] as int?) ?? 0;

                  return Expanded(
                    child: GestureDetector(
                      onTap: () => onTabSelected(index),
                      behavior: HitTestBehavior.opaque,
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 250),
                        curve: Curves.easeOutCubic,
                        padding: const EdgeInsets.symmetric(vertical: 6),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? const Color(0xFF0EA5E9).withValues(alpha: 0.22)
                              : Colors.transparent,
                          borderRadius: BorderRadius.circular(22),
                          border: isSelected
                              ? Border.all(color: const Color(0xFF0EA5E9).withValues(alpha: 0.6), width: 1)
                              : null,
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Stack(
                              clipBehavior: Clip.none,
                              children: [
                                Icon(
                                  iconData,
                                  size: isSelected ? 22 : 20,
                                  color: isSelected
                                      ? const Color(0xFF38BDF8)
                                      : Colors.white.withValues(alpha: 0.55),
                                ),
                                if (badge > 0)
                                  Positioned(
                                    top: -4,
                                    right: -6,
                                    child: Container(
                                      padding: const EdgeInsets.all(2),
                                      decoration: const BoxDecoration(
                                        color: Color(0xFFEF4444),
                                        shape: BoxShape.circle,
                                      ),
                                      constraints: const BoxConstraints(minWidth: 12, minHeight: 12),
                                      child: Center(
                                        child: Text(
                                          '$badge',
                                          style: const TextStyle(
                                            color: Colors.white,
                                            fontSize: 7,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                            const SizedBox(height: 2),
                            Text(
                              item['label'] as String,
                              style: TextStyle(
                                fontSize: 9,
                                fontWeight: isSelected ? FontWeight.w800 : FontWeight.w500,
                                color: isSelected
                                    ? const Color(0xFF38BDF8)
                                    : Colors.white.withValues(alpha: 0.55),
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],
                        ),
                      ),
                    ),
                  );
                }),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
