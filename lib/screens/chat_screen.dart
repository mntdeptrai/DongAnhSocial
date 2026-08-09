import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/custom_loader.dart';

class ChatScreen extends StatefulWidget {
  const ChatScreen({super.key});

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> with WidgetsBindingObserver {
  List<dynamic> _friends = [];
  bool _isLoading = false;

  Timer? _friendsTimer;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _loadFriends();
    _startTimer();
  }

  void _startTimer() {
    _friendsTimer?.cancel();
    _friendsTimer = Timer.periodic(const Duration(seconds: 10), (timer) {
      _silentRefreshFriends();
    });
  }

  void _stopTimer() {
    _friendsTimer?.cancel();
    _friendsTimer = null;
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused ||
        state == AppLifecycleState.inactive ||
        state == AppLifecycleState.hidden) {
      _stopTimer();
    } else if (state == AppLifecycleState.resumed) {
      _silentRefreshFriends();
      _startTimer();
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _stopTimer();
    super.dispose();
  }

  void _sortFriendsList(List<dynamic> list) {
    list.sort((a, b) {
      final aUnread = (a['unread_count'] ?? (a['unread'] == true ? 1 : 0)) as int;
      final bUnread = (b['unread_count'] ?? (b['unread'] == true ? 1 : 0)) as int;
      if (aUnread != bUnread) {
        return bUnread.compareTo(aUnread); // Ưu tiên chưa đọc lên đầu
      }

      final aTime = a['last_message_at'] ?? a['updated_at'] ?? a['time'];
      final bTime = b['last_message_at'] ?? b['updated_at'] ?? b['time'];
      if (aTime != null && bTime != null) {
        return bTime.toString().compareTo(aTime.toString()); // Gần đây nhất lên đầu
      } else if (aTime != null) {
        return -1;
      } else if (bTime != null) {
        return 1;
      }

      final aHasMsg = (a['last_message'] != null || a['latest_message'] != null) ? 1 : 0;
      final bHasMsg = (b['last_message'] != null || b['latest_message'] != null) ? 1 : 0;
      if (aHasMsg != bHasMsg) {
        return bHasMsg.compareTo(aHasMsg);
      }

      final aOnline = a['is_online'] == true ? 1 : 0;
      final bOnline = b['is_online'] == true ? 1 : 0;
      return bOnline.compareTo(aOnline);
    });
  }

  Future<void> _loadFriends() async {
    setState(() {
      _isLoading = true;
    });
    final friends = await ApiService.getFriends();
    _sortFriendsList(friends);
    if (mounted) {
      setState(() {
        _friends = friends;
        _isLoading = false;
      });
    }
  }

