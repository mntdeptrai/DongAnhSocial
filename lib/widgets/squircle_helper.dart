import 'package:flutter/material.dart';

/// Helper utility for creating smooth rounded corner shapes across the mobile app
class SquircleHelper {
  /// Returns a BorderRadius with smooth corner curvature
  static BorderRadius radius(double radius, {double cornerSmoothing = 0.6}) {
    return BorderRadius.circular(radius);
  }

  /// Returns a RoundedRectangleBorder shape for Cards, Dialogs, and Buttons
  static RoundedRectangleBorder shape({
    required double radius,
    double cornerSmoothing = 0.6,
    BorderSide side = BorderSide.none,
  }) {
    return RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(radius),
      side: side,
    );
  }

  /// Returns a BoxDecoration for Containers using smooth rounded corners
  static BoxDecoration decoration({
    required double radius,
    Color? color,
    Gradient? gradient,
    List<BoxShadow>? boxShadow,
    BorderSide borderSide = BorderSide.none,
    double cornerSmoothing = 0.6,
  }) {
    return BoxDecoration(
      color: color,
      gradient: gradient,
      boxShadow: boxShadow,
      borderRadius: BorderRadius.circular(radius),
      border: borderSide != BorderSide.none ? Border.fromBorderSide(borderSide) : null,
    );
  }
}
