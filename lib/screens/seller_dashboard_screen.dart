import 'package:flutter/material.dart';
import '../services/api_service.dart';

class SellerDashboardScreen extends StatefulWidget {
  const SellerDashboardScreen({super.key});

  @override
  State<SellerDashboardScreen> createState() => _SellerDashboardScreenState();
}

class _SellerDashboardScreenState extends State<SellerDashboardScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _isLoading = true;

  // 9 registration fields according to market regulation form
  final _merchantNameController = TextEditingController();
  final _businessItemsController = TextEditingController();
  final _priceListedController = TextEditingController();
  final _productOriginController = TextEditingController();
  final _bankAccountController = TextEditingController();
  final _bankNameController = TextEditingController();
  final _phoneController = TextEditingController();

  bool _hasSmartphone = true;
  bool _hasAttpCertificate = true;

  // Real API Data lists
  List<dynamic> _myProducts = [];
  List<dynamic> _receivedOrders = [];

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
      final profileRes = await ApiService.getSellerProfile();
      final productsRes = await ApiService.getMarketProducts();
      final ordersRes = await ApiService.getSellerOrders();

      if (mounted) {
        final data = profileRes['data'] ?? profileRes;
        _merchantNameController.text = data['merchant_name'] ?? '';
        _businessItemsController.text = data['business_items'] ?? '';
        _priceListedController.text = data['price_listed'] ?? '';
        _productOriginController.text = data['product_origin'] ?? '';
        _bankAccountController.text = data['bank_account'] ?? '';
        _bankNameController.text = data['bank_name'] ?? '';
        _phoneController.text = data['phone'] ?? '';
        _hasSmartphone = data['has_smartphone'] ?? false;
        _hasAttpCertificate = data['has_attp_certificate'] ?? false;

        _myProducts = productsRes;
        _receivedOrders = ordersRes;
      }
    } catch (e) {
      debugPrint('SellerDashboard fetch error: $e');
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
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
    await ApiService.updateSellerProfile(body);
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('🎉 Đã cập nhật hồ sơ gian hàng chợ thành công!'),
          backgroundColor: Color(0xFF059669),
        ),
      );
    }
  }

  void _showAddProductModal() {
    final nameCtrl = TextEditingController();
    final priceCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return Padding(
          padding: EdgeInsets.only(
            top: 20,
            left: 20,
            right: 20,
            bottom: MediaQuery.of(context).viewInsets.bottom + 20,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    '➕ Thêm Sản Phẩm OCOP Mới',
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
              const Divider(),
              const SizedBox(height: 8),
              TextField(
                controller: nameCtrl,
                decoration: const InputDecoration(
                  labelText: 'Tên sản phẩm / Món ăn',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: priceCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'Giá niêm yết (VNĐ)',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () {
                  if (nameCtrl.text.isNotEmpty) {
                    setState(() {
                      _myProducts.insert(0, {
                        'name': nameCtrl.text.trim(),
                        'price': priceCtrl.text.trim(),
                        'stall_name': _merchantNameController.text,
                        'in_stock': true,
                      });
                    });
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Đã thêm sản phẩm thành công!'),
                        backgroundColor: Color(0xFF059669),
                      ),
                    );
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF059669),
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 44),
                ),
                child: const Text('Thêm Sản Phẩm', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    const emeraldPrimary = Color(0xFF059669);
    const slateNavy = Color(0xFF0F172A);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: emeraldPrimary))
          : CustomScrollView(
              slivers: [
                // Merchant Header Banner
                SliverToBoxAdapter(
                  child: Container(
                    padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [slateNavy, Color(0xFF065F46)],
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
                                  Container(
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withValues(alpha: 0.15),
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Icon(Icons.storefront_rounded, color: Colors.white, size: 26),
                                  ),
                                  const SizedBox(width: 12),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text(
                                        'Chủ Gian Hàng OCOP',
                                        style: TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.w500),
                                      ),
                                      Text(
                                        _merchantNameController.text.isNotEmpty
                                            ? _merchantNameController.text
                                            : 'Gian Hàng Chợ Số',
                                        style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF10B981).withValues(alpha: 0.2),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: const Color(0xFF34D399)),
                                ),
                                child: const Row(
                                  children: [
                                    CircleAvatar(radius: 4, backgroundColor: Color(0xFF34D399)),
                                    SizedBox(width: 6),
                                    Text('Đang Bán', style: TextStyle(color: Color(0xFF34D399), fontSize: 11, fontWeight: FontWeight.bold)),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 20),

                          // Revenue Metric Cards
                          Row(
                            children: [
                              Expanded(
                                child: _buildMerchantMetric(
                                  label: 'Doanh Thu',
                                  value: 'API Live',
                                  subtext: 'Thời gian thực',
                                  icon: Icons.account_balance_wallet_rounded,
                                  color: const Color(0xFF34D399),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: _buildMerchantMetric(
                                  label: 'Đơn Hàng',
                                  value: '${_receivedOrders.length}',
                                  subtext: 'Đã nhận',
                                  icon: Icons.shopping_bag_rounded,
                                  color: const Color(0xFF38BDF8),
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: _buildMerchantMetric(
                                  label: 'Sản Phẩm',
                                  value: '${_myProducts.length}',
                                  subtext: 'Niêm yết',
                                  icon: Icons.inventory_2_rounded,
                                  color: const Color(0xFFFBBF24),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ),

                // Tab Bar
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
                      indicatorColor: emeraldPrimary,
                      labelColor: emeraldPrimary,
                      unselectedLabelColor: Colors.grey.shade600,
                      indicatorSize: TabBarIndicatorSize.label,
                      labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                      tabs: const [
                        Tab(text: 'Đơn Hàng API'),
                        Tab(text: 'Sản Phẩm API'),
                        Tab(text: 'Hồ Sơ Gian Hàng'),
                      ],
                    ),
                  ),
                ),

                // Tab Views
                SliverFillRemaining(
                  hasScrollBody: true,
                  child: TabBarView(
                    controller: _tabController,
                    children: [
                      _buildOrdersTab(),
                      _buildProductsTab(),
                      _buildProfileRegTab(),
                    ],
                  ),
                ),
              ],
            ),
    );
  }

  Widget _buildMerchantMetric({
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
        border: Border.all(color: Colors.white.withValues(alpha: 0.18)),
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

  Widget _buildOrdersTab() {
    if (_receivedOrders.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.shopping_bag_outlined, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 12),
            const Text('Chưa có đơn hàng nào từ hệ thống API', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            const SizedBox(height: 4),
            Text('Các đơn hàng từ khách mua sắm sẽ tự động xuất hiện tại đây.', style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      itemCount: _receivedOrders.length,
      itemBuilder: (context, index) {
        final order = _receivedOrders[index];

        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          child: ListTile(
            contentPadding: const EdgeInsets.all(16),
            title: Text('Đơn hàng #${order['id'] ?? order['code']}', style: const TextStyle(fontWeight: FontWeight.bold)),
            subtitle: Text('Khách: ${order['customer_name'] ?? order['user_name'] ?? 'Khách mua'}\nTổng: ${order['total_amount'] ?? order['total']}'),
          ),
        );
      },
    );
  }

  Widget _buildProductsTab() {
    if (_myProducts.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.inventory_2_outlined, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 12),
            const Text('Chưa có sản phẩm OCOP nào từ API', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
            const SizedBox(height: 12),
            ElevatedButton.icon(
              onPressed: _showAddProductModal,
              icon: const Icon(Icons.add),
              label: const Text('Thêm Sản Phẩm Mới'),
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF059669), foregroundColor: Colors.white),
            ),
          ],
        ),
      );
    }

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Tổng số sản phẩm (${_myProducts.length})', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
              ElevatedButton.icon(
                onPressed: _showAddProductModal,
                icon: const Icon(Icons.add, size: 16),
                label: const Text('Thêm Món'),
                style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF059669), foregroundColor: Colors.white),
              ),
            ],
          ),
        ),
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            itemCount: _myProducts.length,
            itemBuilder: (context, index) {
              final p = _myProducts[index];

              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: ListTile(
                  contentPadding: const EdgeInsets.all(12),
                  leading: const CircleAvatar(
                    backgroundColor: Color(0xFFECFDF5),
                    child: Icon(Icons.shopping_bag_rounded, color: Color(0xFF059669)),
                  ),
                  title: Text(p['name'] ?? 'Sản phẩm OCOP', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                  subtitle: Text('Giá niêm yết: ${p['price'] ?? 'Liên hệ'}'),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildProfileRegTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Hồ sơ Đăng ký 9 Hạng mục Chợ số:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
          const SizedBox(height: 12),
          TextField(controller: _merchantNameController, decoration: const InputDecoration(labelText: 'Tên gian hàng / HKD', border: OutlineInputBorder())),
          const SizedBox(height: 10),
          TextField(controller: _businessItemsController, decoration: const InputDecoration(labelText: 'Mặt hàng kinh doanh', border: OutlineInputBorder())),
          const SizedBox(height: 10),
          TextField(controller: _bankAccountController, decoration: const InputDecoration(labelText: 'Số tài khoản ngân hàng', border: OutlineInputBorder())),
          const SizedBox(height: 10),
          TextField(controller: _bankNameController, decoration: const InputDecoration(labelText: 'Tên ngân hàng chi nhánh', border: OutlineInputBorder())),
          const SizedBox(height: 10),
          TextField(controller: _phoneController, decoration: const InputDecoration(labelText: 'Số điện thoại liên hệ', border: OutlineInputBorder())),
          const SizedBox(height: 14),
          SwitchListTile(
            title: const Text('Có smartphone nhận đơn hàng online'),
            value: _hasSmartphone,
            activeTrackColor: const Color(0xFF059669),
            onChanged: (val) => setState(() => _hasSmartphone = val),
          ),
          SwitchListTile(
            title: const Text('Đã có chứng nhận An toàn thực phẩm (ATTP)'),
            value: _hasAttpCertificate,
            activeTrackColor: const Color(0xFF059669),
            onChanged: (val) => setState(() => _hasAttpCertificate = val),
          ),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _saveProfile,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF059669),
              foregroundColor: Colors.white,
              minimumSize: const Size(double.infinity, 48),
            ),
            child: const Text('Lưu Hồ Sơ Gian Hàng', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }
}
