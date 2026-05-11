import 'package:dio/dio.dart';

import '../core/constants/api_constants.dart';
import '../services/dio_client.dart';
import '../services/base_client.dart';
import '../models/complaint_model.dart';
import '../models/employee_model.dart';

class DepartmentManagerService {
  DepartmentManagerService._();

  static final DepartmentManagerService instance = DepartmentManagerService._();

  final Dio _dio = DioClient.instance.dio;

  Future<Map<String, dynamic>> fetchManagerStats() async {
    try {
      final response = await _dio.get(ApiConstants.managerStats);
      final data = response.data;
      if (data is Map) {
        final map = Map<String, dynamic>.from(data);
        if (map['data'] is Map) {
          return Map<String, dynamic>.from(map['data'] as Map);
        }
        return map;
      }
      return {};
    } on DioException catch (e) {
      throw BaseClient.handleError(e);
    }
  }

  Future<List<ComplaintModel>> fetchAllComplaints() async {
    try {
      final response = await _dio.get(ApiConstants.allComplaints);
      return _parseComplaintList(response.data);
    } on DioException catch (e) {
      throw BaseClient.handleError(e);
    }
  }

  Future<List<ComplaintModel>> fetchComplaintsByStatus(String status) async {
    try {
      final String backendStatus = _mapStatusToBackend(status);
      final response = await _dio.get(
        ApiConstants.filterComplaints(backendStatus),
      );
      return _parseComplaintList(response.data);
    } on DioException catch (e) {
      throw BaseClient.handleError(e);
    }
  }

  Future<ComplaintModel> fetchComplaintDetails(int id) async {
    try {
      final response = await _dio.get(ApiConstants.viewComplaint(id));
      final data = response.data;
      if (data is Map) {
        final map = Map<String, dynamic>.from(data);
        final raw = map['data'] ?? map;
        return ComplaintModel.fromJson(Map<String, dynamic>.from(raw as Map));
      }
      throw 'صيغة البيانات غير متوقعة';
    } on DioException catch (e) {
      throw BaseClient.handleError(e);
    }
  }

  Future<bool> updateComplaintStatus(int id, String status) async {
    try {
      final response = await _dio.post(
        ApiConstants.updateStatus(id),
        data: {'status': _mapStatusToBackend(status)},
      );
      final data = response.data;
      if (data is Map) {
        return data['success'] == true || response.statusCode == 200;
      }
      return response.statusCode == 200;
    } on DioException catch (e) {
      throw BaseClient.handleError(e);
    }
  }

  Future<bool> respondToComplaint(int id, String reply) async {
    try {
      final response = await _dio.post(
        ApiConstants.respondToComplaint(id),
        data: {'reply': reply},
      );
      final data = response.data;
      if (data is Map) {
        return data['success'] == true || response.statusCode == 200;
      }
      return response.statusCode == 200;
    } on DioException catch (e) {
      throw BaseClient.handleError(e);
    }
  }

  Future<EmployeeModel> createEmployee({
    required String name,
    required String email,
    required String username,
    required String phone,
    required String password,
    required String passwordConfirmation,
    required int roleId,
    required int authorityId,
    required int departmentId,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.adminCreateUser,
        data: {
          'name': name,
          'email': email,
          'username': username,
          'phone': phone,
          'password': password,
          'password_confirmation': passwordConfirmation,
          'role_id': roleId,
          'authority_id': authorityId,
          'department_id': departmentId,
        },
      );
      final data = response.data;
      if (data is Map) {
        final map = Map<String, dynamic>.from(data);
        final raw = map['data'] ?? map;
        return EmployeeModel.fromJson(Map<String, dynamic>.from(raw as Map));
      }
      throw 'صيغة البيانات غير متوقعة';
    } on DioException catch (e) {
      throw BaseClient.handleError(e);
    }
  }

  Future<List<Map<String, dynamic>>> fetchMyDepartments() async {
    try {
      final response = await _dio.get(ApiConstants.myDepartments);
      final data = response.data;
      if (data is Map && data['data'] is List) {
        return List<Map<String, dynamic>>.from(
          (data['data'] as List).map(
            (e) => Map<String, dynamic>.from(e as Map),
          ),
        );
      }
      return [];
    } on DioException catch (e) {
      throw BaseClient.handleError(e);
    }
  }

  String _mapStatusToBackend(String status) {
    switch (status.toLowerCase()) {
      case 'new':
      case 'pending':
        return ApiConstants.statusPending;
      case 'in_progress':
      case 'in progress':
        return ApiConstants.statusInProgress;
      case 'closed':
      case 'resolved':
        return ApiConstants.statusResolved;
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
}
