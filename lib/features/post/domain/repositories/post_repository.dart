import '../../../../core/errors/result.dart';
import '../entities/post_entity.dart';

abstract class PostRepository {
  Future<Result<List<PostEntity>>> getFeed({int page = 1, String? filterType});
  Future<Result<PostEntity>> createPost({
    required String title,
    required String content,
    required String type,
    List<String>? imagePaths,
  });
  Future<Result<bool>> toggleLike(String postId);
}
