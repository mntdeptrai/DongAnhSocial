import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../screens/chat_screen.dart';
import '../main.dart';

/// Authentic Messenger & Zalo style Floating Chat Heads Manager
/// - When collapsed: Draggable floating chat head avatar at last dropped position
/// - Smooth liquid physics animations with auto-fade (X) dismiss zone
/// - When expanded: Chat heads animate to top header bar with active tab indicators
/// - Multi-chat head support: Drag individual chat head to (X) zone or tap (x) to remove specific chat
class DraggableFloatingChatBubble extends StatefulWidget {
  final VoidCallback? onOpenChatTab;

  const DraggableFloatingChatBubble({super.key, this.onOpenChatTab});

  @override
  State<DraggableFloatingChatBubble> createState() => _DraggableFloatingChatBubbleState();
}

class _DraggableFloatingChatBubbleState extends State<DraggableFloatingChatBubble> with SingleTickerProviderStateMixin, WidgetsBindingObserver {
  // Last dragged position (preserved across expands/collapses)
  Offset _position = const Offset(300, 480);
  bool _isExpanded = false;
  bool _isDragging = false;
  bool _isDismissed = false;
  bool _isActivated = false;

  // Unread badge count
  int _unreadCount = 0;

  // Floating Message Preview Tooltip (Messenger Style 2.5s)
  String? _previewText;
  bool _showPreview = false;
  Timer? _previewTimer;

  late AnimationController _pulseController;
  late Animation<double> _pulseAnimation;

  List<dynamic> _friendsList = [];
  List<dynamic> _openChatHeads = [];
  dynamic _activeFriend;
  Timer? _pollingTimer;

