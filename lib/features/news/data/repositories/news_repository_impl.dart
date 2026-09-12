import '../../../../core/errors/failure.dart';
import '../../../../core/errors/result.dart';
import '../../domain/entities/news_article_entity.dart';
import '../../domain/repositories/news_repository.dart';
import '../datasources/news_remote_datasource.dart';

class NewsRepositoryImpl implements NewsRepository {
  final NewsRemoteDataSource remoteDataSource;

  NewsRepositoryImpl({required this.remoteDataSource});

  @override
  Future<Result<List<NewsArticleEntity>>> getNewsArticles({int page = 1}) async {
    try {
      final articles = await remoteDataSource.getNewsArticles(page: page);
      return Success(articles);
    } on Failure catch (failure) {
      return FailureResult(failure);
    } catch (e) {
      return FailureResult(ServerFailure(e.toString()));
    }
  }

  @override
  Future<Result<NewsArticleEntity>> getArticleDetail(int id) async {
    try {
      final article = await remoteDataSource.getArticleDetail(id);
      return Success(article);
    } on Failure catch (failure) {
      return FailureResult(failure);
    } catch (e) {
      return FailureResult(ServerFailure(e.toString()));
    }
  }
}
