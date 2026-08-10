import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../services/api_service.dart';

void showPublicProfileModal(BuildContext context, dynamic userId) {
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (modalContext) => PublicProfileSheet(userId: userId),
  );
}

class PublicProfileSheet extends StatefulWidget {
  final dynamic userId;
  const PublicProfileSheet({super.key, required this.userId});

  @override
  State<PublicProfileSheet> createState() => _PublicProfileSheetState();
}

class _PublicProfileSheetState extends State<PublicProfileSheet> {
  bool _isLoading = true;
  Map<String, dynamic>? _userData;
  List<dynamic> _posts = [];
  List<dynamic> _checkins = [];
  String _friendshipStatus = 'none'; // none, pending_sent, pending_received, accepted, self
  bool _isSendingRequest = false;
  int _selectedTab = 0;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    setState(() => _isLoading = true);
    final data = await ApiService.getPublicProfile(widget.userId);
    if (mounted) {
      if (data != null && data['success'] == true) {
        setState(() {
          _userData = data['user'];
          _posts = data['posts'] ?? [];
          _checkins = data['checkins'] ?? [];
          _friendshipStatus = data['user']?['friendship_status'] ?? 'none';
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _sendFriendRequest() async {
    if (_isSendingRequest) return;
    setState(() => _isSendingRequest = true);

    final res = await ApiService.sendFriendRequest(widget.userId);
    if (mounted) {
      setState(() {
        _isSendingRequest = false;
        if (res['success'] == true) {
          _friendshipStatus = res['status'] ?? 'pending_sent';
        }
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Đã gửi lời mời kết bạn!'),
          backgroundColor: res['success'] == true ? const Color(0xFF10B981) : Colors.red,
        ),
      );
    }
  }

  Widget _buildFriendButton() {
    if (_friendshipStatus == 'self') return const SizedBox.shrink();

    if (_friendshipStatus == 'accepted') {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 9),
        decoration: BoxDecoration(
          color: const Color(0xFFECFDF5),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFF10B981)),
        ),
        child: const Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 16),
            SizedBox(width: 6),
            Text('Bạn bè', style: TextStyle(color: Color(0xFF10B981), fontWeight: FontWeight.bold, fontSize: 13)),
          ],
        ),
      );
    }

    if (_friendshipStatus == 'pending_sent') {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 9),
        decoration: BoxDecoration(
          color: const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFFCBD5E1)),
        ),
        child: const Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.hourglass_top_rounded, color: Color(0xFF64748B), size: 16),
            SizedBox(width: 6),
            Text('Đã gửi yêu cầu', style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.bold, fontSize: 13)),
          ],
        ),
      );
    }

    return ElevatedButton.icon(
      onPressed: _isSendingRequest ? null : _sendFriendRequest,
      icon: _isSendingRequest
          ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
          : const Icon(Icons.person_add_alt_1_rounded, size: 16),
      label: Text(
        _isSendingRequest ? 'Đang gửi...' : 'Thêm bạn bè',
        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
      ),
      style: ElevatedButton.styleFrom(
        backgroundColor: const Color(0xFF0EA5E9),
        foregroundColor: Colors.white,
        elevation: 0,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final media = MediaQuery.of(context);
    final maxH = media.size.height * 0.88;

    return Container(
      height: maxH,
      decoration: const BoxDecoration(
        color: Color(0xFFF8FAFC),
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        children: [
          // Drag handle indicator
          Container(
            margin: const EdgeInsets.only(top: 10, bottom: 6),
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.grey.shade300,
              borderRadius: BorderRadius.circular(2),
            ),
          ),

          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: Color(0xFF0EA5E9)))
                : _userData == null
                    ? const Center(child: Text('Không tìm thấy người dùng này'))
                    : SingleChildScrollView(
                        child: Column(
                          children: [
                            // Cover + Avatar Banner Stack
                            Stack(
                              clipBehavior: Clip.none,
                              children: [
                                // Cover Photo
                                Container(
                                  height: 130,
                                  width: double.infinity,
                                  decoration: BoxDecoration(
                                    image: DecorationImage(
                                      image: NetworkImage(ApiService.getCoverUrl(_userData)),
                                      fit: BoxFit.cover,
                                    ),
                                  ),
                                ),

                                // Floating Profile Card
                                Padding(
                                  padding: const EdgeInsets.fromLTRB(16, 65, 16, 0),
                                  child: Container(
                                    width: double.infinity,
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      borderRadius: BorderRadius.circular(20),
                                      boxShadow: [
                                        BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 4)),
                                      ],
                                    ),
                                    padding: const EdgeInsets.all(16),
                                    child: Column(
                                      children: [
                                        // Avatar + Star
                                        Container(
                                          padding: const EdgeInsets.all(3),
                                          decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                                          child: CircleAvatar(
                                            radius: 38,
                                            backgroundImage: NetworkImage(ApiService.getAvatarUrl(_userData)),
                                          ),
                                        ),
                                        const SizedBox(height: 8),

                                        // Name & Role Star
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Flexible(
                                              child: Text(
                                                _userData!['name'] ?? 'Người dùng',
                                                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                            if ((_userData!['role'] ?? '').toString().toLowerCase() == 'admin') ...[
                                              const SizedBox(width: 4),
                                              const Icon(Icons.star_rounded, color: Color(0xFFEF4444), size: 18),
                                            ] else if (_userData!['is_verified'] == true || _userData!['is_verified'] == 1) ...[
                                              const SizedBox(width: 4),
                                              const Icon(Icons.star_rounded, color: Color(0xFFF59E0B), size: 18),
                                            ],
                                          ],
                                        ),

                                        if ((_userData!['email'] ?? '').isNotEmpty) ...[
                                          const SizedBox(height: 2),
                                          Text(
                                            _userData!['email'],
                                            style: const TextStyle(color: Color(0xFF64748B), fontSize: 12),
                                          ),
                                        ],

                                        const SizedBox(height: 14),

                                        // Social counters
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                                          children: [
                                            _counterItem(_userData!['followers_count']?.toString() ?? '0', 'Người theo dõi'),
                                            _counterItem(_posts.length.toString(), 'Bài viết'),
                                            _counterItem(_checkins.length.toString(), 'Check-in'),
                                          ],
                                        ),

                                        const SizedBox(height: 16),

                                        // Action buttons
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            _buildFriendButton(),
                                            const SizedBox(width: 10),
                                            OutlinedButton.icon(
                                              onPressed: () {
                                                Navigator.pop(context);
                                                ScaffoldMessenger.of(context).showSnackBar(
                                                  SnackBar(content: Text('💬 Mở tin nhắn với ${_userData!['name']}')),
                                                );
                                              },
                                              icon: const Icon(Icons.chat_bubble_outline_rounded, size: 16),
                                              label: const Text('Nhắn tin', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                              style: OutlinedButton.styleFrom(
                                                foregroundColor: const Color(0xFF334155),
                                                side: const BorderSide(color: Color(0xFFCBD5E1)),
                                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                              ),
                                            ),
                                            const SizedBox(width: 8),
                                            InkWell(
                                              onTap: () {
                                                final uid = _userData!['id'] ?? widget.userId;
                                                final shareUrl = 'https://donganhdiscovery.xadonganh.com/profile/$uid';
                                                Clipboard.setData(ClipboardData(text: shareUrl));
                                                ScaffoldMessenger.of(context).showSnackBar(
                                                  SnackBar(
                                                    content: Text('📋 Đã sao chép liên kết trang cá nhân:\n$shareUrl'),
                                                    behavior: SnackBarBehavior.floating,
                                                  ),
                                                );
                                              },
                                              child: Container(
                                                padding: const EdgeInsets.all(10),
                                                decoration: BoxDecoration(
                                                  border: Border.all(color: const Color(0xFFCBD5E1)),
                                                  borderRadius: BorderRadius.circular(12),
                                                ),
                                                child: const Icon(Icons.share, size: 18, color: Color(0xFF475569)),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),

                            const SizedBox(height: 16),

                            // Tabs (Bài viết vs Check-in)
                            Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 16),
                              child: Container(
                                height: 40,
                                padding: const EdgeInsets.all(3),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFE2E8F0),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Row(
                                  children: [
                                    Expanded(
                                      child: GestureDetector(
                                        onTap: () => setState(() => _selectedTab = 0),
                                        child: Container(
                                          decoration: BoxDecoration(
                                            color: _selectedTab == 0 ? const Color(0xFF0EA5E9) : Colors.transparent,
                                            borderRadius: BorderRadius.circular(9),
                                          ),
                                          alignment: Alignment.center,
                                          child: Text(
                                            '📝 Bài viết (${_posts.length})',
                                            style: TextStyle(
                                              fontWeight: FontWeight.bold,
                                              fontSize: 12,
                                              color: _selectedTab == 0 ? Colors.white : const Color(0xFF475569),
                                            ),
                                          ),
                                        ),
                                      ),
                                    ),
                                    Expanded(
                                      child: GestureDetector(
                                        onTap: () => setState(() => _selectedTab = 1),
                                        child: Container(
                                          decoration: BoxDecoration(
                                            color: _selectedTab == 1 ? const Color(0xFF10B981) : Colors.transparent,
                                            borderRadius: BorderRadius.circular(9),
                                          ),
                                          alignment: Alignment.center,
                                          child: Text(
                                            '📍 Check-in (${_checkins.length})',
                                            style: TextStyle(
                                              fontWeight: FontWeight.bold,
                                              fontSize: 12,
                                              color: _selectedTab == 1 ? Colors.white : const Color(0xFF475569),
                                            ),
                                          ),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),

                            const SizedBox(height: 12),

                            // List Content
                            Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 16),
                              child: _selectedTab == 0
                                  ? (_posts.isEmpty
                                      ? const Padding(
                                          padding: EdgeInsets.all(24.0),
                                          child: Text('Chưa có bài viết nào', style: TextStyle(color: Colors.grey)),
                                        )
                                      : ListView.builder(
                                          shrinkWrap: true,
                                          physics: const NeverScrollableScrollPhysics(),
                                          itemCount: _posts.length,
                                          itemBuilder: (context, index) {
                                            final item = _posts[index];
                                            return Card(
                                              margin: const EdgeInsets.only(bottom: 10),
                                              child: ListTile(
                                                title: Text(item['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                                subtitle: Text(item['description'] ?? '', maxLines: 2, overflow: TextOverflow.ellipsis),
                                              ),
                                            );
                                          },
                                        ))
                                  : (_checkins.isEmpty
                                      ? const Padding(
                                          padding: EdgeInsets.all(24.0),
                                          child: Text('Chưa có nhật ký check-in nào', style: TextStyle(color: Colors.grey)),
                                        )
                                      : ListView.builder(
                                          shrinkWrap: true,
                                          physics: const NeverScrollableScrollPhysics(),
                                          itemCount: _checkins.length,
                                          itemBuilder: (context, index) {
                                            final item = _checkins[index];
                                            return Card(
                                              margin: const EdgeInsets.only(bottom: 10),
                                              child: ListTile(
                                                leading: const Icon(Icons.location_on, color: Color(0xFF10B981)),
                                                title: Text(item['eatery_name'] ?? 'Địa điểm', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                                subtitle: Text(item['comment'] ?? '', maxLines: 2, overflow: TextOverflow.ellipsis),
                                              ),
                                            );
                                          },
                                        )),
                            ),
                          ],
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _counterItem(String count, String label) {
    return Column(
      children: [
        Text(count, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF0F172A))),
        const SizedBox(height: 2),
        Text(label, style: const TextStyle(color: Color(0xFF64748B), fontSize: 11)),
      ],
    );
  }
}
