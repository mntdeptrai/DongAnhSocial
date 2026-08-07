import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'my_checkins_screen.dart';
import 'my_orders_screen.dart';
import 'seller_dashboard_screen.dart';

class ProfileScreen extends StatefulWidget {
  final VoidCallback onLogout;
  final VoidCallback onLoginRequest;

  const ProfileScreen({
    super.key,
    required this.onLogout,
    required this.onLoginRequest,
  });

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  void _showEditProfileDialog(BuildContext context) {
    final user = ApiService.currentUser;
    final nameController = TextEditingController(text: user?['name'] ?? '');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.edit_note_rounded, color: Color(0xFF0EA5E9)),
            SizedBox(width: 8),
            Text('Chỉnh sửa hồ sơ'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Họ và tên', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
            const SizedBox(height: 6),
            TextField(
              controller: nameController,
              decoration: InputDecoration(
                hintText: 'Nhập họ tên mới...',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              ),
            ),
            const SizedBox(height: 12),
            Text('Email: ${user?['email'] ?? 'Chưa cập nhật'}', style: const TextStyle(color: Colors.grey, fontSize: 12)),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Hủy'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Đã cập nhật thông tin cá nhân thành công!'),
                  backgroundColor: Color(0xFF059669),
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF0EA5E9),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Lưu thay đổi'),
          ),
        ],
      ),
    );
  }

  void _showChangePasswordDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.lock_reset_rounded, color: Color(0xFF0EA5E9)),
            SizedBox(width: 8),
            Text('Đổi mật khẩu'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              obscureText: true,
              decoration: InputDecoration(
                labelText: 'Mật khẩu hiện tại',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              obscureText: true,
              decoration: InputDecoration(
                labelText: 'Mật khẩu mới',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy')),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Đã đổi mật khẩu thành công!'),
                  backgroundColor: Color(0xFF059669),
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF0EA5E9),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Cập nhật'),
          ),
        ],
      ),
    );
  }

  void _showSavedPlaces(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => Container(
        padding: const EdgeInsets.all(20),
        child: const Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
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
            Icon(Icons.settings, color: Color(0xFF0EA5E9)),
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
              activeThumbColor: const Color(0xFF0EA5E9),
              onChanged: (val) {},
            ),
            SwitchListTile(
              title: const Text('Âm thanh thông báo'),
              value: true,
              activeThumbColor: const Color(0xFF0EA5E9),
              onChanged: (val) {},
            ),
            SwitchListTile(
              title: const Text('Đồng bộ vị trí GPS thời gian thực'),
              value: true,
              activeThumbColor: const Color(0xFF0EA5E9),
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
            Icon(Icons.help_outline, color: Color(0xFF0EA5E9)),
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
              backgroundColor: const Color(0xFF0EA5E9),
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
                        const Expanded(
                          child: Row(
                            children: [
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
    const primaryColor = Color(0xFF0EA5E9);
    final isGuest = !ApiService.isAuthenticated;
    final user = ApiService.currentUser;
    final isAdmin = user?['role'] == 'admin';

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
                  onPressed: widget.onLoginRequest,
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
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text(
          'Trang cá nhân',
          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 19, letterSpacing: -0.3),
        ),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_outlined, color: primaryColor),
            onPressed: () => _showNotificationsModal(context),
          ),
        ],
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Banner + Profile Card Container
            Stack(
              clipBehavior: Clip.none,
              children: [
                // Cover Photo Banner
                Container(
                  height: 140,
                  width: double.infinity,
                  decoration: const BoxDecoration(
                    image: DecorationImage(
                      image: NetworkImage('https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=800&q=60'),
                      fit: BoxFit.cover,
                    ),
                  ),
                  child: Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          Colors.black.withValues(alpha: 0.3),
                          Colors.black.withValues(alpha: 0.6),
                        ],
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                      ),
                    ),
                    padding: const EdgeInsets.all(12),
                    alignment: Alignment.topRight,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.6),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.camera_alt_outlined, color: Colors.white, size: 14),
                          SizedBox(width: 4),
                          Text('Đổi ảnh bìa', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600)),
                        ],
                      ),
                    ),
                  ),
                ),

                // Main Profile Card (Floating)
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 75, 16, 0),
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(24),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.05),
                          blurRadius: 15,
                          offset: const Offset(0, 5),
                        ),
                      ],
                    ),
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 20),
                    child: Column(
                      children: [
                        // Avatar + Verified Star Badge
                        Stack(
                          alignment: Alignment.bottomRight,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(4),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                shape: BoxShape.circle,
                                boxShadow: [
                                  BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 8),
                                ],
                              ),
                              child: CircleAvatar(
                                radius: 42,
                                backgroundColor: primaryColor.withValues(alpha: 0.1),
                                backgroundImage: ResizeImage(
                                  NetworkImage(ApiService.getAvatarUrl(user, user?['name'])),
                                  width: 200,
                                ),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.all(5),
                              decoration: const BoxDecoration(
                                color: Color(0xFF0EA5E9),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.verified, color: Colors.white, size: 16),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),

                        // Name & Role Badge
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Flexible(
                              child: Text(
                                user?['name'] ?? '',
                                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            const SizedBox(width: 4),
                            const Icon(Icons.star, color: Color(0xFFF59E0B), size: 18),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          isAdmin
                              ? '🏛️ Quản trị viên hệ thống DongAnh Social ⭐️'
                              : '👤 Thành viên cộng đồng DongAnh Social ⭐️',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: isAdmin ? const Color(0xFFDC2626) : const Color(0xFF0284C7),
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 4),
                        Text(
                          user?['email'] ?? '',
                          style: const TextStyle(color: Color(0xFF64748B), fontSize: 13),
                        ),
                        const SizedBox(height: 16),

                        // Social Stats Counters Row
                        Container(
                          padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceAround,
                            children: [
                              _socialStat('4', 'Người theo dõi'),
                              _statDivider(),
                              _socialStat('0', 'Đang theo dõi'),
                              _statDivider(),
                              _socialStat('2', 'Bài viết'),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Quick Actions Buttons Row
                        Row(
                          children: [
                            Expanded(
                              flex: 2,
                              child: ElevatedButton.icon(
                                onPressed: () => _showEditProfileDialog(context),
                                icon: const Icon(Icons.edit, size: 16),
                                label: const Text('Chỉnh sửa hồ sơ', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF2563EB),
                                  foregroundColor: Colors.white,
                                  elevation: 0,
                                  padding: const EdgeInsets.symmetric(vertical: 10),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              flex: 2,
                              child: OutlinedButton.icon(
                                onPressed: () => _showChangePasswordDialog(context),
                                icon: const Icon(Icons.key, size: 16),
                                label: const Text('Đổi mật khẩu', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xFF334155),
                                  side: const BorderSide(color: Color(0xFFCBD5E1)),
                                  padding: const EdgeInsets.symmetric(vertical: 10),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            InkWell(
                              onTap: () {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Đã sao chép liên kết hồ sơ cá nhân!')),
                                );
                              },
                              child: Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  border: Border.all(color: const Color(0xFFCBD5E1)),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: const Icon(Icons.share, size: 18, color: Color(0xFF475569)),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 20),

            // Activity Statistics Cards Section
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Thống kê hoạt động',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900, color: Color(0xFF334155)),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: _activityStatCard('12', 'Địa điểm đã đi', Icons.map_outlined, const Color(0xFF0EA5E9), const Color(0xFFE0F2FE)),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _activityStatCard('8', 'Bài Check-in', Icons.location_on_outlined, const Color(0xFF10B981), const Color(0xFFD1FAE5)),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Service Modules & Settings Section
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFFF1F5F9)),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 10),
                  ],
                ),
                child: Column(
                  children: [
                    // Gian hàng của tôi
                    Material(
                      color: const Color(0xFFF0FDFA),
                      borderRadius: const BorderRadius.vertical(top: Radius.circular(20)),
                      child: ListTile(
                        leading: Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: const Color(0xFF0EA5E9),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.storefront, color: Colors.white, size: 20),
                        ),
                        title: const Text(
                          '🏪 Gian hàng của tôi',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: Color(0xFF0284C7)),
                        ),
                        subtitle: const Text('Kê khai dữ liệu số, niêm yết giá & quản lý đơn hàng', style: TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                        trailing: const Icon(Icons.arrow_forward_ios, size: 14, color: Color(0xFF0284C7)),
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (context) => const SellerDashboardScreen()),
                          );
                        },
                      ),
                    ),
                    const Divider(height: 1, color: Color(0xFFE2E8F0)),

                    // Lịch sử đơn hàng của tôi
                    Material(
                      color: const Color(0xFFFFF7ED),
                      child: ListTile(
                        leading: Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEA580C),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.shopping_bag_rounded, color: Colors.white, size: 20),
                        ),
                        title: const Text(
                          '📦 Lịch sử đơn hàng của tôi',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: Color(0xFFC2410C)),
                        ),
                        subtitle: const Text('Theo dõi trạng thái, đơn đã nhận, hủy đơn & hoàn hàng', style: TextStyle(fontSize: 11, color: Color(0xFF64748B))),
                        trailing: const Icon(Icons.arrow_forward_ios, size: 14, color: Color(0xFFC2410C)),
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (context) => const MyOrdersScreen()),
                          );
                        },
                      ),
                    ),
                    const Divider(height: 1, color: Color(0xFFE2E8F0)),

                    _optionTile(Icons.history_toggle_off_rounded, 'Lịch sử check-in của tôi', () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (context) => const MyCheckinsScreen()),
                      );
                    }),
                    const Divider(height: 1, color: Color(0xFFF1F5F9)),
                    _optionTile(Icons.notifications_active_outlined, 'Thông báo ứng dụng & Tin nhắn', () {
                      _showNotificationsModal(context);
                    }),
                    const Divider(height: 1, color: Color(0xFFF1F5F9)),
                    _optionTile(Icons.favorite_border_rounded, 'Địa điểm đã lưu', () {
                      _showSavedPlaces(context);
                    }),
                    const Divider(height: 1, color: Color(0xFFF1F5F9)),
                    _optionTile(Icons.settings_outlined, 'Cấu hình ứng dụng', () {
                      _showAppConfig(context);
                    }),
                    const Divider(height: 1, color: Color(0xFFF1F5F9)),
                    _optionTile(Icons.help_outline_rounded, 'Hỗ trợ & Trợ giúp', () {
                      _showSupportHelp(context);
                    }),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 24),

            // Logout Button
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () async {
                    await ApiService.logout();
                    widget.onLogout();
                  },
                  icon: const Icon(Icons.logout, size: 18),
                  label: const Text('Đăng xuất tài khoản', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFFEF2F2),
                    foregroundColor: const Color(0xFFDC2626),
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                      side: const BorderSide(color: Color(0xFFFCA5A5), width: 1),
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  Widget _socialStat(String count, String label) {
    return Column(
      children: [
        Text(
          count,
          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
        ),
      ],
    );
  }

  Widget _statDivider() {
    return Container(
      height: 24,
      width: 1,
      color: const Color(0xFFE2E8F0),
    );
  }

  Widget _activityStatCard(String value, String label, IconData icon, Color iconColor, Color bgColor) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFF1F5F9)),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.02), blurRadius: 10),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(14),
            ),
            child: Icon(icon, color: iconColor, size: 22),
          ),
          const SizedBox(width: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                value,
                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
              ),
              Text(
                label,
                style: const TextStyle(color: Color(0xFF64748B), fontSize: 11, fontWeight: FontWeight.w500),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _optionTile(IconData icon, String title, [VoidCallback? onTap]) {
    return ListTile(
      leading: Icon(icon, color: const Color(0xFF64748B), size: 20),
      title: Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF334155))),
      trailing: const Icon(Icons.arrow_forward_ios, size: 13, color: Color(0xFF94A3B8)),
      onTap: onTap,
    );
  }
}
