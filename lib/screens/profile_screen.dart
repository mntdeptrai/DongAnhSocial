import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'my_checkins_screen.dart';
import 'my_orders_screen.dart';
import 'seller_dashboard_screen.dart';

class ProfileScreen extends StatelessWidget {
  final VoidCallback onLogout;
  final VoidCallback onLoginRequest;

  const ProfileScreen({
    super.key,
    required this.onLogout,
    required this.onLoginRequest,
  });

  void _showSavedPlaces(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => Container(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.favorite, color: Colors.redAccent, size: 24),
                SizedBox(width: 8),
                Text('Địa điểm đã lưu', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0077B6))),
              ],
            ),
            const Divider(height: 20),
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 24),
              child: Center(
                child: Column(
                  children: [
                    Icon(Icons.bookmark_outline, size: 48, color: Colors.grey),
                    SizedBox(height: 8),
                    Text('Chưa có địa điểm đã lưu', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                    SizedBox(height: 4),
                    Text('Các địa điểm yêu thích của bạn sẽ hiển thị tại đây.', style: TextStyle(color: Colors.grey, fontSize: 12)),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showAppConfig(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.settings, color: Color(0xFF00A8EE)),
            SizedBox(width: 8),
            Text('Cấu hình ứng dụng'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            SwitchListTile(
              title: const Text('Thông báo đẩy (Push Notifications)'),
              value: true,
              activeColor: const Color(0xFF00A8EE),
              onChanged: (val) {},
            ),
            SwitchListTile(
              title: const Text('Âm thanh thông báo'),
              value: true,
              activeColor: const Color(0xFF00A8EE),
              onChanged: (val) {},
            ),
            SwitchListTile(
              title: const Text('Đồng bộ vị trí GPS thời gian thực'),
              value: true,
              activeColor: const Color(0xFF00A8EE),
              onChanged: (val) {},
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Đóng'),
          ),
        ],
      ),
    );
  }

  void _showSupportHelp(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.help_outline, color: Color(0xFF00A8EE)),
            SizedBox(width: 8),
            Text('Hỗ trợ & Trợ giúp'),
          ],
        ),
        content: const Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('🏛️ Trung tâm Hỗ trợ Đông Anh Discovery', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            SizedBox(height: 8),
            Text('📞 Hotline hỗ trợ: 0988.xxx.xxx'),
            SizedBox(height: 4),
            Text('✉️ Email: support@xadonganh.com'),
            SizedBox(height: 4),
            Text('🌐 Website: donganhdiscovery.xadonganh.com'),
            SizedBox(height: 12),
            Text('Đội ngũ kỹ thuật trực hỗ trợ 24/7 giải đáp mọi thắc mắc về chợ số, bản đồ & gian hàng OCOP.'),
          ],
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF00A8EE),
              foregroundColor: Colors.white,
            ),
            child: const Text('Đã hiểu'),
          ),
        ],
      ),
    );
  }

  void _showNotificationsModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          return FutureBuilder<List<dynamic>>(
            future: ApiService.getAppNotifications(),
            builder: (context, snapshot) {
              final List<dynamic> notifs = snapshot.data ?? [];
              final bool isLoading = snapshot.connectionState == ConnectionState.waiting;

              return Container(
                padding: const EdgeInsets.all(20),
                constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.7),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Row(
                            children: const [
                              Icon(Icons.notifications_active, color: Color(0xFFFFB800), size: 24),
                              SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  'Thông báo hệ thống & Đơn hàng',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0077B6)),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close, color: Colors.grey),
                          onPressed: () => Navigator.pop(ctx),
                        ),
                      ],
                    ),
                    const Divider(height: 20),
                    if (isLoading)
                      const Padding(
                        padding: EdgeInsets.all(32.0),
                        child: Center(child: CircularProgressIndicator(color: Color(0xFF0284C7))),
                      )
                    else if (notifs.isEmpty)
                      const Padding(
                        padding: EdgeInsets.all(32.0),
                        child: Center(child: Text('Chưa có thông báo mới', style: TextStyle(color: Colors.grey))),
                      )
                    else
                      Expanded(
                        child: ListView.separated(
                          shrinkWrap: true,
                          itemCount: notifs.length,
                          separatorBuilder: (_, __) => const Divider(height: 1),
                          itemBuilder: (context, index) {
                            final item = notifs[index];
                            final String title = item['title'] ?? 'Thông báo';
                            final String body = item['body'] ?? '';
                            final String time = item['time'] ?? 'Vừa xong';
                            final String iconType = item['icon'] ?? 'notifications';

                            IconData iconData = Icons.notifications;
                            Color bg = const Color(0xFFE0F2FE);
                            Color fg = const Color(0xFF0284C7);

                            if (iconType == 'comment') {
                              iconData = Icons.comment;
                              bg = const Color(0xFFE0F2FE);
                              fg = const Color(0xFF0284C7);
                            } else if (iconType == 'card_giftcard') {
                              iconData = Icons.card_giftcard;
                              bg = const Color(0xFFFFFBEB);
                              fg = const Color(0xFFD97706);
                            } else if (iconType == 'local_shipping') {
                              iconData = Icons.local_shipping;
                              bg = const Color(0xFFECFDF5);
                              fg = const Color(0xFF059669);
                            }

                            return ListTile(
                              contentPadding: const EdgeInsets.symmetric(vertical: 4, horizontal: 0),
                              leading: CircleAvatar(backgroundColor: bg, child: Icon(iconData, color: fg, size: 20)),
                              title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              subtitle: Text(body, style: const TextStyle(fontSize: 12)),
                              trailing: Text(time, style: const TextStyle(fontSize: 10, color: Colors.grey)),
                            );
                          },
                        ),
                      ),
                  ],
                ),
              );
            },
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = const Color(0xFF00A8EE);
    final isGuest = !ApiService.isAuthenticated;
    final user = ApiService.currentUser;

    if (isGuest) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Cá nhân', style: TextStyle(fontWeight: FontWeight.bold)),
          backgroundColor: Colors.white,
          foregroundColor: const Color(0xFF0F172A),
          elevation: 0,
        ),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.account_circle_outlined, size: 64, color: Colors.grey[300]),
                const SizedBox(height: 16),
                const Text(
                  'Bạn đang ở chế độ Khách vãng lai. Hãy đăng nhập để lưu trữ lịch sử check-in và chat với bạn bè.',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 15, color: Colors.grey),
                ),
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: onLoginRequest,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: primaryColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('Đăng nhập ngay', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Trang cá nhân',
          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 20, letterSpacing: -0.5),
        ),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_outlined, color: Color(0xFF00A8EE)),
            onPressed: () => _showNotificationsModal(context),
          ),
        ],
      ),
      backgroundColor: const Color(0xFFF8FAFC),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // User profile card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: Colors.grey[200]!.withOpacity(0.4),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 40,
                    backgroundColor: primaryColor.withOpacity(0.1),
                    child: Text(
                      user?['name']?[0] ?? '👤',
                      style: TextStyle(color: primaryColor, fontSize: 32, fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    user?['name'] ?? '',
                    style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    user?['email'] ?? '',
                    style: TextStyle(color: Colors.grey[500], fontSize: 14),
                  ),
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(
                      color: user?['role'] == 'admin' ? Colors.red[50] : Colors.blue[50],
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      user?['role'] == 'admin' ? '🏛️ Quản trị viên' : '👤 Thành viên cộng đồng',
                      style: TextStyle(
                        color: user?['role'] == 'admin' ? Colors.red : Colors.blue[700],
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Statistics Section
            const Text(
              'Thống kê hoạt động',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.grey),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: _statCard('12', 'Địa điểm đã đi', Icons.map_outlined, primaryColor),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _statCard('8', 'Bài Check-in', Icons.location_on_outlined, Colors.green),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Options List
            Material(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              clipBehavior: Clip.antiAlias,
              child: Container(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.grey[100]!),
                ),
                child: Column(
                  children: [
                    Material(
                      color: const Color(0xFF00A8EE).withOpacity(0.08),
                      child: ListTile(
                        leading: const CircleAvatar(
                          backgroundColor: Color(0xFF00A8EE),
                          child: Icon(Icons.storefront, color: Colors.white, size: 20),
                        ),
                        title: const Text(
                          '🏪 Gian hàng của tôi',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF00A8EE)),
                        ),
                        subtitle: const Text('Kê khai dữ liệu số, niêm yết giá & quản lý đơn hàng', style: TextStyle(fontSize: 11)),
                        trailing: const Icon(Icons.arrow_forward_ios, size: 14, color: Color(0xFF00A8EE)),
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (context) => const SellerDashboardScreen()),
                          );
                        },
                      ),
                    ),
                    const Divider(height: 1),
                    Material(
                      color: const Color(0xFFEA580C).withOpacity(0.06),
                      child: ListTile(
                        leading: const CircleAvatar(
                          backgroundColor: Color(0xFFEA580C),
                          child: Icon(Icons.shopping_bag_rounded, color: Colors.white, size: 20),
                        ),
                        title: const Text(
                          '📦 Lịch sử đơn hàng của tôi',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFFEA580C)),
                        ),
                        subtitle: const Text('Theo dõi trạng thái, đơn đã nhận, hủy đơn & hoàn hàng', style: TextStyle(fontSize: 11)),
                        trailing: const Icon(Icons.arrow_forward_ios, size: 14, color: Color(0xFFEA580C)),
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (context) => const MyOrdersScreen()),
                          );
                        },
                      ),
                    ),
                    const Divider(height: 1),
                    _optionTile(Icons.history, 'Lịch sử check-in của tôi', () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (context) => const MyCheckinsScreen()),
                      );
                    }),
                    const Divider(height: 1),
                    _optionTile(Icons.notifications_active_outlined, 'Thông báo ứng dụng & Tin nhắn', () {
                      _showNotificationsModal(context);
                    }),

                    const Divider(height: 1),
                    _optionTile(Icons.favorite_border, 'Địa điểm đã lưu', () {
                      _showSavedPlaces(context);
                    }),
                    const Divider(height: 1),
                    _optionTile(Icons.settings_outlined, 'Cấu hình ứng dụng', () {
                      _showAppConfig(context);
                    }),
                    const Divider(height: 1),
                    _optionTile(Icons.help_outline, 'Hỗ trợ & Trợ giúp', () {
                      _showSupportHelp(context);
                    }),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 32),

            // Logout Button
            ElevatedButton.icon(
              onPressed: () async {
                await ApiService.logout();
                onLogout();
              },
              icon: const Icon(Icons.logout),
              label: const Text('Đăng Xuất', style: TextStyle(fontWeight: FontWeight.bold)),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red[50],
                foregroundColor: Colors.red,
                elevation: 0,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _statCard(String value, String label, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey[100]!),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 12),
          Text(
            value,
            style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: const TextStyle(color: Colors.grey, fontSize: 12),
          ),
        ],
      ),
    );
  }

  Widget _optionTile(IconData icon, String title, [VoidCallback? onTap]) {
    return ListTile(
      leading: Icon(icon, color: Colors.grey[600]),
      title: Text(title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
      trailing: const Icon(Icons.arrow_forward_ios, size: 14, color: Colors.grey),
      onTap: onTap,
    );
  }
}
