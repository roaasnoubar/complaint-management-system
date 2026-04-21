import 'package:dio/dio.dart';
import '../models/user_model.dart';
import 'base_client.dart';

/// Authentication API (login / register) using [BaseClient.dio].
class AuthService {
  AuthService({Dio? dio}) : _dio = dio ?? BaseClient.dio;

  final Dio _dio;

  static const String _loginPath = 'auth/login';
  static const String _registerPath = 'auth/register';

  Future<UserModel> login(String name, String password) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        _loginPath,
        data: <String, dynamic>{'username': name, 'password': password},
      );

      final data = response.data;
      if (data == null) throw Exception('لا توجد بيانات في الاستجابة');

      return _parseUserFromResponse(data);
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    }
  }

  Future<UserModel> register({
    required String name,
    required String email,
    required String password,
    required String phone,
    required String birthdate,
  }) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        _registerPath,
        data: <String, dynamic>{
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': password,
          'phone': phone,
          'birthdate': birthdate,
        },
      );

      final data = response.data;
      if (data == null) throw Exception('فشل في جلب الاستجابة من السيرفر');

      return _parseUserFromResponse(data);
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    }
  }

  static UserModel _parseUserFromResponse(Map<String, dynamic> data) {
    Map<String, dynamic>? userMap;

    if (data['user'] is Map<String, dynamic>) {
      userMap = data['user'];
    } else if (data['data'] is Map<String, dynamic>) {
      final nested = data['data'];
      userMap = (nested['user'] is Map<String, dynamic>)
          ? nested['user']
          : nested;
    } else if (data.containsKey('user_id') || data.containsKey('id')) {
      userMap = data;
    }

    if (userMap == null)
      throw Exception('تعذر العثور على بيانات المستخدم في رد السيرفر');

    final normalized = Map<String, dynamic>.from(userMap);

    return UserModel.fromJson(normalized);
  }
}
