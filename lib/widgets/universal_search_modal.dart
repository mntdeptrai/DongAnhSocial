import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'custom_loader.dart';
import 'public_profile_modal.dart';
import '../screens/chat_screen.dart';
import '../screens/eatery_detail_screen.dart';

class UniversalSearchModal extends StatefulWidget {
  final Function(int tabIndex)? onNavigateToTab;

  const UniversalSearchModal({super.key, this.onNavigateToTab});

  static void show(BuildContext context, {Function(int tabIndex)? onNavigateToTab}) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => UniversalSearchModal(onNavigateToTab: onNavigateToTab),
    );
  }

  @override
  State<UniversalSearchModal> createState() => _UniversalSearchModalState();
}

class _UniversalSearchModalState extends State<UniversalSearchModal> {
  final TextEditingController _searchController = TextEditingController();
  String _query = '';
  String _activeFilter = 'Tất cả';

  List<dynamic> _userResults = [];
  List<dynamic> _eateries = [];
  List<dynamic> _products = [];
  bool _isLoading = true;
  bool _isSearchingUsers = false;

  @override
  void initState() {
    super.initState();
    _fetchSearchData();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchSearchData() async {
    setState(() => _isLoading = true);
    try {
      final friendsFuture = ApiService.searchUsers('');
      final eateriesFuture = ApiService.getEateries('dong-anh-food-map');
      final productsFuture = ApiService.getMarketProducts();

      final results = await Future.wait([friendsFuture, eateriesFuture, productsFuture]);

      if (mounted) {
        setState(() {
          _userResults = results[0];
          _eateries = results[1];
          _products = results[2];
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _onSearchQueryChanged(String query) async {
    setState(() => _query = query);
    if (query.trim().isEmpty) {
      final defaultUsers = await ApiService.searchUsers('');
      if (mounted) {
        setState(() => _userResults = defaultUsers);
      }
      return;
    }

    setState(() => _isSearchingUsers = true);
    final searched = await ApiService.searchUsers(query);
    if (mounted) {
      setState(() {
        _userResults = searched;
        _isSearchingUsers = false;
      });
    }
  }

  List<dynamic> get _filteredUsers {
    return _userResults;
  }

  List<dynamic> get _filteredEateries {
    if (_query.trim().isEmpty) return _eateries;
    final q = _query.toLowerCase().trim();
    return _eateries.where((e) {
      final name = (e['name'] ?? '').toString().toLowerCase();
      final address = (e['address'] ?? '').toString().toLowerCase();
      final category = (e['category_name'] ?? '').toString().toLowerCase();
      return name.contains(q) || address.contains(q) || category.contains(q);
    }).toList();
  }

  List<dynamic> get _filteredProducts {
    if (_query.trim().isEmpty) return _products;
    final q = _query.toLowerCase().trim();
    return _products.where((p) {
      final name = (p['name'] ?? p['product_name'] ?? '').toString().toLowerCase();
      return name.contains(q);
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      height: MediaQuery.of(context).size.height * 0.88,
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
        border: Border.all(color: const Color(0xFF0EA5E9).withValues(alpha: 0.15)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.15),
            blurRadius: 20,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: Column(
        children: [
          // Header & Drag Handle
          Container(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 14),
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              border: Border(bottom: BorderSide(color: Color(0x1F0EA5E9))),
            ),
            child: Column(
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: const Color(0xFFCBD5E1),
                      borderRadius: BorderRadius.circular(4),
                    ),
                  ),
                ),
                const SizedBox(height: 12),

                // Search Field Box
                Container(
                  height: 46,
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(23),
                    border: Border.all(color: const Color(0xFF0EA5E9).withValues(alpha: 0.2)),
                  ),
                  child: Row(
                    children: [
                      const SizedBox(width: 14),
                      const Icon(Icons.search_rounded, color: Color(0xFF0EA5E9), size: 22),
                      const SizedBox(width: 10),
                      Expanded(
                        child: TextField(
                          controller: _searchController,
                          autofocus: true,
                          style: const TextStyle(fontSize: 14, color: Color(0xFF0F172A), fontWeight: FontWeight.w600),
                          decoration: const InputDecoration(
                            hintText: 'Tìm Thành viên, Quán ăn & Sản phẩm OCOP...',
                            hintStyle: TextStyle(color: Color(0xFF94A3B8), fontSize: 13),
                            border: InputBorder.none,
                            isDense: true,
                          ),
                          onChanged: _onSearchQueryChanged,
                        ),
                      ),
                      if (_query.isNotEmpty)
                        GestureDetector(
                          onTap: () {
                            _searchController.clear();
                            _onSearchQueryChanged('');
                          },
                          child: const Padding(
                            padding: EdgeInsets.symmetric(horizontal: 10),
                            child: Icon(Icons.cancel, color: Color(0xFF94A3B8), size: 18),
                          ),
                        ),
                      const SizedBox(width: 4),
                    ],
                  ),
                ),
                const SizedBox(height: 12),

                // Filter Pill Sliders
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: ['Tất cả', '👥 Thành viên', '🍲 Quán ăn', '🏆 OCOP'].map((filter) {
                      final bool isSelected = _activeFilter == filter;
                      return GestureDetector(
                        onTap: () => setState(() => _activeFilter = filter),
                        child: Container(
                          margin: const EdgeInsets.only(right: 8),
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                          decoration: BoxDecoration(
                            color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: isSelected ? const Color(0xFF0EA5E9) : const Color(0xFFE2E8F0),
                            ),
                          ),
                          child: Text(
                            filter,
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: isSelected ? Colors.white : const Color(0xFF475569),
                            ),
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                ),
              ],
            ),
          ),

          // Search Results Area
          Expanded(
            child: _isLoading
                ? const CustomPulseLoader(
                    message: 'Đang kết nối dữ liệu...',
                    icon: Icons.search_rounded,
                    primaryColor: Color(0xFF0EA5E9),
                  )
                : ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      // Section 1: Members & People (All Users)
                      if (_activeFilter == 'Tất cả' || _activeFilter == '👥 Thành viên') ...[
                        if (_filteredUsers.isNotEmpty) ...[
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text(
                                '👥 THÀNH VIÊN & NGƯỜI DÙNG',
                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: Color(0xFF0EA5E9), letterSpacing: 0.5),
                              ),
                              if (_isSearchingUsers)
                                const SizedBox(width: 12, height: 12, child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF0EA5E9))),
                            ],
                          ),
                          const SizedBox(height: 10),
                          ..._filteredUsers.map((u) => _buildUserResultItem(u)),
                          const SizedBox(height: 16),
                        ],
                      ],

                      // Section 2: Eateries & Places
                      if (_activeFilter == 'Tất cả' || _activeFilter == '🍲 Quán ăn') ...[
                        if (_filteredEateries.isNotEmpty) ...[
                          const Padding(
                            padding: EdgeInsets.only(bottom: 10),
                            child: Text(
                              '🍲 QUÁN ĂN & ĐỊA ĐIỂM DỊCH VỤ',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: Color(0xFF0EA5E9), letterSpacing: 0.5),
                            ),
                          ),
                          ..._filteredEateries.map((e) => _buildEateryResultItem(e)),
                          const SizedBox(height: 16),
                        ],
                      ],

                      // Section 3: OCOP Products
                      if (_activeFilter == 'Tất cả' || _activeFilter == '🏆 OCOP') ...[
                        if (_filteredProducts.isNotEmpty) ...[
                          const Padding(
                            padding: EdgeInsets.only(bottom: 10),
                            child: Text(
                              '🏆 SẢN PHẨM & ĐẶC SẢN OCOP',
                              style: TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: Color(0xFF0EA5E9), letterSpacing: 0.5),
                            ),
                          ),
                          ..._filteredProducts.map((p) => _buildProductResultItem(p)),
                        ],
                      ],

