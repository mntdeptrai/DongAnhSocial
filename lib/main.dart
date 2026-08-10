import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/services.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'services/api_service.dart';
import 'services/cart_service.dart';
import 'services/notification_state_service.dart';
import 'screens/login_screen.dart';
import 'screens/map_screen.dart';
import 'screens/feed_screen.dart';
import 'screens/news_bulletin_screen.dart';
import 'screens/chat_screen.dart';
import 'screens/profile_screen.dart';
import 'screens/my_checkins_screen.dart';
import 'screens/notifications_screen.dart';
import 'screens/utilities_screen.dart';
import 'screens/seller_dashboard_screen.dart';
import 'screens/principal_dashboard_screen.dart';
import 'screens/manager_dashboard_screen.dart';
import 'screens/admin_dashboard_screen.dart';
import 'widgets/role_menu_drawer.dart';
import 'widgets/top_nav_bar.dart';
import 'widgets/floating_island_header.dart';
import 'widgets/floating_dock_nav_bar.dart';
import 'widgets/universal_search_modal.dart';
import 'widgets/my_cart_modal.dart';
import 'widgets/squircle_helper.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  debugPrint('🔥 FCM Background Message: ${message.messageId} - ${message.notification?.title}');
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Low Memory (2GB RAM) optimization: Cap in-memory ImageCache to 35MB & 50 objects max
  PaintingBinding.instance.imageCache.maximumSizeBytes = 35 * 1024 * 1024;
  PaintingBinding.instance.imageCache.maximumSize = 50;

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
      ApiService.updateFcmToken(fcmToken);
    }

    // Token refresh listener
    messaging.onTokenRefresh.listen((newToken) {
      debugPrint('🔥 FCM Token Refreshed: $newToken');
      ApiService.updateFcmToken(newToken);
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
      title: 'Khám phá DongAnh',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        splashFactory: InkRipple.splashFactory,
        pageTransitionsTheme: const PageTransitionsTheme(
          builders: {
            TargetPlatform.android: CupertinoPageTransitionsBuilder(),
            TargetPlatform.iOS: CupertinoPageTransitionsBuilder(),
          },
        ),
        colorScheme: ColorScheme.fromSeed(
          seedColor: primaryColor,
          primary: primaryColor,
          secondary: accentColor,
          surface: Colors.white,
        ),
        scaffoldBackgroundColor: const Color(0xFFF8FAFC),
        fontFamily: 'Plus Jakarta Sans',
        appBarTheme: const AppBarTheme(
          centerTitle: false,
          elevation: 0,
          scrolledUnderElevation: 0.5,
          backgroundColor: Color(0xFFF8FAFC),
        ),
        cardTheme: CardThemeData(
          elevation: 0,
          color: Colors.white,
          shape: SquircleHelper.shape(
            radius: 22,
            side: const BorderSide(color: Color(0x1F0EA5E9), width: 1),
          ),
        ),
      ),
      home: const AppEntryScreen(),
    );
  }
}

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
    NativeNotificationService.requestPermission();
    NativeNotificationService.requestOverlayPermission();
  }

  void _checkLoginStatus() {
    setState(() {
      _isLoggedIn = ApiService.isAuthenticated;
      if (_isLoggedIn) {
        _isSkipped = false;
        NativeNotificationService.setAuthToken(ApiService.token);
      }
    });
  }

  void _onLoginSuccess() {
    setState(() {
      _isLoggedIn = true;
      _isSkipped = false;
    });
    NativeNotificationService.setAuthToken(ApiService.token);
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
  final GlobalKey<FeedScreenState> _feedScreenKey = GlobalKey<FeedScreenState>();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    // Giới hạn dung lượng bộ nhớ đệm ảnh ở mức 15MB / 50 ảnh cho máy 2GB RAM
    PaintingBinding.instance.imageCache.maximumSizeBytes = 15 * 1024 * 1024;
    PaintingBinding.instance.imageCache.maximumSize = 50;

    // Tất cả mọi tài khoản (Admin, Seller, Manager, User) khi đăng nhập đều vào Giao diện Người dùng bình thường trước
    _activeRole = 'user';

    _fetchDynamicCounts();

    // Cấu hình hiển thị Thông báo nổi (Heads-up Notification Banner) trên iOS & Android
    FirebaseMessaging.instance.setForegroundNotificationPresentationOptions(
      alert: true,
      badge: true,
      sound: true,
    );

    // Lắng nghe sự kiện Push Notification từ FCM (Event-Driven - Không dùng Short Polling)
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      debugPrint('🔔 Nhận FCM Push Notification: ${message.notification?.title}');
      NotificationStateService.notifyNewNotification();
      if (mounted) {
        _fetchDynamicCounts();
        final title = message.notification?.title ?? 'Bản tin Đông Anh';
        final body = message.notification?.body ?? 'Bạn vừa nhận được một thông báo mới';
        _showInAppNotificationBanner(title, body, onTap: () {
          if (message.data['target'] == 'chat') {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (context) => const ChatScreen()),
            ).then((_) => _fetchDynamicCounts());
          }
        });
      }
    });

    NativeNotificationService.initialize((data) {
      if (data['target'] == 'chat' && mounted) {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => const ChatScreen()),
        ).then((_) => _fetchDynamicCounts());
      }
    });
  }

  void _showInAppNotificationBanner(String title, String body, {VoidCallback? onTap}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).hideCurrentSnackBar();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        behavior: SnackBarBehavior.floating,
        margin: const EdgeInsets.only(top: 10, left: 14, right: 14, bottom: 20),
        backgroundColor: Colors.transparent,
        elevation: 0,
        duration: const Duration(seconds: 5),
        content: GestureDetector(
          onTap: onTap,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: const Color(0xFF0F172A),
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.25),
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: const BoxDecoration(
                    color: Color(0xFF0EA5E9),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.notifications_active_rounded, color: Colors.white, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        body,
                        style: const TextStyle(color: Colors.white70, fontSize: 11),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_right_rounded, color: Colors.white54, size: 20),
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused ||
        state == AppLifecycleState.inactive ||
        state == AppLifecycleState.hidden) {
      // Giải phóng RAM bộ nhớ tạm khi ứng dụng chạy ngầm
      PaintingBinding.instance.imageCache.clear();
      PaintingBinding.instance.imageCache.clearLiveImages();
      // Pause camera khi app đi nền để giải phóng RAM camera buffer
      _feedScreenKey.currentState?.pauseCamera();
    } else if (state == AppLifecycleState.resumed) {
      // Làm mới dữ liệu 1 lần duy nhất khi người dùng mở lại ứng dụng
      _fetchDynamicCounts();
      // Resume camera chỉ khi đang ở tab Feed
      if (_currentIndex == 0 && _activeRole == 'user') {
        _feedScreenKey.currentState?.resumeCamera();
      }
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  Future<void> _fetchDynamicCounts() async {
    try {
      final notifs = await ApiService.getAppNotifications();
      final unreadNotifs = notifs.where((item) {
        if (item is! Map) return false;
        return item['is_read'] != true && item['is_read'] != 1;
      }).length;

      final unreadMsgCount = await ApiService.getUnreadMessagesCount();
      final cCount = await CartService.refreshCartCount();

      if (mounted) {
        NotificationStateService.updateUnreadCount(unreadNotifs);
        setState(() {
          _unreadNotifsCount = unreadNotifs;
          _unreadMessagesCount = unreadMsgCount;
          _cartCount = cCount;
        });
      }
    } catch (_) {}
  }

  Widget _buildActiveRoleContent() {
    final userRole = ApiService.currentUser?['role'] ?? 'user';

    // Khóa bảo mật theo phân quyền thực tế (Strict Role Guard)
    if (_activeRole == 'seller' && (userRole == 'seller' || userRole == 'admin')) {
      return SellerDashboardScreen(onBack: () => setState(() => _activeRole = 'user'));
    } else if (_activeRole == 'principal' && (userRole == 'principal' || userRole == 'admin')) {
      return PrincipalDashboardScreen(onBack: () => setState(() => _activeRole = 'user'));
    } else if (_activeRole == 'manager' && (userRole == 'manager' || userRole == 'admin')) {
      return ManagerDashboardScreen(onBack: () => setState(() => _activeRole = 'user'));
    } else if (_activeRole == 'admin' && userRole == 'admin') {
      return AdminDashboardScreen(onBack: () => setState(() => _activeRole = 'user'));
    }

    // Lazy: Chỉ tạo screen đang active, không tạo sẵn 6 screens
    return KeyedSubtree(
      key: ValueKey<int>(_currentIndex),
      child: _buildLazyScreen(),
    );
  }

  /// Lazy Tab Builder: Chỉ khởi tạo screen đang active, KHÔNG tạo sẵn 6 screens.
  /// Tiết kiệm ~80-150MB RAM so với pre-built list.
  Widget _buildLazyScreen() {
    switch (_currentIndex) {
      case 0:
        return const NewsBulletinScreen();
      case 1:
        return FeedScreen(key: _feedScreenKey);
      case 2:
        return const MapScreen();
      case 3:
        return const UtilitiesScreen();
      case 4:
        return const NotificationsScreen();
      case 5:
        return ProfileScreen(
          onLogout: widget.onLogout,
          onLoginRequest: widget.onLoginRequest,
        );
      default:
        return const NewsBulletinScreen();
    }
  }

  @override
  Widget build(BuildContext context) {

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
          ? FloatingIslandHeader(
              currentIndex: _currentIndex,
              onTabSelected: (index) {
                setState(() {
                  _currentIndex = index;
                  _activeRole = 'user';
                });
                if (index == 1) {
                  _feedScreenKey.currentState?.resumeCamera();
                } else {
                  _feedScreenKey.currentState?.pauseCamera();
                }
                if (index == 4) {
                  _fetchDynamicCounts();
                }
              },
              onRoleDashboardTap: (role) {
                setState(() {
                  _activeRole = role;
                });
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
          Padding(
            padding: EdgeInsets.only(bottom: _activeRole == 'user' ? 68.0 : 0.0),
            child: AnimatedSwitcher(
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
              child: _buildActiveRoleContent(),
            ),
          ),
          if (_activeRole == 'user')
            Positioned(
              left: 0,
              right: 0,
              bottom: 0,
              child: FloatingDockNavBar(
                currentIndex: _currentIndex,
                unreadNotifsCount: _unreadNotifsCount,
                onTabSelected: (index) {
                  setState(() {
                    _currentIndex = index;
                    _activeRole = 'user';
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
              ),
            ),
        ],
      ),
    );
  }
}
