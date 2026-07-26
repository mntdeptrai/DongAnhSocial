import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'services/api_service.dart';
import 'screens/login_screen.dart';
import 'screens/map_screen.dart';
import 'screens/feed_screen.dart';
import 'screens/chat_screen.dart';
import 'screens/profile_screen.dart';
import 'screens/my_checkins_screen.dart';
import 'screens/notifications_screen.dart';
import 'screens/utilities_screen.dart';
import 'screens/seller_dashboard_screen.dart';
import 'screens/manager_dashboard_screen.dart';
import 'screens/admin_dashboard_screen.dart';
import 'widgets/role_switch_banner.dart';
import 'widgets/role_menu_drawer.dart';
import 'widgets/top_nav_bar.dart';
import 'widgets/floating_chat_bubble.dart';
import 'widgets/universal_search_modal.dart';
import 'widgets/my_cart_modal.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  debugPrint('🔥 FCM Background Message: ${message.messageId} - ${message.notification?.title}');
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await ApiService.init();

  try {
    await Firebase.initializeApp();
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

    final messaging = FirebaseMessaging.instance;
    await messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    final fcmToken = await messaging.getToken();
    if (fcmToken != null) {
      debugPrint('🔥 Firebase FCM Token: $fcmToken');
      if (ApiService.isAuthenticated) {
        ApiService.updateFcmToken(fcmToken);
      }
    }

    // Token refresh listener
    messaging.onTokenRefresh.listen((newToken) {
      debugPrint('🔥 FCM Token Refreshed: $newToken');
      if (ApiService.isAuthenticated) {
        ApiService.updateFcmToken(newToken);
      }
    });

    // Foreground notification listener
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      debugPrint('🔥 FCM Foreground Message: ${message.notification?.title} - ${message.notification?.body}');
    });
  } catch (e) {
    debugPrint('⚠️ Firebase initialization error: $e');
  }

  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    const primaryColor = Color(0xFF0EA5E9);
    const accentColor = Color(0xFF06B6D4);

    return MaterialApp(
      title: 'Đông Anh Discovery',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        splashFactory: InkRipple.splashFactory,
        colorScheme: ColorScheme.fromSeed(
          seedColor: primaryColor,
          primary: primaryColor,
          secondary: accentColor,
          surface: Colors.white,
          background: const Color(0xFFF0FDFA),
        ),
        scaffoldBackgroundColor: const Color(0xFFF0FDFA),
        fontFamily: 'Be Vietnam Pro',
        appBarTheme: const AppBarTheme(
          centerTitle: false,
          elevation: 0,
          scrolledUnderElevation: 0.5,
          backgroundColor: Color(0xFFF0FDFA),
        ),
        cardTheme: CardThemeData(
          elevation: 0,
          color: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
            side: BorderSide(color: primaryColor.withOpacity(0.12), width: 1),
          ),
        ),
      ),
      home: const AppEntryScreen(),
    );
  }
}

class NotificationHelper {
  static const _channel = MethodChannel('com.example.mobile/notifications');
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

class AppEntryScreen extends StatefulWidget {
  const AppEntryScreen({super.key});

  @override
  State<AppEntryScreen> createState() => _AppEntryScreenState();
}

class _AppEntryScreenState extends State<AppEntryScreen> {
  bool _isLoggedIn = false;
  bool _isSkipped = false;

  @override
  void initState() {
    super.initState();
    _checkLoginStatus();
    NotificationHelper.requestPermission();
    NotificationHelper.requestOverlayPermission();
  }

  void _checkLoginStatus() {
    setState(() {
      _isLoggedIn = ApiService.isAuthenticated;
      if (_isLoggedIn) {
        _isSkipped = false;
        NotificationHelper.setAuthToken(ApiService.token);
      }
    });
  }

  void _onLoginSuccess() {
    setState(() {
      _isLoggedIn = true;
      _isSkipped = false;
    });
    NotificationHelper.setAuthToken(ApiService.token);
  }

  void _onSkip() {
    setState(() {
      _isSkipped = true;
    });
  }

  void _onLogout() {
    setState(() {
      _isLoggedIn = false;
      _isSkipped = false;
    });
  }

  void _showLoginScreen() {
    setState(() {
      _isLoggedIn = false;
      _isSkipped = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (!_isLoggedIn && !_isSkipped) {
      return LoginScreen(
        onLoginSuccess: _onLoginSuccess,
        onSkip: _onSkip,
      );
    }

    return MainLayout(
      onLogout: _onLogout,
      onLoginRequest: _showLoginScreen,
    );
  }
}

class MainLayout extends StatefulWidget {
  final VoidCallback onLogout;
  final VoidCallback onLoginRequest;

  const MainLayout({
    super.key,
    required this.onLogout,
    required this.onLoginRequest,
  });

  @override
  State<MainLayout> createState() => _MainLayoutState();
}

class _MainLayoutState extends State<MainLayout> with WidgetsBindingObserver {
  int _currentIndex = 0;
  int _unreadNotifsCount = 0;
  int _unreadMessagesCount = 0;
  int _cartCount = 0;
  String _activeRole = 'user'; // Active Role: 'user', 'seller', 'manager', 'admin'
  Timer? _pollTimer;
  final GlobalKey<FeedScreenState> _feedScreenKey = GlobalKey<FeedScreenState>();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    // Giới hạn dung lượng bộ nhớ đệm ảnh ở mức 30MB để tránh tràn RAM
    PaintingBinding.instance.imageCache.maximumSizeBytes = 30 * 1024 * 1024;
    PaintingBinding.instance.imageCache.maximumSize = 100;

    // Thiết lập giao diện duy nhất chuẩn theo phân quyền tài khoản đã được cấp
    final userRole = ApiService.currentUser?['role'] ?? 'user';
    _activeRole = userRole;

    _fetchDynamicCounts();
    _startTimer();

    NotificationHelper.initialize((data) {
      if (data['target'] == 'chat' && mounted) {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => const ChatScreen()),
        ).then((_) => _fetchDynamicCounts());
      }
    });
  }

