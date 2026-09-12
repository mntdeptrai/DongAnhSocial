class NewsArticleEntity {
  final int id;
  final String title;
  final String content;
  final String? summary;
  final String? bannerUrl;
  final String? publishedAt;
  final String? authorName;

  const NewsArticleEntity({
    required this.id,
    required this.title,
    required this.content,
    this.summary,
    this.bannerUrl,
    this.publishedAt,
    this.authorName,
  });
}
