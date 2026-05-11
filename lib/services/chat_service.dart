import 'package:dio/dio.dart';
import '../models/message_model.dart';
import '../core/constants/api_constants.dart';
import 'base_client.dart';
import 'dio_client.dart';

class ChatService {
  ChatService({Dio? dio}) : _dio = dio ?? DioClient.instance.dio;

  final Dio _dio;

  // ──────────────────────────────────────────────
  // جلب سجل الرسائل
  // ──────────────────────────────────────────────
  Future<List<MessageModel>> getHistory(int complaintId) async {
    try {
      final response = await _dio.get<dynamic>(
        ApiConstants.chatHistory(complaintId),
      );
      final raw = _unwrapList(response.data);
      return raw.map(_messageFromDynamic).toList();
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } catch (e) {
      throw Exception('تعذر قراءة قائمة الرسائل: $e');
    }
  }

  // ──────────────────────────────────────────────
  // إرسال رسالة
  // ──────────────────────────────────────────────
  Future<MessageModel> sendMessage({
    required int complaintId,
    required String message,
  }) async {
    try {
      final response = await _dio.post<dynamic>(
        ApiConstants.sendMessage(complaintId),
        data: <String, dynamic>{
          'complaint_id': complaintId,
          'message': message,
        },
      );
      return _parseMessageFromResponse(response.data);
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } catch (e) {
      throw Exception('فشل إرسال الرسالة');
    }
  }

  // ──────────────────────────────────────────────
  // ──────────────────────────────────────────────
  Future<void> updateChatStatus(int complaintId, bool isClosed) async {
    try {
      const String path = '/chat/toggle-status';

      await _dio.post<dynamic>(
        path,
        data: <String, dynamic>{
          'complaint_id': complaintId,
          'is_closed': isClosed ? 1 : 0,
        },
      );
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } catch (e) {
      throw Exception('فشل تحديث حالة المحادثة');
    }
  }

  static List<dynamic> _unwrapList(dynamic body) {
    if (body == null) throw const FormatException('الرد فارغ من السيرفر');

    if (body is Map) {
      final map = Map<String, dynamic>.from(body);
      if (map['data'] is List) return map['data'] as List;
      if (map['data'] is Map) {
        final nested = map['data'] as Map;
        if (nested['messages'] is List) return nested['messages'] as List;
      }
    }
    if (body is List) return body;
    throw const FormatException('تنسيق قائمة الرسائل غير مدعوم');
  }

  static MessageModel _parseMessageFromResponse(dynamic body) {
    if (body == null) throw const FormatException('الرد فارغ');
    final map = Map<String, dynamic>.from(body is Map ? body : {});
    final data = map['data'];
    if (data is Map) {
      return MessageModel.fromJson(Map<String, dynamic>.from(data));
    }
    return MessageModel.fromJson(map);
  }

  static MessageModel _messageFromDynamic(dynamic item) {
    return MessageModel.fromJson(Map<String, dynamic>.from(item as Map));
  }
}
