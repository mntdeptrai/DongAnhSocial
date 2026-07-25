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

  // Seller Products & Orders State
  List<Map<String, dynamic>> _myProducts = [];
  List<Map<String, dynamic>> _receivedOrders = [];

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

        if (productsRes.isNotEmpty) {
          _myProducts = List<Map<String, dynamic>>.from(productsRes);
        } else {
          _myProducts = [];
        }

        _receivedOrders = [];
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
        const SnackBar(content: Text('🎉 Đã cập nhật hồ sơ gian hàng chợ thành công!')),
      );
    }
  }

  void _showAddProductModal() {
    final nameCtrl = TextEditingController();
    final priceCtrl = TextEditingController();
    final stallCtrl = TextEditingController();

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
                    '➕ Thêm Mặt Hàng / Niêm Yết Giá',
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
                  labelText: 'Tên mặt hàng / Sản phẩm',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: priceCtrl,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'Giá niêm yết (VNĐ)',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: stallCtrl,
                decoration: const InputDecoration(
                  labelText: 'Vị trí Sạp / Gian hàng tại chợ',
                  border: OutlineInputBorder(),
                  isDense: true,
                ),
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  icon: const Icon(Icons.save),
                  label: const Text('LƯU MẶT HÀNG MỚI', style: TextStyle(fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0EA5E9),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () {
                    if (nameCtrl.text.trim().isEmpty) return;
                    setState(() {
                      _myProducts.insert(0, {
                        'id': DateTime.now().millisecondsSinceEpoch,
                        'name': nameCtrl.text.trim(),
                        'price': double.tryParse(priceCtrl.text) ?? 30000,
                        'stall_name': stallCtrl.text.trim(),
                        'seller_name': _merchantNameController.text,
                        'seller_phone': _phoneController.text,
                        'star_rating': '4 sao',
                        'image_path': 'https://picsum.photos/202/200'
                      });
                    });
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Đã thêm sản phẩm mới vào sạp chợ!')),
                    );
                  },
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            const Text(
              'DongAnh',
              style: TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 18),
            ),
            const Text(
              ' Seller Hub',
              style: TextStyle(color: Color(0xFFFFB800), fontWeight: FontWeight.w900, fontSize: 18),
            ),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: const Color(0xFFFFB800),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Text('Số hóa Sạp Chợ 🏪', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF38BDF8), Color(0xFF00A8EE), Color(0xFF0284C7)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
        ),
        foregroundColor: Colors.white,
        elevation: 0,
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(48),
          child: Container(
            color: Colors.white,
            child: TabBar(
              controller: _tabController,
              labelColor: const Color(0xFF0284C7),
              unselectedLabelColor: Colors.grey[600],
              labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
              indicatorColor: const Color(0xFFFFB800),
              indicatorWeight: 3.5,
              tabs: const [
                Tab(icon: Icon(Icons.badge_outlined, size: 18), text: '📋 Hồ Sơ Kê Khai'),
                Tab(icon: Icon(Icons.storefront_outlined, size: 18), text: '🛍️ Mặt Hàng & Giá'),
                Tab(icon: Icon(Icons.receipt_long_outlined, size: 18), text: '🧾 Đơn Hàng'),
              ],
            ),
          ),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : TabBarView(
              controller: _tabController,
              children: [
                _buildMerchantProfileTab(),
                _buildProductManagementTab(),
                _buildOrdersTab(),
              ],
            ),
    );
  }

  /// Tab 1: Biểu mẫu Kê khai Dữ liệu số Thương nhân Chợ (9 Mục theo biểu mẫu chính quyền)
  Widget _buildMerchantProfileTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.blue[50],
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.blue[200]!),
            ),
            child: Row(
              children: [
                const Icon(Icons.info_outline, color: Color(0xFF0EA5E9)),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Biểu mẫu kê khai Dữ liệu số Tiểu thương & Ban quản lý chợ Xã Đông Anh',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.blue[900]),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // 1. Họ và tên cá nhân, tổ chức kinh doanh
          _buildFormField(
            label: '1. HỌ VÀ TÊN CÁ NHÂN / TỔ CHỨC KINH DOANH TẠI CHỢ',
            controller: _merchantNameController,
            icon: Icons.person_outline,
            hint: 'Ví dụ: Nguyễn Thị Hương',
          ),

          // 2. Mặt hàng buôn bán
          _buildFormField(
            label: '2. MẶT HÀNG BUÔN BÁN (Ghi cụ thể từng mặt hàng)',
            controller: _businessItemsController,
            icon: Icons.shopping_basket_outlined,
            hint: 'Ví dụ: Bún Mạch Tràng, Tương Cổ Loa, Rau sạch',
            maxLines: 2,
          ),

          // 3. Niêm yết giá
          _buildFormField(
            label: '3. NIÊM YẾT GIÁ (Ghi tương xứng giá của từng mặt hàng)',
            controller: _priceListedController,
            icon: Icons.sell_outlined,
            hint: 'Ví dụ: Bún 35k/kg, Tương 60k/chai',
          ),

          // 4. Nguồn gốc xuất xứ
          _buildFormField(
            label: '4. NGUỒN GỐC XUẤT XỨ (Nhập từ đâu hay Tự sản xuất?)',
            controller: _productOriginController,
            icon: Icons.nature_people_outlined,
            hint: 'Ví dụ: Tự sản xuất tại làng nghề Cổ Loa',
          ),

          // 5. Số tài khoản ngân hàng & Ngân hàng
          Row(
            children: [
              Expanded(
                flex: 2,
                child: _buildFormField(
                  label: '5. SỐ TÀI KHOẢN NGÂN HÀNG',
                  controller: _bankAccountController,
                  icon: Icons.account_balance_wallet_outlined,
                  hint: '1028734912',
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                flex: 2,
                child: _buildFormField(
                  label: 'NGÂN HÀNG',
                  controller: _bankNameController,
                  icon: Icons.account_balance_outlined,
                  hint: 'VietinBank',
                ),
              ),
            ],
          ),

          // 6. Mã QR thanh toán
          Container(
            margin: const EdgeInsets.only(bottom: 16),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.grey[300]!),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  '6. MÃ QR THANH TOÁN TẠI CỬA HÀNG / SẠP CHỢ',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.black87),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Container(
                      width: 60,
                      height: 60,
                      decoration: BoxDecoration(
                        color: Colors.grey[100],
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.grey[300]!),
                      ),
                      child: const Icon(Icons.qr_code_2, size: 36, color: Color(0xFF0EA5E9)),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'VietQR đã liên kết thành công',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Khách quét mã thanh toán trực tiếp qua ngân hàng',
                            style: TextStyle(fontSize: 11, color: Colors.grey[600]),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // 7. Số điện thoại của chủ cửa hàng
          _buildFormField(
            label: '7. SỐ ĐIỆN THOẠI CỦA CHỦ CỬA HÀNG BUÔN BÁN',
            controller: _phoneController,
            icon: Icons.phone_android_outlined,
            hint: '0988xxxxxx',
            keyboardType: TextInputType.phone,
          ),

          // 8. Có sử dụng điện thoại thông minh
          Container(
            margin: const EdgeInsets.only(bottom: 16),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.grey[300]!),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  '8. CÓ SỬ DỤNG ĐIỆN THOẠI THÔNG MINH?',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                ),
                Switch(
                  value: _hasSmartphone,
                  activeColor: const Color(0xFF0EA5E9),
                  onChanged: (val) => setState(() => _hasSmartphone = val),
                ),
              ],
            ),
          ),

          // 9. Giấy chứng nhận ATTP
          Container(
            margin: const EdgeInsets.only(bottom: 24),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.grey[300]!),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Expanded(
                  child: Text(
                    '9. GIẤY CHỨNG NHẬN AN TOÀN THỰC PHẨM (CN ATTP)',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                ),
                Switch(
                  value: _hasAttpCertificate,
                  activeColor: Colors.green,
                  onChanged: (val) => setState(() => _hasAttpCertificate = val),
                ),
              ],
            ),
          ),

          // Save Profile Button
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              icon: const Icon(Icons.cloud_upload_outlined),
              label: const Text('LƯU HỒ SƠ ĐĂNG KÝ GIAN HÀNG', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF0EA5E9),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: _saveProfile,
            ),
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _buildFormField({
    required String label,
    required TextEditingController controller,
    required IconData icon,
    required String hint,
    int maxLines = 1,
    TextInputType keyboardType = TextInputType.text,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.black87),
          ),
          const SizedBox(height: 6),
          TextField(
            controller: controller,
            maxLines: maxLines,
            keyboardType: keyboardType,
            decoration: InputDecoration(
              prefixIcon: Icon(icon, color: const Color(0xFF0EA5E9), size: 20),
              hintText: hint,
              hintStyle: TextStyle(color: Colors.grey[400], fontSize: 13),
              filled: true,
              fillColor: Colors.white,
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(color: Colors.grey[300]!),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(color: Colors.grey[300]!),
              ),
            ),
          ),
        ],
      ),
    );
  }

  /// Tab 2: Quản lý Mặt Hàng & Giá Niêm Yết
  Widget _buildProductManagementTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Mặt Hàng Hiện Tại (${_myProducts.length})',
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              ElevatedButton.icon(
                icon: const Icon(Icons.add, size: 18),
                label: const Text('Thêm Sản Phẩm', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0EA5E9),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: _showAddProductModal,
              ),
            ],
          ),
          const SizedBox(height: 16),
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _myProducts.length,
            itemBuilder: (context, index) {
              final prod = _myProducts[index];
              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                child: ListTile(
                  contentPadding: const EdgeInsets.all(10),
                  leading: ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.network(
                      (prod['image_path'] != null && prod['image_path'].toString().isNotEmpty)
                          ? (prod['image_path'].toString().startsWith('http')
                              ? prod['image_path'].toString()
                              : 'https://donganhdiscovery.xadonganh.com/' + (prod['image_path'].toString().startsWith('/') ? prod['image_path'].toString().substring(1) : prod['image_path'].toString()))
                          : 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=200&q=80',
                      width: 60,
                      height: 60,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(
                        width: 60,
                        height: 60,
                        color: Colors.orange[50],
                        child: const Icon(Icons.shopping_bag, color: Colors.orange),
                      ),
                    ),
                  ),
                  title: Text(
                    prod['name'] ?? '',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                  ),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const SizedBox(height: 2),
                      Text('🏷️ Niêm yết: ${prod['price']} VNĐ', style: const TextStyle(color: Color(0xFF0EA5E9), fontWeight: FontWeight.bold)),
                      Text('🏪 ${prod['stall_name'] ?? 'Gian hàng chợ'}', style: TextStyle(fontSize: 12, color: Colors.grey[600])),
                    ],
                  ),
                  trailing: IconButton(
                    icon: const Icon(Icons.delete_outline, color: Colors.red),
                    onPressed: () {
                      setState(() {
                        _myProducts.removeAt(index);
                      });
                    },
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  /// Tab 3: Quản Lý Đơn Hàng Đã Nhận
  Widget _buildOrdersTab() {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _receivedOrders.length,
      itemBuilder: (context, index) {
        final order = _receivedOrders[index];
        final bool isDone = order['status'] == 'Đã hoàn thành';

        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          child: Padding(
            padding: const EdgeInsets.all(14.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Mã đơn: ${order['id']}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: isDone ? Colors.green[50] : Colors.orange[50],
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        order['status'],
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: isDone ? Colors.green : Colors.orange[800],
                        ),
                      ),
                    ),
                  ],
                ),
                const Divider(),
                Text('👤 Khách hàng: ${order['customer']} (${order['phone']})', style: const TextStyle(fontSize: 13)),
                const SizedBox(height: 4),
                Text('🛒 Sản phẩm: ${order['items']}', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                const SizedBox(height: 4),
                Text('💰 Tổng tiền: ${order['total']} VNĐ (Thanh toán COD)', style: const TextStyle(fontSize: 13, color: Color(0xFF0EA5E9), fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                if (!isDone)
                  Align(
                    alignment: Alignment.centerRight,
                    child: ElevatedButton.icon(
                      icon: const Icon(Icons.check, size: 16),
                      label: const Text('XÁC NHẬN GIAO HÀNG', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      ),
                      onPressed: () {
                        setState(() {
                          order['status'] = 'Đã hoàn thành';
                        });
                      },
                    ),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }
}
