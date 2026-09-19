import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';

class ModerationService {
  static const String _blockedUsersKey = 'moderation_blocked_users';
  static const String _bannedUsersMetaKey = 'moderation_banned_users_meta';
  static const String _hiddenPostsKey = 'moderation_hidden_posts';
  static const String _reportTicketsKey = 'moderation_report_tickets';

  static final Set<String> _blockedUserIds = {};
  static final Map<String, Map<String, dynamic>> _bannedUsersMeta = {};
  static final Set<String> _hiddenPostIds = {};
  static final List<Map<String, dynamic>> _reportTickets = [];
  static bool _isInitialized = false;

  static Future<void> init() async {
    if (_isInitialized) return;
    final prefs = await SharedPreferences.getInstance();
    final blocked = prefs.getStringList(_blockedUsersKey) ?? [];
    final hidden = prefs.getStringList(_hiddenPostsKey) ?? [];
    _blockedUserIds.addAll(blocked);
    _hiddenPostIds.addAll(hidden);

    final metaRaw = prefs.getString(_bannedUsersMetaKey);
    if (metaRaw != null && metaRaw.isNotEmpty) {
      try {
        final Map decoded = jsonDecode(metaRaw);
        decoded.forEach((key, value) {
          if (value is Map) {
            _bannedUsersMeta[key.toString()] = Map<String, dynamic>.from(value);
          }
        });
      } catch (_) {}
    }

    _cleanExpiredBans();

    final reportsRaw = prefs.getString(_reportTicketsKey);
    if (reportsRaw != null && reportsRaw.isNotEmpty) {
      try {
        final List decoded = jsonDecode(reportsRaw);
        _reportTickets.clear();
        _reportTickets.addAll(decoded.map((e) => Map<String, dynamic>.from(e as Map)));
      } catch (_) {}
    }

    if (_reportTickets.isEmpty) {
      _seedDefaultReports();
      await _persistReports();
    }

    _isInitialized = true;
  }

  static void _cleanExpiredBans() {
    final now = DateTime.now();
    final expiredIds = <String>[];
    _bannedUsersMeta.forEach((id, meta) {
      final untilStr = meta['banned_until'];
      if (untilStr != null) {
        final until = DateTime.tryParse(untilStr.toString());
        if (until != null && now.isAfter(until)) {
          expiredIds.add(id);
        }
      }
    });

    for (final id in expiredIds) {
      _bannedUsersMeta.remove(id);
      _blockedUserIds.remove(id);
    }
  }

  static void _seedDefaultReports() {
    final now = DateTime.now();
    _reportTickets.addAll([
      {
        'id': 'rep_101',
        'content_id': '9901',
        'content_type': 'post',
        'title': 'Bán thuốc gia truyền không rõ nguồn gốc cam đoan khỏi bệnh 100%',
        'author_name': 'Trần Văn Đạt',
        'author_id': '9901_user',
        'snippet': 'Bà con ai bị viêm khớp, đau lưng lâu năm liên hệ sđt 0988xxx cam kết khỏi sau 3 ngày...',
        'image_url': '',
        'reason': 'Spam hoặc thông tin sai lệch',
        'details': 'Quảng cáo bán thuốc không phép trong bài viết trải nghiệm du lịch.',
        'reported_at': now.subtract(const Duration(minutes: 35)).toIso8601String(),
        'status': 'pending',
      },
      {
        'id': 'rep_102',
        'content_id': '9902',
        'content_type': 'comment',
        'title': 'Bình luận xúc phạm danh dự chủ quán ăn',
        'author_name': 'Lê Tuấn Khang',
        'author_id': '9902_user',
        'snippet': 'Quán này làm ăn chộp giật, đồ ăn bẩn thỉu...',
        'image_url': '',
        'reason': 'Bạo lực, đe dọa hoặc quấy rối',
        'details': 'Bình luận mang tính chất thóa mạ ác ý không có bằng chứng thực tế.',
        'reported_at': now.subtract(const Duration(hours: 2, minutes: 10)).toIso8601String(),
        'status': 'pending',
      },
    ]);
  }

