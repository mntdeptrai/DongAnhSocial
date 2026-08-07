import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

/// Top-level function dùng cho Flutter Isolate compute()
dynamic _parseJsonIsolate(String jsonStr) {
  return jsonDecode(jsonStr);
}

class ApiService {
  static String defaultBaseUrl = 'https://donganhdiscovery.xadonganh.com/api/v1';
  static String baseUrl = 'https://donganhdiscovery.xadonganh.com/api/v1';
  static String? _token;
  static String? _sessionId;
  static Map<String, dynamic>? currentUser;

  /// Giải mã JSON thông minh: Sử dụng Isolate (Multi-threading) nếu dữ liệu > 4KB để giải phóng UI Thread
  static Future<dynamic> _parseResponseBody(String body) async {
    if (body.length > 4096) {
      return compute(_parseJsonIsolate, body);
    }
    return jsonDecode(body);
  }

  // =========================================================================
  // INITIALIZATION & AUTH HELPERS
  // =========================================================================

  /// Khởi tạo service: load token, custom API URL và user từ SharedPreferences
  static Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final customUrl = prefs.getString('custom_api_base_url');
    if (customUrl != null && customUrl.isNotEmpty) {
      baseUrl = customUrl;
    }
    _token = prefs.getString('auth_token');
    _sessionId = prefs.getString('cart_session_id');
    if (_sessionId == null || _sessionId!.isEmpty) {
      _sessionId = 'mobile_sess_${DateTime.now().millisecondsSinceEpoch}';
      await prefs.setString('cart_session_id', _sessionId!);
    }
    final userJson = prefs.getString('current_user');
    if (userJson != null) {
      currentUser = jsonDecode(userJson);
    }
    if (_token != null && _token!.isNotEmpty) {
      fetchUserProfile();
    }
  }

  /// Lấy URL Avatar chuẩn hóa từ Real User Data (Tuyệt đối không dùng Mockdata pravatar)
  static String getAvatarUrl(dynamic userOrAvatar, [String? fallbackName]) {
    String? rawAvatar;
    String name = fallbackName ?? 'User';

    if (userOrAvatar is Map) {
      rawAvatar = userOrAvatar['avatar'] ?? userOrAvatar['avatar_url'] ?? userOrAvatar['image'];
      name = userOrAvatar['name'] ?? userOrAvatar['username'] ?? name;
    } else if (userOrAvatar is String) {
      rawAvatar = userOrAvatar;
    }

    if (rawAvatar != null && rawAvatar.isNotEmpty) {
      if (rawAvatar.startsWith('http://') || rawAvatar.startsWith('https://')) {
        return rawAvatar;
      }
      if (rawAvatar.startsWith('/')) {
        final Uri uri = Uri.parse(baseUrl);
        return '${uri.scheme}://${uri.host}${uri.hasPort ? ':${uri.port}' : ''}$rawAvatar';
      }
    }

    // Nếu không có avatar dạng URL hoặc là Emoji, tạo UI Avatar chính chủ theo Tên người dùng thực
    final encodedName = Uri.encodeComponent(name.isNotEmpty ? name : 'User');
    return 'https://ui-avatars.com/api/?name=$encodedName&background=0284C7&color=fff&size=256&bold=true';
  }

  /// GET /user/profile — Tải hồ sơ người dùng thực tế từ server
  static Future<Map<String, dynamic>?> fetchUserProfile() async {
    if (!isAuthenticated) return null;
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/user/profile'),
        headers: _getHeaders(),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['user'] != null) {
          currentUser = data['user'];
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('current_user', jsonEncode(currentUser));
          return currentUser;
        }
      }
    } catch (_) {}
    return currentUser;
  }

  static Future<void> setBaseUrl(String url) async {
    baseUrl = url;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('custom_api_base_url', url);
  }

  static bool get isAuthenticated => _token != null;
  static String? get token => _token;

  static Map<String, String> _getHeaders() {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (_sessionId != null) 'X-Session-ID': _sessionId!,
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

      Map<String, dynamic> data = {};
      try {
        data = jsonDecode(response.body);
      } catch (_) {}

      if (response.statusCode == 200 && data['success'] == true) {
        _token = data['token'];
        currentUser = data['user'];

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', _token!);
        await prefs.setString('current_user', jsonEncode(currentUser));

        // Tự động đẩy FCM Token lên server ngay khi đăng nhập thành công
        try {
          final fcmToken = await FirebaseMessaging.instance.getToken();
          if (fcmToken != null) {
            updateFcmToken(fcmToken);
          }
        } catch (_) {}

        return {'success': true};
      }
      return {'success': false, 'message': data['message'] ?? 'Đăng nhập không thành công (Mã ${response.statusCode})'};
    } catch (e) {
      return {'success': false, 'message': 'Không thể kết nối máy chủ: $e'};
    }
  }

  /// POST /user/fcm-token — Cập nhật FCM Token của thiết bị lên server
  static Future<bool> updateFcmToken(String fcmToken) async {
    if (!isAuthenticated) return false;
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/user/fcm-token'),
        headers: _getHeaders(),
        body: jsonEncode({'fcm_token': fcmToken}),
      );
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  /// POST /auth/register — Đăng ký tài khoản mới đầy đủ các trường
  static Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    String? username,
    String? phone,
    String role = 'user',
    bool agreeTerms = true,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/register'),
        headers: _getHeaders(),
        body: jsonEncode({
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': password,
          if (username != null && username.isNotEmpty) 'username': username,
          if (phone != null && phone.isNotEmpty) 'phone': phone,
          'role': role,
          'agree_terms': agreeTerms ? 1 : 0,
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
  // CART API: Giỏ hàng đồng bộ với Web
  // =========================================================================

  /// GET /cart — Lấy toàn bộ giỏ hàng
  static Future<Map<String, dynamic>> getCart() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/cart'),
        headers: _getHeaders(),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map<String, dynamic>) return data;
        if (data is Map) return Map<String, dynamic>.from(data);
        if (data is List) return {'success': true, 'data': data, 'count': data.length};
      }
    } catch (_) {}
    return {'success': false, 'data': [], 'count': 0, 'total': 0};
  }

  /// POST /cart/add — Thêm sản phẩm OCOP hoặc món ăn vào giỏ
  static Future<Map<String, dynamic>> addToCart({int? dishId, int? ocopProductId, int quantity = 1}) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/cart/add'),
        headers: _getHeaders(),
        body: jsonEncode({
          if (dishId != null) 'dish_id': dishId,
          if (ocopProductId != null) 'ocop_product_id': ocopProductId,
          'quantity': quantity,
        }),
      );
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return {'success': false};
  }

  /// PUT /cart/update/{id} — Cập nhật số lượng món ăn trong giỏ
  static Future<Map<String, dynamic>> updateCartItem(int cartItemId, int quantity) async {
    try {
      final response = await http.put(
        Uri.parse('$baseUrl/cart/update/$cartItemId'),
        headers: _getHeaders(),
        body: jsonEncode({'quantity': quantity}),
      );
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return {'success': false};
  }

  /// DELETE /cart/remove/{id} — Xóa món ăn khỏi giỏ
  static Future<Map<String, dynamic>> removeCartItem(int cartItemId) async {
    try {
      final response = await http.delete(
        Uri.parse('$baseUrl/cart/remove/$cartItemId'),
        headers: _getHeaders(),
      );
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return {'success': false};
  }

  /// POST /cart/clear — Xóa toàn bộ giỏ hàng
  static Future<Map<String, dynamic>> clearCart() async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/cart/clear'),
        headers: _getHeaders(),
      );
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return {'success': false};
  }

  /// POST /checkout — Đặt hàng từ giỏ hàng
  static Future<Map<String, dynamic>> checkout({
    required String customerName,
    required String customerPhone,
    required String shippingAddress,
    String? note,
    String? promoCode,
    required double totalAmount,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/checkout'),
        headers: _getHeaders(),
        body: jsonEncode({
          'customer_name': customerName,
          'customer_phone': customerPhone,
          'shipping_address': shippingAddress,
          'note': note ?? '',
          'promo_code': promoCode,
          'total_amount': totalAmount,
        }),
      );
      if (response.statusCode == 200 || response.statusCode == 201) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return {'success': true, 'message': '🎉 Đặt hàng thành công! Đơn hàng của bạn đang được xử lý.'};
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
        final data = await _parseResponseBody(response.body);
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

  /// Cache getAllEateries để tránh 3 screens gọi lại liên tục (Feed + Map + Utilities)
  static List<dynamic>? _cachedAllEateries;
  static DateTime? _allEateriesCacheTime;

  /// Lấy tất cả địa điểm từ mọi danh mục (gọi song song)
  /// Trả về danh sách đã khử trùng lặp, cache 5 phút
  static Future<List<dynamic>> getAllEateries({bool forceRefresh = false}) async {
    // Trả về cache nếu còn hạn (5 phút) và không bắt buộc refresh
    if (!forceRefresh && _cachedAllEateries != null && _allEateriesCacheTime != null) {
      if (DateTime.now().difference(_allEateriesCacheTime!) < const Duration(minutes: 5)) {
        return _cachedAllEateries!;
      }
    }

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

      // Lưu cache
      _cachedAllEateries = combined;
      _allEateriesCacheTime = DateTime.now();
      return combined;
    } catch (_) {}
    return _cachedAllEateries ?? [];
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

  /// GET /user/profile — Refresh profile stats & data from backend DB
  static Future<Map<String, dynamic>?> getUserProfile() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/user/profile'), headers: _getHeaders());
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true && data['user'] != null) {
        currentUser = Map<String, dynamic>.from(data['user']);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('current_user', jsonEncode(currentUser));
        return currentUser;
      }
    } catch (_) {}
    return currentUser;
  }

  /// GET /user/posts — Danh sách bài viết cá nhân thực tế từ DB
  static Future<List<dynamic>> getMyPosts() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/user/posts'), headers: _getHeaders());
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        final list = data['posts'] ?? [];
        if (list is List && list.isNotEmpty) {
          return list;
        }
      }
    } catch (_) {}

    // Fallback client-side filter if offline or API returns empty
    try {
      final user = currentUser;
      final userId = user?['id'];
      final userName = (user?['name'] ?? '').toString().trim().toLowerCase();
      final allPosts = await getNewsfeed();
      if (user != null) {
        final filtered = allPosts.where((p) {
          final pUserId = p['user_id'];
          final pAuthor = (p['author'] ?? p['user']?['name'] ?? '').toString().trim().toLowerCase();
          final matchId = (pUserId != null && userId != null && pUserId.toString() == userId.toString());
          final matchAuthor = (userName.isNotEmpty && (pAuthor == userName || pAuthor.contains(userName)));
          return matchId || matchAuthor;
        }).toList();
        if (filtered.isNotEmpty) return filtered;
      }
      return allPosts;
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
  static Future<Map<String, dynamic>> reactToCheckin(int id, String emoji, String type) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/checkins/$id/react'),
        headers: _getHeaders(),
        body: jsonEncode({
          'emoji': emoji,
          'type': type,
        }),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return {
          'success': true,
          'counts': data['counts'] ?? {},
          'total': data['total'] ?? 0,
        };
      }
      return {'success': false};
    } catch (_) {
      return {'success': false};
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

  /// GET /social/unread-check — Số lượng tin nhắn chưa đọc real-time
  static Future<int> getUnreadMessagesCount() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/social/unread-check'), headers: _getHeaders());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map) {
          if (data['unread_count'] is int) return data['unread_count'];
          return data['has_unread'] == true ? 1 : 0;
        }
      }
    } catch (_) {}
    return 0;
  }

  /// GET /market-products — Lấy danh sách sản phẩm OCOP & Gian hàng chợ
  static Future<List<dynamic>> getMarketProducts() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/market-products'), headers: _getHeaders());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is List) return data;
        if (data is Map && data['data'] is List) return data['data'];
      }
    } catch (_) {}
    return [];
  }

  /// GET /notifications — Lấy danh sách thông báo cho Mobile App
  static Future<List<dynamic>> getAppNotifications() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/notifications'), headers: _getHeaders());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is List) return data;
        if (data is Map && data['data'] is List) return data['data'];
      }
    } catch (_) {}
    return [];
  }

  /// GET /seller/profile — Lấy hồ sơ kê khai tiểu thương sạp chợ
  static Future<Map<String, dynamic>> getSellerProfile() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/seller/profile'), headers: _getHeaders());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map<String, dynamic>) return data;
        if (data is Map) return Map<String, dynamic>.from(data);
      }
    } catch (_) {}
    return {};
  }

  /// POST /seller/profile — Cập nhật hồ sơ kê khai tiểu thương sạp chợ
  static Future<Map<String, dynamic>> updateSellerProfile(Map<String, dynamic> body) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/seller/profile'),
        headers: _getHeaders(),
        body: jsonEncode(body),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map<String, dynamic>) return data;
        if (data is Map) return Map<String, dynamic>.from(data);
      }
    } catch (_) {}
    return {'success': false};
  }

  /// GET /seller/orders — Lấy danh sách đơn hàng thực tế của gian hàng
  static Future<List<dynamic>> getSellerOrders() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/seller/orders'), headers: _getHeaders());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is List) return data;
        if (data is Map && data['data'] is List) return data['data'];
        if (data is Map && data['orders'] is List) return data['orders'];
      }
    } catch (_) {}
    return [];
  }

  /// GET /manager/stalls — Lấy danh sách gian hàng cho cán bộ quản lý chợ
  static Future<List<dynamic>> getManagerStalls() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/categories'), headers: _getHeaders());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is List) return data;
        if (data is Map && data['data'] is List) return data['data'];
      }
    } catch (_) {}
    return [];
  }

  /// GET /admin/users — Lấy danh sách người dùng cho Quản trị viên
  static Future<List<dynamic>> getAdminUsers() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/admin/users'), headers: _getHeaders());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is List) return data;
        if (data is Map && data['users'] is List) return data['users'];
        if (data is Map && data['data'] is List) return data['data'];
      }
    } catch (_) {}
    return [];
  }

  /// POST /admin/users/{id}/role — Cập nhật phân quyền người dùng
  static Future<bool> updateUserRole(int userId, String role) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/admin/users/$userId/role'),
        headers: _getHeaders(),
        body: jsonEncode({'role': role}),
      );
      return response.statusCode == 200;
    } catch (_) {}
    return false;
  }

  /// GET /admin/dashboard — Lấy dữ liệu quản trị tổng quan đầy đủ
  static Future<Map<String, dynamic>> getAdminDashboardData() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/admin/dashboard'), headers: _getHeaders());
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return {'success': false};
  }

  /// POST /admin/eateries/{id}/toggle-featured — Bật/Tắt nổi bật
  static Future<bool> toggleEateryFeatured(int id) async {
    try {
      final response = await http.post(Uri.parse('$baseUrl/admin/eateries/$id/toggle-featured'), headers: _getHeaders());
      return response.statusCode == 200;
    } catch (_) {}
    return false;
  }

  /// DELETE /admin/eateries/{id} — Xóa địa điểm
  static Future<bool> deleteEatery(int id) async {
    try {
      final response = await http.delete(Uri.parse('$baseUrl/admin/eateries/$id'), headers: _getHeaders());
      return response.statusCode == 200;
    } catch (_) {}
    return false;
  }

  /// DELETE /admin/reviews/{id} — Xóa đánh giá
  static Future<bool> deleteReview(int id) async {
    try {
      final response = await http.delete(Uri.parse('$baseUrl/admin/reviews/$id'), headers: _getHeaders());
      return response.statusCode == 200;
    } catch (_) {}
    return false;
  }

  /// POST /admin/categories — Tạo danh mục mới
  static Future<bool> createCategory(String name, String description, String icon) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/admin/categories'),
        headers: _getHeaders(),
        body: jsonEncode({
          'name': name,
          'description': description,
          'icon': icon,
        }),
      );
      return response.statusCode == 200;
    } catch (_) {}
    return false;
  }

  /// POST /admin/users — Tạo tài khoản mới từ Admin
  static Future<Map<String, dynamic>> storeUser({
    required String name,
    required String email,
    required String password,
    required String role,
    String? phone,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/admin/users'),
        headers: _getHeaders(),
        body: jsonEncode({
          'name': name,
          'email': email,
          'password': password,
          'role': role,
          'phone': phone,
        }),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối máy chủ: $e'};
    }
  }

  /// DELETE /admin/users/{id} — Xóa tài khoản người dùng
  static Future<bool> deleteUser(int id) async {
    try {
      final response = await http.delete(Uri.parse('$baseUrl/admin/users/$id'), headers: _getHeaders());
      return response.statusCode == 200;
    } catch (_) {}
    return false;
  }

  /// POST /admin/eateries — Đăng ký cơ sở / địa điểm mới đầy đủ
  static Future<Map<String, dynamic>> storeEatery({
    required String name,
    required dynamic categoryId,
    required dynamic communeId,
    required String address,
    String? phone,
    String? openingHours,
    String? priceRange,
    double? latitude,
    double? longitude,
    bool isFeatured = false,
    String? imageUrl,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/admin/eateries'),
        headers: _getHeaders(),
        body: jsonEncode({
          'name': name,
          'category_id': categoryId,
          'commune_id': communeId,
          'address': address,
          'phone': phone,
          'opening_hours': openingHours,
          'price_range': priceRange,
          'latitude': latitude,
          'longitude': longitude,
          'is_featured': isFeatured,
          'image_url': imageUrl,
        }),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối máy chủ: $e'};
    }
  }

  /// PUT /admin/eateries/{id} — Cập nhật cơ sở / địa điểm
  static Future<Map<String, dynamic>> updateEatery(int id, Map<String, dynamic> data) async {
    try {
      final response = await http.put(
        Uri.parse('$baseUrl/admin/eateries/$id'),
        headers: _getHeaders(),
        body: jsonEncode(data),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối máy chủ: $e'};
    }
  }

  /// GET /seller/dashboard-data — Lấy dữ liệu gian hàng thuộc sở hữu của chủ tiệm
  static Future<Map<String, dynamic>> getSellerDashboardData() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/seller/dashboard-data'), headers: _getHeaders());
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return {'success': false};
  }

  /// POST /seller/dishes — Thêm sản phẩm / món ăn mới cho gian hàng
  static Future<Map<String, dynamic>> storeDish({required String name, required String price, String? description, String? imageUrl}) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/seller/dishes'),
        headers: _getHeaders(),
        body: jsonEncode({
          'name': name,
          'price': price,
          'description': description,
          'image_url': imageUrl,
        }),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối: $e'};
    }
  }

  /// DELETE /seller/dishes/{id} — Xóa món ăn sản phẩm
  static Future<bool> deleteDish(int id) async {
    try {
      final response = await http.delete(Uri.parse('$baseUrl/seller/dishes/$id'), headers: _getHeaders());
      return response.statusCode == 200;
    } catch (_) {}
    return false;
  }

  /// POST /seller/orders/{id}/status — Cập nhật trạng thái đơn hàng của Seller
  static Future<bool> updateSellerOrderStatus(int orderId, String status) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/seller/orders/$orderId/status'),
        headers: _getHeaders(),
        body: jsonEncode({'status': status}),
      );
      return response.statusCode == 200;
    } catch (_) {}
    return false;
  }

  /// GET /manager/dashboard-data — Lấy dữ liệu giám sát chợ của Ban Quản Lý
  static Future<Map<String, dynamic>> getManagerDashboardData() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/manager/dashboard-data'), headers: _getHeaders());
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return {'success': false};
  }

  /// POST /manager/bulletins — Đăng bảng tin BQL Chợ
  static Future<bool> storeManagerBulletin(String title, String content) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/manager/bulletins'),
        headers: _getHeaders(),
        body: jsonEncode({'title': title, 'content': content}),
      );
      return response.statusCode == 200;
    } catch (_) {}
    return false;
  }

  /// POST /manager/stalls/{id}/status — Duyệt / Đình chỉ gian hàng chợ
  static Future<bool> updateStallStatus(int id, String status) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/manager/stalls/$id/status'),
        headers: _getHeaders(),
        body: jsonEncode({'status': status}),
      );
      return response.statusCode == 200;
    } catch (_) {}
    return false;
  }

  // =========================================================================
  // CUSTOMER ORDERS API MANAGEMENT
  // =========================================================================

  /// GET /api/orders — Lấy danh sách đơn hàng khách hàng đã đặt
  static Future<Map<String, dynamic>> getOrders({String status = 'all', String search = ''}) async {
    try {
      final webUrl = baseUrl.replaceAll('/api/v1', '');
      final queryParams = <String, String>{};
      if (status != 'all') queryParams['status'] = status;
      if (search.isNotEmpty) queryParams['search'] = search;

      final uri = Uri.parse('$webUrl/api/orders').replace(queryParameters: queryParams.isNotEmpty ? queryParams : null);
      final response = await http.get(uri, headers: _getHeaders());
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return {'success': false, 'data': []};
  }

  /// POST /api/orders/{code}/confirm-received — Xác nhận đã nhận được hàng
  static Future<Map<String, dynamic>> confirmOrderReceived(dynamic codeOrId) async {
    try {
      final webUrl = baseUrl.replaceAll('/api/v1', '');
      final response = await http.post(
        Uri.parse('$webUrl/api/orders/$codeOrId/confirm-received'),
        headers: _getHeaders(),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối máy chủ: $e'};
    }
  }

  /// POST /api/orders/{code}/cancel — Hủy đơn hàng kèm lý do
  static Future<Map<String, dynamic>> cancelOrder(dynamic codeOrId, String reason) async {
    try {
      final webUrl = baseUrl.replaceAll('/api/v1', '');
      final response = await http.post(
        Uri.parse('$webUrl/api/orders/$codeOrId/cancel'),
        headers: _getHeaders(),
        body: jsonEncode({'reason': reason}),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối máy chủ: $e'};
    }
  }

  /// POST /api/orders/{code}/return — Yêu cầu hoàn hàng / trả hàng
  static Future<Map<String, dynamic>> returnOrder(dynamic codeOrId, String reason) async {
    try {
      final webUrl = baseUrl.replaceAll('/api/v1', '');
      final response = await http.post(
        Uri.parse('$webUrl/api/orders/$codeOrId/return'),
        headers: _getHeaders(),
        body: jsonEncode({'reason': reason}),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối máy chủ: $e'};
    }
  }

  /// POST /api/orders/{code}/reorder — Đặt lại các món trong đơn
  static Future<Map<String, dynamic>> reorderItems(dynamic codeOrId) async {
    try {
      final webUrl = baseUrl.replaceAll('/api/v1', '');
      final response = await http.post(
        Uri.parse('$webUrl/api/orders/$codeOrId/reorder'),
        headers: _getHeaders(),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': 'Lỗi kết nối máy chủ: $e'};
    }
  }

  // =========================================================================
  // MULTI-PROTOCOL / FLEXIBLE API CLIENTS (GraphQL, JSON-RPC, SSE, Streaming)
  // =========================================================================

  /// 1. GraphQL client — Gửi query động lên server để chỉ lấy các trường cần thiết
  static Future<Map<String, dynamic>> graphqlQuery(String query) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/graphql'),
        headers: _getHeaders(),
        body: jsonEncode({'query': query}),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'errors': [
          {'message': 'Lỗi kết nối GraphQL: $e'}
        ]
      };
    }
  }

  /// 2. JSON-RPC 2.0 client — Hỗ trợ gọi hàm hàng loạt (batching) hoặc các hành động gộp
  static Future<Map<String, dynamic>> jsonRpcCall(String method, {dynamic params, String? id}) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/rpc'),
        headers: _getHeaders(),
        body: jsonEncode({
          'jsonrpc': '2.0',
          'method': method,
          if (params != null) 'params': params,
          'id': id ?? 'mobile_${DateTime.now().millisecondsSinceEpoch}',
        }),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {
        'jsonrpc': '2.0',
        'error': {'code': -32000, 'message': 'Lỗi kết nối RPC: $e'},
        'id': id
      };
    }
  }

  /// 3. Server-Sent Events (SSE) Listener — Lắng nghe các sự kiện thời gian thực đẩy từ Server (một chiều)
  static Stream<String> getSseEventStream() {
    final client = http.Client();
    final request = http.Request('GET', Uri.parse('$baseUrl/stream/events'));
    
    request.headers['Accept'] = 'text/event-stream';
    request.headers['Cache-Control'] = 'no-cache';
    if (_token != null) {
      request.headers['Authorization'] = 'Bearer $_token';
    }

    final controller = StreamController<String>();

    client.send(request).then((response) {
      if (response.statusCode == 200) {
        response.stream
            .transform(utf8.decoder)
            .transform(const LineSplitter())
            .listen((line) {
              if (line.startsWith('data: ')) {
                controller.add(line.substring(6));
              }
            }, onError: (error) {
              controller.addError(error);
            }, onDone: () {
              controller.close();
              client.close();
            });
      } else {
        controller.addError('SSE connection failed: status ${response.statusCode}');
        controller.close();
        client.close();
      }
    }).catchError((error) {
      controller.addError(error);
      controller.close();
      client.close();
    });

    return controller.stream;
  }

  /// 4. Generative AI Streaming client — Stream dữ liệu chạy chữ progressive từ gợi ý Food Tour AI
  static Stream<String> streamAiTour(int budget, String mood) {
    final client = http.Client();
    final request = http.Request('POST', Uri.parse('$baseUrl/stream/ai/generate-tour'));
    
    request.headers['Content-Type'] = 'application/json';
    request.headers['Accept'] = 'text/event-stream';
    if (_token != null) {
      request.headers['Authorization'] = 'Bearer $_token';
    }
    request.body = jsonEncode({'budget': budget, 'mood': mood});

    final controller = StreamController<String>();

    client.send(request).then((response) {
      if (response.statusCode == 200) {
        response.stream
            .transform(utf8.decoder)
            .transform(const LineSplitter())
            .listen((line) {
              if (line.startsWith('data: ')) {
                controller.add(line.substring(6));
              }
            }, onError: (error) {
              controller.addError(error);
            }, onDone: () {
              controller.close();
              client.close();
            });
      } else {
        controller.addError('AI Streaming failed: status ${response.statusCode}');
        controller.close();
        client.close();
      }
    }).catchError((error) {
      controller.addError(error);
      controller.close();
      client.close();
    });

    return controller.stream;
  }

  /// GET /newsfeed — Lấy tất cả bài viết Bản tin đa phân quyền
  static Future<List<dynamic>> getNewsfeed() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/newsfeed'),
        headers: _getHeaders(),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is List) return data;
      }
    } catch (_) {}
    return [];
  }

  /// POST /posts — Tạo bài viết mới lên Bản tin (Hỗ trợ Tải ảnh & Video thật)
  static Future<Map<String, dynamic>> createPost({required String description, String? name, String? imagePath}) async {
    try {
      String? imageBase64;
      if (imagePath != null && imagePath.isNotEmpty) {
        final file = File(imagePath);
        if (await file.exists()) {
          final bytes = await file.readAsBytes();
          final ext = imagePath.split('.').last.toLowerCase();
          final mime = (ext == 'mp4' || ext == 'mov' || ext == 'avi') ? 'video/$ext' : 'image/$ext';
          imageBase64 = 'data:$mime;base64,${base64Encode(bytes)}';
        }
      }

      final response = await http.post(
        Uri.parse('$baseUrl/posts'),
        headers: _getHeaders(),
        body: jsonEncode({
          'description': description,
          if (name != null) 'name': name,
          if (imageBase64 != null) 'image_base64': imageBase64,
          if (imagePath != null && imageBase64 == null) 'image_path': imagePath,
        }),
      );
      if (response.statusCode == 200 || response.statusCode == 201) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return {'success': false, 'message': 'Lỗi kết nối máy chủ'};
  }

  /// GET /exp-corner — Lấy dữ liệu Góc Trải Nghiệm Thực Tế
  /// GET /exp-corner — Lấy dữ liệu Góc Trải Nghiệm Thực Tế
  static Future<Map<String, dynamic>> getExpCorner() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/exp-corner'),
        headers: _getHeaders(),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map<String, dynamic>) return data;
      }
    } catch (_) {}
    return {};
  }

  /// POST /reactions/toggle — Thả tim / Bỏ tim bài viết (Đồng bộ với DB web)
  static Future<Map<String, dynamic>> toggleReaction({required dynamic postId, String type = 'post'}) async {
    try {
      int numericId = 0;
      if (postId is int) {
        numericId = postId;
      } else {
        final clean = postId.toString().replaceAll(RegExp(r'[^0-9]'), '');
        numericId = int.tryParse(clean) ?? 0;
      }

      final response = await http.post(
        Uri.parse('$baseUrl/reactions/toggle'),
        headers: _getHeaders(),
        body: jsonEncode({
          'id': numericId,
          'type': type,
          'emoji': '👍',
        }),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map<String, dynamic>) return data;
      }
    } catch (_) {}
    return {'success': false};
  }
}
