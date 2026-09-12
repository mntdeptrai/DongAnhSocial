import '../../domain/entities/news_article_entity.dart';

class NewsArticleDto extends NewsArticleEntity {
  const NewsArticleDto({
    required super.id,
    required super.title,
    required super.content,
    super.summary,
    super.bannerUrl,
    super.publishedAt,
    super.authorName,
  });

  factory NewsArticleDto.fromJson(Map<String, dynamic> json) {
    return NewsArticleDto(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      title: json['title'] ?? json['name'] ?? '',
      content: json['content'] ?? json['description'] ?? '',
      summary: json['summary'] ?? json['excerpt'],
      bannerUrl: json['banner_url'] ?? json['image_url'] ?? json['image'],
      publishedAt: json['published_at'] ?? json['created_at'],
      authorName: json['author_name'] ?? json['author']?['name'],
    );
  }
}
