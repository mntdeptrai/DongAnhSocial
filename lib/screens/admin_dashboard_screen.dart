import 'package:flutter/material.dart';
import '../services/api_service.dart';
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
  List<dynamic> _categoriesList = [];
  List<dynamic> _reviewsList = [];
  List<dynamic> _schoolsList = [];
  List<dynamic> _stallsList = [];

  String _userSearchQuery = '';
  String _eaterySearchQuery = '';
  String _schoolSearchQuery = '';
  String _stallSearchQuery = '';
  String _selectedCategoryFilter = 'Tất cả';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(
      length: 5,
      initialIndex: widget.initialTabIndex.clamp(0, 4),
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
            _categoriesList = data['categories'] ?? [];
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
                  Navigator.pop(ctx);
                  final res = await ApiService.storeUser(name: name, email: email, password: pass, role: selectedRole, phone: phone);
                  if (res['success'] == true) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('🎉 ${res['message']}'), backgroundColor: const Color(0xFF10B981)),
                    );
                    _loadAdminData();
                  } else {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('⚠️ ${res['message']}'), backgroundColor: Colors.red),
                    );
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

                  if (res['success'] == true) {
                    ScaffoldMessenger.of(context).showSnackBar(
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

  void _deleteReview(int index) async {
    final review = _reviewsList[index];
    final int id = review['id'] is int ? review['id'] : (int.tryParse(review['id']?.toString() ?? '0') ?? 0);

    setState(() {
      _reviewsList.removeAt(index);
    });

    if (id > 0) {
      await ApiService.deleteReview(id);
    }
  }

  void _showAddCategoryDialog() {
    final nameCtrl = TextEditingController();
    final descCtrl = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Thêm Danh mục Mới', style: TextStyle(fontWeight: FontWeight.bold)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: nameCtrl,
              decoration: const InputDecoration(labelText: 'Tên danh mục', hintText: 'Ví dụ: Cà Phê & Trà Sữa'),
            ),
            const SizedBox(height: 10),
            TextField(
              controller: descCtrl,
              decoration: const InputDecoration(labelText: 'Mô tả ngắn', hintText: 'Các địa điểm cà phê chill'),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFDC2626)),
            onPressed: () async {
              if (nameCtrl.text.trim().isNotEmpty) {
                final name = nameCtrl.text.trim();
                final desc = descCtrl.text.trim();
                Navigator.pop(ctx);
                final success = await ApiService.createCategory(name, desc, '📍');
                if (success) {
                  _loadAdminData();
                }
              }
            },
            child: const Text('Thêm Danh Mục', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
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

        const Text('Quản Lý Nhanh Hệ Thống', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
        const SizedBox(height: 12),

        Row(
          children: [
            Expanded(
              child: _buildQuickActionCard('Quản lý User', '${_usersList.length} tài khoản', Icons.people_outline_rounded, Colors.blue, () {
                _tabController.animateTo(1);
              }),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildQuickActionCard('Cơ sở Bản đồ', '${_eateriesList.length} địa điểm', Icons.storefront_outlined, Colors.orange, () {
                _tabController.animateTo(2);
              }),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildQuickActionCard('Danh mục', '${_categoriesList.length} phân loại', Icons.category_outlined, Colors.purple, () {
                _tabController.animateTo(3);
              }),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildQuickActionCard('Duyệt Đánh giá', '${_reviewsList.length} bình luận', Icons.rate_review_outlined, Colors.green, () {
                _tabController.animateTo(3);
              }),
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
    return Card(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(12)),
                child: Icon(icon, color: color, size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF0F172A))),
                    Text(subtitle, style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
                  ],
                ),
              ),
            ],
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

    final totalUsers = _usersList.length;
    final adminUsers = _usersList.where((u) => u['role'] == 'admin').length;
    final sellerUsers = _usersList.where((u) => u['role'] == 'seller' || u['role'] == 'manager').length;
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
  Widget _buildCategoriesAndReviewsTab(Color crimsonColor) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text('Danh Mục Hệ Thống', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: crimsonColor,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: _showAddCategoryDialog,
              icon: const Icon(Icons.add, size: 16, color: Colors.white),
              label: const Text('Thêm Danh Mục', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
        const SizedBox(height: 10),

        // Categories List
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: _categoriesList.map((cat) {
              return Container(
                margin: const EdgeInsets.only(right: 10),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: Row(
                  children: [
                    Text(cat['icon'] ?? '📍', style: const TextStyle(fontSize: 16)),
                    const SizedBox(width: 8),
                    Text(cat['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  ],
                ),
              );
            }).toList(),
          ),
        ),
        const SizedBox(height: 24),

        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('Duyệt Đánh Giá Bài Viết (${_reviewsList.length})', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
            const Text('Mới nhất', style: TextStyle(fontSize: 12, color: Colors.grey)),
          ],
        ),
        const SizedBox(height: 10),

        // Reviews List
        ListView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: _reviewsList.length,
          itemBuilder: (context, index) {
            final rev = _reviewsList[index];
            return Card(
              margin: const EdgeInsets.only(bottom: 10),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              child: ListTile(
                title: Row(
                  children: [
                    Text(rev['user_name'] ?? 'Khách hàng', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    const SizedBox(width: 8),
                    Text('⭐ ${rev['rating']}', style: const TextStyle(fontSize: 12, color: Colors.orange, fontWeight: FontWeight.bold)),
                  ],
                ),
                subtitle: Text(rev['comment'] ?? '', style: const TextStyle(fontSize: 12, color: Color(0xFF334155))),
                trailing: IconButton(
                  icon: const Icon(Icons.delete_outline_rounded, color: Colors.red, size: 20),
                  onPressed: () => _deleteReview(index),
                ),
              ),
            );
          },
        ),
      ],
    );
  }

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
                            const SizedBox(height: 4),
                            Row(
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
                ),
              );
            },
          ),
      ],
    );
  }
}