  int _lastTotalMessagesCount = -1;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);

    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1800),
    )..repeat(reverse: true);

    _pulseAnimation = Tween<double>(begin: 1.0, end: 1.08).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );

    _initChatHeadData();
    _startPolling();
  }

  void _startPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = Timer.periodic(const Duration(seconds: 5), (_) {
      _pollRecentChatData();
    });
  }

  void _stopPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = null;
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused ||
        state == AppLifecycleState.inactive ||
        state == AppLifecycleState.hidden) {
      _stopPolling();
      _pulseController.stop();
    } else if (state == AppLifecycleState.resumed) {
      if (!_pulseController.isAnimating) {
        _pulseController.repeat(reverse: true);
      }
      _pollRecentChatData();
      _startPolling();
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _stopPolling();
    _previewTimer?.cancel();
    _pulseController.dispose();
    super.dispose();
  }

  Future<void> _initChatHeadData() async {
    if (!ApiService.isAuthenticated) return;
    try {
      final friends = await ApiService.getFriends();
      if (mounted && friends.isNotEmpty) {
        setState(() {
          _friendsList = friends;
          if (_openChatHeads.isEmpty) {
            _openChatHeads = [friends.first];
            _activeFriend = friends.first;
          }
        });
      }
    } catch (_) {}
  }

  Future<void> _pollRecentChatData() async {
    if (!ApiService.isAuthenticated) return;
    try {
      final friends = await ApiService.getFriends();
      if (!mounted || friends.isEmpty) return;

      setState(() {
        _friendsList = friends;
        if (_activeFriend == null) {
          _activeFriend = friends.first;
          if (_openChatHeads.isEmpty) {
            _openChatHeads = [friends.first];
          }
        } else {
          // Sync active friend details (is_online, avatar, etc.)
          final matched = friends.firstWhere(
            (f) => f['id'] == _activeFriend['id'],
            orElse: () => friends.first,
          );
          _activeFriend = matched;
        }
      });

      // Check if there are new unread messages
      if (_activeFriend != null) {
        final res = await ApiService.getMessages(_activeFriend['id']);
        final List<dynamic> messages = res['messages'] ?? [];
        if (_lastTotalMessagesCount != -1 && messages.length > _lastTotalMessagesCount) {
          // New message received! Activate chat bubble & message preview tooltip
          final lastMsg = messages.isNotEmpty ? messages.last : null;
          final currentUserId = ApiService.currentUser?['id'];
          if (lastMsg != null && lastMsg['sender_id'] != currentUserId) {
            setState(() {
              _isActivated = true;
              _isDismissed = false;
              _unreadCount = messages.length - _lastTotalMessagesCount;
              _previewText = lastMsg['message'] ?? 'Đã gửi một tin nhắn';
              _showPreview = true;

              // Ensure friend is in open chat heads
              if (!_openChatHeads.any((f) => f['id'] == _activeFriend['id'])) {
                _openChatHeads.add(_activeFriend);
              }
            });

            // Trigger System Status Bar Notification Banner
            NotificationHelper.showNotification(
              title: _activeFriend?['name'] ?? 'Đông Anh Social',
              body: lastMsg['message'] ?? 'Bạn nhận được tin nhắn mới',
            );

            // Auto-dismiss the text preview banner after 2.5 seconds
            _previewTimer?.cancel();
            _previewTimer = Timer(const Duration(milliseconds: 2500), () {
              if (mounted) {
                setState(() {
                  _showPreview = false;
                });
              }
            });
          }
        }
        _lastTotalMessagesCount = messages.length;
      }
    } catch (_) {}
  }

  bool _isNearDismissZone(Size screenSize) {
    final targetX = screenSize.width / 2 - 26;
    final targetY = screenSize.height - 100;
    final dx = _position.dx - targetX;
    final dy = _position.dy - targetY;
    return (dx * dx + dy * dy) < 10000;
  }

  void _snapToEdge(Size screenSize) {
    if (_isNearDismissZone(screenSize)) {
      setState(() {
        _isDismissed = true;
        _isActivated = false;
        _isDragging = false;
        _isExpanded = false;
        _showPreview = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Đã xóa bong bóng chat'),
          duration: const Duration(seconds: 2),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
      return;
    }

    double x = _position.dx;
    double y = _position.dy;

    y = y.clamp(80.0, screenSize.height - 160.0);

    if (x < screenSize.width / 2) {
      x = 16.0;
    } else {
      x = screenSize.width - 76.0;
    }

    setState(() {
      _position = Offset(x, y);
      _isDragging = false;
    });
  }

  void _toggleChatOverlay() {
    if (!ApiService.isAuthenticated) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Vui lòng đăng nhập để mở trò chuyện!'),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
      return;
    }

    setState(() {
      _isActivated = true;
      _isDismissed = false;
      _isDragging = false;
      _isExpanded = !_isExpanded;
      if (_isExpanded) {
        _unreadCount = 0;
        _showPreview = false;
      }
    });
  }

  void _selectFriend(dynamic friend) {
    setState(() {
      _activeFriend = friend;
      _unreadCount = 0;
      _showPreview = false;
      if (!_openChatHeads.any((f) => f['id'] == friend['id'])) {
        _openChatHeads.add(friend);
      }
    });
  }

  void _closeChatHead(dynamic friend) {
    setState(() {
      _openChatHeads.removeWhere((f) => f['id'] == friend['id']);
      if (_openChatHeads.isNotEmpty) {
        _activeFriend = _openChatHeads.last;
      } else {
        _isExpanded = false;
        _isActivated = false;
      }
    });
  }

  void _showAddFriendDialog() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          decoration: BoxDecoration(
            color: const Color(0xFFF0FDFA),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
            border: Border.all(color: const Color(0xFF0EA5E9).withOpacity(0.15)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.15),
                blurRadius: 20,
                offset: const Offset(0, -5),
              ),
            ],
          ),
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Chọn bạn bè để trò chuyện',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A)),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Expanded(
                child: ListView.builder(
                  shrinkWrap: true,
                  itemCount: _friendsList.length,
                  itemBuilder: (context, index) {
                    final friend = _friendsList[index];
                    final isOnline = friend['is_online'] == true;
                    return ListTile(
                      leading: CircleAvatar(
                        backgroundColor: const Color(0xFF0EA5E9),
                        child: Text(friend['avatar'] ?? (friend['name']?[0] ?? '👤'), style: const TextStyle(color: Colors.white)),
                      ),
                      title: Text(friend['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600)),
                      subtitle: Text(isOnline ? 'Đang hoạt động' : 'Ngoại tuyến', style: TextStyle(color: isOnline ? Colors.green : Colors.grey, fontSize: 12)),
                      onTap: () {
                        Navigator.pop(context);
                        _selectFriend(friend);
                      },
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
    if (!ApiService.isAuthenticated) return const SizedBox.shrink();

    // Only render bubble if activated and not explicitly dismissed by dragging down to (X)
    if (!_isActivated || _isDismissed) {
      return const SizedBox.shrink();
    }

    final screenSize = MediaQuery.of(context).size;
    final friendName = _activeFriend?['name'] ?? 'Đoạn chat';
    final initialChar = friendName.isNotEmpty ? friendName[0].toUpperCase() : '💬';
    final isNearDismiss = _isDragging && _isNearDismissZone(screenSize);

    return Stack(
      children: [
        // ---------------------------------------------------------------------
        // Expanded Messenger Chat Overlay Window (Clean Header Chat Heads)
        // ---------------------------------------------------------------------
        if (_isExpanded)
          Positioned(
            top: 48,
            right: 12,
            left: 12,
            bottom: 24,
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 250),
              curve: Curves.easeOutCubic,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF0F172A).withValues(alpha: 0.25),
                    blurRadius: 36,
                    spreadRadius: 2,
                    offset: const Offset(0, 12),
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(24),
                child: Column(
                  children: [
                    // Messenger Top Chat Heads Bar Header
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                          colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                        ),
                      ),
                      child: Row(
                        children: [
                          // Horizontal Open Chat Heads Avatars Row
                          Expanded(
                            child: SingleChildScrollView(
                              scrollDirection: Axis.horizontal,
                              child: Row(
                                children: [
                                  ..._openChatHeads.map((friend) {
                                    final isSelected = _activeFriend?['id'] == friend['id'];
                                    final isOnline = friend['is_online'] == true;
                                    final avatar = friend['avatar'] ?? (friend['name']?[0] ?? '👤');

                                    return GestureDetector(
                                      onTap: () => _selectFriend(friend),
                                      child: Container(
                                        margin: const EdgeInsets.only(right: 10),
                                        child: Stack(
                                          clipBehavior: Clip.none,
                                          children: [
                                            AnimatedContainer(
                                              duration: const Duration(milliseconds: 150),
                                              padding: const EdgeInsets.all(2),
                                              decoration: BoxDecoration(
                                                shape: BoxShape.circle,
                                                border: Border.all(
                                                  color: isSelected ? const Color(0xFF38BDF8) : Colors.transparent,
                                                  width: 2.5,
                                                ),
                                                boxShadow: isSelected
                                                    ? [
                                                        BoxShadow(
                                                          color: const Color(0xFF38BDF8).withValues(alpha: 0.5),
                                                          blurRadius: 8,
                                                        ),
                                                      ]
                                                    : [],
                                              ),
                                              child: CircleAvatar(
                                                radius: 18,
                                                backgroundColor: isSelected ? const Color(0xFF0EA5E9) : Colors.grey[700],
                                                child: Text(
                                                  avatar,
                                                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                                                ),
                                              ),
                                            ),

                                            // Online dot indicator
                                            Positioned(
                                              bottom: 1,
                                              right: 1,
                                              child: Container(
                                                width: 10,
                                                height: 10,
                                                decoration: BoxDecoration(
                                                  color: isOnline ? const Color(0xFF10B981) : Colors.grey[400],
                                                  shape: BoxShape.circle,
                                                  border: Border.all(color: const Color(0xFF0F172A), width: 1.5),
                                                ),
                                              ),
                                            ),

                                            // Close (x) button for specific chat head
                                            Positioned(
                                              top: -2,
                                              right: -2,
                                              child: GestureDetector(
                                                onTap: () => _closeChatHead(friend),
                                                child: Container(
                                                  padding: const EdgeInsets.all(2),
                                                  decoration: const BoxDecoration(
                                                    color: Color(0xFF334155),
                                                    shape: BoxShape.circle,
                                                  ),
                                                  child: const Icon(Icons.close, size: 10, color: Colors.white70),
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    );
                                  }),

                                  // (+) Button to open friend picker
                                  GestureDetector(
                                    onTap: _showAddFriendDialog,
                                    child: Container(
                                      width: 38,
                                      height: 38,
                                      margin: const EdgeInsets.only(left: 2),
                                      decoration: BoxDecoration(
                                        color: Colors.white.withValues(alpha: 0.12),
                                        shape: BoxShape.circle,
                                        border: Border.all(color: Colors.white24),
                                      ),
                                      child: const Icon(Icons.add, color: Colors.white, size: 20),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),

                          const SizedBox(width: 8),

                          // Header Action Buttons (Full Screen & Collapse Window)
                          InkWell(
                            onTap: () {
                              setState(() => _isExpanded = false);
                              if (widget.onOpenChatTab != null) {
                                widget.onOpenChatTab!();
                              }
                            },
                            borderRadius: BorderRadius.circular(20),
                            child: Container(
                              padding: const EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.1),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.open_in_full_rounded, color: Colors.white, size: 16),
                            ),
                          ),
                          const SizedBox(width: 6),
                          InkWell(
                            onTap: () => setState(() => _isExpanded = false),
                            borderRadius: BorderRadius.circular(20),
                            child: Container(
                              padding: const EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.1),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.close_rounded, color: Colors.white, size: 18),
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Active Friend Details Banner
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      decoration: const BoxDecoration(
                        color: Color(0xFFF8FAFC),
                        border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0))),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  friendName,
                                  style: const TextStyle(
                                    color: Color(0xFF0F172A),
                                    fontWeight: FontWeight.bold,
                                    fontSize: 14,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                Builder(
                                  builder: (context) {
                                    final isOnline = _activeFriend?['is_online'] == true;
                                    return Text(
                                      isOnline ? '🟢 Đang hoạt động' : '⚪ Ngoại tuyến',
                                      style: TextStyle(
                                        color: isOnline ? const Color(0xFF10B981) : Colors.grey[500],
                                        fontSize: 11,
                                        fontWeight: FontWeight.w500,
                                      ),
                                    );
                                  },
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Embedded Clean Chat Messages Screen for Active Friend (No Floating Overlap)
                    Expanded(
                      key: ValueKey<int>(_activeFriend?['id'] ?? 0),
                      child: _activeFriend != null
                          ? ChatDetailScreen(
                              friendId: _activeFriend['id'],
                              friendName: _activeFriend['name'] ?? 'Bạn bè',
                              isEmbedded: true,
                            )
                          : const Center(
                              child: Text(
                                'Chưa chọn cuộc trò chuyện',
                                style: TextStyle(color: Colors.grey),
                              ),
                            ),
                    ),
                  ],
                ),
              ),
            ),
          ),

        // ---------------------------------------------------------------------
        // Drag-to-Dismiss Bottom Center (X) Target Zone (Smooth Fade In/Out)
        // ---------------------------------------------------------------------
        IgnorePointer(
          ignoring: !_isDragging,
          child: AnimatedOpacity(
            opacity: _isDragging ? 1.0 : 0.0,
            duration: const Duration(milliseconds: 200),
            curve: Curves.easeInOut,
            child: Align(
              alignment: Alignment.bottomCenter,
              child: Padding(
                padding: const EdgeInsets.only(bottom: 40),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 150),
                  width: isNearDismiss ? 68 : 54,
                  height: isNearDismiss ? 68 : 54,
                  decoration: BoxDecoration(
                    color: isNearDismiss ? const Color(0xFFEF4444) : const Color(0xFF0F172A).withValues(alpha: 0.85),
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: isNearDismiss ? Colors.white : Colors.white54,
                      width: isNearDismiss ? 2.5 : 1.5,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: isNearDismiss
                            ? const Color(0xFFEF4444).withValues(alpha: 0.6)
                            : Colors.black.withValues(alpha: 0.25),
                        blurRadius: isNearDismiss ? 24 : 12,
                        spreadRadius: isNearDismiss ? 6 : 2,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Center(
                    child: Icon(
                      Icons.close_rounded,
                      color: Colors.white,
                      size: isNearDismiss ? 32 : 24,
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),

        // ---------------------------------------------------------------------
        // Floating Message Preview Tooltip (Messenger Style - 2.5s popup)
        // ---------------------------------------------------------------------
        if (_showPreview && _previewText != null && !_isExpanded && !_isDragging)
          Positioned(
            top: _position.dy + 4,
            left: _position.dx > screenSize.width / 2 ? null : _position.dx + 62,
            right: _position.dx > screenSize.width / 2 ? screenSize.width - _position.dx + 8 : null,
            child: GestureDetector(
              onTap: _toggleChatOverlay,
              child: AnimatedOpacity(
                opacity: _showPreview ? 1.0 : 0.0,
                duration: const Duration(milliseconds: 200),
                child: Material(
                  elevation: 10,
                  borderRadius: BorderRadius.circular(16),
                  color: Colors.transparent,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    constraints: BoxConstraints(maxWidth: screenSize.width * 0.55),
                    decoration: BoxDecoration(
                      color: const Color(0xFF0F172A).withValues(alpha: 0.92),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.white24, width: 1),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.25),
                          blurRadius: 16,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          _activeFriend?['name'] ?? 'Tin nhắn mới',
                          style: const TextStyle(
                            color: Color(0xFF38BDF8),
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          _previewText!,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 13,
                            height: 1.25,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),

        // ---------------------------------------------------------------------
        // Smooth Draggable Floating Chat Head (Rendered ONLY when Collapsed !_isExpanded)
        // ---------------------------------------------------------------------
        if (!_isExpanded)
          AnimatedPositioned(
            duration: _isDragging ? Duration.zero : const Duration(milliseconds: 250),
            curve: Curves.easeOutCubic,
            left: _position.dx,
            top: _position.dy,
            child: GestureDetector(
              onPanStart: (_) {
                setState(() {
                  _isDragging = true;
                  _showPreview = false;
                });
              },
              onPanUpdate: (details) {
                setState(() {
                  _position += details.delta;
                });
              },
              onPanEnd: (_) => _snapToEdge(screenSize),
              onPanCancel: () => _snapToEdge(screenSize),
              onTap: _toggleChatOverlay,
              child: ScaleTransition(
                scale: _pulseAnimation,
                child: Stack(
                  clipBehavior: Clip.none,
                  alignment: Alignment.topRight,
                  children: [
                    // Outer Glow Container
                    AnimatedContainer(
                      duration: const Duration(milliseconds: 150),
                      padding: const EdgeInsets.all(3),
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        gradient: isNearDismiss
                            ? const LinearGradient(colors: [Color(0xFFEF4444), Color(0xFFDC2626)])
                            : const LinearGradient(colors: [Color(0xFF0EA5E9), Color(0xFF6366F1)]),
                        boxShadow: [
                          BoxShadow(
                            color: (isNearDismiss ? const Color(0xFFEF4444) : const Color(0xFF0EA5E9))
                                .withValues(alpha: _isDragging ? 0.6 : 0.35),
                            blurRadius: _isDragging ? 18 : 12,
                            spreadRadius: _isDragging ? 4 : 1.5,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Container(
                        width: 52,
                        height: 52,
                        decoration: const BoxDecoration(
                          shape: BoxShape.circle,
                          color: Colors.white,
                        ),
                        child: ClipOval(
                          child: Container(
                            decoration: BoxDecoration(
                              gradient: isNearDismiss
                                  ? const LinearGradient(colors: [Color(0xFFF87171), Color(0xFFEF4444)])
                                  : const LinearGradient(
                                      begin: Alignment.topLeft,
                                      end: Alignment.bottomRight,
                                      colors: [Color(0xFF38BDF8), Color(0xFF0284C7)],
                                    ),
                            ),
                            child: Center(
                              child: Text(
                                _activeFriend?['avatar'] ?? initialChar,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w800,
                                  fontSize: 22,
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),

                    // Active Status Indicator Dot
                    Positioned(
                      bottom: 2,
                      right: 2,
                      child: Container(
                        width: 14,
                        height: 14,
                        decoration: BoxDecoration(
                          color: (_activeFriend?['is_online'] == true) ? const Color(0xFF10B981) : Colors.grey[400],
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 2.5),
                          boxShadow: const [
                            BoxShadow(color: Colors.black26, blurRadius: 3),
                          ],
                        ),
                      ),
                    ),

                    // Unread Notification Badge Count
                    if (_unreadCount > 0 && !_isDragging)
                      Positioned(
                        top: -2,
                        right: -2,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEF4444),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: Colors.white, width: 2),
                            boxShadow: const [
                              BoxShadow(color: Colors.black26, blurRadius: 4, offset: Offset(0, 2)),
                            ],
                          ),
                          constraints: const BoxConstraints(minWidth: 20, minHeight: 20),
                          child: Text(
                            '$_unreadCount',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            ),
          ),
      ],
    );
  }
}
