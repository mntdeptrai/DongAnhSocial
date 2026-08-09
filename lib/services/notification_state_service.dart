import 'package:flutter/foundation.dart';

class NotificationStateService {
  static final ValueNotifier<int> refreshNotifier = ValueNotifier<int>(0);

  static void notifyNewNotification() {
    refreshNotifier.value++;
  }
}
