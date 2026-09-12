import '../../domain/entities/user_entity.dart';

class UserDto extends UserEntity {
  const UserDto({
    required super.id,
    required super.name,
    super.email,
    super.avatarUrl,
    super.role,
    super.isVerified,
  });

  factory UserDto.fromJson(Map<String, dynamic> json) {
    return UserDto(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name'] ?? json['author_name'] ?? 'Thành viên Đông Anh',
      email: json['email'],
      avatarUrl: json['avatar_url'] ?? json['avatar'] ?? json['author_avatar'],
      role: (json['role'] ?? json['author_role'] ?? 'user').toString().toLowerCase(),
      isVerified: json['is_verified'] == true || json['is_verified'] == 1,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'avatar_url': avatarUrl,
      'role': role,
      'is_verified': isVerified,
    };
  }
}
