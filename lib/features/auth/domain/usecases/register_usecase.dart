import '../../../../core/errors/result.dart';
import '../../../../core/usecases/usecase.dart';
import '../repositories/auth_repository.dart';

class RegisterParams {
  final String name;
  final String email;
  final String password;
  final String username;
  final String phone;
  final String role;
  final bool agreeTerms;

  const RegisterParams({
    required this.name,
    required this.email,
    required this.password,
    required this.username,
    required this.phone,
    required this.role,
    required this.agreeTerms,
  });
}

class RegisterUseCase implements UseCase<bool, RegisterParams> {
  final AuthRepository repository;

  RegisterUseCase(this.repository);

  @override
  Future<Result<bool>> call(RegisterParams params) {
    return repository.register(
      name: params.name,
      email: params.email,
      password: params.password,
      username: params.username,
      phone: params.phone,
      role: params.role,
      agreeTerms: params.agreeTerms,
    );
  }
}
