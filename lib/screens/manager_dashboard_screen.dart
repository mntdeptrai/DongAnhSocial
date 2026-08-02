import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/custom_loader.dart';

class ManagerDashboardScreen extends StatefulWidget {
  final VoidCallback? onBack;

  const ManagerDashboardScreen({super.key, this.onBack});

  @override
  State<ManagerDashboardScreen> createState() => _ManagerDashboardScreenState();
}

class _ManagerDashboardScreenState extends State<ManagerDashboardScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = false;

  Map<String, dynamic> _managerStats = {};
  List<dynamic> _stalls = [];
  String _marketName = 'Chợ Trung Tâm Đông Anh';
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _loadManagerData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadManagerData() async {
    setState(() => _isLoading = true);
    try {
      final res = await ApiService.getManagerDashboardData();
      if (mounted) {
        if (res['success'] == true) {
          _managerStats = res['stats'] ?? {};
          _marketName = res['market_name'] ?? 'Chợ Trung Tâm Đông Anh';
          if (res['stalls'] is List) {
            _stalls = List<dynamic>.from(res['stalls']);
          }
        }
      }
    } catch (e) {
      debugPrint('ManagerDashboard error: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _showCreateBulletinDialog() {
    final titleCtrl = TextEditingController();
    final contentCtrl = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.campaign_rounded, color: Color(0xFF4F46E5)),
            SizedBox(width: 8),
            Text('Đăng Bảng Tin BQL Chợ', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: titleCtrl,
                decoration: const InputDecoration(labelText: 'Tiêu đề thông báo *', hintText: 'Ví dụ: Kế hoạch tổng vệ sinh toàn chợ'),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: contentCtrl,
                maxLines: 4,
                decoration: const InputDecoration(labelText: 'Nội dung thông báo *', hintText: 'Yêu cầu các gian hàng thu dọn rác thải trước 18h00...'),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4F46E5)),
            onPressed: () async {
              if (titleCtrl.text.trim().isNotEmpty && contentCtrl.text.trim().isNotEmpty) {
                final title = titleCtrl.text.trim();
                final content = contentCtrl.text.trim();
                Navigator.pop(ctx);
                final success = await ApiService.storeManagerBulletin(title, content);
                if (success && mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('🎉 Đã đăng bảng tin BQL Chợ thành công!'), backgroundColor: Color(0xFF4F46E5)),
                  );
                }
              }
            },
            child: const Text('Phát Thông Báo', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  void _updateStallStatus(int stallId, String newStatus) async {
    final success = await ApiService.updateStallStatus(stallId, newStatus);
    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('🎉 Đã cập nhật trạng thái gian hàng thành $newStatus!'), backgroundColor: const Color(0xFF4F46E5)),
      );
      _loadManagerData();
    }
  }

  void _sendAttpAlert() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Color(0xFFE11D48)),
            SizedBox(width: 8),
            Expanded(child: Text('Gửi Cảnh báo ATTP')),
          ],
        ),
        content: const Text(
          'Phát thông báo yêu cầu kiểm tra An toàn thực phẩm định kỳ tới tất cả gian hàng trong Chợ số Đông Anh?',
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4F46E5)),
            onPressed: () {
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('🎉 Đã phát cảnh báo ATTP tới toàn bộ tiểu thương!'), backgroundColor: Color(0xFF4F46E5)),
              );
            },
            child: const Text('Phát Thông Báo', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
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
    const indigoTheme = Color(0xFF4F46E5);
    const darkObsidian = Color(0xFF1E1B4B);

    return Scaffold(
      backgroundColor: const Color(0xFFEEF2FF),
      appBar: AppBar(
        backgroundColor: darkObsidian,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: Colors.white),
          tooltip: 'Quay lại',
          onPressed: _handleBack,
        ),
        title: Row(
          children: [
            const Icon(Icons.account_balance_rounded, color: indigoTheme, size: 22),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                'BQL $_marketName',
                style: const TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            onPressed: _loadManagerData,
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: indigoTheme,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
          tabs: const [
            Tab(icon: Icon(Icons.dashboard_rounded, size: 18), text: 'Giám sát'),
            Tab(icon: Icon(Icons.storefront_rounded, size: 18), text: 'Gian hàng'),
            Tab(icon: Icon(Icons.campaign_rounded, size: 18), text: 'Bảng tin BQL'),
          ],
        ),
      ),
      body: _isLoading
          ? const CustomPulseLoader(
              message: 'Đang tải Ban Quản Lý Chợ & ATTP...',
              icon: Icons.admin_panel_settings_rounded,
              primaryColor: Color(0xFF4F46E5),
            )
          : TabBarView(
              controller: _tabController,
              children: [
                _buildOverviewTab(indigoTheme),
                _buildStallsTab(indigoTheme),
                _buildBulletinTab(indigoTheme),
              ],
            ),
    );
  }

  // TAB 1: GIÁM SÁT & TỔNG QUAN CHỢ
  Widget _buildOverviewTab(Color indigoTheme) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // KPI Overview Header Banner
        Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF1E1B4B), Color(0xFF312E81)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(24),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('HỆ THỐNG GIÁM SÁT - $_marketName', style: const TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _buildStatTile('${_managerStats['total_stalls'] ?? _stalls.length}', 'Gian hàng', Icons.store_rounded),
                  _buildStatTile('${_managerStats['active_stalls'] ?? _stalls.length}', 'Hoạt động', Icons.check_circle_rounded),
                  _buildStatTile('${_managerStats['attp_inspected'] ?? 100}%', 'Đạt ATTP', Icons.verified_user_rounded),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        Row(
          children: [
            Expanded(
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(backgroundColor: indigoTheme, padding: const EdgeInsets.symmetric(vertical: 12), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14))),
                onPressed: _showCreateBulletinDialog,
                icon: const Icon(Icons.campaign_rounded, color: Colors.white, size: 18),
                label: const Text('📢 Đăng Thông Báo', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFE11D48), padding: const EdgeInsets.symmetric(vertical: 12), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14))),
                onPressed: _sendAttpAlert,
                icon: const Icon(Icons.warning_amber_rounded, color: Colors.white, size: 18),
                label: const Text('⚠️ Cảnh Báo ATTP', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
              ),
            ),
          ],
        ),
        const SizedBox(height: 20),

        const Text('Danh Sách Gian Hàng Chợ Quản Lý', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
        const SizedBox(height: 10),

        ..._stalls.take(10).map((stall) {
          final id = stall['id'] is int ? stall['id'] : (int.tryParse(stall['id']?.toString() ?? '0') ?? 0);
          final status = (stall['status'] ?? 'active').toString();

          return Card(
            margin: const EdgeInsets.only(bottom: 10),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            child: ListTile(
              leading: const CircleAvatar(backgroundColor: Color(0xFFEEF2FF), child: Icon(Icons.storefront_rounded, color: Color(0xFF4F46E5))),
              title: Text(stall['name'] ?? 'Gian hàng chợ', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
              subtitle: Text(stall['address'] ?? 'Chợ Trung Tâm', style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
              trailing: PopupMenuButton<String>(
                onSelected: (val) => _updateStallStatus(id, val),
                itemBuilder: (ctx) => const [
                  PopupMenuItem(value: 'active', child: Text('✅ Kích hoạt hoạt động')),
                  PopupMenuItem(value: 'suspended', child: Text('⛔ Tạm đình chỉ gian hàng')),
                ],
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(color: (status == 'active' ? Colors.green : Colors.red).withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
                  child: Text(status.toUpperCase(), style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: status == 'active' ? Colors.green : Colors.red)),
                ),
              ),
            ),
          );
        }).toList(),
      ],
    );
  }

  Widget _buildStatTile(String val, String label, IconData icon) {
    return Column(
      children: [
        Icon(icon, color: const Color(0xFF818CF8), size: 20),
        const SizedBox(height: 4),
        Text(val, style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
        Text(label, style: const TextStyle(color: Colors.white70, fontSize: 10)),
      ],
    );
  }

  // TAB 2: QUẢN LÝ TIỂU THƯƠNG
  Widget _buildStallsTab(Color indigoTheme) {
    final filteredStalls = _stalls.where((s) {
      final name = (s['name'] ?? '').toString().toLowerCase();
      return name.contains(_searchQuery.toLowerCase());
    }).toList();

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(16),
          child: TextField(
            onChanged: (v) => setState(() => _searchQuery = v),
            decoration: InputDecoration(
              hintText: 'Tìm kiếm gian hàng, tiểu thương...',
              prefixIcon: const Icon(Icons.search_rounded, color: Colors.grey),
              filled: true,
              fillColor: Colors.white,
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
            ),
          ),
        ),
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            itemCount: filteredStalls.length,
            itemBuilder: (context, index) {
              final stall = filteredStalls[index];
              final id = stall['id'] is int ? stall['id'] : (int.tryParse(stall['id']?.toString() ?? '0') ?? 0);

              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: ListTile(
                  leading: const Icon(Icons.storefront_rounded, color: Color(0xFF4F46E5)),
                  title: Text(stall['name'] ?? 'Gian hàng', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                  subtitle: Text('SĐT: ${stall['phone'] ?? '0987654321'}', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                  trailing: ElevatedButton(
                    style: ElevatedButton.styleFrom(backgroundColor: indigoTheme),
                    onPressed: () => _updateStallStatus(id, 'active'),
                    child: const Text('Duyệt Gian Hàng', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  // TAB 3: BẢNG TIN BQL CHỢ
  Widget _buildBulletinTab(Color indigoTheme) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Card(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('📢 Thông Báo BQL Chợ Mới Nhất', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                const SizedBox(height: 12),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(Icons.campaign_rounded, color: Color(0xFF4F46E5), size: 32),
                  title: const Text('Thông báo kiểm tra ATTP đợt 3/2026', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                  subtitle: const Text('Yêu cầu tất cả các hộ tiểu thương niêm yết giá công khai và giữ vệ sinh khu vực bán hàng.', style: TextStyle(fontSize: 12)),
                ),
                const SizedBox(height: 12),
                ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: indigoTheme,
                    minimumSize: const Size.fromHeight(48),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  onPressed: _showCreateBulletinDialog,
                  icon: const Icon(Icons.add, color: Colors.white),
                  label: const Text('➕ Đăng Bảng Tin Mới', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}
