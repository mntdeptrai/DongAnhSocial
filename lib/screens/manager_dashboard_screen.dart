import 'package:flutter/material.dart';
import '../services/api_service.dart';

class ManagerDashboardScreen extends StatefulWidget {
  const ManagerDashboardScreen({super.key});

  @override
  State<ManagerDashboardScreen> createState() => _ManagerDashboardScreenState();
}

class _ManagerDashboardScreenState extends State<ManagerDashboardScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = false;

  List<dynamic> _pendingApprovals = [];
  List<dynamic> _stalls = [];

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
      final stallsData = await ApiService.getManagerStalls();
      if (mounted) {
        _stalls = stallsData;
        _pendingApprovals = [];
      }
    } catch (e) {
      debugPrint('ManagerDashboard error: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
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
            Text('Gửi Cảnh báo ATTP'),
          ],
        ),
        content: const Text(
          'Phát thông báo yêu cầu kiểm tra An toàn thực phẩm định kỳ tới tất cả gian hàng trong Chợ số Đông Anh?',
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
                  content: Text('Đã gửi thông báo cảnh báo ATTP tới các gian hàng!'),
                  backgroundColor: Color(0xFF4F46E5),
                ),
              );
            },
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4F46E5), foregroundColor: Colors.white),
            child: const Text('Phát Thông Báo'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    const indigoTheme = Color(0xFF4F46E5);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: indigoTheme))
          : CustomScrollView(
              slivers: [
                // Executive Manager Header
                SliverToBoxAdapter(
                  child: Container(
                    padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFF312E81), Color(0xFF4F46E5)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.vertical(bottom: Radius.circular(28)),
                    ),
                    child: SafeArea(
                      bottom: false,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Row(
                                children: [
                                  Builder(
                                    builder: (ctx) => IconButton(
                                      icon: const Icon(Icons.menu_rounded, color: Colors.white, size: 24),
                                      onPressed: () => Scaffold.of(ctx).openDrawer(),
                                    ),
                                  ),
                                  const SizedBox(width: 4),
                                  Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withValues(alpha: 0.15),
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(Icons.admin_panel_settings_rounded, color: Colors.white, size: 22),
                                  ),
                                  const SizedBox(width: 12),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text(
                                        'Cán bộ Quản lý Chợ',
                                        style: TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.w500),
                                      ),
                                      Text(
                                        ApiService.currentUser?['name'] ?? 'BQL Chợ Số Đông Anh',
                                        style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                              IconButton(
                                onPressed: _sendAttpAlert,
                                icon: const Icon(Icons.campaign_rounded, color: Colors.amberAccent, size: 26),
                                tooltip: 'Cảnh báo ATTP',
                              ),
                            ],
                          ),
                          const SizedBox(height: 20),

                          // Metric Grid Cards
                          Row(
                            children: [
                              Expanded(
                                child: _buildMetricCard(
                                  label: 'Gian Hàng API',
                                  value: '${_stalls.length}',
                                  subtext: 'Danh mục thực',
                                  icon: Icons.store_rounded,
                                  color: const Color(0xFF0EA5E9),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: _buildMetricCard(
                                  label: 'Doanh Số API',
                                  value: 'Trực tiếp',
                                  subtext: 'Hệ thống Live',
                                  icon: Icons.payments_rounded,
                                  color: const Color(0xFF10B981),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: _buildMetricCard(
                                  label: 'Cần Duyệt',
                                  value: '${_pendingApprovals.length}',
                                  subtext: 'Hồ sơ',
                                  icon: Icons.fact_check_rounded,
                                  color: const Color(0xFFF59E0B),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ),

                // Tab Selector Bar
                SliverToBoxAdapter(
                  child: Container(
                    margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 10, offset: const Offset(0, 4)),
                      ],
                    ),
                    child: TabBar(
                      controller: _tabController,
                      indicatorColor: indigoTheme,
                      labelColor: indigoTheme,
                      unselectedLabelColor: Colors.grey.shade600,
                      indicatorSize: TabBarIndicatorSize.label,
                      labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                      tabs: const [
                        Tab(text: 'Duyệt Hồ Sơ API'),
                        Tab(text: 'Danh Bạ Gian Hàng'),
                        Tab(text: 'An Toàn Thực Phẩm'),
                      ],
                    ),
                  ),
                ),

                // Tab Contents
                SliverFillRemaining(
                  hasScrollBody: true,
                  child: TabBarView(
                    controller: _tabController,
                    children: [
                      _buildApprovalQueueTab(),
                      _buildStallDirectoryTab(),
                      _buildFoodSafetyTab(),
                    ],
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildMetricCard({
    required String label,
    required String value,
    required String subtext,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(label, style: const TextStyle(color: Colors.white70, fontSize: 11)),
              Icon(icon, color: color, size: 16),
            ],
          ),
          const SizedBox(height: 6),
          Text(value, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
          const SizedBox(height: 2),
          Text(subtext, style: TextStyle(color: color, fontSize: 10, fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }

  Widget _buildApprovalQueueTab() {
    if (_pendingApprovals.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.verified_rounded, size: 64, color: Colors.green.shade400),
            const SizedBox(height: 12),
            const Text('Tất cả hồ sơ từ API đã được xử lý!', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 4),
            Text('Không có gian hàng nào đang chờ duyệt từ hệ thống.', style: TextStyle(color: Colors.grey.shade600)),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: _pendingApprovals.length,
      itemBuilder: (context, index) {
        final item = _pendingApprovals[index];

        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          elevation: 2,
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(item['name'] ?? 'Hồ sơ mới', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildStallDirectoryTab() {
    if (_stalls.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.storefront_outlined, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 12),
            const Text('Đang tải danh bạ gian hàng từ API...', style: TextStyle(color: Colors.grey)),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: _stalls.length,
      itemBuilder: (context, index) {
        final stall = _stalls[index];

        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          child: ListTile(
            contentPadding: const EdgeInsets.all(12),
            leading: const CircleAvatar(
              backgroundColor: Color(0xFFEEF2FF),
              child: Icon(Icons.storefront_rounded, color: Color(0xFF4F46E5)),
            ),
            title: Text(stall['name'] ?? stall['title'] ?? 'Gian hàng OCOP', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            subtitle: Text('Danh mục API ID: #${stall['id'] ?? index + 1}'),
          ),
        );
      },
    );
  }

  Widget _buildFoodSafetyTab() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFEEF2FF),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFC7D2FE)),
            ),
            child: Row(
              children: [
                const Icon(Icons.verified_user_rounded, color: Color(0xFF4F46E5), size: 36),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: const [
                      Text('Quản lý An toàn thực phẩm', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                      SizedBox(height: 2),
                      Text('Dữ liệu chứng nhận ATTP từ hệ thống cơ sở dữ liệu thực tế', style: TextStyle(fontSize: 12, color: Color(0xFF3730A3))),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
          const Text('Tác vụ Quản lý An toàn thực phẩm:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
          const SizedBox(height: 12),
          ElevatedButton.icon(
            onPressed: _sendAttpAlert,
            icon: const Icon(Icons.campaign_rounded),
            label: const Text('Phát Thông Báo Kiểm Tra ATTP Toàn Huyện'),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF4F46E5),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 14),
              minimumSize: const Size(double.infinity, 48),
            ),
          ),
        ],
      ),
    );
  }
}
