class CheckinLocationEntity {
  final int id;
  final String name;
  final String? address;
  final double latitude;
  final double longitude;
  final String? category;
  final String? imageUrl;

  const CheckinLocationEntity({
    required this.id,
    required this.name,
    this.address,
    required this.latitude,
    required this.longitude,
    this.category,
    this.imageUrl,
  });
}
