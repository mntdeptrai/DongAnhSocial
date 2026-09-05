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
    return UserModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name'] ?? json['author_name'] ?? 'Thành viên Đông Anh',
      avatarUrl: json['avatar_url'] ?? json['avatar'] ?? json['author_avatar'],
      role: (json['role'] ?? json['author_role'] ?? 'user').toString().toLowerCase(),
      isVerified: json['is_verified'] == true || json['is_verified'] == 1,
    );
  }

  bool get isAdmin => role == 'admin';
  bool get isPrincipal => role == 'principal';
  bool get isSeller => role == 'seller';
}
