import 'package:dio/dio.dart';
import '../models/user_model.dart';
import '../core/constants/api_constants.dart';
import 'base_client.dart';
import '../core/storage/token_storage.dart';
import 'dio_client.dart';

class AuthService {
  AuthService({Dio? dio}) : _dio = dio ?? DioClient.instance.dio;
  final Dio _dio;

  /// POST /auth/login
  Future<UserModel> login(String username, String password) async {
    try {
      print(
        "DEBUG: Connecting to Login URL -> ${ApiConstants.baseUrl}${ApiConstants.login}",
      );

      final response = await _dio.post<dynamic>(
        ApiConstants.login,
        data: {'username': username, 'password': password},
      );

      final user = _parseUserFromResponse(response.data);
      if ((user.token ?? '').trim().isNotEmpty) {
        await TokenStorage.save(user.token!);
      }
      return user;
    } on DioException catch (e) {
      print("DEBUG: Dio Error during Login -> ${e.message}");
      print("DEBUG: Error Details -> ${e.response?.data}");
      throw Exception(BaseClient.handleError(e));
    }
  }

  Future<UserModel> me() async {
    try {
      final response = await _dio.get<dynamic>(ApiConstants.me);
      return _parseUserFromResponse(response.data);
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
      // --- سطر كشف الخطأ (Debug Line) ---
      print(
        "DEBUG: Connecting to Register URL -> ${ApiConstants.baseUrl}${ApiConstants.register}",
      );

      final response = await _dio.post<dynamic>(
        ApiConstants.register,
        data: <String, dynamic>{
          'name': name,
          'email': email,
          'phone': phone,
          'birthdate': birthdate,
          'password': password,
          'password_confirmation': password,
        },
      );
      return _parseUserFromResponse(response.data);
    } on DioException catch (e) {
      print("DEBUG: Dio Error during Register -> ${e.message}");
      throw Exception(BaseClient.handleError(e));
    }
  }

  /// POST /auth/verify-email
  Future<bool> verifyEmail(String email, String code) async {
    try {
      final response = await _dio.post(
        ApiConstants.verifyEmail,
        data: {'email': email, 'code': code},
      );
      return response.statusCode == 200 || response.statusCode == 201;
    } on DioException catch (e) {
      throw Exception(BaseClient.handleError(e));
    }
  }

  static UserModel _parseUserFromResponse(dynamic responseData) {
    if (responseData == null) {
      throw Exception('استجابة فارغة من السيرفر');
    }
    if (responseData is! Map) {
      throw Exception('تنسيق استجابة غير صالح');
    }

    final root = Map<String, dynamic>.from(responseData);

    final dynamic data = root['data'];
    final Map<String, dynamic> container = (data is Map)
        ? Map<String, dynamic>.from(data)
        : root;

    final dynamic nestedUser = container['user'] ?? container['user_data'];
    final Map<String, dynamic> userMap = (nestedUser is Map)
        ? Map<String, dynamic>.from(nestedUser)
        : Map<String, dynamic>.from(container);

    final String? token =
        (container['token'] ??
                container['access_token'] ??
                root['token'] ??
                root['access_token'])
            ?.toString();
    if (token != null && token.isNotEmpty) userMap['token'] = token;

    return UserModel.fromJson(userMap);
  }
}
