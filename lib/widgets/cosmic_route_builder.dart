import 'package:flutter/material.dart';

/// 🌌 Cosmic Custom Route Page Transition Builder
/// Hiệu ứng chuyển trang siêu cấp vũ trụ với Fade + Scale Zoom + Slide Smooth Curve
class CosmicRouteBuilder<T> extends PageRouteBuilder<T> {
  final Widget page;

  CosmicRouteBuilder({required this.page})
      : super(
          pageBuilder: (context, animation, secondaryAnimation) => page,
          transitionDuration: const Duration(milliseconds: 400),
          reverseTransitionDuration: const Duration(milliseconds: 350),
          transitionsBuilder: (context, animation, secondaryAnimation, child) {
            final curveAnimation = CurvedAnimation(
              parent: animation,
              curve: Curves.easeOutBack,
              reverseCurve: Curves.easeInCubic,
            );

            return FadeTransition(
              opacity: animation,
              child: ScaleTransition(
                scale: Tween<double>(begin: 0.92, end: 1.0).animate(curveAnimation),
                child: SlideTransition(
                  position: Tween<Offset>(
                    begin: const Offset(0.04, 0.04),
                    end: Offset.zero,
                  ).animate(curveAnimation),
                  child: child,
                ),
              ),
            );
          },
        );
}
