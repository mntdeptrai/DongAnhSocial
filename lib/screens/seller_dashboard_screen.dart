import 'package:flutter/material.dart';
import '../services/api_service.dart';

class SellerDashboardScreen extends StatefulWidget {
  final VoidCallback? onBack;

  const SellerDashboardScreen({super.key, this.onBack});

  @override
  State<SellerDashboardScreen> createState() => _SellerDashboardScreenState();
}

class _SellerDashboardScreenState extends State<SellerDashboardScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;

  Map<String, dynamic> _sellerStats = {};
  List<dynamic> _myProducts = [];
  List<dynamic> _receivedOrders = [];

  final _merchantNameController = TextEditingController();
  final _businessItemsController = TextEditingController();
  final _priceListedController = TextEditingController();
  final _productOriginController = TextEditingController();
  final _bankAccountController = TextEditingController();
  final _bankNameController = TextEditingController();
  final _phoneController = TextEditingController();

  bool _hasSmartphone = true;
  bool _hasAttpCertificate = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _fetchSellerData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _merchantNameController.dispose();
    _businessItemsController.dispose();
    _priceListedController.dispose();
    _productOriginController.dispose();
    _bankAccountController.dispose();
    _bankNameController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _fetchSellerData() async {
    setState(() => _isLoading = true);
    try {
      final dashRes = await ApiService.getSellerDashboardData();
      final profileRes = await ApiService.getSellerProfile();

      if (mounted) {
        if (dashRes['success'] == true) {
          _sellerStats = dashRes['stats'] ?? {};
          if (dashRes['eatery'] != null) {
            _merchantNameController.text = dashRes['eatery']['name'] ?? '';
            _phoneController.text = dashRes['eatery']['phone'] ?? '';
          }
          if (dashRes['dishes'] is List) {
            _myProducts = List<dynamic>.from(dashRes['dishes']);
          }
          if (dashRes['orders'] is List) {
            _receivedOrders = List<dynamic>.from(dashRes['orders']);
          }
        } else {
          final data = profileRes['data'] ?? profileRes;
          _merchantNameController.text = data['merchant_name'] ?? '';
          _phoneController.text = data['phone'] ?? '';
          _myProducts = await ApiService.getMarketProducts();
          _receivedOrders = await ApiService.getSellerOrders();
        }
      }
    } catch (e) {
      debugPrint('SellerDashboard fetch error: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _showAddProductDialog() {
    final nameCtrl = TextEditingController();
    final priceCtrl = TextEditingController();
    final descCtrl = TextEditingController();
    final imageCtrl = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.add_shopping_cart_rounded, color: Color(0xFF10B981)),
            SizedBox(width: 8),
            Text('➕ Thêm Sản Phẩm / Món Ăn', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nameCtrl,
                decoration: const InputDecoration(labelText: 'Tên món ăn / Sản phẩm *', hintText: 'Ví dụ: Bún chả nướng than hoa'),
              ),
              const SizedBox(height: 8),
              TextField(
                controller: priceCtrl,
                decoration: const InputDecoration(labelText: 'Giá bán (Đồng) *', hintText: '40.000đ'),
              ),
              const SizedBox(height: 8),
              TextField(
                controller: descCtrl,
                decoration: const InputDecoration(labelText: 'Mô tả sản phẩm', hintText: 'Kèm rau sống & nước chấm chua ngọt'),
              ),
              const SizedBox(height: 8),
              TextField(
                controller: imageCtrl,
                decoration: const InputDecoration(labelText: 'URL Ảnh sản phẩm', hintText: 'https://...'),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Hủy')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF10B981)),
            onPressed: () async {
              if (nameCtrl.text.trim().isNotEmpty && priceCtrl.text.trim().isNotEmpty) {
                final name = nameCtrl.text.trim();
                final price = priceCtrl.text.trim();
                final desc = descCtrl.text.trim();
                final imgUrl = imageCtrl.text.trim();
                Navigator.pop(ctx);
                final res = await ApiService.storeDish(name: name, price: price, description: desc, imageUrl: imgUrl.isNotEmpty ? imgUrl : null);
                if (res['success'] == true) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('🎉 ${res['message']}'), backgroundColor: const Color(0xFF10B981)),
                  );
                  _fetchSellerData();
                }
              }
            },
            child: const Text('Thêm Sản Phẩm', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  void _updateOrderStatus(int orderId, String newStatus) async {
    final success = await ApiService.updateSellerOrderStatus(orderId, newStatus);
    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('🎉 Đã cập nhật đơn hàng thành $newStatus!'), backgroundColor: const Color(0xFF10B981)),
      );
      _fetchSellerData();
    }
  }

  void _deleteDish(int index) async {
    final dish = _myProducts[index];
    final int id = dish['id'] is int ? dish['id'] : (int.tryParse(dish['id']?.toString() ?? '0') ?? 0);

    setState(() => _myProducts.removeAt(index));
    if (id > 0) {
      await ApiService.deleteDish(id);
    }
  }

  Future<void> _saveProfile() async {
    final body = {
      'merchant_name': _merchantNameController.text,
      'business_items': _businessItemsController.text,
      'price_listed': _priceListedController.text,
      'product_origin': _productOriginController.text,
      'bank_account': _bankAccountController.text,
      'bank_name': _bankNameController.text,
      'phone': _phoneController.text,
      'has_smartphone': _hasSmartphone,
      'has_attp_certificate': _hasAttpCertificate,
    };

    final result = await ApiService.updateSellerProfile(body);
    if (mounted) {
      final msg = result['message'] ?? (result['success'] == true ? 'Đã lưu hồ sơ gian hàng thành công!' : 'Lỗi cập nhật');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(msg), backgroundColor: const Color(0xFF10B981)),
      );
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
    const emeraldColor = Color(0xFF10B981);
    const darkObsidian = Color(0xFF064E3B);

    return Scaffold(
      backgroundColor: const Color(0xFFF0FDF4),
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
            Icon(Icons.storefront_rounded, color: emeraldColor, size: 22),
            SizedBox(width: 8),
            Expanded(
              child: Text(
                'Kênh Điều Hành Chủ Gian Hàng (Seller Portal)',
                style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            tooltip: 'Làm mới',
            onPressed: _fetchSellerData,
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: emeraldColor,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
          tabs: const [
            Tab(icon: Icon(Icons.receipt_long_rounded, size: 18), text: 'Đơn hàng'),
            Tab(icon: Icon(Icons.restaurant_menu_rounded, size: 18), text: 'Thực đơn'),
            Tab(icon: Icon(Icons.store_rounded, size: 18), text: 'Hồ sơ tiệm'),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: emeraldColor))
          : TabBarView(
              controller: _tabController,
              children: [
                _buildOrdersTab(emeraldColor),
                _buildMenuTab(emeraldColor),
                _buildProfileTab(emeraldColor),
              ],
            ),
    );
  }

  // TAB 1: ĐƠN HÀNG & DOANH SỐ
  Widget _buildOrdersTab(Color emeraldColor) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // KPI Tiles
        Row(
          children: [
            Expanded(child: _buildKpiCard('DOANH THU', '${_sellerStats['total_revenue'] ?? 0} đ', Icons.payments_rounded, Colors.green)),
            const SizedBox(width: 8),
            Expanded(child: _buildKpiCard('ĐƠN HÔM NAY', '${_sellerStats['today_orders'] ?? _receivedOrders.length}', Icons.shopping_bag_rounded, Colors.blue)),
            const SizedBox(width: 8),
            Expanded(child: _buildKpiCard('ĐƠN CHỜ DỰT', '${_sellerStats['pending_orders'] ?? 0}', Icons.hourglass_top_rounded, Colors.orange)),
          ],
        ),
        const SizedBox(height: 20),

        const Text('Danh Sách Đơn Hàng Đã Nhận', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
        const SizedBox(height: 10),

        _receivedOrders.isEmpty
            ? Container(
                padding: const EdgeInsets.all(32),
                alignment: Alignment.center,
                child: Column(
                  children: [
                    Icon(Icons.inbox_rounded, size: 48, color: Colors.grey.shade400),
                    const SizedBox(height: 8),
                    Text('Chưa có đơn hàng nào phát sinh', style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                  ],
                ),
              )
            : Column(
                children: _receivedOrders.asMap().entries.map((entry) {
                  final order = entry.value;
                  final orderId = order['id'] is int ? order['id'] : (int.tryParse(order['id']?.toString() ?? '0') ?? 0);
                  final status = (order['status'] ?? 'pending').toString();

                  return Card(
                    margin: const EdgeInsets.only(bottom: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    child: Padding(
                      padding: const EdgeInsets.all(14),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Text(
                                  'Đơn hàng #${order['code'] ?? orderId}',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              const SizedBox(width: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: _getStatusColor(status).withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(status.toUpperCase(), style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: _getStatusColor(status))),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Text('Khách: ${order['customer_name'] ?? 'Khách mua tại quầy'} - SĐT: ${order['phone'] ?? order['customer_phone'] ?? '---'}', style: TextStyle(fontSize: 12, color: Colors.grey.shade700)),
                          const SizedBox(height: 4),
                          Text('Tổng tiền: ${order['total_amount'] ?? order['total_price'] ?? '50.000'} đ', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.red, fontSize: 13)),
                          const Divider(height: 16),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.end,
                            children: [
                              OutlinedButton(
                                onPressed: () => _updateOrderStatus(orderId, 'confirmed'),
                                child: const Text('Xác Nhận', style: TextStyle(fontSize: 11)),
                              ),
                              const SizedBox(width: 6),
                              ElevatedButton(
                                style: ElevatedButton.styleFrom(backgroundColor: emeraldColor),
                                onPressed: () => _updateOrderStatus(orderId, 'completed'),
                                child: const Text('Hoàn Thành', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  );
                }).toList(),
              ),
      ],
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'completed':
        return Colors.green;
      case 'confirmed':
        return Colors.blue;
      case 'shipping':
        return Colors.purple;
      default:
        return Colors.orange;
    }
  }

  Widget _buildKpiCard(String label, String val, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(height: 4),
          Text(val, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: color)),
          Text(label, style: TextStyle(fontSize: 9, color: Colors.grey.shade600, fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  // TAB 2: QUẢN LÝ THỰC ĐƠN / SẢN PHẨM
  Widget _buildMenuTab(Color emeraldColor) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Expanded(
              child: Text(
                'Danh Sách Sản Phẩm / Món Ăn (${_myProducts.length})',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
            const SizedBox(width: 8),
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: emeraldColor,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: _showAddProductDialog,
              icon: const Icon(Icons.add, size: 16, color: Colors.white),
              label: const Text('Thêm Món Mới', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
        const SizedBox(height: 12),

        ..._myProducts.asMap().entries.map((entry) {
          final idx = entry.key;
          final prod = entry.value;

          return Card(
            margin: const EdgeInsets.only(bottom: 12),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            child: ListTile(
              leading: Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(color: Colors.grey.shade200, borderRadius: BorderRadius.circular(12)),
                child: const Icon(Icons.fastfood_rounded, color: Color(0xFF10B981)),
              ),
              title: Text(prod['name'] ?? 'Món ăn', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
              subtitle: Text('${prod['price'] ?? '35.000'} đ', style: const TextStyle(color: Colors.red, fontWeight: FontWeight.bold, fontSize: 12)),
              trailing: IconButton(
                icon: const Icon(Icons.delete_outline_rounded, color: Colors.red),
                onPressed: () => _deleteDish(idx),
              ),
            ),
          );
        }).toList(),
      ],
    );
  }

  // TAB 3: HỒ SƠ GIAN HÀNG & QUY ĐỊNH CHỢ
  Widget _buildProfileTab(Color emeraldColor) {
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
                const Text('📝 Hồ Sơ Gian Hàng & Quy Định Chợ', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                const SizedBox(height: 12),
                TextField(controller: _merchantNameController, decoration: const InputDecoration(labelText: 'Tên tiểu thương / Gian hàng')),
                const SizedBox(height: 8),
                TextField(controller: _phoneController, decoration: const InputDecoration(labelText: 'Số điện thoại')),
                const SizedBox(height: 8),
                TextField(controller: _businessItemsController, decoration: const InputDecoration(labelText: 'Mặt hàng kinh doanh chính')),
                const SizedBox(height: 8),
                TextField(controller: _bankNameController, decoration: const InputDecoration(labelText: 'Ngân hàng nhận thanh toán')),
                const SizedBox(height: 8),
                TextField(controller: _bankAccountController, decoration: const InputDecoration(labelText: 'Số tài khoản ngân hàng')),
                const SizedBox(height: 16),
                ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: emeraldColor,
                    minimumSize: const Size.fromHeight(48),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  onPressed: _saveProfile,
                  icon: const Icon(Icons.save_rounded, color: Colors.white),
                  label: const Text('Lưu Hồ Sơ Gian Hàng', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}
