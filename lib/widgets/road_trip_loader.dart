import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'squircle_helper.dart';

/// 🛵💨 Road Trip Loader: Nhóm bạn cưỡi xe máy phượt vi vu Đông Anh - Cổ Loa
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

class _RoadTripLoaderState extends State<RoadTripLoader> with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  final List<String> _travelQuotes = [
    '🛵 Đang cùng hội bạn phượt khám phá Cổ Loa...',
    '🍲 Sắp tới điểm dừng chân quán ngon Đông Anh...',
    '🏰 Đang băng qua cổng thành di sản Cổ Loa...',
    '🌾 Khám phá Chợ số & Nông sản OCOP Đông Anh...',
  ];

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    )..repeat();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final String activeMessage = widget.message ??
        _travelQuotes[(DateTime.now().second ~/ 3) % _travelQuotes.length];

    return Center(
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 22),
        margin: const EdgeInsets.symmetric(horizontal: 20),
        decoration: SquircleHelper.decoration(
          radius: 28,
          color: Colors.white.withValues(alpha: 0.96),
          borderSide: BorderSide(color: widget.primaryColor.withValues(alpha: 0.25), width: 1.5),
          boxShadow: [
            BoxShadow(
              color: widget.primaryColor.withValues(alpha: 0.15),
              blurRadius: 30,
              offset: const Offset(0, 10),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Custom Painter Canvas for Road & Riding Scooter Friends
            SizedBox(
              width: 220,
              height: 110,
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
            const SizedBox(height: 12),

            // Animated Travel Message Banner
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
              decoration: SquircleHelper.decoration(
                radius: 12,
                color: widget.primaryColor.withValues(alpha: 0.08),
                borderSide: BorderSide(color: widget.primaryColor.withValues(alpha: 0.15)),
              ),
              child: Text(
                activeMessage,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w800,
                  color: const Color(0xFF0F172A),
                  letterSpacing: -0.2,
                ),
              ),
            ),
          ],
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

    // 1. Draw Background Clouds moving slowly
    final cloudPaint = Paint()
      color = const Color(0xFFE2E8F0)
      style = PaintingStyle.fill;

    for (int i = 0; i < 3; i++) {
      double cloudX = ((i * 90) - (progress * 50)) % (w + 40) - 20;
      double cloudY = 15.0 + (i * 8);
      canvas.drawCircle(Offset(cloudX, cloudY), 10, cloudPaint);
      canvas.drawCircle(Offset(cloudX + 8, cloudY - 4), 8, cloudPaint);
      canvas.drawCircle(Offset(cloudX + 14, cloudY), 9, cloudPaint);
    }

    // 2. Draw Trees / Silhouettes in background moving medium speed
    final treePaint = Paint()
      color = const Color(0xFFCBD5E1)
      style = PaintingStyle.fill;

    for (int i = 0; i < 4; i++) {
      double treeX = ((i * 70) - (progress * 110)) % (w + 40) - 20;
      double treeY = h - 25;
      // Trunk
      canvas.drawRect(Rect.fromLTWH(treeX + 6, treeY - 14, 4, 14), treePaint);
      // Canopy
      canvas.drawCircle(Offset(treeX + 8, treeY - 20), 10, treePaint);
    }

    // 3. Draw Road Base
    final roadPaint = Paint()
      color = const Color(0xFF334155)
      style = PaintingStyle.fill;
    canvas.drawRRect(
      RRect.fromRectAndRadius(Rect.fromLTWH(0, h - 24, w, 18), const Radius.circular(8)),
      roadPaint,
    );

    // Animated Dashed Lane Line
    final dashPaint = Paint()
      color = const Color(0xFFF59E0B) // Golden Amber road dash
      strokeWidth = 3
      strokeCap = StrokeCap.round;

    double dashOffset = (progress * 40) % 24;
    for (double x = -dashOffset; x < w; x += 24) {
      if (x + 12 > 0 && x < w) {
        canvas.drawLine(Offset(x, h - 15), Offset(x + 12, h - 15), dashPaint);
      }
    }

    // 4. Vehicle Bouncing Effect (Riding over small road bumps)
    final double bounceY = math.sin(progress * math.pi * 6) * 2.2;
    final double scooterX = (w / 2) - 30;
    final double scooterY = h - 38 + bounceY;

    // 5. Draw Scooter Wheels with rotation
    final wheelPaint = Paint()
      color = const Color(0xFF1E293B)
      style = PaintingStyle.fill;
    final rimPaint = Paint()
      color = const Color(0xFF94A3B8)
      style = PaintingStyle.stroke
      strokeWidth = 2.5;

    final double wheelRadius = 9;
    final Offset frontWheel = Offset(scooterX + 44, scooterY + 12);
    final Offset backWheel = Offset(scooterX + 8, scooterY + 12);

    canvas.drawCircle(frontWheel, wheelRadius, wheelPaint);
    canvas.drawCircle(backWheel, wheelRadius, wheelPaint);
    canvas.drawCircle(frontWheel, wheelRadius - 2, rimPaint);
    canvas.drawCircle(backWheel, wheelRadius - 2, rimPaint);

    // Spokes rotation animation
    final double angle = progress * math.pi * 8;
    for (int i = 0; i < 4; i++) {
      double a = angle + (i * math.pi / 2);
      canvas.drawLine(
        frontWheel,
        Offset(frontWheel.dx + math.cos(a) * 6, frontWheel.dy + math.sin(a) * 6),
        rimPaint..strokeWidth = 1.5,
      );
      canvas.drawLine(
        backWheel,
        Offset(backWheel.dx + math.cos(a) * 6, backWheel.dy + math.sin(a) * 6),
        rimPaint..strokeWidth = 1.5,
      );
    }

    // 6. Draw Vintage Vespa / Scooter Body
    final bodyPaint = Paint()
      color = primaryColor
      style = PaintingStyle.fill;

    // Main fender & floorboard
    final bodyPath = Path()
      ..moveTo(scooterX + 2, scooterY + 8)
      ..cubicTo(scooterX + 2, scooterY - 2, scooterX + 16, scooterY - 4, scooterX + 26, scooterY + 4)
      ..lineTo(scooterX + 38, scooterY + 6)
      ..lineTo(scooterX + 46, scooterY - 10) // Front shield
      ..lineTo(scooterX + 42, scooterY + 8)
      ..close();
    canvas.drawPath(bodyPath, bodyPaint);

    // Handlebar & Headlight
    final headlightPaint = Paint()..color = const Color(0xFFFEF08A);
    canvas.drawCircle(Offset(scooterX + 46, scooterY - 14), 4, headlightPaint);
    canvas.drawLine(
      Offset(scooterX + 44, scooterY - 10),
      Offset(scooterX + 42, scooterY - 18),
      Paint()..color = const Color(0xFF475569)..strokeWidth = 3,
    );

    // 7. Draw Group of 3 Friends Riding Together (Driver, Middle Friend, Back Friend)

    // Friend 3 (Back Passenger - Waving Arm)
    _drawFriend(
      canvas: canvas,
      center: Offset(scooterX + 6, scooterY - 18),
      shirtColor: const Color(0xFFEC4899), // Pink shirt
      helmetColor: const Color(0xFFF43F5E),
      isWaving: true,
      armProgress: progress,
    );

    // Friend 2 (Middle Passenger - Hugging/Smiling)
    _drawFriend(
      canvas: canvas,
      center: Offset(scooterX + 18, scooterY - 20),
      shirtColor: const Color(0xFF10B981), // Emerald shirt
      helmetColor: const Color(0xFF059669),
      isWaving: false,
      armProgress: progress,
    );

    // Friend 1 (Driver holding handlebar)
    _drawFriend(
      canvas: canvas,
      center: Offset(scooterX + 32, scooterY - 22),
      shirtColor: const Color(0xFFF59E0B), // Golden Amber shirt
      helmetColor: const Color(0xFFD97706),
      isWaving: false,
      armProgress: progress,
    );

    // 8. Draw Wind / Exhaust Particles behind
    final windPaint = Paint()
      color = primaryColor.withValues(alpha: 0.35)
      strokeWidth = 2.0
      strokeCap = StrokeCap.round;

    for (int i = 0; i < 3; i++) {
      double pX = (scooterX - 10) - ((progress * 40 + i * 15) % 30);
      double pY = scooterY + 8 + (i * 3);
      canvas.drawLine(Offset(pX, pY), Offset(pX - 8, pY), windPaint);
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
    // Body / Torso
    final bodyPaint = Paint()..color = shirtColor;
    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromCenter(center: Offset(center.dx, center.dy + 7), width: 13, height: 14),
        const Radius.circular(5),
      ),
      bodyPaint,
    );

    // Head / Face
    final facePaint = Paint()..color = const Color(0xFFFED7AA); // Peach skin
    canvas.drawCircle(Offset(center.dx, center.dy - 3), 6, facePaint);

    // Helmet
    final helmetPaint = Paint()..color = helmetColor;
    final helmetPath = Path()
      ..addArc(
        Rect.fromCircle(center: Offset(center.dx, center.dy - 4), radius: 7),
        math.pi,
        math.pi,
      );
    canvas.drawPath(helmetPath, helmetPaint);

    // Visor / Sunglasses
    canvas.drawRect(
      Rect.fromLTWH(center.dx, center.dy - 5, 4, 3),
      Paint()..color = const Color(0xFF1E293B),
    );

    // Waving Arm (Back passenger)
    if (isWaving) {
      double waveAngle = math.sin(armProgress * math.pi * 6) * 0.4;
      final armPaint = Paint()
        color = const Color(0xFFFED7AA)
        strokeWidth = 3
        strokeCap = StrokeCap.round;

      Offset armStart = Offset(center.dx - 2, center.dy + 4);
      Offset armEnd = Offset(
        armStart.dx - 8 + math.cos(waveAngle) * 4,
        armStart.dy - 10 + math.sin(waveAngle) * 4,
      );
      canvas.drawLine(armStart, armEnd, armPaint);

      // Waving hand
      canvas.drawCircle(armEnd, 2.5, armPaint);
    }
  }

  @override
  bool shouldRepaint(covariant _RoadTripPainter oldDelegate) {
    return oldDelegate.progress != progress;
  }
}
