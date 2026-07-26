import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

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

  Future<void> _loadFriends() async {
    setState(() {
      _isLoading = true;
    });
    final friends = await ApiService.getFriends();
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
      appBar: AppBar(
        title: const Text(
          'Tin nhắn',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 20),
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
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: Colors.white),
            onPressed: _loadFriends,
          ),
        ],
      ),
      backgroundColor: const Color(0xFFF8FAFC),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF0EA5E9)))
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
              : ListView.builder(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  itemCount: _friends.length,
                  itemBuilder: (context, index) {
                    final friend = _friends[index];
                    final isOnline = friend['is_online'] == true;

                    return ListTile(
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                      leading: Stack(
                        children: [
                          CircleAvatar(
                            radius: 24,
                            backgroundColor: primaryColor.withValues(alpha: 0.1),
                            child: Text(
                              friend['avatar'] ?? (friend['name']?[0] ?? '👤'),
                              style: TextStyle(color: primaryColor, fontWeight: FontWeight.bold, fontSize: 18),
                            ),
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
                      title: Text(
                        friend['name'] ?? '',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                      subtitle: Text(
                        isOnline ? 'Đang hoạt động' : 'Ngoại tuyến',
                        style: TextStyle(
                          color: isOnline ? const Color(0xFF10B981) : Colors.grey[400],
                          fontSize: 12,
                        ),
                      ),
                      trailing: const Icon(Icons.arrow_forward_ios, size: 14, color: Colors.grey),
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
    _pollingTimer = Timer.periodic(const Duration(seconds: 4), (timer) {
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

  void _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty) return;

    _sendAnimController.forward().then((_) => _sendAnimController.reverse());
    _messageController.clear();

    final res = await ApiService.sendMessage(widget.friendId, text);
    if (res['success'] == true) {
      _silentRefreshMessages();
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
