import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../../../core/app_constants.dart';
import '../../../../core/errors/failure.dart';
import '../models/post_dto.dart';

abstract class PostRemoteDataSource {
  Future<List<PostDto>> getFeed({int page = 1, String? filterType});
  Future<PostDto> createPost({
    required String title,
    required String content,
    required String type,
    List<String>? imagePaths,
  });
  Future<bool> toggleLike(String postId);
}

class PostRemoteDataSourceImpl implements PostRemoteDataSource {
  final http.Client client;

  PostRemoteDataSourceImpl({http.Client? client}) : client = client ?? http.Client();

  @override
  Future<List<PostDto>> getFeed({int page = 1, String? filterType}) async {
    try {
      final uri = Uri.parse('${AppConstants.baseUrl}/checkins/feed');
      final response = await client.get(uri, headers: {'Accept': 'application/json'});

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['feed'] is List) {
          final List list = data['feed'];
          return list.map((item) => PostDto.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
      return [];
    } catch (e) {
      throw ServerFailure('Không thể tải trang tin: $e');
    }
  }

  @override
  Future<PostDto> createPost({
    required String title,
    required String content,
    required String type,
    List<String>? imagePaths,
  }) async {
    try {
      final response = await client.post(
        Uri.parse('${AppConstants.apiBaseUrl}/posts'),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'title': title,
          'content': content,
          'type': type,
          'images': imagePaths ?? [],
        }),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 || response.statusCode == 201) {
        return PostDto.fromJson(data['post'] ?? data);
      } else {
        throw ServerFailure(data['message'] ?? 'Tạo bài viết thất bại');
      }
    } catch (e) {
      throw ServerFailure('Lỗi tạo bài viết: $e');
    }
  }

  @override
  Future<bool> toggleLike(String postId) async {
    try {
      final response = await client.post(
        Uri.parse('${AppConstants.apiBaseUrl}/posts/$postId/like'),
        headers: {'Accept': 'application/json'},
      );
      final data = jsonDecode(response.body);
      return data['success'] == true;
    } catch (_) {
      return false;
    }
  }
}
