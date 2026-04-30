import 'package:dio/dio.dart';

class BaseClient {
  BaseClient._();

  static const String baseUrl = 'http://10.161.226.158:8000/api/';

  static const Duration _timeout = Duration(seconds: 20);

  static final Map<String, String> _defaultHeaders = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  static final Dio dio = Dio(
    BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: _timeout,
      receiveTimeout: _timeout,
      sendTimeout: _timeout,
      headers: _defaultHeaders,
    ),
  );

  static String handleError(DioException error) {
    if (error.response?.data != null && error.response?.data is Map) {
      final data = error.response?.data as Map;
      if (data.containsKey('message')) {
        return data['message'].toString();
      }
    }

    final status = error.response?.statusCode;
    if (status != null) {
      switch (status) {
        case 401:
          return 'غير مصرح - يرجى تسجيل الدخول';
        case 404:
          return 'الخدمة غير موجودة حالياً';
        case 500:
          return 'خطأ داخلي في السيرفر';
        case 422:
          return 'البيانات المدخلة غير صحيحة';
        case 403:
          return 'ليس لديك صلاحية للقيام بهذا الإجراء';
        default:
          return 'حدث خطأ (كود: $status)';
      }
    }

    switch (error.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        return 'انتهت مهلة الاتصال، تحقق من الشبكة';
      case DioExceptionType.connectionError:
        return 'تعذر الاتصال بالخادم، تأكد من تشغيل السيرفر والـ IP';
      default:
        return 'حدث خطأ غير متوقع في الاتصال';
    }
  }
}
