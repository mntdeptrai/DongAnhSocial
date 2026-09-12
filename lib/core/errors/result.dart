import 'failure.dart';

/// Class bọc kết quả trả về (Success hoặc Failure) mà không cần thư viện ngoài.
abstract class Result<T> {
  const Result();

  bool get isSuccess => this is Success<T>;
  bool get isFailure => this is FailureResult<T>;

  T? get data => isSuccess ? (this as Success<T>).value : null;
  Failure? get failure => isFailure ? (this as FailureResult<T>).error : null;

  R fold<R>(R Function(Failure error) onFailure, R Function(T data) onSuccess) {
    if (this is Success<T>) {
      return onSuccess((this as Success<T>).value);
    } else {
      return onFailure((this as FailureResult<T>).error);
    }
  }
}

class Success<T> extends Result<T> {
  final T value;
  const Success(this.value);
}

class FailureResult<T> extends Result<T> {
  final Failure error;
  const FailureResult(this.error);
}
