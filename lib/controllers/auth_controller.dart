import 'package:get/get.dart';
import '../core/routes/app_routes.dart';
import '../models/user_model.dart';
import '../services/auth_service.dart';

class AuthController extends GetxController {
  final RxBool isLoading = false.obs;
  final Rxn<UserModel> currentUser = Rxn<UserModel>();
  final AuthService _authService = AuthService();

  Future<void> login(String name, String password) async {
    print("محاولة تسجيل الدخول للمستخدم: $name");
    isLoading.value = true;
    try {
      final user = await _authService.login(name, password);
      currentUser.value = user;

      Get.snackbar(
        'نجاح',
        'مرحباً بك مجدداً ${user.name}',
        snackPosition: SnackPosition.TOP,
      );

      Get.offAllNamed(Routes.HOME);
    } catch (e) {
      Get.snackbar(
        'فشل تسجيل الدخول',
        _errorMessage(e),
        snackPosition: SnackPosition.BOTTOM,
      );
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> register({
    required String name,
    required String email,
    required String password,
    required String phone,
    required String birthdate,
  }) async {
    isLoading.value = true;
    try {
      final result = await _authService.register(
        name: name,
        email: email,
        password: password,
        phone: phone,
        birthdate: birthdate,
      );

      Get.snackbar(
        'تم إرسال الرمز',
        'يرجى التحقق من بريدك الإلكتروني لتأكيد الحساب',
        snackPosition: SnackPosition.TOP,
      );

      Get.offAllNamed(Routes.HOME);
    } catch (e) {
      String message = _errorMessage(e);
      if (message.contains("1062")) {
        message = "هذا البريد الإلكتروني مسجل مسبقاً";
      }

      Get.snackbar(
        'خطأ في التسجيل',
        message,
        snackPosition: SnackPosition.BOTTOM,
      );
    } finally {
      isLoading.value = false;
    }
  }

  static String _errorMessage(Object e) {
    final s = e.toString();
    if (s.contains('Exception: ')) {
      return s.replaceAll('Exception: ', '');
    }
    return s;
  }
}