  Future<void> _silentRefreshFriends() async {
    if (!ApiService.isAuthenticated) return;
    final friends = await ApiService.getFriends();
    _sortFriendsList(friends);
    if (mounted) {
      setState(() {
        _friends = friends;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = const Color(0xFF0EA5E9);
    final isGuest = !ApiService.isAuthenticated;

    if (isGuest) {
      return Scaffold(
        appBar: AppBar(
          title: const Text('Tin nhắn', style: TextStyle(fontWeight: FontWeight.bold)),
          backgroundColor: Colors.white,
          foregroundColor: Colors.grey[800],
          elevation: 0,
        ),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.chat_bubble_outline, size: 64, color: Colors.grey[300]),
                const SizedBox(height: 16),
                const Text(
                  'Vui lòng đăng nhập để bắt đầu trò chuyện với bạn bè.',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 16, color: Colors.grey),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: const Text('Tin nhắn', style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.grey[800],
        elevation: 0.5,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadFriends,
          )
        ],
      ),
      body: _isLoading
          ? const CustomPulseLoader(
              message: 'Đang tải danh sách tin nhắn...',
              icon: Icons.chat_bubble_outline_rounded,
              primaryColor: Color(0xFF0EA5E9),
            )
          : _friends.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.people_outline, size: 64, color: Colors.grey[300]),
                      const SizedBox(height: 12),
                      const Text(
                        'Chưa có bạn bè nào để trò chuyện.',
                        style: TextStyle(fontSize: 16, color: Colors.grey),
                      ),
                    ],
                  ),
                )
              : ListView.separated(
                  padding: const EdgeInsets.symmetric(vertical: 4),
                  itemCount: _friends.length,
                  separatorBuilder: (context, index) => const Divider(height: 1, indent: 72, endIndent: 16, color: Color(0xFFF1F5F9)),
                  itemBuilder: (context, index) {
                    final friend = _friends[index];
                    final isOnline = friend['is_online'] == true;
                    final unreadCount = (friend['unread_count'] ?? (friend['unread'] == true ? 1 : 0)) as int;
                    final hasUnread = unreadCount > 0 || friend['is_new'] == true;

                    final lastMsg = (friend['last_message'] != null && friend['last_message'].toString().isNotEmpty)
                        ? friend['last_message'].toString()
                        : ((friend['latest_message'] != null && friend['latest_message'].toString().isNotEmpty)
                            ? friend['latest_message'].toString()
                            : null);

                    return ListTile(
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      tileColor: hasUnread ? primaryColor.withValues(alpha: 0.05) : Colors.transparent,
                      leading: Stack(
                        children: [
                          CircleAvatar(
                            radius: 26,
                            backgroundColor: primaryColor.withValues(alpha: 0.1),
                            backgroundImage: ResizeImage(NetworkImage(ApiService.getAvatarUrl(friend, friend['name'])), width: 120),
                          ),
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: Container(
                              width: 14,
                              height: 14,
                              decoration: BoxDecoration(
                                color: isOnline ? const Color(0xFF10B981) : Colors.grey[400],
                                shape: BoxShape.circle,
                                border: Border.all(color: Colors.white, width: 2),
                              ),
                            ),
                          ),
                        ],
                      ),
                      title: Row(
                        children: [
                          Expanded(
                            child: Text(
                              friend['name'] ?? '',
                              style: TextStyle(
                                fontWeight: hasUnread ? FontWeight.w900 : FontWeight.w700,
                                fontSize: 16,
                                color: hasUnread ? const Color(0xFF0F172A) : const Color(0xFF334155),
                              ),
                            ),
                          ),
                        ],
                      ),
                      subtitle: Padding(
                        padding: const EdgeInsets.only(top: 4.0),
                        child: Text(
                          lastMsg ?? (isOnline ? 'Đang hoạt động' : 'Ngoại tuyến'),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontWeight: hasUnread ? FontWeight.w800 : FontWeight.normal,
                            color: hasUnread
                                ? const Color(0xFF0EA5E9)
                                : (lastMsg != null ? Colors.grey[600] : (isOnline ? const Color(0xFF10B981) : Colors.grey[400])),
                            fontSize: 14,
                          ),
                        ),
                      ),
                      trailing: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          if (hasUnread)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFF0EA5E9),
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: [
                                  BoxShadow(
                                    color: const Color(0xFF0EA5E9).withValues(alpha: 0.3),
                                    blurRadius: 6,
                                    offset: const Offset(0, 2),
                                  )
                                ],
                              ),
                              child: Text(
                                unreadCount > 0 ? '$unreadCount tin mới' : 'Mới',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                            )
                          else
                            const Icon(Icons.chevron_right, size: 20, color: Colors.grey),
                        ],
                      ),
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => ChatDetailScreen(
                              friendId: friend['id'],
                              friendName: friend['name'] ?? 'Bạn bè',
                            ),
                          ),
                        );
                      },
                    );
                  },
                ),
    );
  }
}

class ChatDetailScreen extends StatefulWidget {
  final int friendId;
  final String friendName;
  final bool isEmbedded;

  const ChatDetailScreen({
    super.key,
    required this.friendId,
    required this.friendName,
    this.isEmbedded = false,
  });

  @override
  State<ChatDetailScreen> createState() => _ChatDetailScreenState();
}

class _ChatDetailScreenState extends State<ChatDetailScreen> with SingleTickerProviderStateMixin, WidgetsBindingObserver {
  final _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  List<dynamic> _messages = [];
  bool _isLoading = false;
  Timer? _pollingTimer;

