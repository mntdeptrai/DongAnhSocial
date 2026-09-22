class UserModel {
  final int id;
  final String name;
  final String? avatarUrl;
  final String role;
  final bool isVerified;

  const UserModel({
    required this.id,
    required this.name,
    this.avatarUrl,
    this.role = 'user',
    this.isVerified = false,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    final isPostOrItem = json.containsKey('title') ||
        json.containsKey('content') ||
        json.containsKey('description') ||
        json.containsKey('post_type') ||
        json.containsKey('type') ||
        json.containsKey('is_food_tour') ||
        json.containsKey('is_checkin');

    final dynamic rawUserId = json['user_id'] ??
        json['author_id'] ??
        (json['author'] is Map ? json['author']['id'] : null) ??
        (json['user'] is Map ? json['user']['id'] : null) ??
        (!isPostOrItem ? json['id'] : null);

    final rawName = json['author_name'] ??
        (json['author'] is Map ? json['author']['name'] : null) ??
        (json['user'] is Map ? json['user']['name'] : null) ??
        json['name'] ??
        'Thành viên Đông Anh';

    final rawAvatar = json['author_avatar'] ??
        (json['author'] is Map ? (json['author']['avatar_url'] ?? json['author']['avatar']) : null) ??
        (json['user'] is Map ? (json['user']['avatar_url'] ?? json['user']['avatar']) : null) ??
        json['avatar_url'] ??
        json['avatar'];

    final rawRole = json['author_role'] ??
        (json['author'] is Map ? json['author']['role'] : null) ??
        (json['user'] is Map ? json['user']['role'] : null) ??
        json['role'] ??
        'user';

    return UserModel(
      id: rawUserId is int ? rawUserId : int.tryParse(rawUserId?.toString() ?? '0') ?? 0,
      name: rawName.toString(),
      avatarUrl: rawAvatar?.toString(),
      role: rawRole.toString().toLowerCase(),
      isVerified: json['is_verified'] == true || json['is_verified'] == 1,
    );
  }

  bool get isAdmin => role == 'admin';
  bool get isPrincipal => role == 'principal';
  bool get isSeller => role == 'seller';
}
