import 'package:flutter/services.dart';

class NativeNotificationService {
  static const _channel = MethodChannel('com.donganh.social/notifications');
  static Function(Map<String, dynamic>)? _onNotificationTapped;

  static void initialize(Function(Map<String, dynamic>) onTapped) {
    _onNotificationTapped = onTapped;
    _channel.setMethodCallHandler((call) async {
      if (call.method == 'onNotificationTapped') {
        final data = Map<String, dynamic>.from(call.arguments as Map);
        _onNotificationTapped?.call(data);
      }
    });
    checkInitialNotification();
  }

  static Future<void> checkInitialNotification() async {
    try {
      final res = await _channel.invokeMethod('getInitialNotification');
      if (res != null) {
        final data = Map<String, dynamic>.from(res as Map);
        _onNotificationTapped?.call(data);
      }
    } catch (_) {}
  }

  static Future<void> requestPermission() async {
    try {
      await _channel.invokeMethod('requestNotificationPermission');
    } catch (_) {}
  }

  static Future<void> openSettings() async {
    try {
      await _channel.invokeMethod('openNotificationSettings');
    } catch (_) {}
  }

  static Future<bool> requestOverlayPermission() async {
    try {
      final res = await _channel.invokeMethod<bool>('requestOverlayPermission');
      return res ?? false;
    } catch (_) {
      return false;
    }
  }

  static Future<bool> canDrawOverlays() async {
    try {
      final res = await _channel.invokeMethod<bool>('canDrawOverlays');
      return res ?? false;
    } catch (_) {
      return false;
    }
  }

  static Future<void> showNotification({required String title, required String body}) async {
    try {
      await _channel.invokeMethod('showNotification', {
        'title': title,
        'body': body,
      });
    } catch (_) {}
  }

  static Future<void> setAuthToken(String? token) async {
    if (token == null) return;
    try {
      await _channel.invokeMethod('setAuthToken', {'token': token});
    } catch (_) {}
  }
}
