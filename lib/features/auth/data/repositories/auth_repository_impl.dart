import '../../../../core/errors/failure.dart';
import '../../../../core/errors/result.dart';
import '../../domain/entities/user_entity.dart';
import '../../domain/repositories/auth_repository.dart';
import '../datasources/auth_remote_datasource.dart';

class AuthRepositoryImpl implements AuthRepository {
  final AuthRemoteDataSource remoteDataSource;

  AuthRepositoryImpl({required this.remoteDataSource});

  @override
  Future<Result<UserEntity>> login(String email, String password) async {
    try {
      final user = await remoteDataSource.login(email, password);
      return Success(user);
    } on Failure catch (failure) {
      return FailureResult(failure);
    } catch (e) {
      return FailureResult(ServerFailure(e.toString()));
    }
  }

  @override
  Future<Result<bool>> register({
    required String name,
    required String email,
    required String password,
    required String username,
    required String phone,
    required String role,
    required bool agreeTerms,
  }) async {
    try {
      final success = await remoteDataSource.register(
        name: name,
        email: email,
        password: password,
        username: username,
        phone: phone,
        role: role,
        agreeTerms: agreeTerms,
      );
      return Success(success);
    } on Failure catch (failure) {
      return FailureResult(failure);
    } catch (e) {
      return FailureResult(ServerFailure(e.toString()));
    }
  }

  @override
  Future<Result<void>> logout() async {
    return const Success(null);
  }

  @override
  Future<Result<UserEntity?>> getCurrentUser() async {
    return const Success(null);
  }
}
