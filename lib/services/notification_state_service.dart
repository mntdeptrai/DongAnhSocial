import 'package:flutter/foundation.dart';

class NotificationStateService {
  /// Reactive unread notification count notifier (Global Real-time State)
  static final ValueNotifier<int> unreadCountNotifier = ValueNotifier<int>(0);

  /// Real-time event trigger for refresh listeners
  static final ValueNotifier<int> refreshNotifier = ValueNotifier<int>(0);

  static void updateUnreadCount(int count) {
    unreadCountNotifier.value = count < 0 ? 0 : count;
  }

  static void decrementUnreadCount({int amount = 1}) {
    final current = unreadCountNotifier.value;
    unreadCountNotifier.value = (current - amount) < 0 ? 0 : (current - amount);
  }

  static void clearUnreadCount() {
    unreadCountNotifier.value = 0;
  }

  static void notifyNewNotification() {
    refreshNotifier.value++;
    unreadCountNotifier.value++;
  }
}
