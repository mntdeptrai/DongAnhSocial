import '../../domain/entities/chat_message_entity.dart';

class ChatMessageDto extends ChatMessageEntity {
  const ChatMessageDto({
    required super.id,
    required super.senderId,
    required super.receiverId,
    required super.message,
    super.createdAt,
    super.isRead,
  });

  factory ChatMessageDto.fromJson(Map<String, dynamic> json) {
    return ChatMessageDto(
      id: (json['id'] ?? '').toString(),
      senderId: json['sender_id'] is int ? json['sender_id'] : int.tryParse(json['sender_id']?.toString() ?? '0') ?? 0,
      receiverId: json['receiver_id'] is int ? json['receiver_id'] : int.tryParse(json['receiver_id']?.toString() ?? '0') ?? 0,
      message: json['message'] ?? json['content'] ?? '',
      createdAt: json['created_at']?.toString(),
      isRead: json['is_read'] == true || json['is_read'] == 1,
    );
  }
}
