import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../core/routes/app_routes.dart';

class SuccessPage extends StatelessWidget {
  final String complaintId;

  const SuccessPage({super.key, required this.complaintId});

  @override
  Widget build(BuildContext context) {
    final dynamic args = Get.arguments;
    final String resolvedId = complaintId.isNotEmpty
        ? complaintId
        : ((args is Map ? args['complaintId'] : args)?.toString() ?? '');

    return Scaffold(
      backgroundColor: const Color(0xFFE0F7FA),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.check_circle_outline,
                size: 100,
                color: Color(0xFF00838F),
              ),
              const SizedBox(height: 20),
              const Text(
                "تم تقديم الشكوى بنجاح",
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF006064),
                ),
              ),
              const SizedBox(height: 10),
              Text(
                "رقم الشكوى الخاص بك هو:",
                style: TextStyle(fontSize: 16, color: Colors.grey[700]),
              ),
              Text(
                "#$resolvedId",
                style: const TextStyle(
                  fontSize: 30,
                  fontWeight: FontWeight.w900,
                  color: Color(0xFF00838F),
                ),
              ),
              const SizedBox(height: 40),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF00838F),
                    padding: const EdgeInsets.symmetric(vertical: 15),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  onPressed: () {
                    // التعديل الجوهري هنا:
                    // نقوم بالتوجه لصفحة لوحة التحكم مباشرة ومسح السجل
                    // تأكدي أن اسم المسار في ملف app_routes هو DASHBOARD أو حسب تسميتك له
                    Get.offAllNamed(Routes.DASHBOARD);
                  },
                  child: const Text(
                    "العودة للرئيسية",
                    style: TextStyle(color: Colors.white, fontSize: 18),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
