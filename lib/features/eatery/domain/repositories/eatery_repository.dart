import '../../../../core/errors/result.dart';
import '../entities/eatery_entity.dart';

abstract class EateryRepository {
  Future<Result<List<EateryEntity>>> getEateries();
  Future<Result<EateryEntity>> getEateryDetail(int eateryId);
}
