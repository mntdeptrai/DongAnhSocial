import '../../../../core/errors/result.dart';
import '../entities/news_article_entity.dart';

abstract class NewsRepository {
  Future<Result<List<NewsArticleEntity>>> getNewsArticles({int page = 1});
  Future<Result<NewsArticleEntity>> getArticleDetail(int id);
}
