import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'core/app_constants.dart';
import 'services/api_service.dart';
import 'services/native_notification_service.dart';
import 'screens/login_screen.dart';
import 'screens/main_layout.dart';
import 'widgets/squircle_helper.dart';
import 'widgets/custom_loader.dart';

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

  runApp(const MyApp());

  // Non-blocking async Firebase & FCM initialization
  _initFirebaseServices();
}

Future<void> _initFirebaseServices() async {
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

    messaging.onTokenRefresh.listen((newToken) {
      debugPrint('🔥 FCM Token Refreshed: $newToken');
      ApiService.updateFcmToken(newToken);
    });

    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      debugPrint('🔥 FCM Foreground Message: ${message.notification?.title} - ${message.notification?.body}');
      final title = message.notification?.title ?? message.data['title'] ?? 'Bản tin Đông Anh';
      final body = message.notification?.body ?? message.data['body'] ?? 'Bạn vừa nhận được một thông báo mới';
      NativeNotificationService.showNotification(title: title, body: body);
    });
  } catch (e) {
    debugPrint('⚠️ Firebase initialization error: $e');
  }
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
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
          seedColor: AppConstants.primaryColor,
          primary: AppConstants.primaryColor,
          secondary: AppConstants.accentColor,
          surface: Colors.white,
        ),
        scaffoldBackgroundColor: AppConstants.surfaceColor,
        fontFamily: AppConstants.fontFamily,
        appBarTheme: const AppBarTheme(
          centerTitle: false,
          elevation: 0,
          scrolledUnderElevation: 0.5,
          backgroundColor: AppConstants.surfaceColor,
        ),
        cardTheme: CardThemeData(
          elevation: 0,
          color: Colors.white,
          shape: SquircleHelper.shape(
            radius: 22,
            side: const BorderSide(color: AppConstants.cardBorderColor, width: 1),
          ),
        ),
      ),
      home: const AppEntryScreen(),
    );
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
  bool _isInitializing = true;

  @override
  void initState() {
    super.initState();
    _checkLoginStatus();
    NativeNotificationService.requestPermission();
    NativeNotificationService.requestOverlayPermission();

    // App Launch Splash Loader animation delay
    Future.delayed(const Duration(milliseconds: 1400), () {
      if (mounted) {
        setState(() {
          _isInitializing = false;
        });
      }
    });
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
    if (_isInitializing) {
      return const Scaffold(
        backgroundColor: AppConstants.darkColor,
        body: Center(
          child: CustomPulseLoader(
            message: 'Đang khởi động Nền tảng số Đông Anh 2026...',
            primaryColor: AppConstants.primaryColor,
          ),
        ),
      );
    }

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
