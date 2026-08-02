import 'package:flutter/material.dart';
import 'package:figma_squircle/figma_squircle.dart';

/// Helper utility for creating Figma/iOS-style Squircle smooth corner shapes in Flutter
class SquircleHelper {
  /// Returns a SmoothBorderRadius with Figma continuous curvature (default smoothing 0.6)
  static SmoothBorderRadius radius(double radius, {double cornerSmoothing = 0.6}) {
    return SmoothBorderRadius(
      cornerRadius: radius,
      cornerSmoothing: cornerSmoothing,
    );
  }

  /// Returns a SmoothRectangleBorder shape for Cards, Dialogs, and Buttons
  static SmoothRectangleBorder shape({
    required double radius,
    double cornerSmoothing = 0.6,
    BorderSide side = BorderSide.none,
  }) {
    return SmoothRectangleBorder(
      borderRadius: SmoothBorderRadius(
        cornerRadius: radius,
        cornerSmoothing: cornerSmoothing,
      ),
      side: side,
    );
  }

  /// Returns a ShapeDecoration for Containers using Squircle corners
  static ShapeDecoration decoration({
    required double radius,
    Color? color,
    Gradient? gradient,
    List<BoxShadow>? boxShadow,
    BorderSide borderSide = BorderSide.none,
    double cornerSmoothing = 0.6,
  }) {
    return ShapeDecoration(
      color: color,
      gradient: gradient,
      shadows: boxShadow,
      shape: SmoothRectangleBorder(
        borderRadius: SmoothBorderRadius(
          cornerRadius: radius,
          cornerSmoothing: cornerSmoothing,
        ),
        side: borderSide,
      ),
    );
  }
}
