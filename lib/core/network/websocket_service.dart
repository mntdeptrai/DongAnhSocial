import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:web_socket_channel/web_socket_channel.dart';
import '../app_constants.dart';

/// Class quản lý kết nối Realtime WebSocket (Laravel Reverb / Pusher Protocol).
/// Tự động kết nối lại (Auto-reconnect), Ping/Pong heartbeat và phát Event Stream.
class WebSocketService {
  static final WebSocketService _instance = WebSocketService._internal();
  factory WebSocketService() => _instance;
  WebSocketService._internal();

  WebSocketChannel? _channel;
  StreamController<Map<String, dynamic>>? _eventController;
  Timer? _pingTimer;
  Timer? _reconnectTimer;
  bool _isConnected = false;

  Stream<Map<String, dynamic>> get eventStream {
    _eventController ??= StreamController<Map<String, dynamic>>.broadcast();
    return _eventController!.stream;
  }

  bool get isConnected => _isConnected;

  /// Khởi tạo kết nối Realtime tới Reverb WebSocket Server
  Future<void> connect({String appKey = 'donganhreverbkey', String host = AppConstants.baseHost}) async {
    if (_isConnected) return;

    try {
      final wsUrl = Uri.parse('wss://$host/app/$appKey?protocol=7&client=js&version=7.0.3');
      debugPrint('🔌 Connecting to WebSocket: $wsUrl');

      _channel = WebSocketChannel.connect(wsUrl);
      _isConnected = true;

      _channel!.stream.listen(
        (message) {
          _onMessageReceived(message);
        },
        onError: (error) {
          debugPrint('❌ WebSocket Error: $error');
          _handleDisconnect();
        },
        onDone: () {
          debugPrint('🔌 WebSocket Disconnected');
          _handleDisconnect();
        },
      );

      _startPingTimer();
    } catch (e) {
      debugPrint('⚠️ WebSocket Connect Exception: $e');
      _handleDisconnect();
    }
  }

  /// Xử lý message nhận từ WebSocket Server (Pusher / Reverb Protocol)
  void _onMessageReceived(dynamic message) {
    try {
      final Map<String, dynamic> data = jsonDecode(message.toString());
      final String? event = data['event'];

      if (event == 'pusher:ping') {
        sendEvent('pusher:pong', {});
        return;
      }

      if (event != null && !event.startsWith('pusher_internal:')) {
        _eventController?.add(data);
      }
    } catch (e) {
      debugPrint('⚠️ Error parsing WS message: $e');
    }
  }

  /// Lắng nghe thông điệp từ Channel cụ thể (e.g. 'checkin-feed', 'chat-room', 'orders')
  void subscribeChannel(String channelName) {
    sendEvent('pusher:subscribe', {
      'channel': channelName,
    });
  }

  /// Hủy đăng ký Channel
  void unsubscribeChannel(String channelName) {
    sendEvent('pusher:unsubscribe', {
      'channel': channelName,
    });
  }

  /// Gửi event đi qua WebSocket
  void sendEvent(String eventName, Map<String, dynamic> data, {String? channel}) {
    if (_channel != null && _isConnected) {
      final payload = jsonEncode({
        'event': eventName,
        'data': data,
        if (channel != null) 'channel': channel,
      });
      _channel!.sink.add(payload);
    }
  }

  void _startPingTimer() {
    _pingTimer?.cancel();
    _pingTimer = Timer.periodic(const Duration(seconds: 30), (_) {
      if (_isConnected) {
        sendEvent('pusher:ping', {});
      }
    });
  }

  void _handleDisconnect() {
    _isConnected = false;
    _pingTimer?.cancel();
    _channel?.sink.close();

    // Thử kết nối lại tự động sau 5 giây (Auto-reconnect)
    _reconnectTimer?.cancel();
    _reconnectTimer = Timer(const Duration(seconds: 5), () {
      connect();
    });
  }

  void dispose() {
    _pingTimer?.cancel();
    _reconnectTimer?.cancel();
    _channel?.sink.close();
    _eventController?.close();
    _eventController = null;
    _isConnected = false;
  }
}