                      if (_filteredUsers.isEmpty && _filteredEateries.isEmpty && _filteredProducts.isEmpty)
                        Container(
                          padding: const EdgeInsets.symmetric(vertical: 40),
                          child: const Column(
                            children: [
                              Icon(Icons.search_off_rounded, size: 48, color: Color(0xFF94A3B8)),
                              SizedBox(height: 12),
                              Text(
                                'Không tìm thấy kết quả phù hợp',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF475569)),
                              ),
                              SizedBox(height: 4),
                              Text(
                                'Thử tìm từ khóa như "Ngọc Anh", "Bún chả", "Chợ Tó"...',
                                style: TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                              ),
                            ],
                          ),
                        ),
                    ],
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildUserResultItem(dynamic user) {
    final String name = user['name'] ?? 'Người dùng';
    final String email = user['email'] ?? user['phone'] ?? '';
    final String avatar = user['avatar_url'] ?? user['avatar'] ?? '';
    final String role = (user['role'] ?? 'user').toString().toUpperCase();
    final bool isSeller = role.contains('SELLER') || role.contains('STALL');
    final String status = user['friendship_status'] ?? 'none';

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFF0EA5E9).withValues(alpha: 0.1)),
      ),
      child: ListTile(
        onTap: () {
          Navigator.pop(context);
          showPublicProfileModal(context, user['id']);
        },
        leading: CircleAvatar(
          backgroundColor: isSeller ? const Color(0xFF059669) : const Color(0xFF0EA5E9),
          backgroundImage: avatar.startsWith('http') ? NetworkImage(avatar) : null,
          child: !avatar.startsWith('http')
              ? Text(name.isNotEmpty ? name[0].toUpperCase() : 'U', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold))
              : null,
        ),
        title: Row(
          children: [
            Flexible(
              child: Text(
                name,
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A)),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
            if (role.contains('ADMIN')) ...[
              const SizedBox(width: 4),
              const Icon(Icons.star_rounded, color: Color(0xFFEF4444), size: 16),
            ] else if (user['is_verified'] == true || user['is_verified'] == 1) ...[
              const SizedBox(width: 4),
              const Icon(Icons.star_rounded, color: Color(0xFFF59E0B), size: 16),
            ],
          ],
        ),
        subtitle: Text(
          email.isNotEmpty ? email : 'Thành viên Đông Anh Social',
          style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
        trailing: status == 'accepted'
            ? OutlinedButton.icon(
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (context) => const ChatScreen()),
                  );
                },
                icon: const Icon(Icons.chat_bubble_outline_rounded, size: 13),
                label: const Text('Nhắn tin', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                style: OutlinedButton.styleFrom(
                  foregroundColor: const Color(0xFF0EA5E9),
                  side: const BorderSide(color: Color(0xFF0EA5E9)),
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                ),
              )
            : status == 'pending_sent'
                ? Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFCBD5E1)),
                    ),
                    child: const Text('Đã gửi', style: TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.bold)),
                  )
                : ElevatedButton.icon(
                    onPressed: () async {
                      final messenger = ScaffoldMessenger.of(context);
                      final res = await ApiService.sendFriendRequest(user['id']);
                      if (mounted) {
                        if (res['success'] == true) {
                          setState(() {
                            user['friendship_status'] = 'pending_sent';
                          });
                        }
                        messenger.showSnackBar(
                          SnackBar(
                            content: Text(res['message'] ?? 'Đã gửi lời mời kết bạn!'),
                            backgroundColor: res['success'] == true ? const Color(0xFF10B981) : Colors.red,
                          ),
                        );
                      }
                    },
                    icon: const Icon(Icons.person_add_alt_1_rounded, size: 13),
                    label: const Text('Kết bạn', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0EA5E9),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
      ),
    );
  }

  Widget _buildEateryResultItem(dynamic eatery) {
    final name = eatery['name'] ?? 'Quán ăn';
    final address = eatery['address'] ?? 'Đông Anh, Hà Nội';
    final catName = eatery['category_name'] ?? 'Đặc sản';

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFF0EA5E9).withValues(alpha: 0.1)),
      ),
      child: ListTile(
        leading: Container(
          width: 42,
          height: 42,
          decoration: BoxDecoration(
            color: const Color(0xFFF0FDFA),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: const Color(0xFF0EA5E9).withValues(alpha: 0.2)),
          ),
          child: const Icon(Icons.restaurant_rounded, color: Color(0xFF0EA5E9), size: 22),
        ),
        title: Text(name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A))),
        subtitle: Text('$catName • $address', style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)), maxLines: 1, overflow: TextOverflow.ellipsis),
        trailing: const Icon(Icons.chevron_right_rounded, color: Color(0xFF94A3B8)),
        onTap: () {
          Navigator.pop(context);
          final eaterySlug = eatery['slug'] ?? '';
          final catSlug = eatery['category_slug'] ?? 'dong-anh-food-map';
          if (eaterySlug.isNotEmpty) {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => EateryDetailScreen(categorySlug: catSlug, eaterySlug: eaterySlug),
              ),
            );
          }
        },
      ),
    );
  }

  Widget _buildProductResultItem(dynamic product) {
    final name = product['name'] ?? product['product_name'] ?? 'Sản phẩm OCOP';
    final price = double.tryParse(product['price']?.toString() ?? '0') ?? 0;

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFF0EA5E9).withValues(alpha: 0.1)),
      ),
      child: ListTile(
        leading: Container(
          width: 42,
          height: 42,
          decoration: BoxDecoration(
            color: const Color(0xFFFFFBEB),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: Colors.amber.withValues(alpha: 0.3)),
          ),
          child: const Icon(Icons.workspace_premium_rounded, color: Colors.amber, size: 22),
        ),
        title: Text(name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF0F172A))),
        subtitle: Text(price > 0 ? '${price.toInt()}đ' : 'Liên hệ gian hàng', style: const TextStyle(fontSize: 12, color: Color(0xFFEE4D2D), fontWeight: FontWeight.bold)),
        trailing: const Icon(Icons.add_shopping_cart_rounded, color: Color(0xFF0EA5E9)),
        onTap: () {
          Navigator.pop(context);
          widget.onNavigateToTab?.call(3);
        },
      ),
    );
  }
}
