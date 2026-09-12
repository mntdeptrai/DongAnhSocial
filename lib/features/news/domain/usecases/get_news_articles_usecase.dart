import '../../../../core/errors/result.dart';
import '../../../../core/usecases/usecase.dart';
import '../entities/news_article_entity.dart';
import '../repositories/news_repository.dart';

class GetNewsArticlesParams {
  final int page;

  const GetNewsArticlesParams({this.page = 1});
}

class GetNewsArticlesUseCase implements UseCase<List<NewsArticleEntity>, GetNewsArticlesParams> {
  final NewsRepository repository;

  GetNewsArticlesUseCase(this.repository);

  @override
  Future<Result<List<NewsArticleEntity>>> call(GetNewsArticlesParams params) {
    return repository.getNewsArticles(page: params.page);
  }
}
