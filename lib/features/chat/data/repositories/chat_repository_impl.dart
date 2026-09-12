import '../../../../core/errors/failure.dart';
import '../../../../core/errors/result.dart';
import '../../domain/entities/chat_message_entity.dart';
import '../../domain/repositories/chat_repository.dart';
import '../datasources/chat_remote_datasource.dart';

class ChatRepositoryImpl implements ChatRepository {
  final ChatRemoteDataSource remoteDataSource;

  ChatRepositoryImpl({required this.remoteDataSource});

  @override
  Future<Result<List<ChatMessageEntity>>> getMessages(int otherUserId) async {
    try {
      final messages = await remoteDataSource.getMessages(otherUserId);
      return Success(messages);
    } on Failure catch (failure) {
      return FailureResult(failure);
    } catch (e) {
      return FailureResult(ServerFailure(e.toString()));
    }
  }

  @override
  Future<Result<ChatMessageEntity>> sendMessage(int receiverId, String content) async {
    try {
      final message = await remoteDataSource.sendMessage(receiverId, content);
      return Success(message);
    } on Failure catch (failure) {
      return FailureResult(failure);
    } catch (e) {
      return FailureResult(ServerFailure(e.toString()));
    }
  }
}
