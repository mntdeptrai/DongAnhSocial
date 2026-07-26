import 'package:flutter/material.dart';
import '../services/api_service.dart';

class AdminDashboardScreen extends StatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> {
  List<dynamic> _usersList = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadAdminData();
  }

  Future<void> _loadAdminData() async {
    setState(() => _isLoading = true);
    try {
      final users = await ApiService.getAdminUsers();
      if (mounted) {
        _usersList = users;
      }
    } catch (e) {
      debugPrint('AdminDashboard fetch error: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _changeUserRole(int index, String newRole) {
    setState(() {
      _usersList[index]['role'] = newRole;
    });

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Đã cập nhật quyền cho ${_usersList[index]['name'] ?? 'người dùng'}!'),
        backgroundColor: const Color(0xFFDC2626),
      ),
    );
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
        title: const Row(
          children: [
            Icon(Icons.shield_rounded, color: crimsonColor),
            SizedBox(width: 8),
            Text('Trung tâm Quản trị System Admin', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: crimsonColor))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Admin Metric Header
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [darkObsidian, Color(0xFF1E1010)],
                      ),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: crimsonColor.withValues(alpha: 0.4)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('THỐNG KÊ HỆ THỐNG TOÀN HUYỆN ĐÔNG ANH', style: TextStyle(color: crimsonColor, fontSize: 12, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(child: _buildAdminStat('Tài khoản API', '${_usersList.length}', Icons.people_rounded)),
                            Expanded(child: _buildAdminStat('Bản đồ Live', 'Hoạt động', Icons.storefront_rounded)),
                            Expanded(child: _buildAdminStat('Push FCM', 'Online', Icons.cell_tower_rounded)),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),

                  const Text('Quản lý Phân quyền Tài khoản Người dùng từ API:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  const SizedBox(height: 12),

                  _usersList.isEmpty
                      ? Center(
                          child: Padding(
                            padding: const EdgeInsets.symmetric(vertical: 40),
                            child: Column(
                              children: [
                                Icon(Icons.group_outlined, size: 64, color: Colors.grey.shade400),
                                const SizedBox(height: 12),
                                const Text('Chưa có danh sách tài khoản từ API Admin', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                              ],
                            ),
                          ),
                        )
                      : ListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: _usersList.length,
                          itemBuilder: (context, index) {
                            final u = _usersList[index];
                            final name = u['name'] ?? 'Tài khoản #${u['id']}';
                            final email = u['email'] ?? '';
                            final role = u['role'] ?? 'user';

                            return Card(
                              margin: const EdgeInsets.only(bottom: 12),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                              child: ListTile(
                                contentPadding: const EdgeInsets.all(12),
                                leading: CircleAvatar(
                                  backgroundColor: crimsonColor.withValues(alpha: 0.1),
                                  child: Text(name[0].toUpperCase(), style: const TextStyle(fontWeight: FontWeight.bold, color: crimsonColor)),
                                ),
                                title: Text(name, style: const TextStyle(fontWeight: FontWeight.bold)),
                                subtitle: Text('$email\nRole hiện tại: $role'),
                                trailing: PopupMenuButton<String>(
                                  onSelected: (val) => _changeUserRole(index, val),
                                  itemBuilder: (ctx) => [
                                    const PopupMenuItem(value: 'user', child: Text('Gán Role: Consumer')),
                                    const PopupMenuItem(value: 'seller', child: Text('Gán Role: Seller')),
                                    const PopupMenuItem(value: 'manager', child: Text('Gán Role: Manager')),
                                    const PopupMenuItem(value: 'admin', child: Text('Gán Role: Admin')),
                                  ],
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                    decoration: BoxDecoration(
                                      color: crimsonColor.withValues(alpha: 0.1),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: const Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Text('Đổi Role', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: crimsonColor)),
                                        Icon(Icons.arrow_drop_down, color: crimsonColor, size: 18),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
                ],
              ),
            ),
    );
  }

  Widget _buildAdminStat(String title, String val, IconData icon) {
    return Column(
      children: [
        Icon(icon, color: Colors.white70, size: 20),
        const SizedBox(height: 6),
        Text(val, style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
        const SizedBox(height: 2),
        Text(title, style: const TextStyle(color: Colors.white54, fontSize: 10)),
      ],
    );
  }
}
