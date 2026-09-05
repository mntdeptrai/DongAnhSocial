import 'user_model.dart';

class PostModel {
  final String id;
  final dynamic numericId;
  final String? hashId;
  final String title;
  final String content;
  final String type;
  final bool isFoodTour;
  final bool isCheckin;
  final bool isSchool;
  final UserModel author;
  final int initialLikesCount;
  final int commentsCount;
  final bool initialIsLiked;
  final List<String> images;
  final String? createdAt;
  final Map<String, dynamic> rawJson;

  const PostModel({
    required this.id,
    this.numericId,
    this.hashId,
    required this.title,
    required this.content,
    required this.type,
    this.isFoodTour = false,
    this.isCheckin = false,
    this.isSchool = false,
    required this.author,
    this.initialLikesCount = 0,
    this.commentsCount = 0,
    this.initialIsLiked = false,
    this.images = const [],
    this.createdAt,
    required this.rawJson,
  });

  factory PostModel.fromJson(Map<String, dynamic> json) {
    final rawType = (json['type'] ?? json['post_type'] ?? '').toString().toLowerCase();
    final isFood = json['is_food_tour'] == true || rawType == 'food_tour';
    final isCheck = json['is_checkin'] == true || rawType == 'checkin';
    final isSch = rawType == 'school' || json['author_role'] == 'principal' || json['is_school'] == true;

    final extractedImages = parseImageUrls(json);

    return PostModel(
      id: (json['id'] ?? '').toString(),
      numericId: json['numeric_id'] ?? json['id'],
      hashId: json['hashid']?.toString(),
      title: (json['title'] ?? json['name'] ?? '').toString(),
      content: (json['content'] ?? json['title'] ?? json['name'] ?? '').toString(),
      type: rawType.isEmpty ? 'post' : rawType,
      isFoodTour: isFood,
      isCheckin: isCheck,
      isSchool: isSch,
      author: UserModel.fromJson(json),
      initialLikesCount: json['likes_count'] is int
          ? json['likes_count']
          : int.tryParse(json['likes_count']?.toString() ?? '0') ?? 0,
      commentsCount: json['comments_count'] is int
          ? json['comments_count']
          : int.tryParse(json['comments_count']?.toString() ?? '0') ?? 0,
      initialIsLiked: json['is_liked'] == true,
      images: extractedImages,
      createdAt: json['created_at']?.toString(),
      rawJson: json,
    );
  }

  static List<String> parseImageUrls(Map<String, dynamic> item) {
    final List<String> urls = [];

    void addUrl(dynamic raw) {
      if (raw == null) return;
      final s = raw.toString().trim();
      if (s.isEmpty) return;
      final full = s.startsWith('http')
          ? s
          : 'https://donganhdiscovery.xadonganh.com/${s.startsWith('/') ? s.substring(1) : s}';
      if (!urls.contains(full)) urls.add(full);
    }

    if (item['images'] is List) {
      for (var img in item['images']) {
        addUrl(img);
      }
    } else if (item['images'] is String && item['images'].toString().isNotEmpty) {
      try {
        final decoded = item['images'].toString().startsWith('[') ? (item['images'] as String) : null;
        if (decoded != null) {
          final List list = (item['images'] as String)
              .replaceAll('[', '')
              .replaceAll(']', '')
              .replaceAll('"', '')
              .split(',');
          for (var img in list) {
            addUrl(img);
          }
        } else {
          addUrl(item['images']);
        }
      } catch (_) {
        addUrl(item['images']);
      }
    }

    if (item['image_paths'] is List) {
      for (var img in item['image_paths']) {
        addUrl(img);
      }
    }

    if (item['image_path'] != null && item['image_path'].toString().isNotEmpty) {
      addUrl(item['image_path']);
    }

    return urls;
  }
}
