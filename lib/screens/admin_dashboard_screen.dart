import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/moderation_service.dart';
import '../widgets/custom_loader.dart';
import 'map_screen.dart';

class AdminDashboardScreen extends StatefulWidget {
  final VoidCallback? onBack;
  final int initialTabIndex;

  const AdminDashboardScreen({super.key, this.onBack, this.initialTabIndex = 0});

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;

  Map<String, dynamic> _stats = {};
  List<dynamic> _usersList = [];
  List<dynamic> _eateriesList = [];
  List<dynamic> _reviewsList = [];
  List<dynamic> _schoolsList = [];
  List<dynamic> _stallsList = [];

  String _userSearchQuery = '';
  String _eaterySearchQuery = '';
  String _schoolSearchQuery = '';
  String _stallSearchQuery = '';
  final String _selectedCategoryFilter = 'Tất cả';
  String _moderationFilter = 'pending';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(
      length: 6,
      initialIndex: widget.initialTabIndex.clamp(0, 5),
      vsync: this,
    );
    _loadAdminData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadAdminData() async {
    setState(() => _isLoading = true);
    try {
      final data = await ApiService.getAdminDashboardData();
      if (data['success'] == true) {
        if (mounted) {
          setState(() {
            _stats = data['stats'] ?? {};
            _usersList = data['users'] ?? [];
            _eateriesList = data['eateries'] ?? [];
            _reviewsList = data['reviews'] ?? [];
            _schoolsList = (data['schools'] is List) ? data['schools'] : [];
            _stallsList = (data['stalls'] is List) ? data['stalls'] : [];
          });
        }
      } else {
        final users = await ApiService.getAdminUsers();
        if (mounted) {
          setState(() {
            _usersList = users;
          });
        }
      }

      // Fetch real Schools from DB if empty
      if (_schoolsList.isEmpty) {
        final educationData = await ApiService.getEateries('smart-education-map');
        if (educationData.isNotEmpty && mounted) {
          setState(() {
            _schoolsList = educationData.map((e) => {
              'id': e['id'],
              'name': e['name'],
              'address': e['address'] ?? 'Xã Đông Anh, Hà Nội',
              'level': e['category_name'] ?? 'Trường học',
            }).toList();
          });
        }
      }

      // Fetch real Market Stalls from DB if empty
      if (_stallsList.isEmpty) {
        final marketData = await ApiService.getManagerDashboardData();
        if (marketData['stalls'] is List && (marketData['stalls'] as List).isNotEmpty && mounted) {
          setState(() {
            _stallsList = (marketData['stalls'] as List).map((s) => {
              'id': s['id'],
              'name': s['name'],
              'vendor': s['address'] ?? 'Chủ gian hàng Đông Anh',
              'phone': s['phone'] ?? 'Chưa cập nhật',
              'status': s['status'] ?? 'approved',
            }).toList();
          });
        }
      }
    } catch (e) {
      debugPrint('AdminDashboard fetch error: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  // =========================================================================
  // ACTIONS: USERS
  // =========================================================================

  void _showAddUserDialog() {
    final nameCtrl = TextEditingController();
    final emailCtrl = TextEditingController();
    final passCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();
    String selectedRole = 'user';

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Row(
            children: [
              Icon(Icons.person_add_alt_1_rounded, color: Color(0xFFDC2626)),
              SizedBox(width: 8),
              Text('➕ Thêm User Mới', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: nameCtrl,
                  decoration: const InputDecoration(labelText: 'Họ và Tên *', hintText: 'Ví dụ: Nguyễn Văn A'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: emailCtrl,
                  keyboardType: TextInputType.emailAddress,
                  decoration: const InputDecoration(labelText: 'Email *', hintText: 'user@donganh.vn'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: passCtrl,
                  obscureText: true,
                  decoration: const InputDecoration(labelText: 'Mật khẩu *', hintText: 'Tối thiểu 6 ký tự'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: phoneCtrl,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(labelText: 'Số điện thoại', hintText: '0987654321'),
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  initialValue: selectedRole,
                  decoration: const InputDecoration(labelText: 'Phân quyền (Role)'),
                  items: const [
                    DropdownMenuItem(value: 'user', child: Text('👤 Customer (Người dùng)')),
                    DropdownMenuItem(value: 'seller', child: Text('🛍️ Seller (Chủ gian hàng)')),
                    DropdownMenuItem(value: 'manager', child: Text('🏛️ Manager (Ban QL Chợ)')),
                    DropdownMenuItem(value: 'admin', child: Text('🛡️ Administrator')),
                  ],
                  onChanged: (val) {
                    if (val != null) setDialogState(() => selectedRole = val);
                  },
                ),
              ],
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy')),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFDC2626)),
              onPressed: () async {
                if (nameCtrl.text.trim().isNotEmpty && emailCtrl.text.trim().isNotEmpty && passCtrl.text.trim().isNotEmpty) {
                  final name = nameCtrl.text.trim();
                  final email = emailCtrl.text.trim();
                  final pass = passCtrl.text.trim();
                  final phone = phoneCtrl.text.trim();
                  final messenger = ScaffoldMessenger.of(context);
                  Navigator.pop(ctx);
                  final res = await ApiService.storeUser(name: name, email: email, password: pass, role: selectedRole, phone: phone);
                  if (mounted) {
                    if (res['success'] == true) {
                      messenger.showSnackBar(
                        SnackBar(content: Text('🎉 ${res['message']}'), backgroundColor: const Color(0xFF10B981)),
                      );
                      _loadAdminData();
                    } else {
                      messenger.showSnackBar(
                        SnackBar(content: Text('⚠️ ${res['message']}'), backgroundColor: Colors.red),
                      );
                    }
                  }
                }
              },
              child: const Text('Thêm User Mới', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  void _showEditUserDialog(Map<String, dynamic> user) {
    final nameCtrl = TextEditingController(text: user['name'] ?? '');
    final emailCtrl = TextEditingController(text: user['email'] ?? '');
    final phoneCtrl = TextEditingController(text: user['phone'] ?? '');
    String selectedRole = user['role'] ?? 'user';
    String selectedStatus = user['status'] ?? 'active';

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Row(
            children: [
              Icon(Icons.edit_note_rounded, color: Color(0xFFDC2626)),
              SizedBox(width: 8),
              Text('✏️ Chỉnh Sửa Tài Khoản', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: nameCtrl,
                  decoration: const InputDecoration(labelText: 'Họ và Tên *'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: emailCtrl,
                  keyboardType: TextInputType.emailAddress,
                  decoration: const InputDecoration(labelText: 'Email *'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: phoneCtrl,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(labelText: 'Số điện thoại'),
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  initialValue: selectedRole,
                  decoration: const InputDecoration(labelText: 'Phân quyền (Role)'),
                  items: const [
                    DropdownMenuItem(value: 'user', child: Text('👤 Customer (Người dùng)')),
                    DropdownMenuItem(value: 'seller', child: Text('🛍️ Seller (Chủ gian hàng)')),
                    DropdownMenuItem(value: 'manager', child: Text('🏛️ Manager (Ban QL Chợ)')),
                    DropdownMenuItem(value: 'admin', child: Text('🛡️ Administrator')),
                  ],
                  onChanged: (val) {
                    if (val != null) setDialogState(() => selectedRole = val);
                  },
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  initialValue: selectedStatus,
                  decoration: const InputDecoration(labelText: 'Trạng thái tài khoản'),
                  items: const [
                    DropdownMenuItem(value: 'active', child: Text('🟢 Hoạt động (Active)')),
                    DropdownMenuItem(value: 'ban_24h', child: Text('⏳ Tạm khóa 24 giờ')),
                    DropdownMenuItem(value: 'ban_3d', child: Text('⏳ Tạm khóa 3 ngày')),
                    DropdownMenuItem(value: 'ban_7d', child: Text('⏳ Tạm khóa 7 ngày')),
                    DropdownMenuItem(value: 'ban_30d', child: Text('⏳ Tạm khóa 30 ngày')),
                    DropdownMenuItem(value: 'disabled', child: Text('🔴 Khóa vĩnh viễn (Disabled)')),
                  ],
                  onChanged: (val) {
                    if (val != null) setDialogState(() => selectedStatus = val);
                  },
                ),
              ],
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy')),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFDC2626)),
              onPressed: () async {
                if (nameCtrl.text.trim().isNotEmpty && emailCtrl.text.trim().isNotEmpty) {
                  final name = nameCtrl.text.trim();
                  final email = emailCtrl.text.trim();
                  final phone = phoneCtrl.text.trim();
                  final messenger = ScaffoldMessenger.of(context);
                  Navigator.pop(ctx);
                  final uId = user['id'].toString();
                  if (selectedStatus == 'active') {
                    await ModerationService.unblockUser(uId);
                  } else if (selectedStatus == 'ban_24h') {
                    await ModerationService.banUserWithDuration(userId: uId, duration: const Duration(hours: 24));
                  } else if (selectedStatus == 'ban_3d') {
                    await ModerationService.banUserWithDuration(userId: uId, duration: const Duration(days: 3));
                  } else if (selectedStatus == 'ban_7d') {
                    await ModerationService.banUserWithDuration(userId: uId, duration: const Duration(days: 7));
                  } else if (selectedStatus == 'ban_30d') {
                    await ModerationService.banUserWithDuration(userId: uId, duration: const Duration(days: 30));
                  } else if (selectedStatus == 'disabled') {
                    await ModerationService.banUserWithDuration(userId: uId, duration: null);
                  }
                  final res = await ApiService.updateUserWeb(user['id'], {
                    'name': name,
                    'email': email,
                    'role': selectedRole,
                    'phone': phone,
                    'status': selectedStatus == 'active' ? 'active' : 'disabled',
                  });
                  if (mounted) {
                    if (res['id'] != null || res['success'] == true) {
                      messenger.showSnackBar(
                        const SnackBar(content: Text('🎉 Cập nhật tài khoản thành công!'), backgroundColor: Color(0xFF10B981)),
                      );
                      _loadAdminData();
                    } else {
                      messenger.showSnackBar(
                        SnackBar(content: Text('⚠️ ${res['message'] ?? 'Lỗi cập nhật'}'), backgroundColor: Colors.red),
                      );
                    }
                  }
                }
              },
              child: const Text('Lưu Thay Đổi', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  void _showEditEateryDialog(Map<String, dynamic> eatery) {
    final nameCtrl = TextEditingController(text: eatery['name'] ?? '');
    final addressCtrl = TextEditingController(text: eatery['address'] ?? '');
    final phoneCtrl = TextEditingController(text: eatery['phone'] ?? '');
    final hoursCtrl = TextEditingController(text: eatery['opening_hours'] ?? '06:00 - 22:00');
    final priceCtrl = TextEditingController(text: eatery['price_range'] ?? '');
    final imageCtrl = TextEditingController(text: eatery['image_path'] ?? eatery['image'] ?? '');
    bool isFeatured = eatery['is_featured'] ?? false;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Row(
            children: [
              Icon(Icons.edit_location_alt_rounded, color: Color(0xFFDC2626)),
              SizedBox(width: 8),
              Text('✏️ Chỉnh Sửa Địa Điểm', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: nameCtrl,
                  decoration: const InputDecoration(labelText: 'Tên Cơ sở / Địa điểm *'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: addressCtrl,
                  decoration: const InputDecoration(labelText: 'Địa chỉ đầy đủ *'),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: phoneCtrl,
                        keyboardType: TextInputType.phone,
                        decoration: const InputDecoration(labelText: 'Số điện thoại'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: hoursCtrl,
                        decoration: const InputDecoration(labelText: 'Giờ mở cửa'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: priceCtrl,
                  decoration: const InputDecoration(labelText: 'Mức giá tham khảo'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: imageCtrl,
                  decoration: const InputDecoration(labelText: 'URL Ảnh đại diện cơ sở'),
                ),
                const SizedBox(height: 8),
                CheckboxListTile(
                  title: const Text('⭐ Đánh dấu địa điểm Nổi bật', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                  value: isFeatured,
                  contentPadding: EdgeInsets.zero,
                  activeColor: const Color(0xFFDC2626),
                  onChanged: (val) => setDialogState(() => isFeatured = val ?? false),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy')),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFDC2626)),
              onPressed: () async {
                if (nameCtrl.text.trim().isNotEmpty && addressCtrl.text.trim().isNotEmpty) {
                  final name = nameCtrl.text.trim();
                  final address = addressCtrl.text.trim();
                  final phone = phoneCtrl.text.trim();
                  final hours = hoursCtrl.text.trim();
                  final price = priceCtrl.text.trim();
                  final imgUrl = imageCtrl.text.trim();

                  final messenger = ScaffoldMessenger.of(context);
                  Navigator.pop(ctx);
                  final res = await ApiService.updateEatery(eatery['id'], {
                    'name': name,
                    'address': address,
                    'phone': phone,
                    'opening_hours': hours,
                    'price_range': price,
                    'is_featured': isFeatured,
                    if (imgUrl.isNotEmpty) 'image_url': imgUrl,
                  });

                  if (mounted) {
                    if (res['eatery'] != null || res['success'] == true) {
                      messenger.showSnackBar(
                        const SnackBar(content: Text('🎉 Cập nhật địa điểm thành công!'), backgroundColor: Color(0xFF10B981)),
                      );
                      _loadAdminData();
                    } else {
                      messenger.showSnackBar(
                        SnackBar(content: Text('⚠️ ${res['message'] ?? 'Lỗi cập nhật'}'), backgroundColor: Colors.red),
                      );
                    }
                  }
                }
              },
              child: const Text('Lưu Thay Đổi', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  void _changeUserRole(int index, String newRole) async {
    final user = _usersList[index];
    final int userId = user['id'] is int ? user['id'] : (int.tryParse(user['id']?.toString() ?? '0') ?? 0);

    setState(() {
      _usersList[index]['role'] = newRole;
    });

    if (userId > 0) {
      await ApiService.updateUserRole(userId, newRole);
    }

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('🎉 Đã cập nhật quyền ${newRole.toUpperCase()} cho ${user['name'] ?? 'người dùng'}!'),
          backgroundColor: const Color(0xFF10B981),
        ),
      );
    }
  }

  void _deleteUser(int index) async {
    final user = _usersList[index];
    final int userId = user['id'] is int ? user['id'] : (int.tryParse(user['id']?.toString() ?? '0') ?? 0);

    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Xóa tài khoản'),
        content: Text('Bạn có chắc muốn xóa người dùng "${user['name']}" khỏi hệ thống?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Hủy')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Xóa ngay', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirm == true && userId > 0) {
      setState(() => _usersList.removeAt(index));
      await ApiService.deleteUser(userId);
    }
  }

  // =========================================================================
  // ACTIONS: EATERIES (BẢN ĐỒ SỐ)
  // =========================================================================

  void _showAddEateryDialog() {
    final nameCtrl = TextEditingController();
    final addressCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();
    final hoursCtrl = TextEditingController(text: '06:00 - 22:00');
    final priceCtrl = TextEditingController(text: '30.000đ - 100.000đ');
    final latCtrl = TextEditingController(text: '21.117158');
    final lngCtrl = TextEditingController(text: '105.895619');
    final imageCtrl = TextEditingController();
    bool isFeatured = false;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Row(
            children: [
              Icon(Icons.add_location_alt_rounded, color: Color(0xFFDC2626)),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  '📍 Đăng Ký Địa Điểm Mới',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: nameCtrl,
                  decoration: const InputDecoration(labelText: 'Tên cơ sở / Quán ăn / Khách sạn *', hintText: 'Ví dụ: Bún chả Hùng Thái Cổ Loa'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: addressCtrl,
                  decoration: const InputDecoration(labelText: 'Địa chỉ chi tiết *', hintText: 'Thôn Mạch Tràng, Cổ Loa, Đông Anh'),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: phoneCtrl,
                        keyboardType: TextInputType.phone,
                        decoration: const InputDecoration(labelText: 'SĐT liên hệ', hintText: '0987654321'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: hoursCtrl,
                        decoration: const InputDecoration(labelText: 'Giờ mở cửa', hintText: '06:00 - 22:00'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: latCtrl,
                        keyboardType: const TextInputType.numberWithOptions(decimal: true),
                        decoration: const InputDecoration(labelText: 'Vĩ độ (Lat)', hintText: '21.117158'),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: lngCtrl,
                        keyboardType: const TextInputType.numberWithOptions(decimal: true),
                        decoration: const InputDecoration(labelText: 'Kinh độ (Lng)', hintText: '105.895619'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: priceCtrl,
                  decoration: const InputDecoration(labelText: 'Mức giá tham khảo', hintText: '30.000đ - 80.000đ'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: imageCtrl,
                  decoration: const InputDecoration(labelText: 'URL Ảnh đại diện cơ sở', hintText: 'https://...'),
                ),
                const SizedBox(height: 8),
                CheckboxListTile(
                  title: const Text('⭐ Đánh dấu địa điểm Nổi bật', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                  value: isFeatured,
                  contentPadding: EdgeInsets.zero,
                  activeColor: const Color(0xFFDC2626),
                  onChanged: (val) => setDialogState(() => isFeatured = val ?? false),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy')),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFDC2626)),
              onPressed: () async {
                if (nameCtrl.text.trim().isNotEmpty && addressCtrl.text.trim().isNotEmpty) {
                  final name = nameCtrl.text.trim();
                  final address = addressCtrl.text.trim();
                  final phone = phoneCtrl.text.trim();
                  final hours = hoursCtrl.text.trim();
                  final price = priceCtrl.text.trim();
                  final lat = double.tryParse(latCtrl.text.trim()) ?? 21.117158;
                  final lng = double.tryParse(lngCtrl.text.trim()) ?? 105.895619;
                  final imgUrl = imageCtrl.text.trim();

                  Navigator.pop(ctx);
                  final messenger = ScaffoldMessenger.of(context);
                  final res = await ApiService.storeEatery(
                    name: name,
                    categoryId: 1,
                    communeId: 1,
                    address: address,
                    phone: phone,
                    openingHours: hours,
                    priceRange: price,
                    latitude: lat,
                    longitude: lng,
                    isFeatured: isFeatured,
                    imageUrl: imgUrl.isNotEmpty ? imgUrl : null,
                  );

                  if (mounted && res['success'] == true) {
                    messenger.showSnackBar(
                      SnackBar(content: Text('🎉 ${res['message']}'), backgroundColor: const Color(0xFF10B981)),
                    );
                    _loadAdminData();
                  }
                }
              },
              child: const Text('Đăng Ký Địa Điểm', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  void _toggleFeatured(int index) async {
    final eatery = _eateriesList[index];
    final int id = eatery['id'] is int ? eatery['id'] : (int.tryParse(eatery['id']?.toString() ?? '0') ?? 0);
    final currentStatus = eatery['is_featured'] == true;

    setState(() {
      _eateriesList[index]['is_featured'] = !currentStatus;
    });

    if (id > 0) {
      await ApiService.toggleEateryFeatured(id);
    }
  }

  void _deleteEatery(int index) async {
    final eatery = _eateriesList[index];
    final int id = eatery['id'] is int ? eatery['id'] : (int.tryParse(eatery['id']?.toString() ?? '0') ?? 0);

    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Xác nhận xóa địa điểm'),
        content: Text('Bạn có chắc chắn muốn xóa địa điểm "${eatery['name']}"? Action này không thể hoàn tác.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Hủy')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Xóa ngay', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirm == true && id > 0) {
      setState(() {
        _eateriesList.removeAt(index);
      });
      await ApiService.deleteEatery(id);
    }
  }

  void _handleBack() {
    if (Navigator.canPop(context)) {
      Navigator.pop(context);
    } else if (widget.onBack != null) {
      widget.onBack!();
    }
  }

  @override
  Widget build(BuildContext context) {
    const crimsonColor = Color(0xFFDC2626);
    const darkObsidian = Color(0xFF090D16);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: darkObsidian,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: Colors.white),
          tooltip: 'Quay lại',
          onPressed: _handleBack,
        ),
        title: const Row(
          children: [
            Icon(Icons.shield_rounded, color: crimsonColor, size: 22),
            SizedBox(width: 8),
            Expanded(
              child: Text(
                'Kênh Điều Hành Tối Cao System Admin',
                style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            tooltip: 'Làm mới dữ liệu',
            onPressed: _loadAdminData,
          ),
          IconButton(
            icon: const Icon(Icons.home_rounded, color: Colors.white),
            tooltip: 'Quay về trang chủ',
            onPressed: _handleBack,
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          indicatorColor: crimsonColor,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.grey.shade400,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
          tabs: const [
            Tab(icon: Icon(Icons.dashboard_rounded, size: 18), text: 'Tổng quan'),
            Tab(icon: Icon(Icons.shield_rounded, size: 18), text: 'Kiểm duyệt vi phạm'),
            Tab(icon: Icon(Icons.location_on_rounded, size: 18), text: 'Địa điểm & Cơ sở'),
            Tab(icon: Icon(Icons.school_rounded, size: 18), text: 'Trường học & Sáp nhập'),
            Tab(icon: Icon(Icons.shopping_bag_rounded, size: 18), text: 'Gian hàng & OCOP'),
            Tab(icon: Icon(Icons.people_alt_rounded, size: 18), text: 'Tài khoản'),
          ],
        ),
      ),
      body: _isLoading
          ? const CustomPulseLoader(
              message: 'Đang tải dữ liệu Quản trị Admin...',
              icon: Icons.shield_rounded,
              primaryColor: Color(0xFFDC2626),
            )
          : TabBarView(
              controller: _tabController,
              children: [
                _buildOverviewTab(),
                _buildModerationTab(crimsonColor),
                _buildEateriesTab(crimsonColor),
                _buildSchoolsTab(crimsonColor),
                _buildStallsTab(crimsonColor),
                _buildUsersTab(crimsonColor),
              ],
            ),
    );
  }

  // =========================================================================
  // TAB 1: 📊 TỔNG QUAN HỆ THỐNG (CHẤT LƯỢNG WEB ADMIN)
  // =========================================================================
  Widget _buildOverviewTab() {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Top Action Shortcuts matching Web Screenshots 1 & 2
        Row(
          children: [
            Expanded(
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFDC2626),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                onPressed: _showAddEateryDialog,
                icon: const Icon(Icons.add_location_alt_rounded, color: Colors.white, size: 18),
                label: const Text('➕ Đăng Ký Cơ Sở Mới', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0284C7),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                onPressed: () {
                  Navigator.push(context, MaterialPageRoute(builder: (_) => const MapScreen()));
                },
                icon: const Icon(Icons.map_rounded, color: Colors.white, size: 18),
                label: const Text('🗺️ Xem Bản Đồ Số', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),

        // KPI Header Banner
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF090D16), Color(0xFF1E293B)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(24),
            boxShadow: const [
              BoxShadow(color: Color(0x33DC2626), blurRadius: 12, offset: Offset(0, 4)),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Row(
                children: [
                  Icon(Icons.analytics_rounded, color: Color(0xFFDC2626), size: 22),
                  SizedBox(width: 8),
                  Text(
                    'THỐNG KÊ HỆ THỐNG TOÀN HUYỆN ĐÔNG ANH',
                    style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 0.5),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _buildStatTile('${_stats['total_users'] ?? _usersList.length}', 'Tài khoản', Icons.person_rounded),
                  _buildStatTile('${_stats['total_eateries'] ?? _eateriesList.length}', 'Bản đồ Live', Icons.map_rounded),
                  _buildStatTile('${_stats['total_reviews'] ?? _reviewsList.length}', 'Đánh giá', Icons.star_rounded),
                  _buildStatTile('${_stats['total_sellers'] ?? 0}', 'Chủ tiệm', Icons.storefront_rounded),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),

        const Text(
          'Quản Lý Nhanh Hệ Thống',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
        ),
        const SizedBox(height: 12),

        Row(
          children: [
            Expanded(
              child: _buildQuickActionCard(
                'Kiểm duyệt UGC',
                '${ModerationService.pendingReportsCount} báo cáo chờ duyệt',
                Icons.shield_rounded,
                const Color(0xFFE11D48),
                () => _tabController.animateTo(1),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildQuickActionCard(
                'Quản lý User',
                '${_stats['total_users'] ?? _usersList.length} tài khoản',
                Icons.people_alt_rounded,
                const Color(0xFF0284C7),
                () => _tabController.animateTo(5),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildQuickActionCard(
                'Cơ sở Bản đồ',
                '${_stats['total_eateries'] ?? _eateriesList.length} địa điểm',
                Icons.storefront_rounded,
                const Color(0xFFF59E0B),
                () => _tabController.animateTo(2),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildQuickActionCard(
                'Gian hàng & OCOP',
                '${_stats['total_stalls'] ?? (_stallsList.isNotEmpty ? _stallsList.length : 33)} gian hàng',
                Icons.shopping_bag_rounded,
                const Color(0xFF10B981),
                () => _tabController.animateTo(4),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildStatTile(String value, String label, IconData icon) {
    return Column(
      children: [
        Icon(icon, color: const Color(0xFFDC2626), size: 20),
        const SizedBox(height: 4),
        Text(value, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
        Text(label, style: TextStyle(color: Colors.grey.shade400, fontSize: 11)),
      ],
    );
  }

  Widget _buildQuickActionCard(String title, String subtitle, IconData icon, Color color, VoidCallback onTap) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF64748B).withValues(alpha: 0.06),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(20),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(20),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: color.withValues(alpha: 0.25)),
                  ),
                  child: Icon(icon, color: color, size: 22),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A)),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        subtitle,
                        style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                Icon(Icons.chevron_right_rounded, color: Colors.grey.shade400, size: 18),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // =========================================================================
  // TAB 2: 👥 QUẢN LÝ TÀI KHOẢN NGƯỜI DÙNG (MATCHING WEB SCREENSHOT 2)
  // =========================================================================
  Widget _buildUsersTab(Color crimsonColor) {
    final filteredUsers = _usersList.where((u) {
      final name = (u['name'] ?? '').toString().toLowerCase();
      final email = (u['email'] ?? '').toString().toLowerCase();
      final q = _userSearchQuery.toLowerCase();
      return name.contains(q) || email.contains(q);
    }).toList();

    final totalUsers = _stats['total_users'] ?? _usersList.length;
    final adminUsers = _usersList.where((u) => u['role'] == 'admin').length;
    final sellerUsers = _stats['total_sellers'] ?? _usersList.where((u) => u['role'] == 'seller' || u['role'] == 'manager').length;
    final customerUsers = _usersList.where((u) => u['role'] == 'user' || u['role'] == null).length;

    return Column(
      children: [
        // Top Stat Badges matching Web Screenshot 2
        Container(
          padding: const EdgeInsets.all(12),
          color: Colors.white,
          child: Row(
            children: [
              Expanded(child: _buildUserBadge('TỔNG USER', '$totalUsers', Colors.blue)),
              const SizedBox(width: 6),
              Expanded(child: _buildUserBadge('ADMIN', '$adminUsers', Colors.purple)),
              const SizedBox(width: 6),
              Expanded(child: _buildUserBadge('SELLER', '$sellerUsers', Colors.green)),
              const SizedBox(width: 6),
              Expanded(child: _buildUserBadge('CUSTOMER', '$customerUsers', Colors.orange)),
            ],
          ),
        ),

        // User Header Action Bar & Search Input
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
          child: Row(
            children: [
              Expanded(
                child: TextField(
                  onChanged: (v) => setState(() => _userSearchQuery = v),
                  decoration: InputDecoration(
                    hintText: 'Tìm kiếm theo Tên, Email, SĐT...',
                    prefixIcon: const Icon(Icons.search_rounded, color: Colors.grey, size: 20),
                    filled: true,
                    fillColor: Colors.white,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide.none),
                    contentPadding: const EdgeInsets.symmetric(vertical: 10),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4F46E5),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
                onPressed: _showAddUserDialog,
                icon: const Icon(Icons.person_add_rounded, color: Colors.white, size: 16),
                label: const Text('+ Thêm mới', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
              ),
            ],
          ),
        ),

        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            itemCount: filteredUsers.length,
            itemBuilder: (context, index) {
              final user = filteredUsers[index];
              final currentRole = user['role'] ?? 'user';

              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  child: Row(
                    children: [
                      CircleAvatar(
                        backgroundColor: crimsonColor.withValues(alpha: 0.1),
                        child: Text(
                          (user['name'] ?? 'U').toString().substring(0, 1).toUpperCase(),
                          style: TextStyle(color: crimsonColor, fontWeight: FontWeight.bold),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              user['name'] ?? 'Người dùng',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 2),
                            Text(
                              user['email'] ?? '',
                              style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 4),
                            Wrap(
                              spacing: 4,
                              runSpacing: 4,
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: _getRoleColor(currentRole).withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Text(
                                    currentRole.toUpperCase(),
                                    style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: _getRoleColor(currentRole)),
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF10B981).withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: const Text('HOẠT ĐỘNG', style: TextStyle(fontSize: 9, color: Color(0xFF10B981), fontWeight: FontWeight.bold)),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 6),
                      PopupMenuButton<String>(
                        initialValue: currentRole,
                        onSelected: (newRole) => _changeUserRole(index, newRole),
                        itemBuilder: (ctx) => const [
                          PopupMenuItem(value: 'user', child: Text('👤 Member (User)')),
                          PopupMenuItem(value: 'seller', child: Text('🛍️ Seller (Chủ gian hàng)')),
                          PopupMenuItem(value: 'manager', child: Text('🏛️ Manager (BQL Chợ)')),
                          PopupMenuItem(value: 'admin', child: Text('🛡️ Admin (Quản trị hệ thống)')),
                        ],
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                          decoration: BoxDecoration(
                            color: crimsonColor.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text('Đổi Role', style: TextStyle(color: Color(0xFFDC2626), fontSize: 11, fontWeight: FontWeight.bold)),
                              Icon(Icons.arrow_drop_down, color: Color(0xFFDC2626), size: 16),
                            ],
                          ),
                        ),
                      ),
                      IconButton(
                        constraints: const BoxConstraints(),
                        padding: const EdgeInsets.all(4),
                        icon: const Icon(Icons.edit_rounded, color: Color(0xFF0284C7), size: 20),
                        tooltip: 'Chỉnh sửa user',
                        onPressed: () => _showEditUserDialog(user),
                      ),
                      IconButton(
                        constraints: const BoxConstraints(),
                        padding: const EdgeInsets.all(4),
                        icon: const Icon(Icons.delete_outline_rounded, color: Colors.red, size: 20),
                        tooltip: 'Xóa user',
                        onPressed: () => _deleteUser(index),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildUserBadge(String label, String count, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Column(
        children: [
          Text(label, style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: color)),
          const SizedBox(height: 2),
          Text(count, style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: color)),
        ],
      ),
    );
  }

  Color _getRoleColor(String role) {
    switch (role) {
      case 'admin':
        return const Color(0xFFDC2626);
      case 'manager':
        return Colors.purple;
      case 'seller':
        return const Color(0xFF10B981);
      default:
        return Colors.blue;
    }
  }

  // =========================================================================
  // TAB 3: 🏬 QUẢN LÝ DANH SÁCH CƠ SỞ BẢN ĐỒ SỐ (MATCHING WEB SCREENSHOT 1)
  // =========================================================================
  Widget _buildEateriesTab(Color crimsonColor) {
    final filteredEateries = _eateriesList.where((e) {
      final name = (e['name'] ?? '').toString().toLowerCase();
      final cat = (e['category_name'] ?? '').toString().toLowerCase();
      final q = _eaterySearchQuery.toLowerCase();
      final matchesSearch = name.contains(q) || cat.contains(q);
      if (_selectedCategoryFilter == 'Tất cả') return matchesSearch;
      return matchesSearch && (e['category_name'] == _selectedCategoryFilter);
    }).toList();

    return Column(
      children: [
        // Action Bar & Filter Header
        Container(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
          color: Colors.white,
          child: Column(
            children: [
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      onChanged: (v) => setState(() => _eaterySearchQuery = v),
                      decoration: InputDecoration(
                        hintText: 'Tìm theo tên, địa chỉ, SĐT...',
                        prefixIcon: const Icon(Icons.search_rounded, color: Colors.grey, size: 20),
                        filled: true,
                        fillColor: const Color(0xFFF1F5F9),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide.none),
                        contentPadding: const EdgeInsets.symmetric(vertical: 10),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: crimsonColor,
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    onPressed: _showAddEateryDialog,
                    icon: const Icon(Icons.add_business_rounded, color: Colors.white, size: 16),
                    label: const Text('+ Thêm Mới', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
                  ),
                ],
              ),
            ],
          ),
        ),

        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            itemCount: filteredEateries.length,
            itemBuilder: (context, index) {
              final eatery = filteredEateries[index];
              final isFeatured = eatery['is_featured'] == true;

              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: ListTile(
                  contentPadding: const EdgeInsets.all(12),
                  leading: Container(
                    width: 50,
                    height: 50,
                    decoration: BoxDecoration(color: Colors.grey.shade200, borderRadius: BorderRadius.circular(12)),
                    child: const Icon(Icons.storefront_rounded, color: Color(0xFF0284C7)),
                  ),
                  title: Text(eatery['name'] ?? 'Địa điểm', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(eatery['address'] ?? 'Đông Anh, Hà Nội', style: TextStyle(fontSize: 11, color: Colors.grey.shade600), maxLines: 1),
                      const SizedBox(height: 4),
                      Wrap(
                        spacing: 4,
                        runSpacing: 4,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(color: Colors.blue.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(6)),
                            child: Text(eatery['category_name'] ?? 'Địa điểm', style: const TextStyle(fontSize: 10, color: Colors.blue, fontWeight: FontWeight.bold)),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(color: const Color(0xFF10B981).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(6)),
                            child: const Text('Hoạt động', style: TextStyle(fontSize: 9, color: Color(0xFF10B981), fontWeight: FontWeight.bold)),
                          ),
                          if (isFeatured) ...[
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(color: Colors.orange.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(6)),
                              child: const Text('⭐ Nổi bật', style: TextStyle(fontSize: 9, color: Colors.orange, fontWeight: FontWeight.bold)),
                            ),
                          ]
                        ],
                      ),
                    ],
                  ),
                  trailing: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      IconButton(
                        icon: Icon(isFeatured ? Icons.star_rounded : Icons.star_outline_rounded, color: isFeatured ? Colors.orange : Colors.grey),
                        tooltip: 'Bật/Tắt Nổi bật',
                        onPressed: () => _toggleFeatured(index),
                      ),
                      IconButton(
                        icon: const Icon(Icons.edit_location_alt_rounded, color: Color(0xFF0284C7)),
                        tooltip: 'Chỉnh sửa địa điểm',
                        onPressed: () => _showEditEateryDialog(eatery),
                      ),
                      IconButton(
                        icon: const Icon(Icons.delete_outline_rounded, color: Colors.red),
                        tooltip: 'Xóa địa điểm',
                        onPressed: () => _deleteEatery(index),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  // =========================================================================
  // TAB 4: 🏷️ DANH MỤC & ĐÁNH GIÁ (REVIEWS MODERATION)


  // =========================================================================
  // TAB 3: 🏫 QUẢN LÝ TRƯỜNG HỌC & SÁP NHẬP
  // =========================================================================
  Widget _buildSchoolsTab(Color crimsonColor) {
    final filtered = _schoolsList.where((sch) {
      final q = _schoolSearchQuery.toLowerCase();
      final name = (sch['name'] ?? '').toString().toLowerCase();
      final address = (sch['address'] ?? '').toString().toLowerCase();
      return name.contains(q) || address.contains(q);
    }).toList();

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Row(
          children: [
            Expanded(
              child: TextField(
                onChanged: (val) => setState(() => _schoolSearchQuery = val),
                decoration: InputDecoration(
                  hintText: 'Search trường học, mầm non, tiểu học...',
                  prefixIcon: const Icon(Icons.search_rounded, color: Colors.grey),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  fillColor: Colors.white,
                  filled: true,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                ),
              ),
            ),
            const SizedBox(width: 10),
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF0284C7),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              ),
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('🏫 Chức năng Thêm Trường học & Bản đồ Tuyển sinh đã mở!')),
                );
              },
              icon: const Icon(Icons.add_rounded, color: Colors.white, size: 18),
              label: const Text('Thêm Trường', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
            ),
          ],
        ),
        const SizedBox(height: 16),

        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: const Color(0xFFE0F2FE),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFBAE6FD)),
          ),
          child: const Row(
            children: [
              Icon(Icons.school_rounded, color: Color(0xFF0284C7), size: 24),
              SizedBox(width: 12),
              Expanded(
                child: Text(
                  'Quản lý hệ thống Trường học, Tuyến tuyển sinh & Phương án sáp nhập đơn vị hành chính Huyện Đông Anh.',
                  style: TextStyle(fontSize: 12, color: Color(0xFF0369A1), fontWeight: FontWeight.w500),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        if (filtered.isEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(32),
              child: Text('Không tìm thấy trường học nào.', style: TextStyle(color: Colors.grey)),
            ),
          )
        else
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: filtered.length,
            itemBuilder: (context, index) {
              final sch = filtered[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 10),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Row(
                    children: [
                      Container(
                        width: 42,
                        height: 42,
                        decoration: BoxDecoration(color: crimsonColor.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(12)),
                        child: Icon(Icons.school_rounded, color: crimsonColor),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(sch['name'] ?? 'Trường học', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                            const SizedBox(height: 2),
                            Text('📍 ${sch['address'] ?? 'Đông Anh, Hà Nội'}', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                            const SizedBox(height: 2),
                            Text('Cấp: ${sch['level'] ?? 'Mầm Nông/Tiểu Học/THCS'}', style: TextStyle(fontSize: 10, color: crimsonColor, fontWeight: FontWeight.bold)),
                          ],
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.edit_rounded, color: Colors.blue, size: 20),
                        onPressed: () {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text('✏️ Đã mở trình chỉnh sửa thông tin "${sch['name']}"')),
                          );
                        },
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
      ],
    );
  }

  // =========================================================================
  // TAB 4: 🛍️ QUẢN LÝ GIAN HÀNG & OCOP
  // =========================================================================
  Widget _buildStallsTab(Color crimsonColor) {
    final filtered = _stallsList.where((stl) {
      final q = _stallSearchQuery.toLowerCase();
      final name = (stl['name'] ?? '').toString().toLowerCase();
      final vendor = (stl['vendor'] ?? '').toString().toLowerCase();
      return name.contains(q) || vendor.contains(q);
    }).toList();

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Row(
          children: [
            Expanded(
              child: TextField(
                onChanged: (val) => setState(() => _stallSearchQuery = val),
                decoration: InputDecoration(
                  hintText: 'Search gian hàng, sản phẩm OCOP...',
                  prefixIcon: const Icon(Icons.search_rounded, color: Colors.grey),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  fillColor: Colors.white,
                  filled: true,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                ),
              ),
            ),
            const SizedBox(width: 10),
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF059669),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              ),
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('🛍️ Mở kênh đăng ký Gian Hàng OCOP mới!')),
                );
              },
              icon: const Icon(Icons.storefront_rounded, color: Colors.white, size: 18),
              label: const Text('Tạo Gian Hàng', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
            ),
          ],
        ),
        const SizedBox(height: 16),

        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: const Color(0xFFD1FAE5),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFA7F3D0)),
          ),
          child: const Row(
            children: [
              Icon(Icons.shopping_bag_rounded, color: Color(0xFF059669), size: 24),
              SizedBox(width: 12),
              Expanded(
                child: Text(
                  'Quản lý danh sách Gian hàng Chợ Truyền thống, Đặc sản OCOP Đông Anh & Duyệt người bán hàng (Seller).',
                  style: TextStyle(fontSize: 12, color: Color(0xFF047857), fontWeight: FontWeight.w500),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        if (filtered.isEmpty)
          const Center(
            child: Padding(
              padding: EdgeInsets.all(32),
              child: Text('Không tìm thấy gian hàng nào.', style: TextStyle(color: Colors.grey)),
            ),
          )
        else
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: filtered.length,
            itemBuilder: (context, index) {
              final stl = filtered[index];
              final isApproved = stl['status'] == 'approved' || stl['status'] == 'active';
              final starRating = stl['star_rating'];

              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Container(
                            width: 42,
                            height: 42,
                            decoration: BoxDecoration(color: const Color(0xFF059669).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(12)),
                            child: const Icon(Icons.storefront_rounded, color: Color(0xFF059669)),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(stl['name'] ?? 'Gian hàng OCOP', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                                const SizedBox(height: 2),
                                Text('Chủ: ${stl['vendor'] ?? 'Tiểu thương'} - SĐT: ${stl['phone'] ?? '---'}', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                                const SizedBox(height: 6),
                                Wrap(
                                  spacing: 6,
                                  runSpacing: 4,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: isApproved ? Colors.green.shade50 : Colors.amber.shade50,
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        isApproved ? '✅ Đã duyệt' : '⏳ Chờ duyệt',
                                        style: TextStyle(
                                          fontSize: 10,
                                          color: isApproved ? Colors.green.shade700 : Colors.amber.shade800,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: starRating != null ? const Color(0xFFFEF3C7) : const Color(0xFFE2E8F0),
                                        borderRadius: BorderRadius.circular(6),
                                        border: Border.all(
                                          color: starRating != null ? const Color(0xFFFDE68A) : Colors.transparent,
                                        ),
                                      ),
                                      child: Text(
                                        starRating != null ? '⭐ OCOP $starRating' : '🏪 Chợ Dân Sinh',
                                        style: TextStyle(
                                          fontSize: 10,
                                          color: starRating != null ? const Color(0xFFB45309) : const Color(0xFF475569),
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                          Switch(
                            value: isApproved,
                            activeThumbColor: const Color(0xFF059669),
                            onChanged: (val) {
                              setState(() {
                                stl['status'] = val ? 'approved' : 'pending';
                              });
                            },
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.end,
                        children: [
                          OutlinedButton.icon(
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              side: const BorderSide(color: Color(0xFFF59E0B)),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                            onPressed: () => _showGrantStarDialog(stl),
                            icon: const Icon(Icons.stars_rounded, size: 16, color: Color(0xFFD97706)),
                            label: const Text('⭐ Cấp Sao OCOP', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFFD97706))),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
      ],
    );
  }

  void _showGrantStarDialog(Map<String, dynamic> stl) {
    final int stallId = stl['id'] ?? 0;
    String? currentRating = stl['star_rating'];

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(
            children: [
              const Icon(Icons.stars_rounded, color: Color(0xFFF59E0B)),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Cấp Sao OCOP: ${stl['name'] ?? 'Gian hàng'}',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Lựa chọn cấp sao chứng nhận OCOP cho sản phẩm/gian hàng này:', style: TextStyle(fontSize: 13)),
              const SizedBox(height: 14),
              Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  {'label': '❌ Không cấp sao (Chợ Dân Sinh)', 'value': null, 'color': const Color(0xFF64748B)},
                  {'label': '⭐⭐⭐ Cấp 3 Sao OCOP Chuẩn', 'value': '3 sao', 'color': const Color(0xFFD97706)},
                  {'label': '⭐⭐⭐⭐ Cấp 4 Sao OCOP Cao Cấp', 'value': '4 sao', 'color': const Color(0xFF059669)},
                  {'label': '⭐⭐⭐⭐⭐ Cấp 5 Sao OCOP Quốc Gia', 'value': '5 sao', 'color': const Color(0xFFDC2626)},
                ].map((opt) {
                  final String? val = opt['value'] as String?;
                  final bool isSelected = currentRating == val;
                  return InkWell(
                    onTap: () => setDialogState(() => currentRating = val),
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      decoration: BoxDecoration(
                        color: isSelected ? (opt['color'] as Color).withValues(alpha: 0.1) : Colors.transparent,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: isSelected ? (opt['color'] as Color) : Colors.grey.shade300,
                          width: isSelected ? 1.5 : 1.0,
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            isSelected ? Icons.radio_button_checked : Icons.radio_button_off,
                            color: isSelected ? (opt['color'] as Color) : Colors.grey,
                            size: 20,
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              opt['label'] as String,
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                                color: isSelected ? (opt['color'] as Color) : const Color(0xFF1E293B),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                }).toList(),
              ),
            ],
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy')),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF0284C7)),
              onPressed: () async {
                final messenger = ScaffoldMessenger.of(context);
                Navigator.pop(ctx);
                final res = await ApiService.updateStallStarRating(stallId, currentRating);
                if (mounted) {
                  if (res['success'] == true) {
                    setState(() {
                      stl['star_rating'] = currentRating;
                    });
                    messenger.showSnackBar(
                      SnackBar(content: Text(res['message'] ?? '🎉 Đã cập nhật sao OCOP!'), backgroundColor: const Color(0xFF059669)),
                    );
                    _loadAdminData();
                  } else {
                    messenger.showSnackBar(
                      SnackBar(content: Text('⚠️ ${res['message']}'), backgroundColor: Colors.red),
                    );
                  }
                }
              },
              child: const Text('Lưu Chứng Nhận', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildModerationTab(Color primaryColor) {
    final allReports = ModerationService.getReportTickets();
    final pendingCount = allReports.where((r) => r['status'] == 'pending').length;
    final resolvedCount = allReports.where((r) => r['status'] != 'pending').length;

    final filteredReports = allReports.where((r) {
      if (_moderationFilter == 'pending') return r['status'] == 'pending';
      if (_moderationFilter == 'resolved') return r['status'] != 'pending';
      return true;
    }).toList();

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF881337), Color(0xFFE11D48)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFFE11D48).withValues(alpha: 0.2),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.shield_rounded, color: Colors.white, size: 22),
                  ),
                  const SizedBox(width: 10),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Hàng đợi Kiểm duyệt Nội dung (UGC)',
                          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 15),
                        ),
                        Text(
                          'Cam kết xử lý gỡ bỏ vi phạm trong vòng 24 giờ',
                          style: TextStyle(color: Colors.white70, fontSize: 11),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Chờ xử lý', style: TextStyle(color: Colors.white70, fontSize: 11)),
                          const SizedBox(height: 4),
                          Text(
                            '$pendingCount',
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 20),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Đã xử lý', style: TextStyle(color: Colors.white70, fontSize: 11)),
                          const SizedBox(height: 4),
                          Text(
                            '$resolvedCount',
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 20),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Thời hạn SLA', style: TextStyle(color: Colors.white70, fontSize: 11)),
                          SizedBox(height: 4),
                          Text(
                            '< 24 Giờ',
                            style: TextStyle(color: Color(0xFFFDE047), fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              _buildFilterChip('Chờ xử lý ($pendingCount)', 'pending'),
              const SizedBox(width: 8),
              _buildFilterChip('Đã xử lý ($resolvedCount)', 'resolved'),
              const SizedBox(width: 8),
              _buildFilterChip('Tất cả (${allReports.length})', 'all'),
            ],
          ),
        ),
        const SizedBox(height: 16),

        if (filteredReports.isEmpty)
          Container(
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFF1F5F9)),
            ),
            child: const Column(
              children: [
                Icon(Icons.check_circle_outline_rounded, size: 48, color: Color(0xFF10B981)),
                SizedBox(height: 12),
                Text(
                  'Không có báo cáo nào cần xử lý',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A)),
                ),
                SizedBox(height: 4),
                Text(
                  'Cộng đồng Đông Anh Social đang an toàn và tuân thủ tốt tiêu chuẩn.',
                  style: TextStyle(color: Color(0xFF64748B), fontSize: 12),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          )
        else
          ...filteredReports.map((report) => _buildModerationReportCard(report)),
      ],
    );
  }

  Widget _buildModerationReportCard(Map<String, dynamic> report) {
    final status = report['status'] ?? 'pending';
    final isPending = status == 'pending';
    final contentType = report['content_type'] ?? 'post';
    final title = report['title'] ?? 'Nội dung';
    final authorName = report['author_name'] ?? 'Ẩn danh';
    final authorId = (report['author_id'] ?? '').toString();
    final reason = report['reason'] ?? 'Vi phạm tiêu chuẩn';
    final details = (report['details'] ?? '').toString();
    final snippet = (report['snippet'] ?? '').toString();
    final ticketId = (report['id'] ?? '').toString();

    Color statusColor = const Color(0xFFE11D48);
    String statusText = 'Chờ xử lý';
    if (status == 'resolved_removed') {
      statusColor = const Color(0xFF64748B);
      statusText = 'Đã gỡ nội dung';
    } else if (status == 'resolved_banned') {
      statusColor = const Color(0xFFDC2626);
      statusText = 'Đã khóa tài khoản';
    } else if (status == 'dismissed') {
      statusColor = const Color(0xFF059669);
      statusText = 'Đã duyệt an toàn';
    }

    IconData typeIcon = Icons.article_outlined;
    String typeText = 'Bài viết';
    if (contentType == 'video') {
      typeIcon = Icons.play_circle_outline_rounded;
      typeText = 'Video Shorts';
    } else if (contentType == 'comment') {
      typeIcon = Icons.chat_bubble_outline_rounded;
      typeText = 'Bình luận';
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isPending ? const Color(0xFFFECDD3) : const Color(0xFFE2E8F0),
          width: isPending ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(typeIcon, size: 14, color: const Color(0xFF475569)),
                    const SizedBox(width: 4),
                    Text(typeText, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF475569))),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  statusText,
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: statusColor),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: const Color(0xFFFFF1F2),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFFFE4E6)),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(Icons.warning_amber_rounded, size: 18, color: Color(0xFFE11D48)),
                const SizedBox(width: 8),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Lý do: $reason',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5, color: Color(0xFF9F1239)),
                      ),
                      if (details.isNotEmpty) ...[
                        const SizedBox(height: 2),
                        Text(
                          'Mô tả: $details',
                          style: const TextStyle(fontSize: 11.5, color: Color(0xFFBE123C)),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),

          Text(
            title,
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
          ),
          if (snippet.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(
              snippet,
              style: const TextStyle(fontSize: 12.5, color: Color(0xFF475569)),
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
            ),
          ],
          const SizedBox(height: 8),

          Row(
            children: [
              const Icon(Icons.person_outline_rounded, size: 14, color: Color(0xFF94A3B8)),
              const SizedBox(width: 4),
              Text(
                'Tác giả: $authorName',
                style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
              ),
            ],
          ),

          if (isPending) ...[
            const SizedBox(height: 14),
            const Divider(height: 1),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFFDC2626),
                      side: const BorderSide(color: Color(0xFFFECDD3)),
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    onPressed: () => _confirmRemoveContent(ticketId, title),
                    icon: const Icon(Icons.delete_outline_rounded, size: 16),
                    label: const Text('Gỡ nội dung', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold)),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF991B1B),
                      side: const BorderSide(color: Color(0xFFFCA5A5)),
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    onPressed: () => _confirmBanUser(ticketId, authorId, authorName),
                    icon: const Icon(Icons.block_rounded, size: 16),
                    label: const Text('Khóa tác giả', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold)),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton(
                  tooltip: 'Bác bỏ báo cáo (Nội dung an toàn)',
                  style: IconButton.styleFrom(
                    backgroundColor: const Color(0xFFF1F5F9),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  icon: const Icon(Icons.check_rounded, color: Color(0xFF059669), size: 18),
                  onPressed: () async {
                    await ModerationService.resolveReportTicket(ticketId: ticketId, action: 'dismiss');
                    setState(() {});
                    if (mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Đã bác bỏ báo cáo. Nội dung được giữ nguyên.')),
                      );
                    }
                  },
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  void _confirmRemoveContent(String ticketId, String title) {
    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Gỡ bỏ nội dung vi phạm?'),
        content: Text('Nội dung "$title" sẽ bị ẩn và gỡ bỏ hoàn toàn khỏi hệ thống.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogCtx), child: const Text('Hủy')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFDC2626),
              foregroundColor: Colors.white,
            ),
            onPressed: () async {
              Navigator.pop(dialogCtx);
              await ModerationService.resolveReportTicket(ticketId: ticketId, action: 'remove_content');
              setState(() {});
              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Đã gỡ bỏ nội dung vi phạm thành công.')),
                );
              }
            },
            child: const Text('Gỡ nội dung'),
          ),
        ],
      ),
    );
  }

  void _confirmBanUser(String ticketId, String authorId, String authorName) {
    int selectedHours = 24;
    final reasonCtrl = TextEditingController(text: 'Vi phạm tiêu chuẩn cộng đồng');

    final durations = [
      {'label': '24 giờ (1 ngày)', 'hours': 24},
      {'label': '3 ngày (72 giờ)', 'hours': 72},
      {'label': '7 ngày (1 tuần)', 'hours': 168},
      {'label': '30 ngày (1 tháng)', 'hours': 720},
      {'label': 'Khóa vĩnh viễn', 'hours': -1},
    ];

    showDialog(
      context: context,
      builder: (dialogCtx) => StatefulBuilder(
        builder: (modalCtx, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEE2E2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.block_rounded, color: Color(0xFFDC2626), size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text('Khóa tài khoản $authorName', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
              ),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Chọn thời hạn khóa:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155))),
                const SizedBox(height: 8),
                ...durations.map((d) {
                  final isSel = selectedHours == d['hours'];
                  return InkWell(
                    onTap: () => setDialogState(() => selectedHours = d['hours'] as int),
                    borderRadius: BorderRadius.circular(8),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
                      child: Row(
                        children: [
                          Icon(
                            isSel ? Icons.radio_button_checked_rounded : Icons.radio_button_unchecked_rounded,
                            color: isSel ? const Color(0xFFDC2626) : const Color(0xFF94A3B8),
                            size: 18,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            d['label'] as String,
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: isSel ? FontWeight.bold : FontWeight.normal,
                              color: isSel ? const Color(0xFF991B1B) : const Color(0xFF475569),
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                }),
                const SizedBox(height: 12),
                const Text('Lý do khóa:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF334155))),
                const SizedBox(height: 6),
                TextField(
                  controller: reasonCtrl,
                  decoration: InputDecoration(
                    hintText: 'Nhập lý do vi phạm...',
                    hintStyle: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(dialogCtx), child: const Text('Hủy')),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF991B1B),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              onPressed: () async {
                final messenger = ScaffoldMessenger.of(context);
                Navigator.pop(dialogCtx);
                final Duration? duration = selectedHours > 0 ? Duration(hours: selectedHours) : null;
                await ModerationService.resolveReportTicket(
                  ticketId: ticketId,
                  action: 'ban_user',
                  banDuration: duration,
                  banReason: reasonCtrl.text.trim(),
                );
                if (mounted) {
                  setState(() {});
                  final durationText = ModerationService.formatDurationText(duration);
                  messenger.showSnackBar(
                    SnackBar(content: Text('Đã khóa tài khoản $authorName ($durationText).')),
                  );
                }
              },
              child: const Text('Xác nhận khóa'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _moderationFilter == value;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (_) => setState(() => _moderationFilter = value),
      selectedColor: const Color(0xFFE11D48),
      labelStyle: TextStyle(
        fontSize: 12,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
        color: isSelected ? Colors.white : const Color(0xFF475569),
      ),
      backgroundColor: const Color(0xFFF1F5F9),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20), side: BorderSide.none),
    );
  }
}
