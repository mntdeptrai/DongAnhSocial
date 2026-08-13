import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_webrtc/flutter_webrtc.dart';
import 'package:permission_handler/permission_handler.dart';
import '../services/api_service.dart';
import '../widgets/custom_loader.dart';
import 'news_bulletin_screen.dart';
import 'feed_screen.dart';

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

  bool _isShowingCallScreen = false;

  void _startTimer() {
    _friendsTimer?.cancel();
    _friendsTimer = Timer.periodic(const Duration(seconds: 3), (timer) {
      _silentRefreshFriends();
      _checkIncomingCall();
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
        return bUnread.compareTo(aUnread); // Ưu tiên tin chưa đọc lên đầu
      }

      final aTimestamp = (a['latest_message_timestamp'] ?? 0) as int;
      final bTimestamp = (b['latest_message_timestamp'] ?? 0) as int;
      if (aTimestamp != bTimestamp) {
        return bTimestamp.compareTo(aTimestamp); // Gần đây nhất lên đầu
      }

      final aTime = a['last_message_at'] ?? a['updated_at'] ?? a['time'];
      final bTime = b['last_message_at'] ?? b['updated_at'] ?? b['time'];
      if (aTime != null && bTime != null) {
        return bTime.toString().compareTo(aTime.toString());
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

  Future<void> _checkIncomingCall() async {
    if (_isShowingCallScreen || !ApiService.isAuthenticated) return;
    final res = await ApiService.checkPendingCall();
    if (res['has_call'] == true && mounted && !_isShowingCallScreen) {
      _isShowingCallScreen = true;
      final callerName = (res['caller_name'] ?? 'Người dùng').toString();
      final callerId = res['caller_id'] is int ? res['caller_id'] as int : int.tryParse(res['caller_id'].toString()) ?? 0;
      final callId = res['call_id'] is int ? res['call_id'] as int : int.tryParse(res['call_id'].toString()) ?? 0;
      final isVideo = res['call_type'] == 'video';

      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => ActiveCallScreen(
            friendName: callerName,
            friendId: callerId,
            callId: callId,
            isCaller: false,
            isVideo: isVideo,
            onCallEnded: (duration) {
              _isShowingCallScreen = false;
            },
          ),
        ),
      ).then((_) {
        _isShowingCallScreen = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    const primaryColor = Color(0xFF0EA5E9);
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
                      trailing: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          IconButton(
                            icon: const Icon(Icons.phone_rounded, color: Color(0xFF0EA5E9), size: 20),
                            tooltip: 'Gọi thoại',
                            onPressed: () {
                              final fId = friend['id'] is int ? friend['id'] as int : int.tryParse(friend['id'].toString()) ?? 0;
                              showGeneralDialog(
                                context: context,
                                barrierDismissible: false,
                                barrierColor: Colors.black.withValues(alpha: 0.9),
                                transitionDuration: const Duration(milliseconds: 300),
                                pageBuilder: (ctx, anim1, anim2) {
                                  return ActiveCallScreen(
                                    friendName: friend['name'] ?? 'Bạn bè',
                                    friendId: fId,
                                    isCaller: true,
                                    isVideo: false,
                                    onCallEnded: (_) {},
                                  );
                                },
                              );
                            },
                          ),
                          IconButton(
                            icon: const Icon(Icons.videocam_rounded, color: Color(0xFF0EA5E9), size: 22),
                            tooltip: 'Gọi video HD',
                            onPressed: () {
                              final fId = friend['id'] is int ? friend['id'] as int : int.tryParse(friend['id'].toString()) ?? 0;
                              showGeneralDialog(
                                context: context,
                                barrierDismissible: false,
                                barrierColor: Colors.black.withValues(alpha: 0.9),
                                transitionDuration: const Duration(milliseconds: 300),
                                pageBuilder: (ctx, anim1, anim2) {
                                  return ActiveCallScreen(
                                    friendName: friend['name'] ?? 'Bạn bè',
                                    friendId: fId,
                                    isCaller: true,
                                    isVideo: true,
                                    onCallEnded: (_) {},
                                  );
                                },
                              );
                            },
                          ),
                          if (hasUnread)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                              decoration: BoxDecoration(
                                color: const Color(0xFF0EA5E9),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Text(
                                unreadCount > 0 ? '$unreadCount' : 'Mới',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 10,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                            ),
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
    const primaryColor = Color(0xFF0EA5E9);
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
                            child: const Icon(Icons.chat_bubble_outline, size: 48, color: primaryColor),
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
                                        child: _buildMessageBubbleWidget(msg, isSelf),
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
        actions: [
          IconButton(
            icon: const Icon(Icons.phone_rounded, color: Color(0xFF0EA5E9)),
            tooltip: 'Gọi thoại',
            onPressed: () => _startCall(isVideo: false),
          ),
          IconButton(
            icon: const Icon(Icons.videocam_rounded, color: Color(0xFF0EA5E9)),
            tooltip: 'Gọi video HD',
            onPressed: () => _startCall(isVideo: true),
          ),
          const SizedBox(width: 4),
        ],
      ),
      backgroundColor: const Color(0xFFF8FAFC),
      body: bodyContent,
    );
  }

  void _startCall({required bool isVideo}) {
    showGeneralDialog(
      context: context,
      barrierDismissible: false,
      barrierColor: Colors.black.withValues(alpha: 0.9),
      transitionDuration: const Duration(milliseconds: 300),
      pageBuilder: (context, anim1, anim2) {
        return ActiveCallScreen(
          friendName: widget.friendName,
          friendId: widget.friendId,
          isCaller: true,
          isVideo: isVideo,
          onCallEnded: (durationSeconds) {
            if (durationSeconds > 0) {
              final min = (durationSeconds ~/ 60).toString().padLeft(2, '0');
              final sec = (durationSeconds % 60).toString().padLeft(2, '0');
              final text = isVideo ? '📹 Cuộc gọi video ($min:$sec)' : '📞 Cuộc gọi thoại ($min:$sec)';
              _sendSystemCallMessage(text);
            }
          },
        );
      },
    );
  }

  void _sendSystemCallMessage(String text) async {
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
      await ApiService.sendMessage(widget.friendId, text);
    } catch (_) {}
  }

  Widget _buildMessageBubbleWidget(dynamic msg, bool isSelf) {
    final String text = msg['message'] ?? '';
    final bool isSharedPost = text.contains('[Chia sẻ bài viết]') || text.contains('[Check-in') || (text.contains('http') && text.contains('donganhdiscovery'));

    if (!isSharedPost) {
      return Text(
        text,
        style: TextStyle(
          color: isSelf ? Colors.white : const Color(0xFF0F172A),
          fontSize: 14,
          height: 1.35,
          fontWeight: FontWeight.w500,
        ),
      );
    }

    String displayTitle = text
        .replaceAll('📰 [Chia sẻ bài viết]', '')
        .replaceAll('📸 [Check-in Đông Anh]', '')
        .replaceAll('📸 [Check-in]', '')
        .replaceAll(RegExp(r'🔗.*'), '')
        .trim();

    if (displayTitle.isEmpty) displayTitle = 'Bài viết từ Đông Anh Social';

    return InkWell(
      onTap: () => _navigateToSharedPost(context, text),
      borderRadius: BorderRadius.circular(12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                text.contains('Check-in') ? Icons.camera_alt_rounded : Icons.newspaper_rounded,
                size: 15,
                color: isSelf ? Colors.white : const Color(0xFF0EA5E9),
              ),
              const SizedBox(width: 5),
              Text(
                text.contains('Check-in') ? 'Check-in Đông Anh' : 'Bài viết được chia sẻ',
                style: TextStyle(
                  color: isSelf ? Colors.white : const Color(0xFF0EA5E9),
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            displayTitle,
            style: TextStyle(
              color: isSelf ? Colors.white : const Color(0xFF0F172A),
              fontSize: 13,
              fontWeight: FontWeight.w600,
            ),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: isSelf ? Colors.white.withValues(alpha: 0.2) : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(6),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  '👁️ Chạm để xem bài viết',
                  style: TextStyle(
                    color: isSelf ? Colors.white : const Color(0xFF0EA5E9),
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(width: 4),
                Icon(
                  Icons.arrow_forward_ios_rounded,
                  size: 10,
                  color: isSelf ? Colors.white : const Color(0xFF0EA5E9),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }



  void _navigateToSharedPost(BuildContext context, String messageText) {
    final bool isCheckIn = messageText.contains('Check-in');

    dynamic targetPostId;
    final RegExp regExp = RegExp(r'(?:post=|checkin\/)([0-9a-zA-Z_\-]+)');
    final match = regExp.firstMatch(messageText);
    if (match != null) {
      targetPostId = match.group(1);
    }

    String title = messageText
        .replaceAll('📰 [Chia sẻ bài viết]', '')
        .replaceAll('📸 [Check-in Đông Anh]', '')
        .replaceAll('📸 [Check-in]', '')
        .replaceAll(RegExp(r'🔗.*'), '')
        .trim();

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => isCheckIn
            ? FeedScreen(targetPostId: targetPostId, targetTitle: title)
            : NewsBulletinScreen(targetPostId: targetPostId, targetTitle: title),
      ),
    );
  }
}

class ActiveCallScreen extends StatefulWidget {
  final String friendName;
  final int friendId;
  final int? callId; // null nếu là caller (sẽ tạo mới từ API)
  final bool isCaller;
  final bool isVideo;
  final Function(int durationSeconds) onCallEnded;

  const ActiveCallScreen({
    super.key,
    required this.friendName,
    required this.friendId,
    this.callId,
    required this.isCaller,
    required this.isVideo,
    required this.onCallEnded,
  });

  @override
  State<ActiveCallScreen> createState() => _ActiveCallScreenState();
}

class _ActiveCallScreenState extends State<ActiveCallScreen> {
  bool _isMuted = false;
  bool _isVideoOn = true;
  bool _isSpeakerOn = true;
  bool _isConnected = false;
  int _secondsElapsed = 0;
  Timer? _callTimer;
  Timer? _statusPollTimer;
  int? _activeCallId;
  bool _callEnded = false;

  final _localRenderer = RTCVideoRenderer();
  final _remoteRenderer = RTCVideoRenderer();
  RTCPeerConnection? _peerConnection;
  MediaStream? _localStream;
  MediaStream? _remoteStream;

  @override
  void initState() {
    super.initState();
    _isVideoOn = widget.isVideo;
    _activeCallId = widget.callId;

    _initWebRTC();
  }

  Future<void> _initWebRTC() async {
    // Xin quyền Micro & Camera hiển thị Dialog của iOS/Android
    try {
      await [
        Permission.microphone,
        if (widget.isVideo) Permission.camera,
      ].request();
    } catch (e) {
      debugPrint('[Permissions] Error requesting permissions: $e');
    }

    await _localRenderer.initialize();
    await _remoteRenderer.initialize();

    final configuration = <String, dynamic>{
      'iceServers': [
        {'urls': 'stun:stun.l.google.com:19302'},
        {'urls': 'stun:stun1.l.google.com:19302'},
        {'urls': 'stun:stun2.l.google.com:19302'},
        {'urls': 'stun:stun3.l.google.com:19302'},
        {'urls': 'stun:stun4.l.google.com:19302'},
      ]
    };

    try {
      _peerConnection = await createPeerConnection(configuration);

      _peerConnection?.onIceCandidate = (candidate) {
        if (_activeCallId != null && candidate.candidate != null && candidate.candidate!.isNotEmpty) {
          final candMap = {
            'candidate': {
              'candidate': candidate.candidate,
              'sdpMid': candidate.sdpMid,
              'sdpMLineIndex': candidate.sdpMLineIndex,
            }
          };
          ApiService.sendSignal(_activeCallId!, widget.friendId, jsonEncode(candMap));
        }
      };

      _peerConnection?.onTrack = (event) {
        if (event.streams.isNotEmpty) {
          setState(() {
            _remoteStream = event.streams[0];
            _remoteRenderer.srcObject = _remoteStream;
          });
        }
      };

      _peerConnection?.onAddStream = (stream) {
        setState(() {
          _remoteStream = stream;
          _remoteRenderer.srcObject = stream;
        });
      };

      final mediaConstraints = <String, dynamic>{
        'audio': true,
        'video': widget.isVideo ? {'facingMode': 'user'} : false,
      };

      _localStream = await navigator.mediaDevices.getUserMedia(mediaConstraints);
      _localRenderer.srcObject = _localStream;

      _localStream?.getTracks().forEach((track) {
        _peerConnection?.addTrack(track, _localStream!);
      });
    } catch (e) {
      debugPrint('[WebRTC] Native setup warning: $e');
    }

    if (widget.isCaller) {
      _initiateCallOnServer();
    } else {
      _answerCallOnServer();
    }
  }

  /// Caller: Tạo SDP Offer → Gọi API khởi tạo cuộc gọi → Polling chờ Receiver
  Future<void> _initiateCallOnServer() async {
    String offerSdpStr = '';
    if (_peerConnection != null) {
      try {
        final offer = await _peerConnection!.createOffer({
          'offerToReceiveAudio': 1,
          'offerToReceiveVideo': widget.isVideo ? 1 : 0,
        });
        await _peerConnection!.setLocalDescription(offer);
        offerSdpStr = jsonEncode({'type': offer.type, 'sdp': offer.sdp});
      } catch (e) {
        debugPrint('[WebRTC] Create offer error: $e');
      }
    }

    final res = await ApiService.initiateCall(
      widget.friendId,
      widget.isVideo ? 'video' : 'audio',
      signalData: offerSdpStr.isNotEmpty ? offerSdpStr : null,
    );
    if (!mounted) return;

    if (res['status'] == 'success' && res['call_id'] != null) {
      final cid = res['call_id'] is int ? res['call_id'] as int : int.tryParse(res['call_id'].toString()) ?? 0;
      setState(() => _activeCallId = cid);
      _startStatusPolling();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res['message']?.toString() ?? 'Lỗi khởi tạo cuộc gọi')),
      );
      Navigator.of(context).pop();
    }
  }

  /// Receiver: Lấy SDP Offer từ Caller → Tạo SDP Answer → Gửi Answer lên server
  Future<void> _answerCallOnServer() async {
    if (_activeCallId != null && _peerConnection != null) {
      try {
        final status = await ApiService.getCallStatus(_activeCallId!);
        if (status['signal_data'] != null) {
          final offerMap = jsonDecode(status['signal_data']);
          if (offerMap['sdp'] != null && offerMap['sdp'].toString().isNotEmpty) {
            await _peerConnection!.setRemoteDescription(
              RTCSessionDescription(offerMap['sdp'], offerMap['type'] ?? 'offer'),
            );

            final answer = await _peerConnection!.createAnswer({
              'offerToReceiveAudio': 1,
              'offerToReceiveVideo': widget.isVideo ? 1 : 0,
            });
            await _peerConnection!.setLocalDescription(answer);

            final answerMap = {'type': answer.type, 'sdp': answer.sdp};
            await ApiService.answerCall(_activeCallId!, widget.friendId, signalData: jsonEncode(answerMap));
          }
        }
      } catch (e) {
        debugPrint('[WebRTC] Answer error: $e');
      }
    } else if (_activeCallId != null) {
      await ApiService.answerCall(_activeCallId!, widget.friendId);
    }

    if (!mounted) return;
    setState(() => _isConnected = true);
    _startDurationTimer();
    _startStatusPolling();
  }

  /// Polling trạng thái cuộc gọi + Trao đổi SDP Answer & ICE candidates mỗi 2 giây
  void _startStatusPolling() {
    _statusPollTimer?.cancel();
    _statusPollTimer = Timer.periodic(const Duration(seconds: 2), (timer) async {
      if (!mounted || _activeCallId == null || _callEnded) {
        timer.cancel();
        return;
      }
      final status = await ApiService.getCallStatus(_activeCallId!);
      if (!mounted || _callEnded) return;

      final callStatus = status['status']?.toString() ?? '';

      // Caller: Phát hiện Receiver đã chấp nhận -> nạp SDP Answer từ Receiver
      if (widget.isCaller && !_isConnected && callStatus == 'answered') {
        if (status['signal_data'] != null && _peerConnection != null) {
          try {
            final ansMap = jsonDecode(status['signal_data']);
            if (ansMap['sdp'] != null && ansMap['sdp'].toString().isNotEmpty) {
              await _peerConnection!.setRemoteDescription(
                RTCSessionDescription(ansMap['sdp'], ansMap['type'] ?? 'answer'),
              );
            }
          } catch (e) {
            debugPrint('[WebRTC] Set remote answer error: $e');
          }
        }
        setState(() => _isConnected = true);
        _startDurationTimer();
      }

      // Receiver: Nếu chưa có Remote Description -> Thử nạp SDP Offer từ Caller nếu có sẵn
      if (!widget.isCaller && _peerConnection != null) {
        final remoteDesc = await _peerConnection!.getRemoteDescription();
        if (remoteDesc == null && status['signal_data'] != null) {
          try {
            final offerMap = jsonDecode(status['signal_data']);
            if (offerMap['sdp'] != null && offerMap['sdp'].toString().isNotEmpty) {
              await _peerConnection!.setRemoteDescription(
                RTCSessionDescription(offerMap['sdp'], offerMap['type'] ?? 'offer'),
              );

              final answer = await _peerConnection!.createAnswer({
                'offerToReceiveAudio': 1,
                'offerToReceiveVideo': widget.isVideo ? 1 : 0,
              });
              await _peerConnection!.setLocalDescription(answer);

              final answerMap = {'type': answer.type, 'sdp': answer.sdp};
              await ApiService.answerCall(_activeCallId!, widget.friendId, signalData: jsonEncode(answerMap));
            }
          } catch (e) {
            debugPrint('[WebRTC] Receiver polling offer setup error: $e');
          }
        }
      }

      // Cả 2 bên: Nạp ICE candidates nhận được từ đối phương
      if (status['ice_candidates'] != null && status['ice_candidates'] is List) {
        for (var iceItem in status['ice_candidates']) {
          _applyIceCandidate(iceItem);
        }
      }

      // Phát hiện cúp máy từ đối phương
      if (callStatus == 'ended' || callStatus == 'rejected' || callStatus == 'missed') {
        timer.cancel();
        _callEnded = true;
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(callStatus == 'rejected' ? 'Cuộc gọi bị từ chối' : 'Cuộc gọi đã kết thúc')),
          );
          widget.onCallEnded(_secondsElapsed);
          Navigator.of(context).pop();
        }
      }
    });
  }

  void _applyIceCandidate(dynamic iceData) {
    try {
      Map<String, dynamic> map;
      if (iceData is String) {
        map = jsonDecode(iceData);
      } else {
        map = Map<String, dynamic>.from(iceData);
      }

      Map<String, dynamic> candObj = map;
      if (map.containsKey('candidate') && map['candidate'] is Map) {
        candObj = Map<String, dynamic>.from(map['candidate']);
      }

      final candidateStr = candObj['candidate']?.toString();
      if (candidateStr != null && candidateStr.isNotEmpty) {
        final sdpMid = candObj['sdpMid']?.toString();
        final sdpMLineIndex = candObj['sdpMLineIndex'] is int
            ? candObj['sdpMLineIndex'] as int
            : int.tryParse(candObj['sdpMLineIndex']?.toString() ?? '0') ?? 0;

        _peerConnection?.addCandidate(
          RTCIceCandidate(candidateStr, sdpMid, sdpMLineIndex),
        );
      }
    } catch (e) {
      debugPrint('[WebRTC] Candidate parse error: $e');
    }
  }

  void _startDurationTimer() {
    _callTimer?.cancel();
    _callTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (mounted) {
        setState(() => _secondsElapsed++);
      }
    });
  }

  @override
  void dispose() {
    _statusPollTimer?.cancel();
    _callTimer?.cancel();
    try {
      _localStream?.getTracks().forEach((track) => track.stop());
      _remoteStream?.getTracks().forEach((track) => track.stop());
      _localRenderer.dispose();
      _remoteRenderer.dispose();
      _peerConnection?.close();
    } catch (_) {}
    super.dispose();
  }

  String _formatDuration(int totalSec) {
    final min = (totalSec ~/ 60).toString().padLeft(2, '0');
    final sec = (totalSec % 60).toString().padLeft(2, '0');
    return '$min:$sec';
  }

  void _endCall() async {
    if (!_callEnded && _activeCallId != null) {
      _callEnded = true;
      await ApiService.hangupCall(_activeCallId!, widget.friendId, 'ended');
    }
    if (mounted) {
      widget.onCallEnded(_secondsElapsed);
      Navigator.of(context).pop();
    }
  }

  void _toggleMute() {
    setState(() => _isMuted = !_isMuted);
    _localStream?.getAudioTracks().forEach((track) {
      track.enabled = !_isMuted;
    });
  }

  void _toggleVideo() {
    setState(() => _isVideoOn = !_isVideoOn);
    _localStream?.getVideoTracks().forEach((track) {
      track.enabled = _isVideoOn;
    });
  }

  void _toggleSpeaker() {
    setState(() => _isSpeakerOn = !_isSpeakerOn);
    _localStream?.getAudioTracks().forEach((track) {
      track.enableSpeakerphone(_isSpeakerOn);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      body: SafeArea(
        child: Stack(
          children: [
            // Remote Video Background Stream
            if (widget.isVideo && _remoteStream != null)
              Positioned.fill(
                child: RTCVideoView(
                  _remoteRenderer,
                  objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
                ),
              )
            else if (widget.isVideo && _isVideoOn)
              Container(
                width: double.infinity,
                height: double.infinity,
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                  ),
                ),
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        width: 140,
                        height: 140,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: const Color(0xFF38BDF8), width: 3),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF38BDF8).withValues(alpha: 0.3),
                              blurRadius: 30,
                              spreadRadius: 5,
                            ),
                          ],
                        ),
                        child: CircleAvatar(
                          backgroundColor: const Color(0xFF0284C7),
                          child: Text(
                            widget.friendName.isNotEmpty ? widget.friendName[0].toUpperCase() : '👤',
                            style: const TextStyle(color: Colors.white, fontSize: 50, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),

            // Local Camera Stream Floating Preview
            if (widget.isVideo && _isVideoOn && _localStream != null)
              Positioned(
                top: 40,
                right: 16,
                width: 110,
                height: 160,
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(16),
                  child: Container(
                    decoration: BoxDecoration(
                      border: Border.all(color: Colors.white24, width: 2),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: RTCVideoView(
                      _localRenderer,
                      mirror: true,
                      objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
                    ),
                  ),
                ),
              ),

            // Top Header & Controls
            Column(
              children: [
                const SizedBox(height: 30),
                Text(
                  widget.friendName,
                  style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 8,
                      height: 8,
                      decoration: BoxDecoration(
                        color: _isConnected ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      _isConnected
                          ? '${widget.isVideo ? "Cuộc gọi video HD" : "Cuộc gọi thoại"} • ${_formatDuration(_secondsElapsed)}'
                          : 'Đang kết nối tín hiệu...',
                      style: const TextStyle(color: Colors.white70, fontSize: 14, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),

                const Spacer(),

                // Control Action Bar
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
                  margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E293B).withValues(alpha: 0.9),
                    borderRadius: BorderRadius.circular(30),
                    border: Border.all(color: Colors.white.withValues(alpha: 0.1)),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: [
                      // Mute Mic
                      IconButton(
                        style: IconButton.styleFrom(
                          backgroundColor: _isMuted ? Colors.white : Colors.white24,
                          padding: const EdgeInsets.all(12),
                        ),
                        icon: Icon(
                          _isMuted ? Icons.mic_off_rounded : Icons.mic_rounded,
                          color: _isMuted ? Colors.black : Colors.white,
                          size: 22,
                        ),
                        onPressed: _toggleMute,
                      ),

                      // Toggle Camera
                      if (widget.isVideo)
                        IconButton(
                          style: IconButton.styleFrom(
                            backgroundColor: !_isVideoOn ? Colors.white : Colors.white24,
                            padding: const EdgeInsets.all(12),
                          ),
                          icon: Icon(
                            _isVideoOn ? Icons.videocam_rounded : Icons.videocam_off_rounded,
                            color: !_isVideoOn ? Colors.black : Colors.white,
                            size: 22,
                          ),
                          onPressed: _toggleVideo,
                        ),

                      // Speaker
                      IconButton(
                        style: IconButton.styleFrom(
                          backgroundColor: _isSpeakerOn ? const Color(0xFF0284C7) : Colors.white24,
                          padding: const EdgeInsets.all(12),
                        ),
                        icon: Icon(
                          _isSpeakerOn ? Icons.volume_up_rounded : Icons.volume_down_rounded,
                          color: Colors.white,
                          size: 22,
                        ),
                        onPressed: _toggleSpeaker,
                      ),

                      // End Call
                      IconButton(
                        style: IconButton.styleFrom(
                          backgroundColor: const Color(0xFFEF4444),
                          padding: const EdgeInsets.all(12),
                        ),
                        icon: const Icon(Icons.call_end_rounded, color: Colors.white, size: 26),
                        onPressed: _endCall,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
