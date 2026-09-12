import '../../../../core/errors/result.dart';
import '../../../../core/usecases/usecase.dart';
import '../entities/checkin_location_entity.dart';
import '../repositories/map_repository.dart';

class GetNearbyLocationsParams {
  final double latitude;
  final double longitude;
  final double radiusKm;

  const GetNearbyLocationsParams({
    required this.latitude,
    required this.longitude,
    this.radiusKm = 5.0,
  });
}

class GetNearbyLocationsUseCase implements UseCase<List<CheckinLocationEntity>, GetNearbyLocationsParams> {
  final MapRepository repository;

  GetNearbyLocationsUseCase(this.repository);

  @override
  Future<Result<List<CheckinLocationEntity>>> call(GetNearbyLocationsParams params) {
    return repository.getNearbyLocations(
      latitude: params.latitude,
      longitude: params.longitude,
      radiusKm: params.radiusKm,
    );
  }
}
