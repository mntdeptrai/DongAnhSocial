import '../../../../core/errors/result.dart';
import '../entities/checkin_location_entity.dart';

abstract class MapRepository {
  Future<Result<List<CheckinLocationEntity>>> getNearbyLocations({
    required double latitude,
    required double longitude,
    double radiusKm = 5.0,
  });
}
