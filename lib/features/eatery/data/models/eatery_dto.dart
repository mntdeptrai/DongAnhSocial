import '../../domain/entities/eatery_entity.dart';

class EateryDto extends EateryEntity {
  const EateryDto({
    required super.id,
    required super.name,
    super.address,
    super.phone,
    super.description,
    super.rating,
    super.bannerUrl,
    super.images,
    super.openTime,
    super.closeTime,
  });

  factory EateryDto.fromJson(Map<String, dynamic> json) {
    double parsedRating = 0.0;
    if (json['rating'] != null) {
      parsedRating = double.tryParse(json['rating'].toString()) ?? 0.0;
    }

    List<String> imgs = [];
    if (json['images'] is List) {
      imgs = (json['images'] as List).map((e) => e.toString()).toList();
    }

    return EateryDto(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name'] ?? 'Quán ăn Đông Anh',
      address: json['address'],
      phone: json['phone'],
      description: json['description'],
      rating: parsedRating,
      bannerUrl: json['banner_url'] ?? json['avatar_url'] ?? json['image'],
      images: imgs,
      openTime: json['open_time'],
      closeTime: json['close_time'],
    );
  }
}
