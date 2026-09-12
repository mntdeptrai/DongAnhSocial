import '../../../../core/errors/failure.dart';
import '../../../../core/errors/result.dart';
import '../../domain/entities/post_entity.dart';
import '../../domain/repositories/post_repository.dart';
import '../datasources/post_remote_datasource.dart';

class PostRepositoryImpl implements PostRepository {
  final PostRemoteDataSource remoteDataSource;

  PostRepositoryImpl({required this.remoteDataSource});

  @override
  Future<Result<List<PostEntity>>> getFeed({int page = 1, String? filterType}) async {
    try {
      final posts = await remoteDataSource.getFeed(page: page, filterType: filterType);
      return Success(posts);
    } on Failure catch (failure) {
      return FailureResult(failure);
    } catch (e) {
      return FailureResult(ServerFailure(e.toString()));
    }
  }

  @override
  Future<Result<PostEntity>> createPost({
    required String title,
    required String content,
    required String type,
    List<String>? imagePaths,
  }) async {
    try {
      final post = await remoteDataSource.createPost(
        title: title,
        content: content,
        type: type,
        imagePaths: imagePaths,
      );
      return Success(post);
    } on Failure catch (failure) {
      return FailureResult(failure);
    } catch (e) {
      return FailureResult(ServerFailure(e.toString()));
    }
  }

  @override
  Future<Result<bool>> toggleLike(String postId) async {
    try {
      final isLiked = await remoteDataSource.toggleLike(postId);
      return Success(isLiked);
    } on Failure catch (failure) {
      return FailureResult(failure);
    } catch (e) {
      return FailureResult(ServerFailure(e.toString()));
    }
  }
}
