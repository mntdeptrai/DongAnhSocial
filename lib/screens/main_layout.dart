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
import 'create_story_screen.dart';
import '../widgets/create_post_modal.dart';
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
      // comment on post, reaction, share, new_post → go to Notifications screen
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const NotificationsScreen()),
      ).then((_) => _fetchDynamicCounts());
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

  /// Lazy Tab Builder: 5 tabs chuẩn (Trang chủ, Bản đồ, [Tạo mới qua modal], Chợ OCOP, Cá nhân)
  Widget _buildLazyScreen() {
    switch (_currentIndex) {
      case 0:
        return const NewsBulletinScreen();
      case 1:
        return const MapScreen();
      case 3:
        return const UtilitiesScreen();
      case 4:
        return ProfileScreen(
          onLogout: widget.onLogout,
          onLoginRequest: widget.onLoginRequest,
        );
      default:
        return const NewsBulletinScreen();
    }
  }

  void _showCreateActionSheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (ctx) {
        return Container(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 36),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: const Color(0xFFE2E8F0),
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(height: 18),
              const Text(
                'Tạo nội dung mới',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                'Chia sẻ câu chuyện & khoảnh khắc của bạn với Đông Anh',
                style: TextStyle(
                  fontSize: 13,
                  color: Color(0xFF64748B),
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),

              // Option 1: Bài viết Bảng tin
              _buildCreateOptionItem(
                context: ctx,
                icon: Icons.edit_note_rounded,
                iconColor: const Color(0xFF0284C7),
                bgColor: const Color(0xFFE0F2FE),
                title: 'Đăng bài viết lên Bảng tin',
                subtitle: 'Chia sẻ thông tin, câu chuyện, hỏi đáp cộng đồng',
                onTap: () {
                  Navigator.pop(ctx);
                  if (!ApiService.isAuthenticated) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Vui lòng đăng nhập để đăng bài viết!'),
                        backgroundColor: Colors.orange,
                      ),
                    );
                    return;
                  }
                  showCreatePostModal(context);
                },
              ),
              const SizedBox(height: 12),

              // Option 2: Check-in / Camera
              _buildCreateOptionItem(
                context: ctx,
                icon: Icons.photo_camera_rounded,
                iconColor: const Color(0xFF059669),
                bgColor: const Color(0xFFD1FAE5),
                title: 'Check-in ẩm thực & địa điểm',
                subtitle: 'Chụp ảnh khoảnh khắc tại các quán ăn & di tích Đông Anh',
                onTap: () {
                  Navigator.pop(ctx);
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const FeedScreen()),
                  );
                },
              ),
              const SizedBox(height: 12),

              // Option 3: Story 24h
              _buildCreateOptionItem(
                context: ctx,
                icon: Icons.auto_awesome_rounded,
                iconColor: const Color(0xFF8B5CF6),
                bgColor: const Color(0xFFEDE9FE),
                title: 'Tạo khoảnh khắc 24h (Story)',
                subtitle: 'Chia sẻ hình ảnh hoặc video ngắn tự biến mất sau 24 giờ',
                onTap: () {
                  Navigator.pop(ctx);
                  if (!ApiService.isAuthenticated) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Vui lòng đăng nhập để tạo Story!'),
                        backgroundColor: Colors.orange,
                      ),
                    );
                    return;
                  }
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const CreateStoryScreen()),
                  );
                },
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildCreateOptionItem({
    required BuildContext context,
    required IconData icon,
    required Color iconColor,
    required Color bgColor,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return Material(
      color: const Color(0xFFF8FAFC),
      borderRadius: BorderRadius.circular(18),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(18),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          child: Row(
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(icon, color: iconColor, size: 24),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: const TextStyle(
                        fontSize: 12,
                        color: Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: Color(0xFF94A3B8)),
            ],
          ),
        ),
      ),
    );
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
                if (index == 2) {
                  _showCreateActionSheet(context);
                  return;
                }
                setState(() {
                  _currentIndex = index;
                  _activeRole = 'user';
                });
              },
              onNotificationsTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                ).then((_) => _fetchDynamicCounts());
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
                onCreateTap: () => _showCreateActionSheet(context),
                onTabSelected: (index) {
                  if (index == 2) {
                    _showCreateActionSheet(context);
                    return;
                  }
                  setState(() {
                    _currentIndex = index;
                    _activeRole = 'user';
                  });
                },
              ),
            ),
        ],
      ),
    );
  }
}
