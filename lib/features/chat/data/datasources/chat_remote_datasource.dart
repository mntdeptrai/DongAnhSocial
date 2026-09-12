import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../../../core/app_constants.dart';
import '../../../../core/errors/failure.dart';
import '../models/chat_message_dto.dart';

abstract class ChatRemoteDataSource {
  Future<List<ChatMessageDto>> getMessages(int otherUserId);
  Future<ChatMessageDto> sendMessage(int receiverId, String content);
}

class ChatRemoteDataSourceImpl implements ChatRemoteDataSource {
  final http.Client client;

  ChatRemoteDataSourceImpl({http.Client? client}) : client = client ?? http.Client();

  @override
  Future<List<ChatMessageDto>> getMessages(int otherUserId) async {
    try {
      final response = await client.get(
        Uri.parse('${AppConstants.apiBaseUrl}/messages/$otherUserId'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true && data['messages'] is List) {
          final List list = data['messages'];
          return list.map((item) => ChatMessageDto.fromJson(item as Map<String, dynamic>)).toList();
        }
      }
      return [];
    } catch (e) {
      throw ServerFailure('Không thể tải tin nhắn: $e');
    }
  }

  @override
  Future<ChatMessageDto> sendMessage(int receiverId, String content) async {
    try {
      final response = await client.post(
        Uri.parse('${AppConstants.apiBaseUrl}/messages'),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'receiver_id': receiverId,
          'message': content,
        }),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200 || response.statusCode == 201) {
        return ChatMessageDto.fromJson(data['data'] ?? data['message'] ?? data);
      } else {
        throw ServerFailure(data['message'] ?? 'Gửi tin nhắn thất bại');
      }
    } catch (e) {
      throw ServerFailure('Lỗi gửi tin nhắn: $e');
    }
  }
}
