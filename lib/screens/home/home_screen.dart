import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../core/routes/app_routes.dart';

class HomeScreen extends StatelessWidget {
  HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFE0F7FA),
      body: Stack(
        children: [
          Positioned(
            top: -50,
            left: -50,
            child: Container(
              width: 250,
              height: 250,
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.5),
                shape: BoxShape.circle,
              ),
            ),
          ),

          SafeArea(
            child: Center(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 30),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Text(
                      'مرحباً بك',
                      style: TextStyle(
                        fontSize: 48,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF006064),
                      ),
                    ),
                    const SizedBox(height: 10),

                    const Text(
                      'نظام إدارة الشكاوى',
                      style: TextStyle(
                        fontSize: 22,
                        color: Color(0xFF00838F),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 20),

                    Container(
                      width: 60,
                      height: 4,
                      decoration: BoxDecoration(
                        color: const Color(0xFF00BCD4),
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                    const SizedBox(height: 40),

                    const Text(
                      'نحن هنا لنستمع إليك..\nساهم في تطوير الخدمات بتقديم بلاغك الآن',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 16,
                        color: Colors.black54,
                        height: 1.5,
                      ),
                    ),
                    const SizedBox(height: 80),

                    SizedBox(
                      width: double.infinity,
                      height: 60,
                      child: ElevatedButton(
                        onPressed: () => _handleStartAction(),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF00838F),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(30),
                          ),
                          elevation: 5,
                        ),
                        child: const Text(
                          'ابدأ الآن',
                          style: TextStyle(
                            fontSize: 20,
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  bool isUserLoggedIn = false;

  void _handleStartAction() {
    if (isUserLoggedIn) {
      Get.toNamed(Routes.ADD_COMPLAINT);
    } else {
      Get.toNamed(Routes.LOGIN);
    }
  }
}
