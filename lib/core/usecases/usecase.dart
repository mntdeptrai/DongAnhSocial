import '../errors/result.dart';

/// Hợp đồng chuẩn cho các UseCase trong ứng dụng.
abstract class UseCase<T, Params> {
  Future<Result<T>> call(Params params);
}

class NoParams {
  const NoParams();
}
