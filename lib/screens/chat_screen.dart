import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class ChatScreen extends StatefulWidget {
  const ChatScreen({super.key});

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  List<dynamic> _friends = [];
  bool _isLoading = false;

  Timer? _friendsTimer;

  @override
  void initState() {
    super.initState();
    _loadFriends();
    // Tự động cập nhật trạng thái bạn bè mỗi 10 giây
    _friendsTimer = Timer.periodic(const Duration(seconds: 10), (timer) {
      _silentRefreshFriends();
    });
  }

  @override
  void dispose() {
    _friendsTimer?.cancel();
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
          title: const Text('Trò chuyện', style: TextStyle(fontWeight: FontWeight.bold)),
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
          'Đoạn chat',
          style: TextStyle(fontWeight: FontWeight.w900, fontSize: 20, letterSpacing: -0.5),
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.grey[800],
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
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
                            backgroundColor: primaryColor.withOpacity(0.1),
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

  const ChatDetailScreen({
    super.key,
    required this.friendId,
    required this.friendName,
  });

  @override
  State<ChatDetailScreen> createState() => _ChatDetailScreenState();
}

class _ChatDetailScreenState extends State<ChatDetailScreen> {
  final _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  List<dynamic> _messages = [];
  bool _isLoading = false;
  Timer? _pollingTimer;

  @override
  void initState() {
    super.initState();
    _loadMessages();
    // Refresh messages every 4 seconds for real-time emulation
    _pollingTimer = Timer.periodic(const Duration(seconds: 4), (timer) {
      _silentRefreshMessages();
    });
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
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

    _messageController.clear();
    final res = await ApiService.sendMessage(widget.friendId, text);
    if (res['success'] == true) {
      _silentRefreshMessages();
    }
  }

  @override
  Widget build(BuildContext context) {
    final primaryColor = const Color(0xFF0EA5E9);
    final currentUserId = ApiService.currentUser?['id'];

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.friendName, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.grey[800],
        elevation: 0.5,
      ),
      backgroundColor: const Color(0xFFF8FAFC),
      body: Column(
        children: [
          // Messages history list
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: Color(0xFF0EA5E9)))
                : _messages.isEmpty
                    ? const Center(
                        child: Text(
                          'Hãy gửi lời chào đầu tiên! 👋',
                          style: TextStyle(color: Colors.grey),
                        ),
                      )
                    : ListView.builder(
                        controller: _scrollController,
                        padding: const EdgeInsets.all(16),
                        itemCount: _messages.length,
                        itemBuilder: (context, index) {
                          final msg = _messages[index];
                          final isSelf = msg['sender_id'] == currentUserId;

                          return Align(
                            alignment: isSelf ? Alignment.centerRight : Alignment.centerLeft,
                            child: Column(
                              crossAxisAlignment: isSelf ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                              children: [
                                Container(
                                  margin: const EdgeInsets.only(bottom: 4),
                                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                  decoration: BoxDecoration(
                                    gradient: isSelf
                                        ? LinearGradient(
                                            colors: [primaryColor, primaryColor.withBlue(220)],
                                          )
                                        : null,
                                    color: isSelf ? null : const Color(0xFFE2E8F0),
                                    borderRadius: BorderRadius.only(
                                      topLeft: const Radius.circular(16),
                                      topRight: const Radius.circular(16),
                                      bottomLeft: isSelf ? const Radius.circular(16) : const Radius.circular(2),
                                      bottomRight: isSelf ? const Radius.circular(2) : const Radius.circular(16),
                                    ),
                                  ),
                                  child: Text(
                                    msg['message'] ?? '',
                                    style: TextStyle(
                                      color: isSelf ? Colors.white : Colors.grey[800],
                                      fontSize: 14,
                                    ),
                                  ),
                                ),
                                Text(
                                  msg['created_at_format'] ?? '',
                                  style: TextStyle(fontSize: 9, color: Colors.grey[400]),
                                ),
                                const SizedBox(height: 12),
                              ],
                            ),
                          );
                        },
                      ),
          ),

          // Message input bar
          SafeArea(
            child: Container(
              color: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _messageController,
                      decoration: InputDecoration(
                        hintText: 'Nhập tin nhắn...',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(24),
                          borderSide: BorderSide.none,
                        ),
                        fillColor: const Color(0xFFF1F5F9),
                        filled: true,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                      ),
                      onSubmitted: (_) => _sendMessage(),
                    ),
                  ),
                  const SizedBox(width: 8),
                  GestureDetector(
                    onTap: _sendMessage,
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: primaryColor,
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.send, color: Colors.white, size: 20),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
