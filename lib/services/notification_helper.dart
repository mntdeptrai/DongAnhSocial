import 'package:flutter/material.dart';
import '../main.dart';

class NotificationHelper {
  static void openSettings() {
    NativeNotificationService.openSettings();
  }

  static Future<bool> checkAndRequestOverlayPermission(BuildContext context) async {
    final bool canDraw = await NativeNotificationService.canDrawOverlays();
    if (canDraw) {
      return true;
    }

    if (!context.mounted) return false;

    final bool? shouldOpenSettings = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.bubble_chart_rounded, color: Color(0xFF0EA5E9), size: 28),
            SizedBox(width: 10),
            Expanded(
              child: Text(
                'Quyền Bong Bóng Chat',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
              ),
            ),
          ],
        ),
        content: const Text(
          'Ứng dụng cần quyền "Hiển thị trên các ứng dụng khác" để hiển thị bong bóng chat nổi giúp bạn nhắn tin thuận tiện. Bạn có muốn mở Cài đặt để bật quyền này không?',
          style: TextStyle(fontSize: 14, color: Color(0xFF334155), height: 1.4),
        ),
        actionsPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Để sau', style: TextStyle(color: Colors.grey, fontWeight: FontWeight.w600)),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF0EA5E9),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              elevation: 0,
            ),
            child: const Text('Mở Cài Đặt', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (shouldOpenSettings == true) {
      await NativeNotificationService.requestOverlayPermission();
    }
    return false;
  }

  static Future<void> requestOverlayPermission() async {
    await NativeNotificationService.requestOverlayPermission();
  }
}
