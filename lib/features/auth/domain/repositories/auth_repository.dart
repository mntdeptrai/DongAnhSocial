import '../../../../core/errors/result.dart';
import '../entities/user_entity.dart';

abstract class AuthRepository {
  Future<Result<UserEntity>> login(String email, String password);
  Future<Result<bool>> register({
    required String name,
    required String email,
    required String password,
    required String username,
    required String phone,
    required String role,
    required bool agreeTerms,
  });
  Future<Result<void>> logout();
  Future<Result<UserEntity?>> getCurrentUser();
}
