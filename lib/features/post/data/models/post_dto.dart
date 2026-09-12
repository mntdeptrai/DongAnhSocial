import '../../../auth/data/models/user_dto.dart';
import '../../domain/entities/post_entity.dart';

class PostDto extends PostEntity {
  const PostDto({
    required super.id,
    super.numericId,
    super.hashId,
    required super.title,
    required super.content,
    required super.type,
    super.isFoodTour,
    super.isCheckin,
    super.isSchool,
    required super.author,
    super.likesCount,
    super.commentsCount,
    super.isLiked,
    super.images,
    super.createdAt,
  });

  factory PostDto.fromJson(Map<String, dynamic> json) {
    final rawType = (json['type'] ?? json['post_type'] ?? '').toString().toLowerCase();
    final isFood = json['is_food_tour'] == true || rawType == 'food_tour';
    final isCheck = json['is_checkin'] == true || rawType == 'checkin';
    final isSch = rawType == 'school' || json['author_role'] == 'principal' || json['is_school'] == true;

    final extractedImages = _parseImageUrls(json);

    return PostDto(
      id: (json['id'] ?? '').toString(),
      numericId: json['numeric_id'] ?? json['id'],
      hashId: json['hashid']?.toString(),
      title: (json['title'] ?? json['name'] ?? '').toString(),
      content: (json['content'] ?? json['title'] ?? json['name'] ?? '').toString(),
      type: rawType.isEmpty ? 'post' : rawType,
      isFoodTour: isFood,
      isCheckin: isCheck,
      isSchool: isSch,
      author: UserDto.fromJson(json),
      likesCount: json['likes_count'] is int
          ? json['likes_count']
          : int.tryParse(json['likes_count']?.toString() ?? '0') ?? 0,
      commentsCount: json['comments_count'] is int
          ? json['comments_count']
          : int.tryParse(json['comments_count']?.toString() ?? '0') ?? 0,
      isLiked: json['is_liked'] == true,
      images: extractedImages,
      createdAt: json['created_at']?.toString(),
    );
  }

  static List<String> _parseImageUrls(Map<String, dynamic> item) {
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
        final List list = (item['images'] as String)
            .replaceAll('[', '')
            .replaceAll(']', '')
            .replaceAll('"', '')
            .split(',');
        for (var img in list) {
          addUrl(img);
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
