import '../../../../core/errors/result.dart';
import '../../../../core/usecases/usecase.dart';
import '../entities/eatery_entity.dart';
import '../repositories/eatery_repository.dart';

class GetEateriesUseCase implements UseCase<List<EateryEntity>, NoParams> {
  final EateryRepository repository;

  GetEateriesUseCase(this.repository);

  @override
  Future<Result<List<EateryEntity>>> call(NoParams params) {
    return repository.getEateries();
  }
}
