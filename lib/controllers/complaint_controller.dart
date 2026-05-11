import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:file_picker/file_picker.dart';

import '../core/routes/app_routes.dart';
import '../services/complaint_service.dart';
import '../models/complaint_model.dart';

/// Controller المواطن — تقديم شكوى + متابعتها
class ComplaintController extends GetxController {
  ComplaintController({ComplaintService? complaintService})
    : _complaintService = complaintService ?? ComplaintService();

  final ComplaintService _complaintService;

  // ── حقول النموذج ──
  final TextEditingController fullNameController = TextEditingController();
  final TextEditingController titleController = TextEditingController();
  final TextEditingController descriptionController = TextEditingController();
  final TextEditingController trackIdController = TextEditingController();

  final RxInt selectedAuthorityId = 0.obs;
  final RxInt selectedDepartmentId = 0.obs;

  final RxBool isUploading = false.obs;
  final RxBool isTracking = false.obs;

  final RxList<String> attachmentPaths = <String>[].obs;
  final Rxn<ComplaintModel> trackedComplaint = Rxn<ComplaintModel>();

  final Map<int, String> authorities = const <int, String>{
    1: 'جامعة الشام الخاصة',
  };

  final Map<int, Map<int, String>> departmentsByAuthority =
      const <int, Map<int, String>>{
        1: <int, String>{
          2: 'قسم النقل',
          3: 'دائرة الامتحانات',
          4: 'قسم شؤون الطلبة',
          5: 'قسم الشؤون الأكاديمية',
          6: 'قسم المالية',
        },
      };

  Map<int, String> get currentDepartments =>
      departmentsByAuthority[selectedAuthorityId.value] ??
      const <int, String>{};

  // ──────────────────────────────────────────────
  // متابعة الشكوى
  // ──────────────────────────────────────────────
  Future<void> trackComplaint() async {
    final String raw = trackIdController.text.trim();

    if (raw.isEmpty) {
      _showError('يرجى إدخال رقم الشكوى للمتابعة');
      return;
    }

    // التحقق من أن المدخل رقم صحيح
    final int? id = int.tryParse(raw);
    if (id == null) {
      _showError('رقم الشكوى يجب أن يكون رقماً صحيحاً');
      return;
    }

    try {
      isTracking.value = true;
      trackedComplaint.value = null;

      final complaint = await _complaintService.getComplaintById(id);
      trackedComplaint.value = complaint;
    } catch (e) {
      _showError(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      isTracking.value = false;
    }
  }

  // ──────────────────────────────────────────────
  // اختيار المرفقات
  // ──────────────────────────────────────────────
  Future<void> pickAttachment() async {
    try {
      final FilePickerResult? result = await FilePicker.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['jpg', 'jpeg', 'png', 'pdf'],
        allowMultiple: true,
      );

      if (result == null) return;

      final List<String> picked = result.files
          .map((f) => f.path)
          .whereType<String>()
          .where((p) => p.trim().isNotEmpty)
          .toList();

      if (picked.isEmpty) return;

      attachmentPaths.assignAll(picked);
      Get.snackbar(
        'تم الاختيار',
        'تم اختيار ${picked.length} ملف',
        snackPosition: SnackPosition.BOTTOM,
        backgroundColor: const Color(0xFF00838F),
        colorText: Colors.white,
        duration: const Duration(seconds: 2),
      );
    } catch (e) {
      _showError('فشل في الوصول للملفات');
    }
  }

  // ──────────────────────────────────────────────
  // التحقق من النموذج
  // ──────────────────────────────────────────────
  bool validateForm() {
    if (fullNameController.text.trim().isEmpty) {
      _showError('يرجى إدخال الاسم الكامل');
      return false;
    }
    if (titleController.text.trim().isEmpty) {
      _showError('يرجى إدخال عنوان الشكوى');
      return false;
    }
    if (descriptionController.text.trim().isEmpty) {
      _showError('يرجى كتابة وصف الشكوى');
      return false;
    }
    if (selectedAuthorityId.value == 0) {
      _showError('يرجى اختيار الجهة');
      return false;
    }
    if (selectedDepartmentId.value == 0) {
      _showError('يرجى اختيار القسم');
      return false;
    }
    return true;
  }

  // ──────────────────────────────────────────────
  // تقديم الشكوى
  // ──────────────────────────────────────────────
  Future<void> submitComplaint() async {
    if (!validateForm()) return;

    try {
      isUploading.value = true;

      final ComplaintModel complaint = await _complaintService.storeComplaint(
        fullName: fullNameController.text.trim(),
        title: titleController.text.trim(),
        description: descriptionController.text.trim(),
        authorityId: selectedAuthorityId.value,
        departmentId: selectedDepartmentId.value,
        attachmentPaths: attachmentPaths.toList(growable: false),
      );

      final String complaintIdStr = (complaint.id ?? '').toString();
      _clearFields();

      Get.toNamed(Routes.SUCCESS, arguments: {'complaintId': complaintIdStr});
    } catch (e) {
      _showError(e.toString().replaceFirst('Exception: ', ''));
    } finally {
      isUploading.value = false;
    }
  }

  void _clearFields() {
    fullNameController.clear();
    titleController.clear();
    descriptionController.clear();
    selectedAuthorityId.value = 0;
    selectedDepartmentId.value = 0;
    attachmentPaths.clear();
  }

  void _showError(String message) {
    Get.snackbar(
      'تنبيه',
      message,
      snackPosition: SnackPosition.BOTTOM,
      backgroundColor: Colors.redAccent,
      colorText: Colors.white,
      duration: const Duration(seconds: 3),
    );
  }

  @override
  void onClose() {
    fullNameController.dispose();
    titleController.dispose();
    descriptionController.dispose();
    trackIdController.dispose();
    super.onClose();
  }
}
