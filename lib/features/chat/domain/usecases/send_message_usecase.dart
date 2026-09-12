import '../../../../core/errors/result.dart';
import '../../../../core/usecases/usecase.dart';
import '../entities/chat_message_entity.dart';
import '../repositories/chat_repository.dart';

class SendMessageParams {
  final int receiverId;
  final String content;

  const SendMessageParams({required this.receiverId, required this.content});
}

class SendMessageUseCase implements UseCase<ChatMessageEntity, SendMessageParams> {
  final ChatRepository repository;

  SendMessageUseCase(this.repository);

  @override
  Future<Result<ChatMessageEntity>> call(SendMessageParams params) {
    return repository.sendMessage(params.receiverId, params.content);
  }
}
