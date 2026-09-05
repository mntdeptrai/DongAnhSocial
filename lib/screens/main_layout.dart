import 'dart:async';
import 'package:flutter/material.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import '../services/api_service.dart';
import '../services/cart_service.dart';
import '../services/notification_state_service.dart';
import '../services/native_notification_service.dart';
import 'chat_screen.dart';
import 'feed_screen.dart';
import 'food_tour_screen.dart';
import 'map_screen.dart';
import 'news_bulletin_screen.dart';
import 'notifications_screen.dart';
import 'profile_screen.dart';
import 'utilities_screen.dart';
import 'seller_dashboard_screen.dart';
import 'principal_dashboard_screen.dart';
import 'manager_dashboard_screen.dart';
import 'admin_dashboard_screen.dart';
import 'active_call_screen.dart';
import '../widgets/role_menu_drawer.dart';
import '../widgets/floating_island_header.dart';
import '../widgets/floating_dock_nav_bar.dart';
import '../widgets/universal_search_modal.dart';
import '../widgets/my_cart_modal.dart';

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
    _startCallPollTimer();

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
      final title = message.notification?.title ?? message.data['title'] ?? 'Bản tin Đông Anh';
      final body = message.notification?.body ?? message.data['body'] ?? 'Bạn vừa nhận được một thông báo mới';
      
      // Đẩy thông báo trực tiếp ra thanh trạng thái (System Notification Shade) của Android
      NativeNotificationService.showNotification(title: title, body: body);

      if (mounted) {
        _fetchDynamicCounts();
        _showInAppNotificationBanner(title, body, onTap: () {
          _navigateByNotificationData(message.data);
        });
      }
    });

    NativeNotificationService.initialize((data) {
      if (mounted) {
        _navigateByNotificationData(data);
      }
    });
  }

  void _navigateByNotificationData(Map<String, dynamic> data) {
    final type = data['type'] ?? data['target'] ?? '';
    final postType = data['post_type'] ?? '';

    if (type == 'chat' || data['target'] == 'chat') {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const ChatScreen()),
      ).then((_) => _fetchDynamicCounts());
    } else if (postType == 'checkin' || type == 'checkin') {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const FeedScreen()),
      );
    } else if (postType == 'diary') {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const FoodTourScreen()),
      );
    } else if (postType == 'eatery' || type == 'review') {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const SellerDashboardScreen()),
      );
    } else {
      // comment on post, reaction, share, new_post → go to Notifications tab
      setState(() {
        _currentIndex = 4;
        _activeRole = 'user';
      });
    }
  }

  Timer? _callPollTimer;
  bool _isShowingGlobalCallScreen = false;

  void _startCallPollTimer() {
    _callPollTimer?.cancel();
    _callPollTimer = Timer.periodic(const Duration(seconds: 3), (timer) async {
      if (_isShowingGlobalCallScreen || !ApiService.isAuthenticated) return;
      final res = await ApiService.checkPendingCall();
      if (res['has_call'] == true && mounted && !_isShowingGlobalCallScreen) {
        _isShowingGlobalCallScreen = true;
        final callerName = (res['caller_name'] ?? 'Người dùng').toString();
        final callerId = res['caller_id'] is int ? res['caller_id'] as int : int.tryParse(res['caller_id'].toString()) ?? 0;
        final callId = res['call_id'] is int ? res['call_id'] as int : int.tryParse(res['call_id'].toString()) ?? 0;
        final isVideo = res['call_type'] == 'video';

        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => ActiveCallScreen(
              friendName: callerName,
              friendId: callerId,
              callId: callId,
              isCaller: false,
              isVideo: isVideo,
              onCallEnded: (duration) {
                _isShowingGlobalCallScreen = false;
              },
            ),
          ),
        ).then((_) {
          _isShowingGlobalCallScreen = false;
        });
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
    _callPollTimer?.cancel();
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
