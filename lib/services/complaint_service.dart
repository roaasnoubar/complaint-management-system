import 'package:dio/dio.dart';

import '../models/complaint_model.dart';
import 'base_client.dart';

class ComplaintService {
  ComplaintService({Dio? dio}) : _dio = dio ?? BaseClient.dio;

  final Dio _dio;

  Future<ComplaintModel> createComplaint(ComplaintModel complaint) async {
    try {
      final payload = Map<String, dynamic>.from(complaint.toJson())
        ..removeWhere((_, v) => v == null);
      final response = await _dio.post<dynamic>('/complaints', data: payload);
      return _parseComplaintFromResponse(response.data);
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } on FormatException {
      throw Exception('تعذر قراءة بيانات الشكوى');
    }
  }

  Future<List<ComplaintModel>> getMyComplaints() async {
    try {
      final response = await _dio.get<dynamic>('/my-complaints');
      final raw = _unwrapList(response.data);
      return raw.map(_complaintFromDynamic).toList();
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } on FormatException {
      throw Exception('تعذر قراءة قائمة الشكاوى');
    }
  }

  Future<List<ComplaintModel>> getAuthorityComplaints() async {
    try {
      final response = await _dio.get<dynamic>('/authority/complaints');
      final raw = _unwrapList(response.data);
      return raw.map(_complaintFromDynamic).toList();
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } on FormatException {
      throw Exception('تعذر قراءة قائمة الشكاوى');
    }
  }

  Future<ComplaintModel> updateComplaintStatus(
    int id,
    String status,
    String responseText,
  ) async {
    try {
      final response = await _dio.post<dynamic>(
        '/complaints/update-status',
        data: <String, dynamic>{
          'complaint_id': id,
          'status': status,
          'response_text': responseText,
        },
      );
      return _parseComplaintFromResponse(response.data);
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } on FormatException {
      throw Exception('تعذر قراءة بيانات الشكوى');
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

  static ComplaintModel _complaintFromDynamic(dynamic item) {
    if (item is! Map) {
      throw const FormatException('Complaint item must be an object');
    }
    return ComplaintModel.fromJson(Map<String, dynamic>.from(item));
  }

  static ComplaintModel _parseComplaintFromResponse(dynamic body) {
    if (body == null) {
      throw const FormatException('Empty response body');
    }
    if (body is! Map) {
      throw const FormatException('Expected a JSON object');
    }
    final map = Map<String, dynamic>.from(body);

    final data = map['data'];
    if (data is Map) {
      return ComplaintModel.fromJson(Map<String, dynamic>.from(data));
    }

    final complaint = map['complaint'];
    if (complaint is Map) {
      return ComplaintModel.fromJson(Map<String, dynamic>.from(complaint));
    }

    if (map.containsKey('complaint_id') ||
        map.containsKey('user_id') ||
        map.containsKey('title')) {
      return ComplaintModel.fromJson(map);
    }

    throw const FormatException(
      'Expected complaint object under "data"/"complaint" or flat keys',
    );
  }
}
