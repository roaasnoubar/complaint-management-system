import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../core/routes/app_routes.dart';
import '../models/user_model.dart';
import '../services/auth_service.dart';

class AuthController extends GetxController {
  final RxBool isLoading = false.obs;
  final Rxn<UserModel> currentUser = Rxn<UserModel>();

  final AuthService _authService = Get.find<AuthService>();
  final GetStorage _storage = GetStorage();

  String? tempEmail;

  Future<void> login(String email, String password) async {
    isLoading.value = true;
    try {
      final user = await _authService.login(email, password);

      if (user != null) {
        _saveUserSession(user);
        currentUser.value = user;

        Get.offAllNamed('/dashboard');
      }
    } catch (e) {
      _showError('فشل تسجيل الدخول', e);
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
      await _authService.register(
        name: name,
        email: email,
        password: password,
        phone: phone,
        birthdate: birthdate,
      );

      tempEmail = email;

      _storage.write('isLoggedIn', true);
      _storage.write('isEmailVerified', false);

      Get.snackbar(
        'تم إرسال الرمز',
        'يرجى التحقق من بريدك الإلكتروني',
        snackPosition: SnackPosition.TOP,
      );

      Get.toNamed(Routes.OTP);
    } catch (e) {
      _showError('خطأ في التسجيل', e);
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> verifyOtp(String code) async {
    if (tempEmail == null) {
      Get.snackbar("خطأ", "لم يتم العثور على البريد الإلكتروني");
      return;
    }

    isLoading.value = true;
    try {
      final bool isVerified = await _authService.verifyEmail(tempEmail!, code);

      if (isVerified) {
        _storage.write('isEmailVerified', true);

        // ملاحظة: من الأفضل هنا تحديث بيانات المستخدم لتشمل الاسم في التخزين
        // لكي يظهر في الداشبورد فوراً

        Get.snackbar(
          'نجاح',
          'تم تفعيل حسابك، مرحباً بك في تطبيق إدارة الشكاوي',
        );

        // التعديل: الانتقال النهائي للداشبورد مع مسح الذاكرة للصفحات السابقة
        Get.offAllNamed('/dashboard');
      }
    } catch (e) {
      _showError('فشل التحقق', e);
    } finally {
      isLoading.value = false;
    }
  }

  void logout() {
    _storage.erase();
    currentUser.value = null;
    Get.offAllNamed(Routes.LOGIN);
  }

  void _saveUserSession(UserModel user) {
    _storage.write('isLoggedIn', true);
    _storage.write('isEmailVerified', true);
    _storage.write('user_data', user.toJson());
  }

  void _showError(String title, Object e) {
    String message = e.toString().replaceAll('Exception: ', '');
    if (message.contains("1062")) message = "هذا البريد مسجل مسبقاً";
    Get.snackbar(title, message, snackPosition: SnackPosition.BOTTOM);
  }
}
