import 'package:dio/dio.dart';

import '../models/chat_model.dart';
import '../models/message_model.dart';
import 'base_client.dart';

class ChatService {
  ChatService({Dio? dio}) : _dio = dio ?? BaseClient.dio;

  final Dio _dio;

  Future<ChatModel> getChatByComplaint(int complaintId) async {
    try {
      final response = await _dio.get<dynamic>('/chats/complaint/$complaintId');
      return _parseChatFromResponse(response.data);
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } on FormatException {
      throw Exception('تعذر قراءة بيانات المحادثة');
    }
  }

  Future<List<MessageModel>> getMessages(int chatId) async {
    try {
      final response = await _dio.get<dynamic>('/chats/$chatId/messages');
      final raw = _unwrapList(response.data);
      return raw.map(_messageFromDynamic).toList();
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } on FormatException {
      throw Exception('تعذر قراءة قائمة الرسائل');
    }
  }

  Future<MessageModel> sendMessage(MessageModel message) async {
    try {
      final payload = Map<String, dynamic>.from(message.toJson())
        ..removeWhere((_, v) => v == null);
      final response = await _dio.post<dynamic>('/messages', data: payload);
      return _parseMessageFromResponse(response.data);
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } on FormatException {
      throw Exception('تعذر قراءة بيانات الرسالة');
    }
  }

  static List<dynamic> _unwrapList(dynamic body) {
    if (body == null) {
      throw const FormatException('Empty response body');
    }
    if (body is List<dynamic>) {
      return body;
    }
    if (body is Map) {
      final data = body['data'];
      if (data is List<dynamic>) {
        return data;
      }
    }
    throw const FormatException(
      'Expected a JSON array or an object with a "data" array',
    );
  }

  static ChatModel _parseChatFromResponse(dynamic body) {
    if (body == null) {
      throw const FormatException('Empty response body');
    }
    if (body is! Map) {
      throw const FormatException('Expected a JSON object');
    }
    final map = Map<String, dynamic>.from(body);

    final data = map['data'];
    if (data is Map) {
      return ChatModel.fromJson(Map<String, dynamic>.from(data));
    }

    final chat = map['chat'];
    if (chat is Map) {
      return ChatModel.fromJson(Map<String, dynamic>.from(chat));
    }

    if (map.containsKey('chat_id') ||
        map.containsKey('complain_id') ||
        map.containsKey('user_id')) {
      return ChatModel.fromJson(map);
    }

    throw const FormatException(
      'Expected chat under "data"/"chat" or flat keys',
    );
  }

  static MessageModel _messageFromDynamic(dynamic item) {
    if (item is! Map) {
      throw const FormatException('Message item must be an object');
    }
    return MessageModel.fromJson(Map<String, dynamic>.from(item));
  }

  static MessageModel _parseMessageFromResponse(dynamic body) {
    if (body == null) {
      throw const FormatException('Empty response body');
    }
    if (body is! Map) {
      throw const FormatException('Expected a JSON object');
    }
    final map = Map<String, dynamic>.from(body);

    final data = map['data'];
    if (data is Map) {
      return MessageModel.fromJson(Map<String, dynamic>.from(data));
    }

    final message = map['message'];
    if (message is Map) {
      return MessageModel.fromJson(Map<String, dynamic>.from(message));
    }

    if (map.containsKey('message_id') ||
        map.containsKey('chat_id') ||
        map.containsKey('sender_id')) {
      return MessageModel.fromJson(map);
    }

    throw const FormatException(
      'Expected message under "data"/"message" or flat keys',
    );
  }
}