  late AnimationController _sendAnimController;
  late Animation<double> _sendScaleAnimation;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);

    _sendAnimController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 150),
    );
    _sendScaleAnimation = Tween<double>(begin: 1.0, end: 0.88).animate(
      CurvedAnimation(parent: _sendAnimController, curve: Curves.easeOut),
    );

    _loadMessages();
    _startTimer();
  }

  void _startTimer() {
    _pollingTimer?.cancel();
    _pollingTimer = Timer.periodic(const Duration(seconds: 2), (timer) {
      _silentRefreshMessages();
    });
  }

  void _stopTimer() {
    _pollingTimer?.cancel();
    _pollingTimer = null;
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused ||
        state == AppLifecycleState.inactive ||
        state == AppLifecycleState.hidden) {
      _stopTimer();
    } else if (state == AppLifecycleState.resumed) {
      _silentRefreshMessages();
      _startTimer();
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _stopTimer();
    _sendAnimController.dispose();
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _loadMessages() async {
    setState(() {
      _isLoading = true;
    });
    final res = await ApiService.getMessages(widget.friendId);
    if (mounted) {
      setState(() {
        _messages = res['messages'] ?? [];
        _isLoading = false;
      });
      _scrollToBottom();
    }
  }

  Future<void> _silentRefreshMessages() async {
    final res = await ApiService.getMessages(widget.friendId);
    if (mounted) {
      final newMessages = res['messages'] ?? [];
      if (newMessages.length != _messages.length) {
        setState(() {
          _messages = newMessages;
        });
        _scrollToBottom();
      }
    }
  }

  void _scrollToBottom() {
    if (_scrollController.hasClients) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      });
    }
  }

  bool _isSendingMessage = false;

  void _sendMessage() async {
    if (_isSendingMessage) return;
    final text = _messageController.text.trim();
    if (text.isEmpty) return;

    _isSendingMessage = true;
    _sendAnimController.forward().then((_) => _sendAnimController.reverse());
    _messageController.clear();

    final currentUserId = ApiService.currentUser?['id'];
    final tempMsg = {
      'id': DateTime.now().millisecondsSinceEpoch,
      'sender_id': currentUserId,
      'receiver_id': widget.friendId,
      'message': text,
      'created_at': 'Vừa xong',
    };

    setState(() {
      _messages.add(tempMsg);
    });
    _scrollToBottom();

    try {
      final res = await ApiService.sendMessage(widget.friendId, text);
      if (res['success'] == true) {
        _silentRefreshMessages();
      }
    } finally {
      _isSendingMessage = false;
    }
  }

  Widget _buildShimmerLoading() {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: 5,
      itemBuilder: (context, index) {
        final isEven = index % 2 == 0;
        return Align(
          alignment: isEven ? Alignment.centerLeft : Alignment.centerRight,
          child: Container(
            margin: const EdgeInsets.only(bottom: 16),
            width: 180 + (index * 20 % 60).toDouble(),
            height: 48,
            decoration: BoxDecoration(
              color: isEven ? const Color(0xFFE2E8F0) : const Color(0xFFBAE6FD),
              borderRadius: BorderRadius.circular(18),
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = const Color(0xFF0EA5E9);
    final currentUserId = ApiService.currentUser?['id'];

    final bodyContent = Column(
      children: [
        // Messages history list
        Expanded(
          child: _isLoading
              ? _buildShimmerLoading()
              : _messages.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(20),
                            decoration: BoxDecoration(
                              color: primaryColor.withValues(alpha: 0.1),
                              shape: BoxShape.circle,
                            ),
                            child: Icon(Icons.chat_bubble_outline, size: 48, color: primaryColor),
                          ),
                          const SizedBox(height: 12),
                          Text(
                            'Hãy gửi lời chào đầu tiên tới ${widget.friendName}! 👋',
                            style: TextStyle(color: Colors.grey[600], fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ],
                      ),
                    )
                  : ListView.builder(
                      controller: _scrollController,
                      reverse: true,
                      padding: const EdgeInsets.all(16),
                      itemCount: _messages.length,
                      itemBuilder: (context, index) {
                        final msg = _messages[_messages.length - 1 - index];
                        final isSelf = msg['sender_id'] == currentUserId;

                        return Align(
                          alignment: isSelf ? Alignment.centerRight : Alignment.centerLeft,
                          child: Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            constraints: BoxConstraints(
                              maxWidth: MediaQuery.of(context).size.width * 0.72,
                            ),
                            child: Row(
                              mainAxisAlignment: isSelf ? MainAxisAlignment.end : MainAxisAlignment.start,
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                if (!isSelf)
                                  CircleAvatar(
                                    radius: 13,
                                    backgroundColor: primaryColor,
                                    child: Text(
                                      widget.friendName.isNotEmpty ? widget.friendName[0].toUpperCase() : '👤',
                                      style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                                    ),
                                  ),
                                if (!isSelf) const SizedBox(width: 8),

                                Flexible(
                                  child: Column(
                                    crossAxisAlignment: isSelf ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                        decoration: BoxDecoration(
                                          gradient: isSelf
                                              ? const LinearGradient(
                                                  colors: [Color(0xFF0EA5E9), Color(0xFF0284C7)],
                                                )
                                              : null,
                                          color: isSelf ? null : Colors.white,
                                          border: isSelf ? null : Border.all(color: const Color(0xFFE2E8F0)),
                                          boxShadow: [
                                            BoxShadow(
                                              color: Colors.black.withValues(alpha: 0.03),
                                              blurRadius: 6,
                                              offset: const Offset(0, 2),
                                            ),
                                          ],
                                          borderRadius: BorderRadius.only(
                                            topLeft: const Radius.circular(18),
                                            topRight: const Radius.circular(18),
                                            bottomLeft: isSelf ? const Radius.circular(18) : const Radius.circular(4),
                                            bottomRight: isSelf ? const Radius.circular(4) : const Radius.circular(18),
                                          ),
                                        ),
                                        child: Text(
                                          msg['message'] ?? '',
                                          style: TextStyle(
                                            color: isSelf ? Colors.white : const Color(0xFF0F172A),
                                            fontSize: 14,
                                            height: 1.35,
                                            fontWeight: FontWeight.w500,
                                          ),
                                        ),
                                      ),
                                      const SizedBox(height: 3),
                                      Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Text(
                                            msg['created_at_format'] ?? '',
                                            style: TextStyle(fontSize: 9, color: Colors.grey[400]),
                                          ),
                                          if (isSelf) ...[
                                            const SizedBox(width: 4),
                                            const Icon(Icons.done_all, size: 12, color: Color(0xFF0EA5E9)),
                                          ],
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
        ),

        // Message input bar
        SafeArea(
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 8, offset: const Offset(0, -2)),
              ],
            ),
            child: Row(
              children: [
                IconButton(
                  icon: const Icon(Icons.add_circle_outline, color: Color(0xFF0EA5E9), size: 22),
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Tính năng đính kèm vị trí / hình ảnh')),
                    );
                  },
                ),
                Expanded(
                  child: TextField(
                    controller: _messageController,
                    decoration: InputDecoration(
                      hintText: 'Nhập tin nhắn...',
                      hintStyle: TextStyle(color: Colors.grey[400], fontSize: 13),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(24),
                        borderSide: BorderSide.none,
                      ),
                      fillColor: const Color(0xFFF1F5F9),
                      filled: true,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                    ),
                    onSubmitted: (_) => _sendMessage(),
                  ),
                ),
                const SizedBox(width: 8),
                ScaleTransition(
                  scale: _sendScaleAnimation,
                  child: GestureDetector(
                    onTap: _sendMessage,
                    child: Container(
                      padding: const EdgeInsets.all(10),
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Color(0xFF0EA5E9), Color(0xFF0284C7)],
                        ),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.send_rounded, color: Colors.white, size: 18),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );

    if (widget.isEmbedded) {
      return bodyContent;
    }

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.friendName, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.grey[800],
        elevation: 0.5,
      ),
      backgroundColor: const Color(0xFFF8FAFC),
      body: bodyContent,
    );
  }
}
