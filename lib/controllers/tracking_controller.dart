import 'package:get/get.dart';

import '../models/complaint_model.dart';
import '../services/tracking_service.dart';

/// Controller متابعة الشكوى — للمواطن فقط
class TrackingController extends GetxController {
  TrackingController({TrackingService? trackingService})
    : _trackingService = trackingService ?? TrackingService();

  final TrackingService _trackingService;

  final RxBool isLoading = false.obs;
  final Rxn<ComplaintModel> complaint = Rxn<ComplaintModel>();
  final RxnString error = RxnString();

  // ──────────────────────────────────────────────
  // تتبع الشكوى بالرقم
  // ──────────────────────────────────────────────
  Future<void> trackById(String idText) async {
    final int? id = int.tryParse(idText.trim());
    if (id == null) {
      error.value = 'رقم الشكوى غير صالح';
      complaint.value = null;
      return;
    }

    isLoading.value = true;
    error.value = null;
    complaint.value = null;

    try {
      complaint.value = await _trackingService.getComplaintById(id);
    } catch (e) {
      error.value = e.toString().replaceFirst('Exception: ', '');
    } finally {
      isLoading.value = false;
    }
  }

  void clearError() {
    error.value = null;
    complaint.value = null;
  }
}
