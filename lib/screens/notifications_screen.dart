import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/custom_loader.dart';
import 'news_bulletin_screen.dart';
import 'feed_screen.dart';
import 'food_tour_screen.dart';
import 'eatery_detail_screen.dart';
import 'my_orders_screen.dart';
import 'seller_dashboard_screen.dart';
import 'chat_screen.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List<dynamic> _notifications = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchNotifications();
  }

  Future<void> _fetchNotifications() async {
    setState(() => _isLoading = true);
    try {
      final res = await ApiService.getAppNotifications();
      if (mounted) {
        setState(() {
          _notifications = res;
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    const primaryColor = Color(0xFF0EA5E9);

    return Scaffold(
      appBar: AppBar(
        title: const Text.rich(
          TextSpan(
            children: [
              TextSpan(
                text: 'DongAnh',
                style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 18),
              ),
              TextSpan(
                text: ' Notifications',
                style: TextStyle(color: Color(0xFFFFB800), fontWeight: FontWeight.w900, fontSize: 18),
              ),
            ],
          ),
        ),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF38BDF8), Color(0xFF00A8EE), Color(0xFF0284C7)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
        ),
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.done_all),
            tooltip: 'Đánh dấu đã đọc tất cả',
            onPressed: () {
              setState(() {
                for (var item in _notifications) {
                  item['is_read'] = true;
                }
              });
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Đã đánh dấu đọc tất cả thông báo!')),
              );
            },
          ),
        ],
      ),
      body: _isLoading
          ? const CustomPulseLoader(
              message: 'Đang làm mới thông báo hệ thống...',
              icon: Icons.notifications_active_rounded,
              primaryColor: Color(0xFF6366F1),
            )
          : _notifications.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.notifications_none_outlined, size: 64, color: Colors.grey[400]),
                      const SizedBox(height: 12),
                      const Text(
                        'Chưa có thông báo nào mới.',
                        style: TextStyle(color: Colors.grey, fontSize: 15),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _fetchNotifications,
                  child: ListView.separated(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    itemCount: _notifications.length,
                    separatorBuilder: (_, __) => const Divider(height: 1),
                    itemBuilder: (context, index) {
                      final item = _notifications[index];
                      final bool isRead = item['is_read'] ?? true;
                      final type = item['type'] ?? 'system';

                      IconData iconData = Icons.notifications_active;
                      Color iconColor = primaryColor;

                      if (type == 'seller_order') {
                        iconData = Icons.storefront_rounded;
                        iconColor = Colors.amber[800]!;
                      } else if (type == 'my_order') {
                        iconData = Icons.local_shipping_rounded;
                        iconColor = Colors.green;
                      } else if (type == 'reaction') {
                        iconData = Icons.favorite_rounded;
                        iconColor = const Color(0xFFEF4444);
                      } else if (type == 'share') {
                        iconData = Icons.share_rounded;
                        iconColor = const Color(0xFF3B82F6);
                      } else if (type == 'review') {
                        iconData = Icons.star_rounded;
                        iconColor = const Color(0xFFF59E0B);
                      } else if (type == 'new_post') {
                        iconData = Icons.article_rounded;
                        iconColor = const Color(0xFF10B981);
                      } else if (type == 'comment' || type == 'checkin') {
                        iconData = Icons.chat_bubble_outline_rounded;
                        iconColor = const Color(0xFF0EA5E9);
                      } else if (type == 'friend') {
                        iconData = Icons.person_add_rounded;
                        iconColor = Colors.purple;
                      }

                      return Container(
                        color: isRead ? Colors.transparent : primaryColor.withOpacity(0.05),
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor: iconColor.withOpacity(0.15),
                            child: Icon(iconData, color: iconColor, size: 22),
                          ),
                          title: Text(
                            item['title'] ?? 'Thông báo',
                            style: TextStyle(
                              fontWeight: isRead ? FontWeight.w600 : FontWeight.w800,
                              fontSize: 15,
                              color: Colors.grey[900],
                            ),
                          ),
                          subtitle: Padding(
                            padding: const EdgeInsets.only(top: 4.0),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  item['body'] ?? '',
                                  style: TextStyle(fontSize: 13, color: Colors.grey[700]),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  item['time'] ?? '',
                                  style: TextStyle(fontSize: 11, color: Colors.grey[500]),
                                ),
                              ],
                            ),
                          ),
                          onTap: () {
                            setState(() {
                              item['is_read'] = true;
                            });

                            final String notifType = item['type'] ?? 'system';
                            final String postType = item['post_type'] ?? '';
                            final String targetUrl = item['target_url'] ?? '';

                            if (notifType == 'seller_order') {
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const SellerDashboardScreen()),
                              );
                            } else if (notifType == 'my_order') {
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const MyOrdersScreen()),
                              );
                            } else if (notifType == 'friend') {
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const ChatScreen()),
                              );
                            } else if (notifType == 'review' || postType == 'eatery') {
                              if (targetUrl.contains('/dia-diem/')) {
                                final parts = targetUrl.split('/dia-diem/');
                                if (parts.length > 1 && parts[1].isNotEmpty) {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (_) => EateryDetailScreen(
                                        categorySlug: 'co-so-kinh-doanh',
                                        eaterySlug: parts[1],
                                      ),
                                    ),
                                  );
                                  return;
                                }
                              }
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const SellerDashboardScreen()),
                              );
                            } else if (postType == 'checkin' || notifType == 'checkin') {
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const FeedScreen()),
                              );
                            } else if (postType == 'diary') {
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const FoodTourScreen()),
                              );
                            } else {
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const NewsBulletinScreen()),
                              );
                            }
                          },
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
