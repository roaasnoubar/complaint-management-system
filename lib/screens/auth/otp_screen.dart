import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:pinput/pinput.dart';
import '../../controllers/auth_controller.dart';

class OtpScreen extends StatelessWidget {
  const OtpScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final authController = Get.find<AuthController>();

    final String userEmail = authController.tempEmail ?? "user@email.com";

    return Scaffold(
      backgroundColor: const Color(0xFFF0F8FA), // لون خلفية هادئ
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: Color(0xFF006064)),
          onPressed: () => Get.back(),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 30),
        child: Column(
          children: [
            const SizedBox(height: 20),
            const Icon(
              Icons.mark_email_read_outlined,
              size: 100,
              color: Colors.orangeAccent,
            ),
            const SizedBox(height: 30),

            const Text(
              "التحقق من الحساب",
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: Color(0xFF006064),
              ),
            ),
            const SizedBox(height: 15),
            Text(
              "يرجى إدخال الرمز المكون من 6 أرقام\nالمرسل إلى بريدك الإلكتروني:\n$userEmail",
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.black54, fontSize: 16),
            ),

            const SizedBox(height: 40),

            Pinput(
              length: 6,
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              defaultPinTheme: PinTheme(
                width: 45,
                height: 55,
                textStyle: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF006064),
                ),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey.shade300),
                ),
              ),
              onCompleted: (pin) => authController.verifyOtp(pin),
            ),

            const SizedBox(height: 50),

            Obx(
              () => ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF00838F),
                  minimumSize: const Size(double.infinity, 55),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(15),
                  ),
                  elevation: 2,
                ),
                onPressed: authController.isLoading.value ? null : () {},
                child: authController.isLoading.value
                    ? const CircularProgressIndicator(color: Colors.white)
                    : const Text(
                        "تأكيد الرمز",
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
              ),
            ),

            const SizedBox(height: 25),

            TextButton(
              onPressed: () {},
              child: const Text(
                "لم يصلك الرمز؟ إعادة إرسال",
                style: TextStyle(
                  color: Color(0xFF00838F),
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
