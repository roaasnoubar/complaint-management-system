import 'package:get/get.dart';
import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:dio/dio.dart' as dio_package;
import '../screens/auth/success_page.dart';

class ComplaintController extends GetxController {
  final nameController = TextEditingController();
  final descriptionController = TextEditingController();

  var selectedDepartmentId = 0.obs;
  var isUploading = false.obs;
  var attachmentPath = ''.obs;

  Future<void> pickAttachment() async {
    try {
      FilePickerResult? result = await FilePicker.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['jpg', 'png', 'pdf'],
      );

      if (result != null && result.files.single.path != null) {
        attachmentPath.value = result.files.single.path!;
        Get.snackbar(
          'نجاح',
          'تم اختيار الملف: ${result.files.single.name}',
          snackPosition: SnackPosition.BOTTOM,
          backgroundColor: const Color(0xFF00838F),
          colorText: Colors.white,
        );
      }
    } catch (e) {
      Get.snackbar('خطأ', 'فشل في الوصول للملفات');
    }
  }

  bool validateForm() {
    if (nameController.text.trim().isEmpty ||
        descriptionController.text.trim().isEmpty) {
      _showErrorSnackBar('يرجى ملء جميع الحقول المطلوبة');
      return false;
    }
    if (selectedDepartmentId.value == 0) {
      _showErrorSnackBar('يرجى اختيار نوع الشكوى');
      return false;
    }
    return true;
  }

  Future<void> submitComplaint() async {
    if (!validateForm()) return;

    try {
      isUploading.value = true;
      dio_package.Dio dio = dio_package.Dio();

      String token = "YOUR_ACTUAL_TOKEN_HERE";

      dio_package.FormData formData = dio_package.FormData.fromMap({
        'title': nameController.text.trim(),
        'description': descriptionController.text.trim(),
        'department_id': selectedDepartmentId.value,
        if (attachmentPath.value.isNotEmpty)
          'attachment': await dio_package.MultipartFile.fromFile(
            attachmentPath.value,
            filename: attachmentPath.value.split('/').last,
          ),
      });

      final response = await dio.post(
        'https://your-laravel-domain.com/api/complaints', // رابط الـ API الخاص بكِ
        data: formData,
        options: dio_package.Options(
          headers: {
            'Authorization': 'Bearer $token',
            'Accept': 'application/json',
          },
        ),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        String complaintId = response.data['data']['id'].toString();

        _clearFields();
        Get.to(() => SuccessPage(complaintId: complaintId));
      }
    } on dio_package.DioException catch (e) {
      String errorMsg = e.response?.data['message'] ?? "حدث خطأ في السيرفر";
      _showErrorSnackBar(errorMsg);
    } catch (e) {
      _showErrorSnackBar("تأكدي من الاتصال بالإنترنت");
    } finally {
      isUploading.value = false;
    }
  }

  void _clearFields() {
    nameController.clear();
    descriptionController.clear();
    selectedDepartmentId.value = 0;
    attachmentPath.value = '';
  }

  void _showErrorSnackBar(String message) {
    Get.snackbar(
      'تنبيه',
      message,
      backgroundColor: Colors.redAccent,
      colorText: Colors.white,
    );
  }

  @override
  void onClose() {
    nameController.dispose();
    descriptionController.dispose();
    super.onClose();
  }
}
