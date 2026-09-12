import '../../../../core/errors/failure.dart';
import '../../../../core/errors/result.dart';
import '../../domain/entities/checkin_location_entity.dart';
import '../../domain/repositories/map_repository.dart';
import '../datasources/map_remote_datasource.dart';

class MapRepositoryImpl implements MapRepository {
  final MapRemoteDataSource remoteDataSource;

  MapRepositoryImpl({required this.remoteDataSource});

  @override
  Future<Result<List<CheckinLocationEntity>>> getNearbyLocations({
    required double latitude,
    required double longitude,
    double radiusKm = 5.0,
  }) async {
    try {
      final locations = await remoteDataSource.getNearbyLocations(
        latitude: latitude,
        longitude: longitude,
        radiusKm: radiusKm,
      );
      return Success(locations);
    } on Failure catch (failure) {
      return FailureResult(failure);
    } catch (e) {
      return FailureResult(ServerFailure(e.toString()));
    }
  }
}
