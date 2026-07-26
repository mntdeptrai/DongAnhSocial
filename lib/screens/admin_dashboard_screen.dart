import 'package:flutter/material.dart';
import '../services/api_service.dart';

class AdminDashboardScreen extends StatefulWidget {
  final VoidCallback? onBack;

  const AdminDashboardScreen({super.key, this.onBack});

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

  String _userSearchQuery = '';
  String _eaterySearchQuery = '';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
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
          });
        }
      } else {
        // Fallback user fetch
        final users = await ApiService.getAdminUsers();
        if (mounted) {
          setState(() {
            _usersList = users;
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
          backgroundColor: const Color(0xFFDC2626),
        ),
      );
    }
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
    String selectedIcon = '📍';

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
                final success = await ApiService.createCategory(name, desc, selectedIcon);
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
                'Trung tâm Quản trị Admin',
                style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
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
          indicatorColor: crimsonColor,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.grey.shade400,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
          tabs: const [
            Tab(icon: Icon(Icons.dashboard_rounded, size: 18), text: 'Tổng quan'),
            Tab(icon: Icon(Icons.people_alt_rounded, size: 18), text: 'Tài khoản'),
            Tab(icon: Icon(Icons.storefront_rounded, size: 18), text: 'Địa điểm'),
            Tab(icon: Icon(Icons.category_rounded, size: 18), text: 'Danh mục'),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: crimsonColor))
          : TabBarView(
              controller: _tabController,
              children: [
                _buildOverviewTab(),
                _buildUsersTab(crimsonColor),
                _buildEateriesTab(crimsonColor),
                _buildCategoriesAndReviewsTab(crimsonColor),
              ],
            ),
    );
  }

  // =========================================================================
  // TAB 1: 📊 TỔNG QUAN HỆ THỐNG
  // =========================================================================
  Widget _buildOverviewTab() {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Header Banner
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
                  Icon(Icons.analytics_rounded, color: Color(0xFFDC2626), size: 24),
                  SizedBox(width: 8),
                  Text(
                    'THỐNG KÊ HỆ THỐNG TOÀN HUYỆN ĐÔNG ANH',
                    style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 0.5),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _buildStatTile('${_stats['total_users'] ?? _usersList.length}', 'Tài khoản', Icons.person_rounded),
                  _buildStatTile('${_stats['total_eateries'] ?? _eateriesList.length}', 'Địa điểm', Icons.map_rounded),
                  _buildStatTile('${_stats['total_reviews'] ?? _reviewsList.length}', 'Đánh giá', Icons.star_rounded),
                  _buildStatTile('${_stats['total_sellers'] ?? 0}', 'Chủ quán', Icons.storefront_rounded),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),

        const Text('Quản Lý Nhanh', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
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
              child: _buildQuickActionCard('Quản lý Địa điểm', '${_eateriesList.length} quán ăn', Icons.storefront_outlined, Colors.orange, () {
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
  // TAB 2: 👥 QUẢN LÝ TÀI KHOẢN & PHÂN QUYỀN
  // =========================================================================
  Widget _buildUsersTab(Color crimsonColor) {
    final filteredUsers = _usersList.where((u) {
      final name = (u['name'] ?? '').toString().toLowerCase();
      final email = (u['email'] ?? '').toString().toLowerCase();
      final q = _userSearchQuery.toLowerCase();
      return name.contains(q) || email.contains(q);
    }).toList();

    return Column(
      children: [
        // User Search Bar
        Padding(
          padding: const EdgeInsets.all(16),
          child: TextField(
            onChanged: (v) => setState(() => _userSearchQuery = v),
            decoration: InputDecoration(
              hintText: 'Tìm tài khoản theo tên hoặc email...',
              prefixIcon: const Icon(Icons.search_rounded, color: Colors.grey),
              filled: true,
              fillColor: Colors.white,
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
              contentPadding: const EdgeInsets.symmetric(vertical: 12),
            ),
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
                child: ListTile(
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  leading: CircleAvatar(
                    backgroundColor: crimsonColor.withValues(alpha: 0.1),
                    child: Text(
                      (user['name'] ?? 'U').toString().substring(0, 1).toUpperCase(),
                      style: TextStyle(color: crimsonColor, fontWeight: FontWeight.bold),
                    ),
                  ),
                  title: Text(user['name'] ?? 'Người dùng', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(user['email'] ?? '', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: _getRoleColor(currentRole).withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          'Role: ${currentRole.toUpperCase()}',
                          style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: _getRoleColor(currentRole)),
                        ),
                      ),
                    ],
                  ),
                  trailing: PopupMenuButton<String>(
                    initialValue: currentRole,
                    onSelected: (newRole) => _changeUserRole(index, newRole),
                    itemBuilder: (ctx) => const [
                      PopupMenuItem(value: 'user', child: Text('👤 Member (User)')),
                      PopupMenuItem(value: 'seller', child: Text('🛍️ Seller (Chủ gian hàng)')),
                      PopupMenuItem(value: 'manager', child: Text('🏛️ Manager (BQL Chợ)')),
                      PopupMenuItem(value: 'admin', child: Text('🛡️ Admin (Quản trị hệ thống)')),
                    ],
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: crimsonColor.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text('Đổi Role', style: TextStyle(color: Color(0xFFDC2626), fontSize: 12, fontWeight: FontWeight.bold)),
                          Icon(Icons.arrow_drop_down, color: Color(0xFFDC2626), size: 18),
                        ],
                      ),
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      ],
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
  // TAB 3: 🏬 QUẢN LÝ ĐỊA ĐIỂM & GIAN HÀNG
  // =========================================================================
  Widget _buildEateriesTab(Color crimsonColor) {
    final filteredEateries = _eateriesList.where((e) {
      final name = (e['name'] ?? '').toString().toLowerCase();
      final q = _eaterySearchQuery.toLowerCase();
      return name.contains(q);
    }).toList();

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(16),
          child: TextField(
            onChanged: (v) => setState(() => _eaterySearchQuery = v),
            decoration: InputDecoration(
              hintText: 'Tìm địa điểm, quán ăn...',
              prefixIcon: const Icon(Icons.search_rounded, color: Colors.grey),
              filled: true,
              fillColor: Colors.white,
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
              contentPadding: const EdgeInsets.symmetric(vertical: 12),
            ),
          ),
        ),
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.symmetric(horizontal: 16),
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
                    child: const Icon(Icons.restaurant, color: Colors.grey),
                  ),
                  title: Text(eatery['name'] ?? 'Địa điểm', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(eatery['address'] ?? 'Đông Anh, Hà Nội', style: TextStyle(fontSize: 11, color: Colors.grey.shade600), maxLines: 1),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(color: Colors.blue.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(6)),
                            child: Text(eatery['category_name'] ?? 'Địa điểm', style: const TextStyle(fontSize: 10, color: Colors.blue, fontWeight: FontWeight.bold)),
                          ),
                          if (isFeatured) ...[
                            const SizedBox(width: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(color: Colors.orange.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(6)),
                              child: const Text('⭐ Nổi bật', style: TextStyle(fontSize: 10, color: Colors.orange, fontWeight: FontWeight.bold)),
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
        ..._reviewsList.asMap().entries.map((entry) {
          final idx = entry.key;
          final rev = entry.value;
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
                onPressed: () => _deleteReview(idx),
              ),
            ),
          );
        }).toList(),
      ],
    );
  }
}
