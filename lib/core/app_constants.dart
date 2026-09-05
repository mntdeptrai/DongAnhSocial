/// Tập trung tất cả hằng số ứng dụng — URL, màu sắc, kích thước.
/// Import file này thay vì hardcode giá trị rải rác.
import 'package:flutter/material.dart';

class AppConstants {
  AppConstants._();

  // ── URLs ──────────────────────────────────────────────────────────────
  static const String baseHost = 'donganhdiscovery.xadonganh.com';
  static const String baseUrl = 'https://$baseHost';
  static const String apiBaseUrl = '$baseUrl/api/v1';

  // ── Colors ────────────────────────────────────────────────────────────
  static const Color primaryColor = Color(0xFF0EA5E9);
  static const Color accentColor = Color(0xFF06B6D4);
  static const Color surfaceColor = Color(0xFFF8FAFC);
  static const Color darkColor = Color(0xFF0F172A);
  static const Color cardBorderColor = Color(0x1F0EA5E9);

  // ── Fonts ─────────────────────────────────────────────────────────────
  static const String fontFamily = 'Plus Jakarta Sans';
}
