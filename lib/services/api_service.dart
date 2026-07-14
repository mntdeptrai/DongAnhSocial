import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String baseUrl = 'https://donganhdiscovery.xadonganh.com/api/v1';
  static String? _token;
  static Map<String, dynamic>? currentUser;

  // Initialize and load token
  static Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    final userJson = prefs.getString('current_user');
    if (userJson != null) {
      currentUser = jsonDecode(userJson);
    }
  }

  static bool get isAuthenticated => _token != null;

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

  // Auth: Login
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

  // Auth: Register
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

  // Auth: Logout
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

  // Map: Fetch categories
  static Future<List<dynamic>> getCategories() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/categories'), headers: _getHeaders());
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return [];
  }

  // Map: Fetch eateries by category
  static Future<List<dynamic>> getEateries(String categorySlug) async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/$categorySlug/eateries'), headers: _getHeaders());
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is List) return data;
        if (data is Map && data.containsKey('eateries')) return data['eateries'];
      }
    } catch (_) {}
    return [];
  }

  // Checkins: Feed
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

  // Checkins: My History
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

  // Checkins: Submit Check-in
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

  // Checkins: Submit Comment
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

  // Checkins: React with emoji
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

  // Chat: Get friends presence
  static Future<List<dynamic>> getFriends() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/friends'), headers: _getHeaders());
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
    } catch (_) {}
    return [];
  }

  // Chat: Get messages history with a friend
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

  // Chat: Send message
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
