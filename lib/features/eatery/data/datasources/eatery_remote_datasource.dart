import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../../../core/app_constants.dart';
import '../../../../core/errors/failure.dart';
import '../models/eatery_dto.dart';

abstract class EateryRemoteDataSource {
  Future<List<EateryDto>> getEateries();
  Future<EateryDto> getEateryDetail(int eateryId);
}

class EateryRemoteDataSourceImpl implements EateryRemoteDataSource {
  final http.Client client;

  EateryRemoteDataSourceImpl({http.Client? client}) : client = client ?? http.Client();

  @override
  Future<List<EateryDto>> getEateries() async {
    try {
      final response = await client.get(
        Uri.parse('${AppConstants.apiBaseUrl}/eateries'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['eateries'] is List) {
          final List list = data['eateries'];
          return list.map((item) => EateryDto.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
      return [];
    } catch (e) {
      throw ServerFailure('Không thể tải danh sách quán ăn: $e');
    }
  }

  @override
  Future<EateryDto> getEateryDetail(int eateryId) async {
    try {
      final response = await client.get(
        Uri.parse('${AppConstants.apiBaseUrl}/eateries/$eateryId'),
        headers: {'Accept': 'application/json'},
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return EateryDto.fromJson(data['eatery'] ?? data);
      } else {
        throw ServerFailure(data['message'] ?? 'Không tìm thấy thông tin quán');
      }
    } catch (e) {
      throw ServerFailure('Lỗi tải chi tiết quán ăn: $e');
    }
  }
}
