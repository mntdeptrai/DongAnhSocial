import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../../../core/app_constants.dart';
import '../../../../core/errors/failure.dart';
import '../models/news_article_dto.dart';

abstract class NewsRemoteDataSource {
  Future<List<NewsArticleDto>> getNewsArticles({int page = 1});
  Future<NewsArticleDto> getArticleDetail(int id);
}

class NewsRemoteDataSourceImpl implements NewsRemoteDataSource {
  final http.Client client;

  NewsRemoteDataSourceImpl({http.Client? client}) : client = client ?? http.Client();

  @override
  Future<List<NewsArticleDto>> getNewsArticles({int page = 1}) async {
    try {
      final response = await client.get(
        Uri.parse('${AppConstants.apiBaseUrl}/news?page=$page'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['news'] is List) {
          final List list = data['news'];
          return list.map((item) => NewsArticleDto.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
      return [];
    } catch (e) {
      throw ServerFailure('Không thể tải tin tức: $e');
    }
  }

  @override
  Future<NewsArticleDto> getArticleDetail(int id) async {
    try {
      final response = await client.get(
        Uri.parse('${AppConstants.apiBaseUrl}/news/$id'),
        headers: {'Accept': 'application/json'},
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['success'] == true) {
        return NewsArticleDto.fromJson(data['article'] ?? data);
      } else {
        throw ServerFailure(data['message'] ?? 'Không tìm thấy tin tức');
      }
    } catch (e) {
      throw ServerFailure('Lỗi tải bài viết: $e');
    }
  }
}
