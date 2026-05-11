import 'package:get/get.dart';

import '../controllers/department_manager/department_manager_controller.dart';

class DepartmentManagerBinding extends Bindings {
  @override
  void dependencies() {
    Get.lazyPut<DepartmentManagerController>(
      () => DepartmentManagerController(),
      fenix: true,
    );
  }
}