  void _startTimer() {
    _pollTimer?.cancel();
    _pollTimer = Timer.periodic(const Duration(seconds: 10), (_) {
      _fetchDynamicCounts();
    });
  }

  void _stopTimer() {
    _pollTimer?.cancel();
    _pollTimer = null;
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused ||
        state == AppLifecycleState.inactive ||
        state == AppLifecycleState.hidden) {
      // Tạm dừng timer và giải phóng RAM ảnh khi chạy ngầm
      _stopTimer();
      PaintingBinding.instance.imageCache.clear();
      PaintingBinding.instance.imageCache.clearLiveImages();
    } else if (state == AppLifecycleState.resumed) {
      // Làm mới dữ liệu và khởi động lại timer khi mở lại ứng dụng
      _fetchDynamicCounts();
      _startTimer();
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _stopTimer();
    super.dispose();
  }

  Future<void> _fetchDynamicCounts() async {
    try {
      final notifs = await ApiService.getAppNotifications();
      final unreadMsgCount = await ApiService.getUnreadMessagesCount();
      final cartRes = await ApiService.getCart();
      int cCount = 0;
      if (cartRes['success'] == true && cartRes['data'] is List) {
        cCount = (cartRes['data'] as List).length;
      }
      if (mounted) {
        setState(() {
          _unreadNotifsCount = notifs.length;
          _unreadMessagesCount = unreadMsgCount;
          _cartCount = cCount;
        });
      }
    } catch (_) {}
  }

  Widget _buildActiveRoleContent(List<Widget> screens) {
    final userRole = ApiService.currentUser?['role'] ?? 'user';

    // Khóa bảo mật theo phân quyền thực tế (Strict Role Guard)
    if (_activeRole == 'seller' && (userRole == 'seller' || userRole == 'admin')) {
      return const SellerDashboardScreen();
    } else if (_activeRole == 'manager' && (userRole == 'manager' || userRole == 'admin')) {
      return const ManagerDashboardScreen();
    } else if (_activeRole == 'admin' && userRole == 'admin') {
      return const AdminDashboardScreen();
    }

    // Mặc định trả về giao diện Người dùng Consumer nếu không đủ quyền hạn
    return KeyedSubtree(
      key: ValueKey<int>(_currentIndex),
      child: screens[_currentIndex],
    );
  }

  @override
  Widget build(BuildContext context) {
    final List<Widget> screens = [
      FeedScreen(key: _feedScreenKey),  // Tab 0: Home (Lướt tin check-in)
      const MyCheckinsScreen(),          // Tab 1: Check-in của tôi
      const MapScreen(),                 // Tab 2: Map (bản đồ địa điểm)
      const UtilitiesScreen(),          // Tab 3: Chợ số OCOP
      const NotificationsScreen(),      // Tab 4: Thông báo
      ProfileScreen(                     // Tab 5: Cá nhân
        onLogout: widget.onLogout,
        onLoginRequest: widget.onLoginRequest,
      ),
    ];

    return Scaffold(
      drawer: RoleMenuDrawer(
        activeRole: _activeRole,
        onRoleChanged: (newRole) {
          setState(() {
            _activeRole = newRole;
          });
        },
        onNavigateTab: (tabIndex) {
          setState(() {
            _currentIndex = tabIndex;
            _activeRole = 'user';
          });
        },
        onLogout: widget.onLogout,
      ),
      appBar: _activeRole == 'user'
          ? TopNavBar(
              currentIndex: _currentIndex,
              onTabSelected: (index) {
                setState(() {
                  _currentIndex = index;
                  _activeRole = 'user'; // Switch back to consumer view when clicking bottom tabs
                });
                if (index == 0) {
                  _feedScreenKey.currentState?.resumeCamera();
                } else {
                  _feedScreenKey.currentState?.pauseCamera();
                }
                if (index == 4) {
                  _fetchDynamicCounts();
                }
              },
              onSearchTap: () {
                UniversalSearchModal.show(context, onNavigateToTab: (index) {
                  setState(() {
                    _currentIndex = index;
                    _activeRole = 'user';
                  });
                });
              },
              onMessengerTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (context) => const ChatScreen()),
                ).then((_) => _fetchDynamicCounts());
              },
              onCartTap: () {
                MyCartModal.show(context, onCartUpdated: _fetchDynamicCounts);
              },
              cartCount: _cartCount,
              unreadMessagesCount: _unreadMessagesCount,
              unreadNotifsCount: _unreadNotifsCount,
            )
          : null,
      body: Stack(
        children: [
          AnimatedSwitcher(
            duration: const Duration(milliseconds: 300),
            switchInCurve: Curves.easeOut,
            switchOutCurve: Curves.easeIn,
            transitionBuilder: (child, animation) {
              return FadeTransition(
                opacity: animation,
                child: ScaleTransition(
                  scale: Tween<double>(begin: 0.98, end: 1.0).animate(animation),
                  child: child,
                ),
              );
            },
            child: _buildActiveRoleContent(screens),
          ),
          DraggableFloatingChatBubble(
            onOpenChatTab: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => const ChatScreen()),
              );
            },
          ),
        ],
      ),
    );
  }
}
