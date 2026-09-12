import '../../../auth/domain/entities/user_entity.dart';

class PostEntity {
  final String id;
  final dynamic numericId;
  final String? hashId;
  final String title;
  final String content;
  final String type;
  final bool isFoodTour;
  final bool isCheckin;
  final bool isSchool;
  final UserEntity author;
  final int likesCount;
  final int commentsCount;
  final bool isLiked;
  final List<String> images;
  final String? createdAt;

  const PostEntity({
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
    this.likesCount = 0,
    this.commentsCount = 0,
    this.isLiked = false,
    this.images = const [],
    this.createdAt,
  });
}
