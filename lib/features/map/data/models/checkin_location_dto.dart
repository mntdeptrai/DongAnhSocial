import '../../domain/entities/checkin_location_entity.dart';

class CheckinLocationDto extends CheckinLocationEntity {
  const CheckinLocationDto({
    required super.id,
    required super.name,
    super.address,
    required super.latitude,
    required super.longitude,
    super.category,
    super.imageUrl,
  });

  factory CheckinLocationDto.fromJson(Map<String, dynamic> json) {
    return CheckinLocationDto(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name'] ?? json['title'] ?? 'Địa điểm Đông Anh',
      address: json['address'],
      latitude: double.tryParse(json['latitude']?.toString() ?? json['lat']?.toString() ?? '0') ?? 0.0,
      longitude: double.tryParse(json['longitude']?.toString() ?? json['lng']?.toString() ?? '0') ?? 0.0,
      category: json['category'],
      imageUrl: json['image_url'] ?? json['image'],
    );
  }
}