  static Future<void> _persistReports() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_reportTicketsKey, jsonEncode(_reportTickets));
  }

  static Future<void> _persistBans() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setStringList(_blockedUsersKey, _blockedUserIds.toList());
    await prefs.setString(_bannedUsersMetaKey, jsonEncode(_bannedUsersMeta));
  }

  static bool isUserBlocked(String userId) {
    if (_bannedUsersMeta.containsKey(userId)) {
      final untilStr = _bannedUsersMeta[userId]?['banned_until'];
      if (untilStr != null) {
        final until = DateTime.tryParse(untilStr.toString());
        if (until != null && DateTime.now().isAfter(until)) {
          _bannedUsersMeta.remove(userId);
          _blockedUserIds.remove(userId);
          _persistBans();
          return false;
        }
      }
      return true;
    }
    return _blockedUserIds.contains(userId);
  }

  static Future<void> blockUser(String userId) async {
    await banUserWithDuration(userId: userId, duration: null);
  }

  static Future<void> banUserWithDuration({
    required String userId,
    Duration? duration,
    String? reason,
  }) async {
    _blockedUserIds.add(userId);
    DateTime? bannedUntil;
    if (duration != null) {
      bannedUntil = DateTime.now().add(duration);
    }

    _bannedUsersMeta[userId] = {
      'banned_until': bannedUntil?.toIso8601String(),
      'reason': reason ?? 'Vi phạm tiêu chuẩn cộng đồng',
      'banned_at': DateTime.now().toIso8601String(),
      'duration_hours': duration?.inHours,
    };

    await _persistBans();
  }

  static Future<void> unblockUser(String userId) async {
    _blockedUserIds.remove(userId);
    _bannedUsersMeta.remove(userId);
    await _persistBans();
  }

  static Map<String, dynamic>? getUserBanInfo(String userId) {
    if (!isUserBlocked(userId)) return null;

    final meta = _bannedUsersMeta[userId];
    if (meta == null) {
      return {
        'is_banned': true,
        'is_permanent': true,
        'banned_until': null,
        'reason': 'Vi phạm tiêu chuẩn cộng đồng',
        'remaining_text': 'Khóa vĩnh viễn',
      };
    }

    final untilStr = meta['banned_until'];
    if (untilStr == null) {
      return {
        'is_banned': true,
        'is_permanent': true,
        'banned_until': null,
        'reason': meta['reason'] ?? 'Vi phạm tiêu chuẩn cộng đồng',
        'remaining_text': 'Khóa vĩnh viễn',
      };
    }

    final until = DateTime.tryParse(untilStr.toString());
    if (until == null) {
      return {
        'is_banned': true,
        'is_permanent': true,
        'banned_until': null,
        'reason': meta['reason'] ?? 'Vi phạm tiêu chuẩn cộng đồng',
        'remaining_text': 'Khóa vĩnh viễn',
      };
    }

    return {
      'is_banned': true,
      'is_permanent': false,
      'banned_until': until,
      'reason': meta['reason'] ?? 'Vi phạm tiêu chuẩn cộng đồng',
      'remaining_text': formatRemainingBan(until),
    };
  }

  static String formatDurationText(Duration? duration) {
    if (duration == null) return 'Vĩnh viễn';
    if (duration.inDays >= 30) return '30 ngày';
    if (duration.inDays >= 7) return '7 ngày';
    if (duration.inDays >= 3) return '3 ngày';
    if (duration.inDays >= 1) return '24 giờ (1 ngày)';
    return '${duration.inHours} giờ';
  }

  static String formatRemainingBan(DateTime bannedUntil) {
    final diff = bannedUntil.difference(DateTime.now());
    if (diff.isNegative) return 'Đã hết hạn';
    if (diff.inDays > 0) {
      return 'Còn ${diff.inDays} ngày ${diff.inHours % 24} giờ';
    }
    if (diff.inHours > 0) {
      return 'Còn ${diff.inHours} giờ ${diff.inMinutes % 60} phút';
    }
    return 'Còn ${diff.inMinutes} phút';
  }

  static List<String> getBlockedUserIds() {
    return _blockedUserIds.toList();
  }

  static bool isPostHidden(String postId) {
    return _hiddenPostIds.contains(postId);
  }

  static Future<void> hidePost(String postId) async {
    _hiddenPostIds.add(postId);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setStringList(_hiddenPostsKey, _hiddenPostIds.toList());
  }

  static Future<void> unhidePost(String postId) async {
    _hiddenPostIds.remove(postId);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setStringList(_hiddenPostsKey, _hiddenPostIds.toList());
  }

  static List<Map<String, dynamic>> getReportTickets() {
    return List.unmodifiable(_reportTickets);
  }

  static int get pendingReportsCount {
    return _reportTickets.where((r) => r['status'] == 'pending').length;
  }

  static Future<void> resolveReportTicket({
    required String ticketId,
    required String action,
    Duration? banDuration,
    String? banReason,
  }) async {
    final index = _reportTickets.indexWhere((r) => r['id'] == ticketId);
    if (index == -1) return;

    final report = _reportTickets[index];
    final contentId = report['content_id']?.toString() ?? '';
    final authorId = report['author_id']?.toString() ?? '';

    if (action == 'remove_content') {
      if (contentId.isNotEmpty) {
        await hidePost(contentId);
      }
      report['status'] = 'resolved_removed';
    } else if (action == 'ban_user') {
      if (authorId.isNotEmpty) {
        await banUserWithDuration(
          userId: authorId,
          duration: banDuration,
          reason: banReason ?? report['reason'],
        );
      }
      if (contentId.isNotEmpty) {
        await hidePost(contentId);
      }
      report['status'] = 'resolved_banned';
      report['ban_duration_text'] = formatDurationText(banDuration);
    } else if (action == 'dismiss') {
      report['status'] = 'dismissed';
    }

    report['resolved_at'] = DateTime.now().toIso8601String();
    await _persistReports();
  }

  static Future<bool> reportContent({
    required String contentId,
    required String contentType,
    required String reason,
    String? details,
    String? title,
    String? authorName,
    String? authorId,
    String? snippet,
    String? imageUrl,
  }) async {
    final report = {
      'id': 'rep_${DateTime.now().millisecondsSinceEpoch}',
      'content_id': contentId,
      'content_type': contentType,
      'title': title ?? 'Nội dung bị báo cáo',
      'author_name': authorName ?? 'Thành viên Đông Anh',
      'author_id': authorId ?? '0',
      'snippet': snippet ?? '',
      'image_url': imageUrl ?? '',
      'reason': reason,
      'details': details ?? '',
      'reported_at': DateTime.now().toIso8601String(),
      'status': 'pending',
    };

    _reportTickets.insert(0, report);
    await _persistReports();

    return await ApiService.reportContent(
      contentId: contentId,
      contentType: contentType,
      reason: reason,
      details: details,
    );
  }
}
