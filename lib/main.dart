import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'services/api_service.dart';
import 'screens/login_screen.dart';
import 'screens/map_screen.dart';
import 'screens/feed_screen.dart';
import 'screens/chat_screen.dart';
import 'screens/profile_screen.dart';
import 'widgets/floating_chat_bubble.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await ApiService.init();
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    final primaryColor = const Color(0xFF0EA5E9);

    return MaterialApp(
      title: 'Đông Anh Discovery',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: primaryColor,
          primary: primaryColor,
          surface: Colors.white,
        ),
        scaffoldBackgroundColor: const Color(0xFFF8FAFC),
        fontFamily: 'Roboto', // Default robust system font
        appBarTheme: const AppBarTheme(
          centerTitle: false,
          elevation: 0,
          scrolledUnderElevation: 0.5,
        ),
        cardTheme: CardThemeData(
          elevation: 0,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        ),
      ),
      home: const AppEntryScreen(),
    );
  }
}

class NotificationHelper {
  static const _channel = MethodChannel('com.example.mobile/notifications');

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

  /// Send auth token to native Kotlin background polling thread
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
        // Send auth token to native background polling
        NotificationHelper.setAuthToken(ApiService.token);
      }
    });
  }

  void _onLoginSuccess() {
    setState(() {
      _isLoggedIn = true;
      _isSkipped = false;
    });
    // Send auth token to native background polling
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
  final GlobalKey<FeedScreenState> _feedScreenKey = GlobalKey<FeedScreenState>();

  @override
  Widget build(BuildContext context) {
    final List<Widget> screens = [
      const MapScreen(),
      FeedScreen(key: _feedScreenKey),
      const ChatScreen(),
      ProfileScreen(
        onLogout: widget.onLogout,
        onLoginRequest: widget.onLoginRequest,
      ),
    ];

    return Scaffold(
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
              setState(() {
                _currentIndex = 2;
              });
            },
          ),
        ],
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
          if (index == 1) {
            _feedScreenKey.currentState?.refreshCamera();
          }
        },
        type: BottomNavigationBarType.fixed,
        backgroundColor: Colors.white,
        selectedItemColor: const Color(0xFF0EA5E9),
        unselectedItemColor: Colors.grey[400],
        selectedLabelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11),
        unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11),
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.map_outlined),
            activeIcon: Icon(Icons.map),
            label: 'Bản đồ',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.feed_outlined),
            activeIcon: Icon(Icons.feed),
            label: 'Feed',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.chat_bubble_outline),
            activeIcon: Icon(Icons.chat_bubble),
            label: 'Chat',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person_outline),
            activeIcon: Icon(Icons.person),
            label: 'Cá nhân',
          ),
        ],
      ),
    );
  }
}
