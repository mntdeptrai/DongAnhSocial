import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'squircle_helper.dart';
import 'road_trip_loader.dart';

export 'road_trip_loader.dart';

/// 🌟 Premium Custom Loader & Skeleton Shimmer Widgets for DongAnh Discovery
class CustomPulseLoader extends StatelessWidget {
  final String message;
  final Color primaryColor;
  final IconData icon;

  const CustomPulseLoader({
    super.key,
    this.message = 'Đang kết nối dữ liệu thực tế...',
    this.primaryColor = const Color(0xFF0EA5E9),
    this.icon = Icons.map_rounded,
  });

  @override
  Widget build(BuildContext context) {
    return RoadTripLoader(
      message: message,
      primaryColor: primaryColor,
    );
  }
}
}

/// 🌊 Sleek 3-Dot Bouncing Wave animation for CTA Buttons
class ButtonDotsLoader extends StatefulWidget {
  final Color color;
  final double size;

  const ButtonDotsLoader({
    super.key,
    this.color = Colors.white,
    this.size = 6.0,
  });

  @override
  State<ButtonDotsLoader> createState() => _ButtonDotsLoaderState();
}

class _ButtonDotsLoaderState extends State<ButtonDotsLoader> with SingleTickerProviderStateMixin {
  late AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1000),
    )..repeat();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        return Row(
          mainAxisSize: MainAxisSize.min,
          children: List.generate(3, (index) {
            final double delay = index * 0.2;
            final double value = math.sin((_controller.value - delay) * math.pi * 2);
            final double offsetY = value * 4.0;

            return Transform.translate(
              offset: Offset(0, offsetY.clamp(-4.0, 4.0)),
              child: Container(
                margin: const EdgeInsets.symmetric(horizontal: 3),
                width: widget.size,
                height: widget.size,
                decoration: BoxDecoration(
                  color: widget.color,
                  shape: BoxShape.circle,
                ),
              ),
            );
          }),
        );
      },
    );
  }
}

/// ✨ Shimmer Skeleton Card Placeholder
class ShimmerCardSkeleton extends StatefulWidget {
  final double height;
  final double radius;

  const ShimmerCardSkeleton({
    super.key,
    this.height = 140,
    this.radius = 20,
  });

  @override
  State<ShimmerCardSkeleton> createState() => _ShimmerCardSkeletonState();
}

class _ShimmerCardSkeletonState extends State<ShimmerCardSkeleton> with SingleTickerProviderStateMixin {
  late AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1500),
    )..repeat();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        final double gradientPosition = _controller.value;

        return Container(
          height: widget.height,
          margin: const EdgeInsets.only(bottom: 14),
          decoration: SquircleHelper.decoration(
            radius: widget.radius,
            gradient: LinearGradient(
              colors: const [
                Color(0xFFE2E8F0),
                Color(0xFFF1F5F9),
                Color(0xFFE2E8F0),
              ],
              stops: const [0.0, 0.5, 1.0],
              begin: Alignment(-1.0 + (gradientPosition * 2.0), -0.3),
              end: Alignment(1.0 + (gradientPosition * 2.0), 0.3),
            ),
          ),
        );
      },
    );
  }
}
