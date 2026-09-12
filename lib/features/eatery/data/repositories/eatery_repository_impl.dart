import '../../../../core/errors/failure.dart';
import '../../../../core/errors/result.dart';
import '../../domain/entities/eatery_entity.dart';
import '../../domain/repositories/eatery_repository.dart';
import '../datasources/eatery_remote_datasource.dart';

class EateryRepositoryImpl implements EateryRepository {
  final EateryRemoteDataSource remoteDataSource;

  EateryRepositoryImpl({required this.remoteDataSource});

  @override
  Future<Result<List<EateryEntity>>> getEateries() async {
    try {
      final list = await remoteDataSource.getEateries();
      return Success(list);
    } on Failure catch (failure) {
      return FailureResult(failure);
    } catch (e) {
      return FailureResult(ServerFailure(e.toString()));
    }
  }

  @override
  Future<Result<EateryEntity>> getEateryDetail(int eateryId) async {
    try {
      final eatery = await remoteDataSource.getEateryDetail(eateryId);
      return Success(eatery);
    } on Failure catch (failure) {
      return FailureResult(failure);
    } catch (e) {
      return FailureResult(ServerFailure(e.toString()));
    }
  }
}
