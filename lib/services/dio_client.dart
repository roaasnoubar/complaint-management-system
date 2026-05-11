import 'package:dio/dio.dart';
import 'package:get/get.dart';
import '../core/constants/api_constants.dart';
import '../core/routes/app_routes.dart';
import '../core/storage/token_storage.dart';

class DioClient {
  DioClient._internal() {
    _dio = Dio(
      BaseOptions(
        baseUrl: ApiConstants.baseUrl,
        headers: const {'Accept': 'application/json'},
      ),
    );

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          // ignore: avoid_print
          print('Connecting to: ${options.baseUrl}${options.path}');
          // ignore: avoid_print
          print('Method: ${options.method}');

          final token = await TokenStorage.read();
          if (token != null && token.trim().isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
        onError: (e, handler) async {
          if (e.response?.statusCode == 401) {
            await TokenStorage.clear();
            if (Get.currentRoute != Routes.LOGIN) {
              Get.offAllNamed(Routes.LOGIN);
            }
          }
          handler.next(e);
        },
      ),
    );
  }

  static final DioClient instance = DioClient._internal();

  late final Dio _dio;

  Dio get dio => _dio;
}

