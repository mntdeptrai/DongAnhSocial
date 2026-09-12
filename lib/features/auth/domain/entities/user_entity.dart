class UserEntity {
  final int id;
  final String name;
  final String? email;
  final String? avatarUrl;
  final String role;
  final bool isVerified;

  const UserEntity({
    required this.id,
    required this.name,
    this.email,
    this.avatarUrl,
    this.role = 'user',
    this.isVerified = false,
  });

  bool get isAdmin => role == 'admin';
  bool get isPrincipal => role == 'principal';
  bool get isSeller => role == 'seller';
}
