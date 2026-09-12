import '../../../../core/errors/result.dart';
import '../../../../core/usecases/usecase.dart';
import '../entities/post_entity.dart';
import '../repositories/post_repository.dart';

class GetFeedParams {
  final int page;
  final String? filterType;

  const GetFeedParams({this.page = 1, this.filterType});
}

class GetFeedUseCase implements UseCase<List<PostEntity>, GetFeedParams> {
  final PostRepository repository;

  GetFeedUseCase(this.repository);

  @override
  Future<Result<List<PostEntity>>> call(GetFeedParams params) {
    return repository.getFeed(page: params.page, filterType: params.filterType);
  }
}
