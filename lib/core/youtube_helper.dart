import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

class YouTubeHelper {
  static final RegExp _ytRegex = RegExp(
    r'(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})',
    caseSensitive: false,
  );

  /// Kiểm tra xem một URL có phải là video YouTube hay không
  static bool isYouTubeUrl(String? url) {
    if (url == null || url.trim().isEmpty) return false;
    final clean = url.trim().toLowerCase();
    return clean.contains('youtube.com') || clean.contains('youtu.be');
  }

  /// Trích xuất mã ID (11 ký tự) của video YouTube
  static String? extractVideoId(String? url) {
    if (url == null || url.trim().isEmpty) return null;
    final match = _ytRegex.firstMatch(url.trim());
    if (match != null && match.groupCount >= 1) {
      return match.group(1);
    }
    return null;
  }

  /// Lấy URL ảnh đại diện (Thumbnail) chất lượng cao từ CDN YouTube
  static String getThumbnailUrl(String? url, {bool highRes = true}) {
    final videoId = extractVideoId(url);
    if (videoId != null && videoId.isNotEmpty) {
      return 'https://img.youtube.com/vi/$videoId/${highRes ? 'hqdefault.jpg' : 'mqdefault.jpg'}';
    }
    return url ?? '';
  }

  /// Lấy đường dẫn chuẩn xem video trên YouTube
  static String getWatchUrl(String? url) {
    final videoId = extractVideoId(url);
    if (videoId != null && videoId.isNotEmpty) {
      return 'https://www.youtube.com/watch?v=$videoId';
    }
    return url ?? '';
  }

  /// Mở video YouTube trên ứng dụng YouTube ngoài hoặc Safari in-app
  static Future<bool> launchYouTube(String url) async {
    try {
      final target = getWatchUrl(url);
      final uri = Uri.parse(target);

      // Ưu tiên mở bằng App YouTube chính thức nếu có
      if (await canLaunchUrl(uri)) {
        final ok = await launchUrl(uri, mode: LaunchMode.externalApplication);
        if (ok) return true;
      }

      // Fallback mở trong trình duyệt In-App
      return await launchUrl(uri, mode: LaunchMode.inAppBrowserView);
    } catch (e) {
      debugPrint('[YouTubeHelper] launch error: $e');
      return false;
    }
  }
}
