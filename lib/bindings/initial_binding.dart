import 'package:get/get.dart';
import '../services/auth_service.dart';
import '../controllers/auth_controller.dart';

class InitialBinding extends Bindings {
  @override
  void dependencies() {

    Get.lazyPut<AuthService>(() => AuthService(), fenix: true);

    Get.lazyPut<AuthController>(() => AuthController(), fenix: true);

  }
}
