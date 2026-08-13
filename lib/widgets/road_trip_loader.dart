import 'dart:math' as math;
import 'package:flutter/material.dart';

/// 🌄☀️🌇🌙 Time-of-Day Period Enum for RoadTripLoader
enum RoadTripTimePeriod {
  morning,   // 05:00 - 10:59 (Bình minh rạng rỡ - Amber Gold & Sky Cyan)
  noon,      // 11:00 - 13:59 (Trưa nắng vàng - Bright Azure & Solar Yellow)
  afternoon, // 14:00 - 17:59 (Hoàng hôn lãng mạn - Sunset Magenta & Orange Coral)
  night,     // 18:00 - 04:59 (Đêm lung linh ngàn sao - Cosmic Midnight Indigo & Neon Purple)
}

/// 🛵💨 Ultra Premium Road Trip Loader — Phượt Vi Vu Đông Anh Theo Khung Giờ 2026
class RoadTripLoader extends StatefulWidget {
  final String? message;
  final Color? primaryColor;
  final DateTime? forcedTime; // Hỗ trợ test hoặc ép khung giờ cụ thể

  const RoadTripLoader({
    super.key,
    this.message,
    this.primaryColor,
    this.forcedTime,
  });

  @override
  State<RoadTripLoader> createState() => _RoadTripLoaderState();
}

