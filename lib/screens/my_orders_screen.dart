import '../services/api_service.dart';
import '../widgets/custom_loader.dart';

class MyOrdersScreen extends StatefulWidget {
  const MyOrdersScreen({super.key});

  @override
  State<MyOrdersScreen> createState() => _MyOrdersScreenState();
}

class _MyOrdersScreenState extends State<MyOrdersScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _searchController = TextEditingController();
  
  bool _isLoading = true;
  List<dynamic> _orders = [];
  Map<String, dynamic> _stats = {
    'total': 0,
    'processing': 0,
    'completed': 0,
    'spent': 0,
  };
  String _currentStatus = 'all';

  final List<Map<String, String>> _statusTabs = [
    {'key': 'all', 'label': '🌐 Tất cả'},
    {'key': 'pending', 'label': '📋 Chờ xác nhận'},
    {'key': 'paid', 'label': '💳 Đã thanh toán'},
    {'key': 'processing', 'label': '🍳 Đang chuẩn bị'},
    {'key': 'shipping', 'label': '🚴 Đang giao'},
    {'key': 'completed', 'label': '✅ Đã nhận'},
    {'key': 'cancelled', 'label': '🚫 Đã hủy'},
    {'key': 'returned', 'label': '🔄 Hoàn hàng'},
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _statusTabs.length, vsync: this);
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) {
        setState(() {
          _currentStatus = _statusTabs[_tabController.index]['key']!;
        });
        _fetchOrders();
      }
    });
    _fetchOrders();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchOrders() async {
    setState(() => _isLoading = true);
    try {
      final res = await ApiService.getOrders(
        status: _currentStatus,
        search: _searchController.text.trim(),
      );
      if (mounted) {
        setState(() {
          _isLoading = false;
          if (res['success'] == true && res['data'] is List) {
            _orders = res['data'];
          } else {
            _orders = [];
          }
          if (res['stats'] is Map<String, dynamic>) {
            _stats = res['stats'];
          }
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _orders = [];
        });
      }
    }
  }

  void _showSnackBar(String message, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message, style: const TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: isError ? const Color(0xFFEF4444) : const Color(0xFF10B981),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        margin: const EdgeInsets.all(16),
      ),
    );
  }

  // --- ACTIONS ---

  void _confirmReceived(dynamic orderId, String orderCode) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 28),
            SizedBox(width: 8),
            Text('Xác nhận đã nhận hàng', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ],
        ),
        content: Text('Bạn đã nhận đủ món từ đơn hàng #$orderCode và muốn chuyển trạng thái sang Hoàn thành?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Chưa nhận', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF10B981),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: const Text('Đã nhận đủ', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (confirm == true) {
      final res = await ApiService.confirmOrderReceived(orderId);
      if (res['success'] == true) {
        _showSnackBar(res['message'] ?? 'Xác nhận thành công!');
        _fetchOrders();
      } else {
        _showSnackBar(res['message'] ?? 'Thao tác thất bại!', isError: true);
      }
    }
  }

  void _cancelOrderModal(dynamic orderId, String orderCode) {
    String selectedReason = 'Đổi ý không muốn mua nữa';
    final customReasonController = TextEditingController();

    final reasonsList = [
      'Đổi ý không muốn mua nữa',
      'Muốn chọn món khác / thay đổi địa chỉ',
      'Thời gian chờ giao quá lâu',
      'Đặt nhầm số lượng',
      'Lý do khác...',
    ];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) => Padding(
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
                  Row(
                    children: [
                      const Icon(Icons.cancel_outlined, color: Color(0xFFEF4444), size: 24),
                      const SizedBox(width: 8),
                      Text('Hủy đơn hàng #$orderCode', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                    ],
                  ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const Divider(height: 20),
              const Text('Vui lòng chọn hoặc nhập lý do hủy đơn:', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Colors.grey)),
              const SizedBox(height: 12),
              ...reasonsList.map((r) => RadioListTile<String>(
                    dense: true,
                    title: Text(r, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
                    value: r,
                    groupValue: selectedReason,
                    activeColor: const Color(0xFFEF4444),
                    onChanged: (val) {
                      setModalState(() {
                        selectedReason = val!;
                      });
                    },
                  )),
              if (selectedReason == 'Lý do khác...')
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  child: TextField(
                    controller: customReasonController,
                    decoration: InputDecoration(
                      hintText: 'Nhập lý do cụ thể...',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    ),
                  ),
                ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () async {
                    Navigator.pop(ctx);
                    final finalReason = selectedReason == 'Lý do khác...' && customReasonController.text.trim().isNotEmpty
                        ? customReasonController.text.trim()
                        : selectedReason;

                    final res = await ApiService.cancelOrder(orderId, finalReason);
                    if (res['success'] == true) {
                      _showSnackBar(res['message'] ?? 'Hủy đơn thành công!');
                      _fetchOrders();
                    } else {
                      _showSnackBar(res['message'] ?? 'Không thể hủy đơn!', isError: true);
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFEF4444),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('Xác nhận Hủy đơn', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _returnOrderModal(dynamic orderId, String orderCode) {
    String selectedReason = 'Sản phẩm hư hỏng / hỏng hóc khi vận chuyển';
    final customNoteController = TextEditingController();

    final returnReasonsList = [
      'Sản phẩm hư hỏng / hỏng hóc khi vận chuyển',
      'Giao sai sản phẩm / thiếu món',
      'Chất lượng không như mô tả / hết hạn',
      'Khác...',
    ];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) => Padding(
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
                  Row(
                    children: [
                      const Icon(Icons.assignment_return_rounded, color: Color(0xFFF59E0B), size: 24),
                      const SizedBox(width: 8),
                      Text('Yêu cầu Hoàn hàng #$orderCode', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                    ],
                  ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const Divider(height: 20),
              const Text('Chọn lý do yêu cầu Hoàn hàng / Trả hàng:', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: Colors.grey)),
              const SizedBox(height: 12),
              ...returnReasonsList.map((r) => RadioListTile<String>(
                    dense: true,
                    title: Text(r, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
                    value: r,
                    groupValue: selectedReason,
                    activeColor: const Color(0xFFF59E0B),
                    onChanged: (val) {
                      setModalState(() {
                        selectedReason = val!;
                      });
                    },
                  )),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                child: TextField(
                  controller: customNoteController,
                  decoration: InputDecoration(
                    hintText: 'Ghi chú mô tả thêm tình trạng hàng...',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () async {
                    Navigator.pop(ctx);
                    String noteText = customNoteController.text.trim();
                    String fullReason = selectedReason + (noteText.isNotEmpty ? ' ($noteText)' : '');

                    final res = await ApiService.returnOrder(orderId, fullReason);
                    if (res['success'] == true) {
                      _showSnackBar(res['message'] ?? 'Đã gửi yêu cầu hoàn hàng!');
                      _fetchOrders();
                    } else {
                      _showSnackBar(res['message'] ?? 'Không thể gửi yêu cầu!', isError: true);
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFF59E0B),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('Gửi Yêu Cầu Hoàn Hàng', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _reorderItems(dynamic orderId) async {
    final res = await ApiService.reorderItems(orderId);
    if (res['success'] == true) {
      _showSnackBar(res['message'] ?? 'Đã thêm lại các món vào giỏ hàng!');
    } else {
      _showSnackBar(res['message'] ?? 'Không thể đặt lại món!', isError: true);
    }
  }

  @override
  Widget build(BuildContext context) {
    const primaryColor = Color(0xFF0EA5E9);

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Đơn hàng của tôi',
          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 19, letterSpacing: -0.5),
        ),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        elevation: 0.5,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: primaryColor),
            onPressed: _fetchOrders,
          ),
        ],
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(100),
          child: Column(
            children: [
              // Search Input Box
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                child: TextField(
                  controller: _searchController,
                  onSubmitted: (_) => _fetchOrders(),
                  decoration: InputDecoration(
                    hintText: 'Tìm mã đơn #ORD001 hoặc tên món ăn...',
                    hintStyle: const TextStyle(fontSize: 13, color: Colors.grey),
                    prefixIcon: const Icon(Icons.search_rounded, size: 20, color: Colors.grey),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, size: 18),
                            onPressed: () {
                              _searchController.clear();
                              _fetchOrders();
                            },
                          )
                        : null,
                    filled: true,
                    fillColor: const Color(0xFFF1F5F9),
                    contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: BorderSide.none,
                    ),
                  ),
                ),
              ),

              // Status Filter Tabs
              TabBar(
                controller: _tabController,
                isScrollable: true,
                indicatorColor: primaryColor,
                indicatorWeight: 3,
                labelColor: primaryColor,
                unselectedLabelColor: const Color(0xFF64748B),
                labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.normal, fontSize: 13),
                tabs: _statusTabs.map((t) => Tab(text: t['label'])).toList(),
              ),
            ],
          ),
        ),
      ),
      backgroundColor: const Color(0xFFF8FAFC),
      body: RefreshIndicator(
        onRefresh: _fetchOrders,
        color: primaryColor,
        child: _isLoading
            ? const CustomPulseLoader(
                message: 'Đang tải lịch sử đơn hàng...',
                icon: Icons.receipt_long_rounded,
                primaryColor: primaryColor,
              )
            : _orders.isEmpty
                ? _buildEmptyState()
                : ListView.builder(
                    padding: const EdgeInsets.all(14),
                    itemCount: _orders.length + 1,
                    itemBuilder: (context, index) {
                      if (index == 0) {
                        return _buildStatsBar();
                      }
                      final order = _orders[index - 1];
                      return _buildOrderCard(order);
                    },
                  ),
      ),
    );
  }

  Widget _buildStatsBar() {
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _statBadge('📦 ${_stats['total'] ?? 0}', 'Tổng đơn'),
          _statBadge('✅ ${_stats['completed'] ?? 0}', 'Hoàn thành'),
          _statBadge('💳 ${_formatCurrency((_stats['spent'] as num?)?.toDouble() ?? 0.0)}', 'Tổng chi tiêu'),
        ],
      ),
    );
  }

  Widget _statBadge(String value, String label) {
    return Column(
      children: [
        Text(value, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13, color: Color(0xFF0EA5E9))),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(fontSize: 11, color: Colors.grey)),
      ],
    );
  }

  Widget _buildEmptyState() {
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      child: Container(
        alignment: Alignment.center,
        padding: const EdgeInsets.symmetric(vertical: 80, horizontal: 24),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.shopping_bag_outlined, size: 56, color: Color(0xFF0EA5E9)),
            ),
            const SizedBox(height: 16),
            const Text(
              'Chưa có đơn hàng nào',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
            ),
            const SizedBox(height: 8),
            const Text(
              'Các đơn hàng bạn đã đặt trên Bản đồ Ẩm thực & Chợ số Đông Anh sẽ hiển thị tại đây.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: Colors.grey),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildOrderCard(Map<String, dynamic> order) {
    final String orderCode = order['order_code'] ?? 'ORD';
    final String status = order['status'] ?? 'pending';
    final String statusLabel = order['status_label'] ?? status;
    final String eateryName = order['eatery_name'] ?? 'Đông Anh Market';
    final String createdAt = order['created_at_formatted'] ?? '';
    final double totalAmount = (order['total_amount'] as num?)?.toDouble() ?? 0.0;
    final String paymentMethod = order['payment_method'] ?? 'COD';
    final String paymentStatus = order['payment_status'] ?? 'pending';
    final String address = order['shipping_address'] ?? 'Đông Anh, Hà Nội';
    final List<dynamic> items = order['items'] ?? [];

    Color statusBg = Colors.blue.shade50;
    Color statusFg = const Color(0xFF0EA5E9);
    IconData statusIcon = Icons.access_time_rounded;

    if (status == 'pending') {
      statusBg = const Color(0xFFFFFBEB);
      statusFg = const Color(0xFFD97706);
      statusIcon = Icons.hourglass_top_rounded;
    } else if (status == 'paid' || status == 'processing') {
      statusBg = const Color(0xFFE0F2FE);
      statusFg = const Color(0xFF0284C7);
      statusIcon = Icons.soup_kitchen_rounded;
    } else if (status == 'shipping' || status == 'delivering') {
      statusBg = const Color(0xFFECFDF5);
      statusFg = const Color(0xFF059669);
      statusIcon = Icons.directions_bike_rounded;
    } else if (status == 'completed') {
      statusBg = const Color(0xFFF0FDF4);
      statusFg = const Color(0xFF16A34A);
      statusIcon = Icons.check_circle_rounded;
    } else if (status == 'cancelled') {
      statusBg = const Color(0xFFFEF2F2);
      statusFg = const Color(0xFFDC2626);
      statusIcon = Icons.cancel_rounded;
    } else if (status == 'returned') {
      statusBg = const Color(0xFFFFF7ED);
      statusFg = const Color(0xFFEA580C);
      statusIcon = Icons.assignment_return_rounded;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Order Header Bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: Colors.grey.shade50,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(18)),
              border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Row(
                    children: [
                      const Icon(Icons.storefront_rounded, size: 18, color: Color(0xFF0EA5E9)),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          eateryName,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: statusBg,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(statusIcon, size: 13, color: statusFg),
                      const SizedBox(width: 4),
                      Text(
                        statusLabel,
                        style: TextStyle(color: statusFg, fontWeight: FontWeight.bold, fontSize: 11),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Items List
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text('#$orderCode', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: Color(0xFF64748B))),
                    Text(createdAt, style: const TextStyle(fontSize: 11, color: Colors.grey)),
                  ],
                ),
                const SizedBox(height: 10),

                ...items.map((item) => Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: Row(
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(10),
                            child: item['image'] != null
                                ? Image.network(
                                    item['image'],
                                    width: 48,
                                    height: 48,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, __, _) => Container(
                                      width: 48,
                                      height: 48,
                                      color: Colors.orange.shade50,
                                      child: const Icon(Icons.fastfood, color: Colors.orange),
                                    ),
                                  )
                                : Container(
                                    width: 48,
                                    height: 48,
                                    color: Colors.orange.shade50,
                                    child: const Icon(Icons.fastfood, color: Colors.orange),
                                  ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  item['name'] ?? 'Sản phẩm',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  'Số lượng: x${item['quantity']}',
                                  style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                                ),
                              ],
                            ),
                          ),
                          Text(
                            _formatCurrency((item['price'] as num?)?.toDouble() ?? 0.0 * (item['quantity'] as num? ?? 1)),
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ],
                      ),
                    )),

                const Divider(height: 16),

                // Payment Status & Delivery Address
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Icon(
                          paymentMethod == 'Online' ? Icons.credit_card_rounded : Icons.payments_rounded,
                          size: 16,
                          color: paymentStatus == 'success' ? Colors.green : Colors.orange,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          paymentMethod == 'Online'
                              ? (paymentStatus == 'success' ? '💳 Đã thanh toán QR' : '⏳ Chờ thanh toán QR')
                              : '💵 COD (Thanh toán khi nhận)',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: paymentStatus == 'success' ? Colors.green.shade700 : Colors.orange.shade800,
                          ),
                        ),
                      ],
                    ),
                    Row(
                      children: [
                        const Text('Thành tiền: ', style: TextStyle(fontSize: 12, color: Colors.grey)),
                        Text(
                          _formatCurrency(totalAmount),
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: Color(0xFFEA580C)),
                        ),
                      ],
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(Icons.location_on_outlined, size: 14, color: Colors.grey),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        address,
                        style: const TextStyle(fontSize: 11, color: Colors.grey),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Order Action Footer Buttons
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              color: Colors.grey.shade50,
              borderRadius: const BorderRadius.vertical(bottom: Radius.circular(18)),
              border: Border(top: BorderSide(color: Colors.grey.shade200)),
            ),
            child: Wrap(
              alignment: WrapAlignment.end,
              spacing: 8,
              runSpacing: 8,
              children: [
                // 1. Confirm Received Button (Xác nhận đã nhận hàng)
                if (status == 'shipping' || status == 'delivering' || status == 'processing' || status == 'paid')
                  ElevatedButton.icon(
                    onPressed: () => _confirmReceived(order['id'], orderCode),
                    icon: const Icon(Icons.check_circle_rounded, size: 16),
                    label: const Text('Đã Nhận Hàng'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF10B981),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                    ),
                  ),

                // 2. Cancel Order Button (Hủy đơn hàng)
                if (status == 'pending' || status == 'paid')
                  OutlinedButton.icon(
                    onPressed: () => _cancelOrderModal(order['id'], orderCode),
                    icon: const Icon(Icons.cancel_outlined, size: 16, color: Color(0xFFEF4444)),
                    label: const Text('Hủy Đơn', style: TextStyle(color: Color(0xFFEF4444))),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      side: const BorderSide(color: Color(0xFFFCA5A5)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                    ),
                  ),

                // 3. Return Order Button (Yêu cầu Hoàn hàng)
                if (status == 'completed' || status == 'shipping' || status == 'delivering')
                  OutlinedButton.icon(
                    onPressed: () => _returnOrderModal(order['id'], orderCode),
                    icon: const Icon(Icons.assignment_return_rounded, size: 16, color: Color(0xFFEA580C)),
                    label: const Text('Hoàn Hàng', style: TextStyle(color: Color(0xFFEA580C))),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      side: const BorderSide(color: Color(0xFFFDBA74)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                    ),
                  ),

                // 4. Reorder Button (Đặt lại món)
                if (status == 'completed' || status == 'cancelled' || status == 'returned')
                  ElevatedButton.icon(
                    onPressed: () => _reorderItems(order['id']),
                    icon: const Icon(Icons.refresh_rounded, size: 16),
                    label: const Text('Đặt Lại Món'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0EA5E9),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  String _formatCurrency(double amount) {
    final int value = amount.toInt();
    final String str = value.toString();
    final RegExp reg = RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))');
    final String result = str.replaceAllMapped(reg, (Match m) => '${m[1]}.');
    return '$result đ';
  }
}
