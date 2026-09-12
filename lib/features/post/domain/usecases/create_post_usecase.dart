import '../../../../core/errors/result.dart';
import '../../../../core/usecases/usecase.dart';
import '../entities/post_entity.dart';
import '../repositories/post_repository.dart';

class CreatePostParams {
  final String title;
  final String content;
  final String type;
  final List<String>? imagePaths;

  const CreatePostParams({
    required this.title,
    required this.content,
    required this.type,
    this.imagePaths,
  });
}

class CreatePostUseCase implements UseCase<PostEntity, CreatePostParams> {
  final PostRepository repository;

  CreatePostUseCase(this.repository);

  @override
  Future<Result<PostEntity>> call(CreatePostParams params) {
    return repository.createPost(
      title: params.title,
      content: params.content,
      type: params.type,
      imagePaths: params.imagePaths,
    );
  }
}
