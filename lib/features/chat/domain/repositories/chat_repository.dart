import '../../../../core/errors/result.dart';
import '../entities/chat_message_entity.dart';

abstract class ChatRepository {
  Future<Result<List<ChatMessageEntity>>> getMessages(int otherUserId);
  Future<Result<ChatMessageEntity>> sendMessage(int receiverId, String content);
}