class _RoadTripLoaderState extends State<RoadTripLoader> with TickerProviderStateMixin {
  late AnimationController _controller;
  late AnimationController _pulseController;
  late Animation<double> _pulseAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    )..repeat();

    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 2000),
    )..repeat(reverse: true);

    _pulseAnimation = Tween<double>(begin: 0.96, end: 1.04).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    _pulseController.dispose();
    super.dispose();
  }

  RoadTripTimePeriod _getPeriod(DateTime now) {
    final hour = now.hour;
    if (hour >= 5 && hour < 11) {
      return RoadTripTimePeriod.morning;
    } else if (hour >= 11 && hour < 14) {
      return RoadTripTimePeriod.noon;
    } else if (hour >= 14 && hour < 18) {
      return RoadTripTimePeriod.afternoon;
    } else {
      return RoadTripTimePeriod.night;
    }
  }

  @override
  Widget build(BuildContext context) {
    final now = widget.forcedTime ?? DateTime.now();
    final period = _getPeriod(now);
    final theme = _RoadTripTheme.fromPeriod(period, overridePrimary: widget.primaryColor);

    final String activeMessage = widget.message ??
        theme.quotes[(now.second ~/ 3) % theme.quotes.length];

    return Center(
      child: ScaleTransition(
        scale: _pulseAnimation,
        child: Container(
          padding: const EdgeInsets.all(24),
          margin: const EdgeInsets.symmetric(horizontal: 24),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: theme.gradientColors,
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(32),
            border: Border.all(color: theme.accentColor.withValues(alpha: theme.isDark ? 0.5 : 0.6), width: 1.5),
            boxShadow: [
              BoxShadow(
                color: theme.accentColor.withValues(alpha: theme.isDark ? 0.38 : 0.25),
                blurRadius: 36,
                spreadRadius: 2,
                offset: const Offset(0, 12),
              ),
              BoxShadow(
                color: theme.isDark ? Colors.black.withValues(alpha: 0.55) : Colors.black.withValues(alpha: 0.08),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Top Glowing Time-of-Day Pill Badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                decoration: BoxDecoration(
                  color: theme.badgeBgColor,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: theme.accentColor.withValues(alpha: 0.5)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 8,
                      height: 8,
                      decoration: BoxDecoration(
                        color: theme.statusDotColor,
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(
                            color: theme.statusDotColor.withValues(alpha: 0.8),
                            blurRadius: 6,
                            spreadRadius: 1,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      theme.badgeText,
                      style: TextStyle(
                        color: theme.badgeTextColor,
                        fontSize: 11,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 0.8,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 14),

              // Custom Painter Canvas for Road, Celestial Sky & Vespa Scooter
              SizedBox(
                width: 240,
                height: 125,
                child: AnimatedBuilder(
                  animation: _controller,
                  builder: (context, child) {
                    return CustomPaint(
                      painter: _RoadTripPainter(
                        progress: _controller.value,
                        period: period,
                        theme: theme,
                      ),
                    );
                  },
                ),
              ),

              const SizedBox(height: 16),

              // Animated Travel Message Banner
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                decoration: BoxDecoration(
                  color: theme.bannerBgColor,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: theme.accentColor.withValues(alpha: 0.3)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: theme.isDark ? 0.2 : 0.05),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Text(
                  activeMessage,
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: theme.textColor,
                    height: 1.3,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// 🎨 Time-of-Day Visual Theme Configuration
class _RoadTripTheme {
  final List<Color> gradientColors;
  final Color accentColor;
  final Color badgeTextColor;
  final Color badgeBgColor;
  final Color statusDotColor;
  final Color textColor;
  final Color bannerBgColor;
  final Color skyBgColor;
  final bool isDark;
  final String badgeText;
  final List<String> quotes;

  _RoadTripTheme({
    required this.gradientColors,
    required this.accentColor,
    required this.badgeTextColor,
    required this.badgeBgColor,
    required this.statusDotColor,
    required this.textColor,
    required this.bannerBgColor,
    required this.skyBgColor,
    required this.isDark,
    required this.badgeText,
    required this.quotes,
  });

  factory _RoadTripTheme.fromPeriod(RoadTripTimePeriod period, {Color? overridePrimary}) {
    switch (period) {
      case RoadTripTimePeriod.morning:
        final accent = overridePrimary ?? const Color(0xFFD97706);
        return _RoadTripTheme(
          gradientColors: const [Color(0xFFFFFBEB), Color(0xFFFEF3C7), Color(0xFFFDE68A)],
          accentColor: accent,
          badgeTextColor: const Color(0xFF92400E),
          badgeBgColor: const Color(0xFFFEF3C7),
          statusDotColor: const Color(0xFF10B981),
          textColor: const Color(0xFF1E293B),
          bannerBgColor: Colors.white.withValues(alpha: 0.8),
          skyBgColor: const Color(0xFFBAE6FD).withValues(alpha: 0.4),
          isDark: false,
          badgeText: '🌅 BÌNH MINH • ĐÔNG ANH LIVE',
          quotes: const [
            '🌅 Bình minh rực rỡ, phượt vi vu Đông Anh...',
            '🍲 Ghé ăn bún mạch Trạ & bánh chưng Lỗ Khê...',
            '🏰 Đang băng qua cổng thành Cổ Loa sáng sớm...',
            '🌾 Khám phá Chợ số & Nông sản OCOP Đông Anh...',
            '✨ Đang nạp dữ liệu chuyến đi ngày mới...',
          ],
        );

      case RoadTripTimePeriod.noon:
        final accent = overridePrimary ?? const Color(0xFF0284C7);
        return _RoadTripTheme(
          gradientColors: const [Color(0xFFF0F9FF), Color(0xFFE0F2FE), Color(0xFFBAE6FD)],
          accentColor: accent,
          badgeTextColor: const Color(0xFF0369A1),
          badgeBgColor: const Color(0xFFE0F2FE),
          statusDotColor: const Color(0xFFFACC15),
          textColor: const Color(0xFF0F172A),
          bannerBgColor: Colors.white.withValues(alpha: 0.85),
          skyBgColor: const Color(0xFF7DD3FC).withValues(alpha: 0.35),
          isDark: false,
          badgeText: '☀️ NẮNG TRƯA • ĐÔNG ANH LIVE',
          quotes: const [
            '☀️ Nắng trưa rạng rỡ ghé đầm sen thưởng trà...',
            '🍲 Trạm dừng chân ẩm thực sinh thái Cổ Loa...',
            '🛵 Vi vu dạo quanh làng nghề mây tre đan...',
            '🌾 Sắp tới gian hàng OCOP Đông Anh uy tín...',
            '✨ Đang tối ưu kết nối dữ liệu tốc độ cao...',
          ],
        );

      case RoadTripTimePeriod.afternoon:
        final accent = overridePrimary ?? const Color(0xFFE11D48);
        return _RoadTripTheme(
          gradientColors: const [Color(0xFFFFF1F2), Color(0xFFFCE7F3), Color(0xFFFBCFE8)],
          accentColor: accent,
          badgeTextColor: const Color(0xFF9F1239),
          badgeBgColor: const Color(0xFFFFE4E6),
          statusDotColor: const Color(0xFFFB923C),
          textColor: const Color(0xFF1E293B),
          bannerBgColor: Colors.white.withValues(alpha: 0.85),
          skyBgColor: const Color(0xFFFDBA74).withValues(alpha: 0.35),
          isDark: false,
          badgeText: '🌇 HOÀNG HÔN • ĐÔNG ANH LIVE',
          quotes: const [
            '🌇 Hoàng hôn tuyệt đẹp trên sông Hoàng Giang...',
            '📸 Check-in sống ảo hoàng hôn thành Cổ Loa...',
            '☕ Thưởng trà chiều tại không gian sinh thái...',
            '🛵 Lướt nhẹ qua những con đường rợp bóng cây...',
            '✨ Đang tải nhanh danh sách điểm đến hoàng hôn...',
          ],
        );

      case RoadTripTimePeriod.night:
        final accent = overridePrimary ?? const Color(0xFFA855F7);
        return _RoadTripTheme(
          gradientColors: const [Color(0xFF0F172A), Color(0xFF1E1B4B), Color(0xFF312E81)],
          accentColor: accent,
          badgeTextColor: const Color(0xFFE9D5FF),
          badgeBgColor: const Color(0xFFA855F7).withValues(alpha: 0.2),
          statusDotColor: const Color(0xFFA855F7),
          textColor: Colors.white,
          bannerBgColor: Colors.white.withValues(alpha: 0.1),
          skyBgColor: Colors.transparent,
          isDark: true,
          badgeText: '🌙 ĐÊM PHỐ CỔ • ĐÔNG ANH LIVE',
          quotes: const [
            '🌙 Đêm phố cổ lung linh ánh đèn huyền ảo...',
            '🍢 Khám phá ẩm thực đêm & đồ nướng Cổ Loa...',
            '✨ Dạo bước dưới bầu trời ngàn sao Đông Anh...',
            '🎧 Vi vu phố đêm chill cùng âm nhạc...',
            '🚀 Đang truyền dữ liệu siêu tốc 4K về đêm...',
          ],
        );
    }
  }
}

class _RoadTripPainter extends CustomPainter {
  final double progress;
  final RoadTripTimePeriod period;
  final _RoadTripTheme theme;

  _RoadTripPainter({
    required this.progress,
    required this.period,
    required this.theme,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final double w = size.width;
    final double h = size.height;

    // 1. Draw Glowing Background Neon Aura
    final auraPaint = Paint()
      ..color = theme.accentColor.withValues(alpha: 0.18)
      ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 22);
    canvas.drawCircle(Offset(w / 2, h / 2), 65, auraPaint);

    // 2. Draw Celestial Sky Objects (Sun / Moon / Stars / Rays)
    _drawSkyElements(canvas, size);

    // 3. Draw Cloud Silhouettes
    final cloudColor = period == RoadTripTimePeriod.afternoon
        ? const Color(0xFFF472B6).withValues(alpha: 0.25)
        : (period == RoadTripTimePeriod.night
            ? Colors.white.withValues(alpha: 0.08)
            : Colors.white.withValues(alpha: 0.2));

    final cloudPaint = Paint()
      ..color = cloudColor
      ..style = PaintingStyle.fill;

    for (int i = 0; i < 3; i++) {
      double cloudX = ((i * 95) - (progress * 45)) % (w + 40) - 20;
      double cloudY = 18.0 + (i * 7);
      canvas.drawCircle(Offset(cloudX, cloudY), 11, cloudPaint);
      canvas.drawCircle(Offset(cloudX + 8, cloudY - 4), 9, cloudPaint);
      canvas.drawCircle(Offset(cloudX + 15, cloudY), 10, cloudPaint);
    }

    // 4. Draw Background Heritage Monuments / Ancient Trees
    final treeColor = period == RoadTripTimePeriod.night
        ? const Color(0xFF1E293B)
        : const Color(0xFF334155);

    final treePaint = Paint()
      ..color = treeColor
      ..style = PaintingStyle.fill;

    for (int i = 0; i < 4; i++) {
      double treeX = ((i * 75) - (progress * 100)) % (w + 40) - 20;
      double treeY = h - 26;
      canvas.drawRect(Rect.fromLTWH(treeX + 6, treeY - 16, 4, 16), treePaint);
      canvas.drawCircle(Offset(treeX + 8, treeY - 22), 11, treePaint);
    }

    // 5. Draw Road Base
    final roadPaint = Paint()
      ..color = const Color(0xFF1E293B)
      ..style = PaintingStyle.fill;
    canvas.drawRRect(
      RRect.fromRectAndRadius(Rect.fromLTWH(0, h - 24, w, 20), const Radius.circular(10)),
      roadPaint,
    );

    // Glowing Road Line Header Accent
    canvas.drawLine(
      Offset(0, h - 24),
      Offset(w, h - 24),
      Paint()
        ..color = theme.accentColor.withValues(alpha: 0.7)
        ..strokeWidth = 2,
    );

    // Animated Dashed Golden Lane Line
    final dashColor = period == RoadTripTimePeriod.night
        ? const Color(0xFFA855F7)
        : const Color(0xFFF59E0B);

    final dashPaint = Paint()
      ..color = dashColor
      ..strokeWidth = 3
      ..strokeCap = StrokeCap.round;

    double dashOffset = (progress * 44) % 24;
    for (double x = -dashOffset; x < w; x += 24) {
      if (x + 12 > 0 && x < w) {
        canvas.drawLine(Offset(x, h - 14), Offset(x + 12, h - 14), dashPaint);
      }
    }

    // 6. Vehicle Bouncing Motion (Simulating riding dynamics)
    final double bounceY = math.sin(progress * math.pi * 6) * 2.5;
    final double scooterX = (w / 2) - 34;
    final double scooterY = h - 40 + bounceY;

    // Speed Blur Trail Lines behind Vespa
    final speedTrailPaint = Paint()
      ..color = theme.accentColor.withValues(alpha: 0.45)
      ..strokeWidth = 2
      ..strokeCap = StrokeCap.round;

    for (int i = 0; i < 4; i++) {
      double lineX = scooterX - 8 - (i * 12) - ((progress * 30) % 15);
      double lineY = scooterY + 6 + (i * 4);
      canvas.drawLine(Offset(lineX, lineY), Offset(lineX - 14, lineY), speedTrailPaint);
    }

    // 7. Draw Scooter Wheels with Rotating Spokes
    final wheelPaint = Paint()
      ..color = const Color(0xFF0F172A)
      ..style = PaintingStyle.fill;
    final rimPaint = Paint()
      ..color = theme.accentColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.5;

    const double wheelRadius = 10;
    final Offset frontWheel = Offset(scooterX + 48, scooterY + 14);
    final Offset backWheel = Offset(scooterX + 10, scooterY + 14);

    canvas.drawCircle(frontWheel, wheelRadius, wheelPaint);
    canvas.drawCircle(backWheel, wheelRadius, wheelPaint);
    canvas.drawCircle(frontWheel, wheelRadius - 2, rimPaint);
    canvas.drawCircle(backWheel, wheelRadius - 2, rimPaint);

    // Spokes Rotation
    final double angle = progress * math.pi * 8;
    for (int i = 0; i < 4; i++) {
      double a = angle + (i * math.pi / 2);
      canvas.drawLine(
        frontWheel,
        Offset(frontWheel.dx + math.cos(a) * 7, frontWheel.dy + math.sin(a) * 7),
        rimPaint..strokeWidth = 1.5,
      );
      canvas.drawLine(
        backWheel,
        Offset(backWheel.dx + math.cos(a) * 7, backWheel.dy + math.sin(a) * 7),
        rimPaint..strokeWidth = 1.5,
      );
    }

    // 8. Draw Vespa Body
    final bodyPaint = Paint()
      ..color = theme.accentColor
      ..style = PaintingStyle.fill;

    final bodyPath = Path()
      ..moveTo(scooterX + 2, scooterY + 9)
      ..cubicTo(scooterX + 2, scooterY - 2, scooterX + 18, scooterY - 4, scooterX + 28, scooterY + 5)
      ..lineTo(scooterX + 40, scooterY + 7)
      ..lineTo(scooterX + 48, scooterY - 10)
      ..lineTo(scooterX + 44, scooterY + 9)
      ..close();
    canvas.drawPath(bodyPath, bodyPaint);

    // Headlight Lens Beam (Stronger beam at night & afternoon)
    final double beamOpacity = period == RoadTripTimePeriod.night ? 0.55 : 0.35;
    final headlightBeamPaint = Paint()
      ..color = const Color(0xFFFEF08A).withValues(alpha: beamOpacity)
      ..style = PaintingStyle.fill;

    final beamPath = Path()
      ..moveTo(scooterX + 48, scooterY - 14)
      ..lineTo(w, scooterY - 24)
      ..lineTo(w, scooterY + 10)
      ..close();
    canvas.drawPath(beamPath, headlightBeamPaint);

    final headlightPaint = Paint()..color = const Color(0xFFFEF08A);
    canvas.drawCircle(Offset(scooterX + 48, scooterY - 14), 4.5, headlightPaint);

    // 9. Draw Group of 3 Friends Riding Vespa (Colors adapt dynamically)
    _drawFriend(
      canvas: canvas,
      center: Offset(scooterX + 6, scooterY - 18),
      shirtColor: const Color(0xFFEC4899), // Pink
      helmetColor: const Color(0xFFF43F5E),
      isWaving: true,
      armProgress: progress,
    );

    _drawFriend(
      canvas: canvas,
      center: Offset(scooterX + 20, scooterY - 21),
      shirtColor: const Color(0xFF10B981), // Emerald Green
      helmetColor: const Color(0xFF059669),
      isWaving: false,
      armProgress: progress,
    );

    _drawFriend(
      canvas: canvas,
      center: Offset(scooterX + 34, scooterY - 23),
      shirtColor: period == RoadTripTimePeriod.night ? const Color(0xFF8B5CF6) : const Color(0xFFF59E0B),
      helmetColor: period == RoadTripTimePeriod.night ? const Color(0xFF7C3AED) : const Color(0xFFD97706),
      isWaving: false,
      armProgress: progress,
    );

    // 10. Floating Animated Destination Pin Overhead
    double pinY = 18 + math.sin(progress * math.pi * 4) * 3;
    final pinPaint = Paint()..color = const Color(0xFFEF4444);
    canvas.drawCircle(Offset(w / 2 + 30, pinY), 7, pinPaint);
    final pinPath = Path()
      ..moveTo(w / 2 + 24, pinY + 2)
      ..lineTo(w / 2 + 30, pinY + 14)
      ..lineTo(w / 2 + 36, pinY + 2)
      ..close();
    canvas.drawPath(pinPath, pinPaint);
    canvas.drawCircle(Offset(w / 2 + 30, pinY), 3, Paint()..color = Colors.white);
  }

  /// Draw Sky Celestial Elements (Sunrise, High Sun, Sunset, Night Moon & Constellations)
  void _drawSkyElements(Canvas canvas, Size size) {
    final double w = size.width;

    switch (period) {
      case RoadTripTimePeriod.morning:
        // Rising Golden Sun with Rays 🌅
        final sunCenter = Offset(w - 36, 26);
        final sunGlow = Paint()
          ..color = const Color(0xFFF59E0B).withValues(alpha: 0.3)
          ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 12);
        canvas.drawCircle(sunCenter, 18, sunGlow);

        final sunPaint = Paint()..color = const Color(0xFFFBBF24);
        canvas.drawCircle(sunCenter, 11, sunPaint);

        // Sun Rays
        final rayPaint = Paint()
          ..color = const Color(0xFFFDE68A).withValues(alpha: 0.6)
          ..strokeWidth = 1.5;
        for (int i = 0; i < 8; i++) {
          double a = (i * math.pi / 4) + (progress * math.pi / 6);
          canvas.drawLine(
            Offset(sunCenter.dx + math.cos(a) * 14, sunCenter.dy + math.sin(a) * 14),
            Offset(sunCenter.dx + math.cos(a) * 19, sunCenter.dy + math.sin(a) * 19),
            rayPaint,
          );
        }
        break;

      case RoadTripTimePeriod.noon:
        // High Radiant Sun ☀️
        final sunCenter = Offset(w - 42, 22);
        final sunGlow = Paint()
          ..color = const Color(0xFFFACC15).withValues(alpha: 0.4)
          ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 16);
        canvas.drawCircle(sunCenter, 20, sunGlow);

        final sunPaint = Paint()..color = const Color(0xFFFACC15);
        canvas.drawCircle(sunCenter, 12, sunPaint);

        // Spinning Sunbeams
        final rayPaint = Paint()
          ..color = const Color(0xFFFEF08A).withValues(alpha: 0.8)
          ..strokeWidth = 2
          ..strokeCap = StrokeCap.round;

        final rayAngle = progress * math.pi * 2;
        for (int i = 0; i < 8; i++) {
          double a = rayAngle + (i * math.pi / 4);
          canvas.drawLine(
            Offset(sunCenter.dx + math.cos(a) * 15, sunCenter.dy + math.sin(a) * 15),
            Offset(sunCenter.dx + math.cos(a) * 21, sunCenter.dy + math.sin(a) * 21),
            rayPaint,
          );
        }
        break;

      case RoadTripTimePeriod.afternoon:
        // Sunset Crimson Sun 🌇
        final sunCenter = Offset(w - 40, 32);
        final sunGlow = Paint()
          ..color = const Color(0xFFF43F5E).withValues(alpha: 0.35)
          ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 14);
        canvas.drawCircle(sunCenter, 18, sunGlow);

        final sunPaint = Paint()..color = const Color(0xFFFB923C);
        canvas.drawCircle(sunCenter, 11, sunPaint);

        // Sunset Horizon Glow Lines
        final glowLinePaint = Paint()
          ..color = const Color(0xFFF43F5E).withValues(alpha: 0.3)
          ..strokeWidth = 1.5;
        canvas.drawLine(Offset(sunCenter.dx - 22, sunCenter.dy + 8), Offset(sunCenter.dx + 22, sunCenter.dy + 8), glowLinePaint);
        canvas.drawLine(Offset(sunCenter.dx - 16, sunCenter.dy + 12), Offset(sunCenter.dx + 16, sunCenter.dy + 12), glowLinePaint);
        break;

      case RoadTripTimePeriod.night:
        // Crescent Moon 🌙
        final moonCenter = Offset(w - 36, 24);
        final moonGlow = Paint()
          ..color = const Color(0xFFA855F7).withValues(alpha: 0.35)
          ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 14);
        canvas.drawCircle(moonCenter, 16, moonGlow);

        final moonPaint = Paint()..color = const Color(0xFFFEF08A);
        canvas.drawCircle(moonCenter, 10, moonPaint);

        // Cutout for crescent shape
        canvas.drawCircle(Offset(moonCenter.dx - 4, moonCenter.dy - 3), 9, Paint()..color = const Color(0xFF0F172A));

        // Twinkling Night Stars & Constellations ⭐
        final starPaint = Paint()..color = Colors.white;
        for (int i = 0; i < 7; i++) {
          double starX = ((i * 38) - (progress * 50)) % (w + 20) - 10;
          double starY = 8.0 + (i * 5) % 28;
          double alpha = (0.4 + math.sin((progress * math.pi * 4) + i) * 0.4).clamp(0.2, 1.0);
          canvas.drawCircle(Offset(starX, starY), 1.5, starPaint..color = Colors.white.withValues(alpha: alpha));
        }
        break;
    }
  }

  void _drawFriend({
    required Canvas canvas,
    required Offset center,
    required Color shirtColor,
    required Color helmetColor,
    required bool isWaving,
    required double armProgress,
  }) {
    // Body / Shirt
    final bodyPaint = Paint()..color = shirtColor;
    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromCenter(center: Offset(center.dx, center.dy + 7), width: 14, height: 15),
        const Radius.circular(5),
      ),
      bodyPaint,
    );

    // Face Skin
    final facePaint = Paint()..color = const Color(0xFFFED7AA);
    canvas.drawCircle(Offset(center.dx, center.dy - 3), 6.5, facePaint);

    // Helmet
    final helmetPaint = Paint()..color = helmetColor;
    final helmetPath = Path()
      ..addArc(
        Rect.fromCircle(center: Offset(center.dx, center.dy - 4), radius: 7.5),
        math.pi,
        math.pi,
      );
    canvas.drawPath(helmetPath, helmetPaint);

    // Visor
    canvas.drawRect(
      Rect.fromLTWH(center.dx, center.dy - 5, 4.5, 3.5),
      Paint()..color = const Color(0xFF0F172A),
    );

    // Waving Arm Effect
    if (isWaving) {
      double waveAngle = math.sin(armProgress * math.pi * 6) * 0.45;
      final armPaint = Paint()
        ..color = const Color(0xFFFED7AA)
        ..strokeWidth = 3.5
        ..strokeCap = StrokeCap.round;

      Offset armStart = Offset(center.dx - 2, center.dy + 4);
      Offset armEnd = Offset(
        armStart.dx - 9 + math.cos(waveAngle) * 4,
        armStart.dy - 11 + math.sin(waveAngle) * 4,
      );
      canvas.drawLine(armStart, armEnd, armPaint);
      canvas.drawCircle(armEnd, 2.8, armPaint);
    }
  }

  @override
  bool shouldRepaint(covariant _RoadTripPainter oldDelegate) {
    return oldDelegate.progress != progress || oldDelegate.period != period;
  }
}
