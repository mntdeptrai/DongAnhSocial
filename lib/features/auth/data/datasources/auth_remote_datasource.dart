import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../../../core/app_constants.dart';
import '../../../../core/errors/failure.dart';
import '../models/user_dto.dart';

abstract class AuthRemoteDataSource {
  Future<UserDto> login(String email, String password);
  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String username,
    required String phone,
    required String role,
    required bool agreeTerms,
  });
}

class AuthRemoteDataSourceImpl implements AuthRemoteDataSource {
  final http.Client client;

  AuthRemoteDataSourceImpl({http.Client? client}) : client = client ?? http.Client();

  @override
  Future<UserDto> login(String email, String password) async {
    try {
      final response = await client.post(
        Uri.parse('${AppConstants.apiBaseUrl}/auth/token'),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'email': email,
          'password': password,
          'device_name': 'mobile_flutter',
        }),
      );

      Map<String, dynamic> data = {};
      try {
        data = jsonDecode(response.body);
      } catch (_) {}

      if (response.statusCode == 200 && data['success'] == true) {
        return UserDto.fromJson(data['user'] ?? {});
      } else {
        throw ServerFailure(
          data['message'] ?? 'Đăng nhập không thành công (Mã ${response.statusCode})',
          statusCode: response.statusCode,
        );
      }
    } on Failure {
      rethrow;
    } catch (e) {
      throw NetworkFailure('Không thể kết nối máy chủ: $e');
    }
  }

  @override
  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String username,
    required String phone,
    required String role,
    required bool agreeTerms,
  }) async {
    try {
      final response = await client.post(
        Uri.parse('${AppConstants.apiBaseUrl}/register'),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'name': name,
          'email': email,
          'password': password,
          'username': username,
          'phone': phone,
          'role': role,
          'agree_terms': agreeTerms,
        }),
      );

      Map<String, dynamic> data = {};
      try {
        data = jsonDecode(response.body);
      } catch (_) {}

      if (response.statusCode == 200 || response.statusCode == 201) {
        return data['success'] == true;
      } else {
        throw ServerFailure(
          data['message'] ?? 'Đăng ký không thành công (Mã ${response.statusCode})',
          statusCode: response.statusCode,
        );
      }
    } on Failure {
      rethrow;
    } catch (e) {
      throw NetworkFailure('Không thể kết nối máy chủ: $e');
    }
  }
}
