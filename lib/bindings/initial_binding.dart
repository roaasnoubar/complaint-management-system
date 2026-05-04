import 'package:get/get.dart';

// Controllers
import '../controllers/auth_controller.dart';

// Services
import '../services/app_data_service.dart';
import '../services/auth_service.dart';
import '../services/chat_service.dart';
import '../services/complaint_service.dart';

class InitialBinding extends Bindings {
  @override
  void dependencies() {
    Get.lazyPut<AuthService>(() => AuthService(), fenix: true);
    Get.lazyPut<AppDataService>(() => AppDataService(), fenix: true);
    Get.lazyPut<ComplaintService>(() => ComplaintService(), fenix: true);
    Get.lazyPut<ChatService>(() => ChatService(), fenix: true);

    Get.lazyPut<AuthController>(() => AuthController(), fenix: true);
  }
}
