import 'package:dio/dio.dart';

import '../models/complaint_model.dart';
import '../core/constants/api_constants.dart';
import 'base_client.dart';
import 'dio_client.dart';

class ComplaintService {
  ComplaintService({Dio? dio}) : _dio = dio ?? DioClient.instance.dio;

  final Dio _dio;

  // ──────────────────────────────────────────────
  // 1. تقديم شكوى جديدة (المواطن)
  // ──────────────────────────────────────────────
  Future<ComplaintModel> storeComplaint({
    required String fullName,
    required String title,
    required String description,
    required int authorityId,
    required int departmentId,
    List<String> attachmentPaths = const <String>[],
  }) async {
    try {
      final List<MultipartFile> attachments = <MultipartFile>[];
      for (final path in attachmentPaths) {
        if (path.trim().isEmpty) continue;
        attachments.add(await MultipartFile.fromFile(path.trim()));
      }

      final FormData formData = FormData.fromMap(<String, dynamic>{
        'full_name': fullName,
        'title': title,
        'description': description,
        'authority_id': authorityId,
        'department_id': departmentId,
        if (attachments.isNotEmpty) 'attachments[]': attachments,
      });

      final response = await _dio.post<dynamic>(
        ApiConstants.storeComplaint,
        data: formData,
      );
      return _parseComplaintFromResponse(response.data);
    } on DioException catch (e) {
      final msg = e.response?.data is Map
          ? e.response!.data['message'] ?? BaseClient.handleError(e)
          : BaseClient.handleError(e);
      throw Exception(msg);
    } catch (e) {
      throw Exception('حدث خطأ غير متوقع أثناء تقديم الشكوى');
    }
  }

  // ──────────────────────────────────────────────
  // 2. جلب الشكاوي حسب الحالة
  // ──────────────────────────────────────────────
  Future<List<ComplaintModel>> getComplaintsByStatus(String status) async {
    try {
      final String backendStatus = _mapStatusToBackend(status);
      final response = await _dio.get<dynamic>(
        ApiConstants.filterComplaints(backendStatus),
      );
      return _parseComplaintList(response.data);
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } catch (e) {
      throw Exception('حدث خطأ أثناء جلب قائمة الشكاوى');
    }
  }

  // ──────────────────────────────────────────────
  // 3. جلب جميع الشكاوي
  // ──────────────────────────────────────────────
  Future<List<ComplaintModel>> getAllComplaints() async {
    try {
      final response = await _dio.get<dynamic>(ApiConstants.allComplaints);
      return _parseComplaintList(response.data);
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } catch (e) {
      throw Exception('حدث خطأ أثناء جلب الشكاوى');
    }
  }

  // ──────────────────────────────────────────────
  // 4. جلب تفاصيل شكوى بالـ ID
  // ──────────────────────────────────────────────
  Future<ComplaintModel> getComplaintById(int id) async {
    try {
      final response = await _dio.get<dynamic>(ApiConstants.viewComplaint(id));
      return _parseComplaintFromResponse(response.data);
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) {
        throw Exception('رقم الشكوى غير موجود، يرجى التأكد من الرقم');
      }
      throw Exception(BaseClient.handleError(e));
    } catch (e) {
      throw Exception('حدث خطأ أثناء جلب بيانات الشكوى');
    }
  }

  // ──────────────────────────────────────────────
  // 5. تغيير حالة الشكوى
  // ──────────────────────────────────────────────
  Future<bool> updateComplaintStatus(int id, String status) async {
    try {
      final response = await _dio.post<dynamic>(
        ApiConstants.updateStatus(id),
        data: {'status': _mapStatusToBackend(status)},
      );
      final data = response.data;
      if (data is Map) {
        return data['success'] == true || response.statusCode == 200;
      }
      return response.statusCode == 200;
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } catch (e) {
      throw Exception('تعذر تحديث حالة الشكوى');
    }
  }

  // ──────────────────────────────────────────────
  // 6. الرد الرسمي وإغلاق الشكوى
  // ──────────────────────────────────────────────
  Future<bool> respondToComplaint(int id, String reply) async {
    try {
      final response = await _dio.post<dynamic>(
        ApiConstants.respondToComplaint(id),
        data: {'reply': reply},
      );
      final data = response.data;
      if (data is Map) {
        return data['success'] == true || response.statusCode == 200;
      }
      return response.statusCode == 200;
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } catch (e) {
      throw Exception('تعذر إرسال الرد');
    }
  }

  Future<ComplaintModel> trackComplaint(String complaintId) async {
    try {
      final id = int.tryParse(complaintId);
      if (id == null) throw Exception('رقم الشكوى غير صالح');
      final response = await _dio.get<dynamic>(ApiConstants.viewComplaint(id));
      return _parseComplaintFromResponse(response.data);
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) {
        throw Exception('رقم الشكوى غير موجود، يرجى التأكد من الرقم');
      }
      throw Exception(BaseClient.handleError(e));
    } catch (e) {
      throw Exception('حدث خطأ أثناء تتبع الشكوى');
    }
  }

  String _mapStatusToBackend(String status) {
    switch (status.toLowerCase()) {
      case 'new':
      case 'pending':
        return ApiConstants.statusPending; // "Pending"
      case 'in_progress':
      case 'in progress':
        return ApiConstants.statusInProgress; // "In Progress"
      case 'closed':
      case 'resolved':
        return ApiConstants.statusResolved; // "Resolved"
      default:
        return status;
    }
  }

  List<ComplaintModel> _parseComplaintList(dynamic data) {
    if (data == null) return [];
    List<dynamic> rawList = [];
    if (data is Map) {
      final map = Map<String, dynamic>.from(data);
      if (map['data'] is List) {
        rawList = map['data'] as List;
      } else if (map['complaints'] is List) {
        rawList = map['complaints'] as List;
      }
    } else if (data is List) {
      rawList = data;
    }
    return rawList
        .whereType<Map>()
        .map((e) => ComplaintModel.fromJson(Map<String, dynamic>.from(e)))
        .toList();
  }

  static ComplaintModel _parseComplaintFromResponse(dynamic body) {
    if (body == null) throw const FormatException('الرد فارغ من السيرفر');
    final map = Map<String, dynamic>.from(body as Map);
    final data = map['data'];
    if (data is Map) {
      final payload = Map<String, dynamic>.from(data);
      final nested = payload['complaint'];
      if (nested is Map) {
        return ComplaintModel.fromJson(Map<String, dynamic>.from(nested));
      }
      return ComplaintModel.fromJson(payload);
    }
    return ComplaintModel.fromJson(map);
  }
}
