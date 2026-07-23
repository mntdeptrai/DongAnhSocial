import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'services/api_service.dart';
import 'screens/login_screen.dart';
import 'screens/map_screen.dart';
import 'screens/feed_screen.dart';
import 'screens/chat_screen.dart';
import 'screens/profile_screen.dart';
import 'screens/my_checkins_screen.dart';
import 'screens/notifications_screen.dart';
import 'screens/utilities_screen.dart';
import 'widgets/top_nav_bar.dart';
import 'widgets/floating_chat_bubble.dart';
import 'widgets/universal_search_modal.dart';
import 'widgets/my_cart_modal.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await ApiService.init();
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

class _MainLayoutState extends State<MainLayout> {
  int _currentIndex = 0;
  int _unreadNotifsCount = 0;
  int _unreadMessagesCount = 0;
  int _cartCount = 0;
  Timer? _pollTimer;
  final GlobalKey<FeedScreenState> _feedScreenKey = GlobalKey<FeedScreenState>();

  @override
  void initState() {
    super.initState();
    _fetchDynamicCounts();
    _pollTimer = Timer.periodic(const Duration(seconds: 10), (_) {
      _fetchDynamicCounts();
    });
    NotificationHelper.initialize((data) {
      if (data['target'] == 'chat' && mounted) {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => const ChatScreen()),
        ).then((_) => _fetchDynamicCounts());
      }
    });
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
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
      appBar: TopNavBar(
        currentIndex: _currentIndex,
        onTabSelected: (index) {
          setState(() {
            _currentIndex = index;
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
      ),
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
            child: KeyedSubtree(
              key: ValueKey<int>(_currentIndex),
              child: screens[_currentIndex],
            ),
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
