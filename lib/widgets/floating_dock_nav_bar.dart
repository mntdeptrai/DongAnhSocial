import 'dart:ui';
import 'package:flutter/material.dart';

class FloatingDockNavBar extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTabSelected;
  final VoidCallback? onCreateTap;

  const FloatingDockNavBar({
    super.key,
    required this.currentIndex,
    required this.onTabSelected,
    this.onCreateTap,
  });

  @override
  Widget build(BuildContext context) {
    const primaryColor = Color(0xFF0EA5E9);
    const unselectedColor = Color(0xFF64748B);

    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(36),
          child: BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
            child: Container(
              height: 64,
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.92),
                borderRadius: BorderRadius.circular(36),
                border: Border.all(
                  color: Colors.black.withValues(alpha: 0.06),
                  width: 1.0,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.08),
                    blurRadius: 20,
                    offset: const Offset(0, 6),
                  ),
                  BoxShadow(
                    color: primaryColor.withValues(alpha: 0.06),
                    blurRadius: 10,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Row(
                children: [
                  // Tab 0: Trang chủ
                  _buildNavItem(
                    index: 0,
                    icon: Icons.home_rounded,
                    label: 'Trang chủ',
                    isSelected: currentIndex == 0,
                    primaryColor: primaryColor,
                    unselectedColor: unselectedColor,
                  ),

                  // Tab 1: Bản đồ
                  _buildNavItem(
                    index: 1,
                    icon: Icons.explore_rounded,
                    label: 'Bản đồ',
                    isSelected: currentIndex == 1,
                    primaryColor: primaryColor,
                    unselectedColor: unselectedColor,
                  ),

                  // Center Tab 2: Nút [+] Tạo mới nổi bật
                  Expanded(
                    child: GestureDetector(
                      onTap: () {
                        if (onCreateTap != null) {
                          onCreateTap!();
                        } else {
                          onTabSelected(2);
                        }
                      },
                      behavior: HitTestBehavior.opaque,
                      child: Center(
                        child: Container(
                          width: 46,
                          height: 46,
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFF0EA5E9), Color(0xFF0284C7)],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF0EA5E9).withValues(alpha: 0.38),
                                blurRadius: 12,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: const Icon(
                            Icons.add_rounded,
                            color: Colors.white,
                            size: 28,
                          ),
                        ),
                      ),
                    ),
                  ),

                  // Tab 3: Chợ OCOP
                  _buildNavItem(
                    index: 3,
                    icon: Icons.storefront_rounded,
                    label: 'Chợ OCOP',
                    isSelected: currentIndex == 3,
                    primaryColor: primaryColor,
                    unselectedColor: unselectedColor,
                  ),

                  // Tab 4: Cá nhân
                  _buildNavItem(
                    index: 4,
                    icon: Icons.person_rounded,
                    label: 'Cá nhân',
                    isSelected: currentIndex == 4,
                    primaryColor: primaryColor,
                    unselectedColor: unselectedColor,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem({
    required int index,
    required IconData icon,
    required String label,
    required bool isSelected,
    required Color primaryColor,
    required Color unselectedColor,
  }) {
    return Expanded(
      child: GestureDetector(
        onTap: () => onTabSelected(index),
        behavior: HitTestBehavior.opaque,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          curve: Curves.easeInOut,
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              AnimatedScale(
                scale: isSelected ? 1.12 : 1.0,
                duration: const Duration(milliseconds: 200),
                child: Icon(
                  icon,
                  size: 22,
                  color: isSelected ? primaryColor : unselectedColor,
                ),
              ),
              const SizedBox(height: 3),
              Text(
                label,
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                  color: isSelected ? primaryColor : unselectedColor,
                  letterSpacing: -0.2,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
