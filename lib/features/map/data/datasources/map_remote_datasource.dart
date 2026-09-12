import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../../../core/app_constants.dart';
import '../../../../core/errors/failure.dart';
import '../models/checkin_location_dto.dart';

abstract class MapRemoteDataSource {
  Future<List<CheckinLocationDto>> getNearbyLocations({
    required double latitude,
    required double longitude,
    double radiusKm = 5.0,
  });
}

class MapRemoteDataSourceImpl implements MapRemoteDataSource {
  final http.Client client;

  MapRemoteDataSourceImpl({http.Client? client}) : client = client ?? http.Client();

  @override
  Future<List<CheckinLocationDto>> getNearbyLocations({
    required double latitude,
    required double longitude,
    double radiusKm = 5.0,
  }) async {
    try {
      final uri = Uri.parse(
        '${AppConstants.apiBaseUrl}/map/nearby?lat=$latitude&lng=$longitude&radius=$radiusKm',
      );
      final response = await client.get(uri, headers: {'Accept': 'application/json'});

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['locations'] is List) {
          final List list = data['locations'];
          return list.map((item) => CheckinLocationDto.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
      return [];
    } catch (e) {
      throw ServerFailure('Không thể lấy địa điểm xung quanh: $e');
    }
  }
}
