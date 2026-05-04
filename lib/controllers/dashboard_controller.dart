import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../models/dashboard_stats_model.dart';

class DashboardController extends GetxController {
  final GetStorage _storage = GetStorage();

  final Rxn<DashboardStatsModel> stats = Rxn<DashboardStatsModel>();
  final RxBool isLoading = true.obs;

  @override
  void onInit() {
    super.onInit();
    _initializeDashboard();
  }

  Future<void> _initializeDashboard() async {
    isLoading.value = true;
    try {
      final userData = _storage.read('user_data');
      String name = userData != null ? userData['name'] : "المستخدم";

      stats.value = DashboardStatsModel(
        userName: name,
        satisfactionRate: "94%",
        responseTime: "48",
        processedComplaints: "1250+",
      );
    } catch (e) {
      Get.snackbar("خطأ", "حدث خطأ أثناء تحميل البيانات");
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> refreshStats() async {
    await _initializeDashboard();
  }
}
