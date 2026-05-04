import 'package:dio/dio.dart';

import '../models/authority_model.dart';
import '../models/department_model.dart';
import 'base_client.dart';

/// Loads authorities and departments (filtered by authority) via [BaseClient.dio].
class AppDataService {
  AppDataService({Dio? dio}) : _dio = dio ?? BaseClient.dio;

  final Dio _dio;

  Future<List<AuthorityModel>> getAuthorities() async {
    try {
      final response = await _dio.get<dynamic>('/authorities');
      final raw = _unwrapList(response.data);
      return raw.map(_authorityFromDynamic).toList();
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } on FormatException {
      throw Exception('تعذر قراءة قائمة الجهات');
    }
  }

  Future<List<DepartmentModel>> getDepartmentsByAuthority(
    int authorityId,
  ) async {
    try {
      final response = await _dio.get<dynamic>(
        '/departments',
        queryParameters: <String, dynamic>{'authority_id': authorityId},
      );
      final raw = _unwrapList(response.data);
      return raw.map(_departmentFromDynamic).toList();
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    } on FormatException {
      throw Exception('تعذر قراءة قائمة الأقسام');
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

  static DepartmentModel _departmentFromDynamic(dynamic item) {
    if (item is! Map) {
      throw const FormatException('Department item must be an object');
    }
    return DepartmentModel.fromJson(Map<String, dynamic>.from(item));
  }

  static AuthorityModel _authorityFromDynamic(dynamic item) {
    if (item is! Map) {
      throw const FormatException('Authority item must be an object');
    }
    return AuthorityModel.fromJson(Map<String, dynamic>.from(item));
  }
}
