import 'package:dio/dio.dart';

/// Shared API error mapping utilities.
///
/// Networking client is implemented in `DioClient`.
class BaseClient {
  static String handleError(DioException error) {
    final data = error.response?.data;
    if (data == null) return "فشل الاتصال بالسيرفر";

    // Common Laravel formats:
    // - { message: "...", errors: {...} }
    // - { error: "..."}
    // - "plain string"
    // - HTML error page (string)
    if (data is Map) {
      final map = Map<String, dynamic>.from(data);
      final msg = map['message'] ?? map['error'];
      if (msg != null) return msg.toString();

      // Try first validation error if present
      final errors = map['errors'];
      if (errors is Map) {
        for (final entry in errors.entries) {
          final v = entry.value;
          if (v is List && v.isNotEmpty) return v.first.toString();
          if (v != null) return v.toString();
        }
      }
      return "حدث خطأ غير متوقع";
    }

    if (data is String) {
      final trimmed = data.trim();
      if (trimmed.isEmpty) return "حدث خطأ غير متوقع";
      // Avoid dumping huge HTML into snackbar
      if (trimmed.startsWith('<!DOCTYPE html') || trimmed.startsWith('<html')) {
        return "حدث خطأ في السيرفر";
      }
      return trimmed;
    }

    return "حدث خطأ غير متوقع";
  }
}
