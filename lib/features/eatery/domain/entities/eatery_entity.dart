class EateryEntity {
  final int id;
  final String name;
  final String? address;
  final String? phone;
  final String? description;
  final double rating;
  final String? bannerUrl;
  final List<String> images;
  final String? openTime;
  final String? closeTime;

  const EateryEntity({
    required this.id,
    required this.name,
    this.address,
    this.phone,
    this.description,
    this.rating = 0.0,
    this.bannerUrl,
    this.images = const [],
    this.openTime,
    this.closeTime,
  });
}
