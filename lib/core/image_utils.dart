/// Utility để resolve image URL từ relative path → full URL.
/// Thay thế logic copy-paste `s.startsWith('http') ? s : 'https://.../$s'`
/// xuất hiện ở 10+ chỗ trong project.
import 'app_constants.dart';

class ImageUtils {
  ImageUtils._();

  /// Chuyển đổi relative path thành full URL.
  /// Nếu đã là full URL (http/https) → trả về nguyên.
  /// Nếu là relative path → ghép với baseUrl.
  /// Nếu null/empty → trả về empty string.
  static String resolveUrl(String? raw) {
    if (raw == null || raw.trim().isEmpty) return '';
    final s = raw.trim();
    if (s.startsWith('http://') || s.startsWith('https://')) return s;
    final path = s.startsWith('/') ? s.substring(1) : s;
    return '${AppConstants.baseUrl}/$path';
  }
}
