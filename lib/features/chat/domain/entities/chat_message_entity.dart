class ChatMessageEntity {
  final String id;
  final int senderId;
  final int receiverId;
  final String message;
  final String? createdAt;
  final bool isRead;

  const ChatMessageEntity({
    required this.id,
    required this.senderId,
    required this.receiverId,
    required this.message,
    this.createdAt,
    this.isRead = false,
  });
}
