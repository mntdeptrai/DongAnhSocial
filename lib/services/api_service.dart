import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String baseUrl = 'https://donganhdiscovery.xadonganh.com/api/v1';
  static String? _token;
  static Map<String, dynamic>? currentUser;

  // =========================================================================
  // INITIALIZATION & AUTH HELPERS
  // =========================================================================

  /// Khởi tạo service: load token và user từ SharedPreferences
  static Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    final userJson = prefs.getString('current_user');
    if (userJson != null) {
      currentUser = jsonDecode(userJson);
    }
  }

  static bool get isAuthenticated => _token != null;
  static String? get token => _token;

  static Map<String, String> _getHeaders() {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (_token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }
    return headers;
  }

  // =========================================================================
  // AUTH: Đăng nhập / Đăng ký / Đăng xuất
  // =========================================================================

  /// POST /auth/token — Đăng nhập bằng Sanctum token
  static Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/token'),
        headers: _getHeaders(),
        body: jsonEncode({
          'email': email,
          'password': password,
          'device_name': 'mobile_flutter',
        }),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        _token = data['token'];
        currentUser = data['user'];

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', _token!);
        await prefs.setString('current_user', jsonEncode(currentUser));
        return {'success': true};
      }
      return {'success': false, 'message': data['message'] ?? 'Đăng nhập thất bại'};
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối mạng'};
    }
  }

  /// POST /auth/register — Đăng ký tài khoản mới
  static Future<Map<String, dynamic>> register(String name, String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/register'),
        headers: _getHeaders(),
        body: jsonEncode({
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': password,
        }),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 || response.statusCode == 201) {
        return {'success': true, 'message': 'Đăng ký thành công'};
      }
      return {'success': false, 'message': data['message'] ?? 'Đăng ký thất bại'};
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối mạng'};
    }
  }

  /// POST /auth/token/revoke — Đăng xuất (xóa token)
  static Future<void> logout() async {
    if (_token != null) {
      try {
        await http.post(
          Uri.parse('$baseUrl/auth/token/revoke'),
          headers: _getHeaders(),
        );
      } catch (_) {}
    }
    _token = null;
    currentUser = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('current_user');
  }

  // =========================================================================
  // CATEGORIES & COMMUNES: Danh mục & Xã/Phường
  // =========================================================================

  /// GET /categories — Danh sách tất cả danh mục
  static Future<List<dynamic>> getCategories() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/categories'), headers: _getHeaders());
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return [];
  }

  /// GET /communes — Danh sách xã/phường Đông Anh
  static Future<List<dynamic>> getCommunes() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/communes'), headers: _getHeaders());
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return [];
  }

  // =========================================================================
  // EATERIES: Địa điểm (CRUD + Search)
  // =========================================================================

  /// GET /{category}/eateries — Danh sách địa điểm theo danh mục
  /// Hỗ trợ query params: ?q=keyword&commune_id=1&is_featured=1
  static Future<List<dynamic>> getEateries(String categorySlug, {String? keyword, int? communeId, bool? isFeatured}) async {
    try {
      final params = <String, String>{};
      if (keyword != null && keyword.isNotEmpty) params['q'] = keyword;
      if (communeId != null) params['commune_id'] = communeId.toString();
      if (isFeatured != null) params['is_featured'] = isFeatured ? '1' : '0';

      final uri = Uri.parse('$baseUrl/$categorySlug/eateries').replace(queryParameters: params.isNotEmpty ? params : null);
      final response = await http.get(uri, headers: _getHeaders());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is List) return data;
        if (data is Map && data.containsKey('eateries')) return data['eateries'];
      }
    } catch (_) {}
    return [];
  }

  /// GET /{category}/eateries/{slug} — Chi tiết một địa điểm (có fallback danh mục)
  static Future<Map<String, dynamic>?> getEateryDetail(String categorySlug, String eaterySlug) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/$categorySlug/eateries/$eaterySlug'),
        headers: _getHeaders(),
      );
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}

    // Fallback: thử các danh mục khác nếu danh mục ban đầu không tìm thấy
    final categories = [
      'dong-anh-food-map',
      'hanh-trinh-di-san',
      'stay-in-dong-anh',
      'wellness-care',
      'dong-anh-market',
      'smart-education-map',
      'discover-dong-anh-community-culture-hub'
    ];
    for (var cat in categories) {
      if (cat == categorySlug) continue;
      try {
        final response = await http.get(
          Uri.parse('$baseUrl/$cat/eateries/$eaterySlug'),
          headers: _getHeaders(),
        );
        if (response.statusCode == 200) {
          return jsonDecode(response.body);
        }
      } catch (_) {}
    }
    return null;
  }

  /// Lấy tất cả địa điểm từ mọi danh mục (gọi song song)
  /// Trả về danh sách đã khử trùng lặp
  static Future<List<dynamic>> getAllEateries() async {
    try {
      final categories = await getCategories();
      if (categories.isEmpty) return [];

      final futures = categories.map((cat) => getEateries(cat['slug'])).toList();
      final results = await Future.wait(futures);

      final Set<String> seenKeys = {};
      final List<dynamic> combined = [];
      for (var list in results) {
        for (var eat in list) {
          final String slug = eat['slug']?.toString() ?? '';
          final String name = eat['name']?.toString().trim().toLowerCase() ?? '';
          final key = slug.isNotEmpty ? slug : name;
          if (!seenKeys.contains(key)) {
            seenKeys.add(key);
            combined.add(eat);
          }
        }
      }
      return combined;
    } catch (_) {}
    return [];
  }

  /// Tìm kiếm địa điểm theo từ khóa trong một danh mục
  static Future<List<dynamic>> searchEateries(String categorySlug, String keyword) async {
    return getEateries(categorySlug, keyword: keyword);
  }

  // =========================================================================
  // REVIEWS: Đánh giá địa điểm
  // =========================================================================

  /// POST /{category}/eateries/{id}/reviews — Gửi đánh giá mới
  static Future<Map<String, dynamic>> storeReview({
    required String categorySlug,
    required int eateryId,
    required int rating,
    required String comment,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/$categorySlug/eateries/$eateryId/reviews'),
        headers: _getHeaders(),
        body: jsonEncode({
          'rating': rating,
          'comment': comment,
        }),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 201) {
        return {'success': true, 'review': data};
      }
      return {'success': false, 'message': data['message'] ?? 'Gửi đánh giá thất bại'};
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối mạng'};
    }
  }

  // =========================================================================
  // VIDEOS: Video Reels đặc sản
  // =========================================================================

  /// GET /videos — Danh sách video Reels (công khai)
  static Future<List<dynamic>> getVideos() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/videos'), headers: _getHeaders());
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return [];
  }

  /// POST /videos/{id}/like — Like một video
  static Future<Map<String, dynamic>> likeVideo(int id) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/videos/$id/like'),
        headers: _getHeaders(),
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return {'success': true, 'likes_count': data['likes_count']};
      }
      return {'success': false, 'message': data['message'] ?? 'Like thất bại'};
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối mạng'};
    }
  }

  // =========================================================================
  // FOOD TOURS: Hành trình ẩm thực
  // =========================================================================

  /// GET /food-tours — Danh sách Food Tour
  static Future<List<dynamic>> getFoodTours() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/food-tours'), headers: _getHeaders());
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return [];
  }

  /// GET /food-tours/{slug} — Chi tiết một Food Tour
  static Future<Map<String, dynamic>?> getFoodTourDetail(String slug) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/food-tours/$slug'),
        headers: _getHeaders(),
      );
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return null;
  }

  // =========================================================================
  // CHECKINS: Check-in / Feed / Reactions / Comments
  // =========================================================================

  /// GET /checkins/feed — Lấy feed check-in công khai
  static Future<List<dynamic>> getFeed() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/checkins/feed'), headers: _getHeaders());
      final data = jsonDecode(response.body);
      if (data['success'] == true) {
        return data['feed'] ?? [];
      }
    } catch (_) {}
    return [];
  }

  /// GET /checkins/my — Lịch sử check-in của tôi (cần đăng nhập)
  static Future<List<dynamic>> getMyCheckins() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/checkins/my'), headers: _getHeaders());
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return data['checkins'] ?? [];
      }
    } catch (_) {}
    return [];
  }

  /// POST /checkins — Gửi check-in mới (hỗ trợ cả khách vãng lai)
  static Future<Map<String, dynamic>> storeCheckin({
    required int eateryId,
    required int rating,
    required String comment,
    String? guestName,
    String? imagePath,
  }) async {
    try {
      final request = http.MultipartRequest('POST', Uri.parse('$baseUrl/checkins'));

      // Add text fields
      request.fields['eatery_id'] = eateryId.toString();
      request.fields['rating'] = rating.toString();
      request.fields['comment'] = comment;
      if (guestName != null && guestName.isNotEmpty) {
        request.fields['guest_name'] = guestName;
      }

      // Add headers
      final headers = _getHeaders();
      headers.forEach((key, value) {
        request.headers[key] = value;
      });

      // Attach image file
      if (imagePath != null && imagePath.isNotEmpty) {
        request.files.add(await http.MultipartFile.fromPath('image', imagePath));
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      final data = jsonDecode(response.body);
      if (response.statusCode == 201 && data['success'] == true) {
        return {'success': true, 'message': 'Đăng check-in thành công'};
      }
      return {'success': false, 'message': data['message'] ?? 'Lỗi không xác định'};
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối mạng'};
    }
  }

  /// POST /checkins/comments — Gửi bình luận
  static Future<Map<String, dynamic>> storeComment({
    required int commentableId,
    required String commentableType,
    required String content,
    String? guestName,
  }) async {
    try {
      final body = {
        'commentable_id': commentableId,
        'commentable_type': commentableType,
        'content': content,
      };
      if (guestName != null && guestName.isNotEmpty) {
        body['guest_name'] = guestName;
      }

      final response = await http.post(
        Uri.parse('$baseUrl/checkins/comments'),
        headers: _getHeaders(),
        body: jsonEncode(body),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 201 && data['success'] == true) {
        return {'success': true, 'comment': data['comment']};
      }
      return {'success': false, 'message': data['message'] ?? 'Gửi bình luận thất bại'};
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối mạng'};
    }
  }

  /// POST /checkins/{id}/react — React emoji vào check-in
  static Future<bool> reactToCheckin(int id, String emoji, String type) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/checkins/$id/react'),
        headers: _getHeaders(),
        body: jsonEncode({
          'emoji': emoji,
          'type': type,
        }),
      );
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  // =========================================================================
  // CHAT: Bạn bè & Tin nhắn
  // =========================================================================

  /// GET /friends — Danh sách bạn bè (cần đăng nhập)
  static Future<List<dynamic>> getFriends() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/friends'), headers: _getHeaders());
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return [];
  }

  /// GET /messages/{friendId} — Lịch sử tin nhắn với bạn
  static Future<Map<String, dynamic>> getMessages(int friendId, {int? beforeId}) async {
    try {
      String url = '$baseUrl/messages/$friendId';
      if (beforeId != null) {
        url += '?before_id=$beforeId';
      }
      final response = await http.get(Uri.parse(url), headers: _getHeaders());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return {
          'messages': data['messages'] ?? [],
          'has_more': data['has_more'] ?? false,
        };
      }
    } catch (_) {}
    return {'messages': [], 'has_more': false};
  }

  /// POST /messages — Gửi tin nhắn
  static Future<Map<String, dynamic>> sendMessage(int friendId, String message) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/messages'),
        headers: _getHeaders(),
        body: jsonEncode({
          'receiver_id': friendId,
          'message': message,
        }),
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['status'] == 'success') {
        return {'success': true, 'message': data['message']};
      }
      return {'success': false, 'message': data['message'] ?? 'Gửi tin thất bại'};
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối'};
    }
  }
}
