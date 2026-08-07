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
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(32),
          child: BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 28, sigmaY: 28),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 250),
              height: 58,
              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 4),
              decoration: BoxDecoration(
                // iOS Crystal Translucent Glass
                color: Colors.white.withValues(alpha: 0.38),
                borderRadius: BorderRadius.circular(32),
                border: Border.all(
                  color: Colors.white.withValues(alpha: 0.75),
                  width: 1.5,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.08),
                    blurRadius: 24,
                    spreadRadius: 0,
                    offset: const Offset(0, 8),
                  ),
                  BoxShadow(
                    color: const Color(0xFF0EA5E9).withValues(alpha: 0.15),
                    blurRadius: 16,
                    spreadRadius: -4,
                    offset: const Offset(0, 4),
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
                        duration: const Duration(milliseconds: 260),
                        curve: Curves.easeOutCubic,
                        margin: const EdgeInsets.symmetric(horizontal: 1.5, vertical: 1),
                        padding: const EdgeInsets.symmetric(vertical: 4),
                        decoration: BoxDecoration(
                          gradient: isSelected
                              ? const LinearGradient(
                                  colors: [Color(0xFF0EA5E9), Color(0xFF0284C7)],
                                  begin: Alignment.topCenter,
                                  end: Alignment.bottomCenter,
                                )
                              : null,
                          color: isSelected ? null : Colors.transparent,
                          borderRadius: BorderRadius.circular(24),
                          boxShadow: isSelected
                              ? [
                                  BoxShadow(
                                    color: const Color(0xFF0EA5E9).withValues(alpha: 0.4),
                                    blurRadius: 10,
                                    offset: const Offset(0, 3),
                                  ),
                                ]
                              : null,
                          border: isSelected
                              ? Border.all(color: Colors.white.withValues(alpha: 0.4), width: 1)
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
                                  size: isSelected ? 20 : 18,
                                  color: isSelected
                                      ? Colors.white
                                      : const Color(0xFF1E293B),
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
                            const SizedBox(height: 1),
                            FittedBox(
                              fit: BoxFit.scaleDown,
                              child: Text(
                                item['label'] as String,
                                style: TextStyle(
                                  fontSize: 9,
                                  fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                                  color: isSelected
                                      ? Colors.white
                                      : const Color(0xFF1E293B),
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
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
