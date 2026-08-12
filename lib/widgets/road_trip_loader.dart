import 'dart:math' as math;
import 'package:flutter/material.dart';

/// 🛵💨 Ultra Premium Road Trip Loader — Phượt Vi Vu Đông Anh 2026
class RoadTripLoader extends StatefulWidget {
  final String? message;
  final Color primaryColor;

  const RoadTripLoader({
    super.key,
    this.message,
    this.primaryColor = const Color(0xFF0EA5E9),
  });

  @override
  State<RoadTripLoader> createState() => _RoadTripLoaderState();
}

class _RoadTripLoaderState extends State<RoadTripLoader> with TickerProviderStateMixin {
  late AnimationController _controller;
  late AnimationController _pulseController;
  late Animation<double> _pulseAnimation;

  final List<String> _travelQuotes = [
    '🛵 Đang cùng hội bạn phượt vi vu Đông Anh...',
    '🍲 Sắp tới điểm dừng chân quán ngon đặc sản...',
    '🏰 Đang băng qua cổng thành di sản Cổ Loa...',
    '🌾 Khám phá Chợ số & Nông sản OCOP Đông Anh...',
    '✨ Đang tối ưu kết nối dữ liệu siêu tốc 4K...',
  ];

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

    _pulseAnimation = Tween<double>(begin: 0.95, end: 1.05).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    _pulseController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final String activeMessage = widget.message ??
        _travelQuotes[(DateTime.now().second ~/ 3) % _travelQuotes.length];

    return Center(
      child: ScaleTransition(
        scale: _pulseAnimation,
        child: Container(
          padding: const EdgeInsets.all(24),
          margin: const EdgeInsets.symmetric(horizontal: 24),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(32),
            border: Border.all(color: widget.primaryColor.withValues(alpha: 0.4), width: 1.5),
            boxShadow: [
              BoxShadow(
                color: widget.primaryColor.withValues(alpha: 0.35),
                blurRadius: 36,
                spreadRadius: 2,
                offset: const Offset(0, 12),
              ),
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.5),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Top Glowing Pill Badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                decoration: BoxDecoration(
                  color: widget.primaryColor.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: widget.primaryColor.withValues(alpha: 0.5)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 8,
                      height: 8,
                      decoration: const BoxDecoration(
                        color: Color(0xFF10B981),
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'ĐÔNG ANH LIVE SMART',
                      style: TextStyle(
                        color: widget.primaryColor,
                        fontSize: 10,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 0.8,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 14),

              // Custom Painter Canvas for Road & Riding Scooter Friends
              SizedBox(
                width: 240,
                height: 120,
                child: AnimatedBuilder(
                  animation: _controller,
                  builder: (context, child) {
                    return CustomPaint(
                      painter: _RoadTripPainter(
                        progress: _controller.value,
                        primaryColor: widget.primaryColor,
                      ),
                    );
                  },
                ),
              ),

              const SizedBox(height: 16),

              // Animated Travel Message Banner with Shimmer Effect
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.white.withValues(alpha: 0.15)),
                ),
                child: Text(
                  activeMessage,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
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

class _RoadTripPainter extends CustomPainter {
  final double progress;
  final Color primaryColor;

  _RoadTripPainter({required this.progress, required this.primaryColor});

  @override
  void paint(Canvas canvas, Size size) {
    final double w = size.width;
    final double h = size.height;

    // 1. Draw Glowing Background Neon Aura
    final auraPaint = Paint()
      ..color = primaryColor.withValues(alpha: 0.12)
      ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 20);
    canvas.drawCircle(Offset(w / 2, h / 2), 65, auraPaint);

    // 2. Draw Moving Stars & Sparkles in sky
    final starPaint = Paint()..color = const Color(0xFF38BDF8);
    for (int i = 0; i < 6; i++) {
      double starX = ((i * 45) - (progress * 60)) % (w + 20) - 10;
      double starY = 10.0 + (i * 6) % 30;
      double starRadius = 1.5 + (i % 3) * 0.8;
      canvas.drawCircle(Offset(starX, starY), starRadius, starPaint..color = Colors.white.withValues(alpha: 0.8));
    }

    // 3. Draw Cloud Silhouettes
    final cloudPaint = Paint()
      ..color = Colors.white.withValues(alpha: 0.15)
      ..style = PaintingStyle.fill;

    for (int i = 0; i < 3; i++) {
      double cloudX = ((i * 95) - (progress * 45)) % (w + 40) - 20;
      double cloudY = 20.0 + (i * 7);
      canvas.drawCircle(Offset(cloudX, cloudY), 11, cloudPaint);
      canvas.drawCircle(Offset(cloudX + 8, cloudY - 4), 9, cloudPaint);
      canvas.drawCircle(Offset(cloudX + 15, cloudY), 10, cloudPaint);
    }

    // 4. Draw Background Heritage Monuments / Ancient Trees
    final treePaint = Paint()
      ..color = const Color(0xFF334155)
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
        ..color = primaryColor.withValues(alpha: 0.6)
        ..strokeWidth = 2,
    );

    // Animated Dashed Golden Lane Line
    final dashPaint = Paint()
      ..color = const Color(0xFFF59E0B)
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
      ..color = primaryColor.withValues(alpha: 0.4)
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
      ..color = const Color(0xFF38BDF8)
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
      ..color = primaryColor
      ..style = PaintingStyle.fill;

    final bodyPath = Path()
      ..moveTo(scooterX + 2, scooterY + 9)
      ..cubicTo(scooterX + 2, scooterY - 2, scooterX + 18, scooterY - 4, scooterX + 28, scooterY + 5)
      ..lineTo(scooterX + 40, scooterY + 7)
      ..lineTo(scooterX + 48, scooterY - 10)
      ..lineTo(scooterX + 44, scooterY + 9)
      ..close();
    canvas.drawPath(bodyPath, bodyPaint);

    // Headlight Lens Beam
    final headlightBeamPaint = Paint()
      ..color = const Color(0xFFFEF08A).withValues(alpha: 0.35)
      ..style = PaintingStyle.fill;

    final beamPath = Path()
      ..moveTo(scooterX + 48, scooterY - 14)
      ..lineTo(w, scooterY - 24)
      ..lineTo(w, scooterY + 10)
      ..close();
    canvas.drawPath(beamPath, headlightBeamPaint);

    final headlightPaint = Paint()..color = const Color(0xFFFEF08A);
    canvas.drawCircle(Offset(scooterX + 48, scooterY - 14), 4.5, headlightPaint);

    // 9. Draw Group of 3 Friends Riding Vespa

    // Friend 3 (Back Passenger - Waving Arm & Smiling)
    _drawFriend(
      canvas: canvas,
      center: Offset(scooterX + 6, scooterY - 18),
      shirtColor: const Color(0xFFEC4899), // Pink
      helmetColor: const Color(0xFFF43F5E),
      isWaving: true,
      armProgress: progress,
    );

    // Friend 2 (Middle Passenger - Embracing)
    _drawFriend(
      canvas: canvas,
      center: Offset(scooterX + 20, scooterY - 21),
      shirtColor: const Color(0xFF10B981), // Emerald Green
      helmetColor: const Color(0xFF059669),
      isWaving: false,
      armProgress: progress,
    );

    // Friend 1 (Driver - Golden Amber Shirt)
    _drawFriend(
      canvas: canvas,
      center: Offset(scooterX + 34, scooterY - 23),
      shirtColor: const Color(0xFFF59E0B), // Amber
      helmetColor: const Color(0xFFD97706),
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
    return oldDelegate.progress != progress;
  }
}
