import 'package:flutter/material.dart';
import '../main.dart';

class NotificationHelper {
  static void openSettings() {
    NativeNotificationService.openSettings();
  }

  static Future<bool> checkAndRequestOverlayPermission(BuildContext context) async {
    return true;
  }

  static Future<void> requestOverlayPermission() async {
    // No-op
  }
}
