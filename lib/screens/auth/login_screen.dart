import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../controllers/auth_controller.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _nameController = TextEditingController();
  final _passwordController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  final Color primaryColor = const Color(0xFF00838F);
  final Color darkTeal = const Color(0xFF006064);
  final Color backgroundColor = const Color(0xFFE0F7FA);

  @override
  Widget build(BuildContext context) {
    final authController = Get.put(AuthController());

    return Scaffold(
      backgroundColor: backgroundColor,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 30),
            child: Form(
              key: _formKey,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    'تسجيل الدخول',
                    style: TextStyle(
                      fontSize: 32,
                      fontWeight: FontWeight.bold,
                      color: darkTeal,
                    ),
                  ),
                  const SizedBox(height: 60),

                  _buildTextField(
                    controller: _nameController,
                    hint: 'اسم المستخدم',
                    icon: Icons.person_outline,
                    validator: (value) => (value == null || value.isEmpty)
                        ? 'يرجى إدخال اسم المستخدم'
                        : null,
                  ),
                  const SizedBox(height: 20),

                  _buildTextField(
                    controller: _passwordController,
                    hint: 'كلمة المرور',
                    icon: Icons.lock_outline,
                    isPassword: true,
                    validator: (value) => (value == null || value.isEmpty)
                        ? 'يرجى إدخال كلمة المرور'
                        : null,
                  ),
                  const SizedBox(height: 40),

                  SizedBox(
                    width: double.infinity,
                    height: 55,
                    child: Obx(
                      () => ElevatedButton(
                        onPressed: authController.isLoading.value
                            ? null
                            : () => _handleLogin(authController),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primaryColor,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(15),
                          ),
                          elevation: 2,
                        ),
                        child: authController.isLoading.value
                            ? const CircularProgressIndicator(
                                color: Colors.white,
                              )
                            : const Text(
                                'دخول',
                                style: TextStyle(
                                  fontSize: 18,
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                      ),
                    ),
                  ),

                  const SizedBox(height: 30),

                  GestureDetector(
                    onTap: () {
                      Get.to(() => const RegisterScreen());
                    },
                    child: RichText(
                      text: TextSpan(
                        text: 'ليس لديك حساب؟ ',
                        style: const TextStyle(
                          color: Colors.black54,
                          fontSize: 14,
                        ),
                        children: [
                          TextSpan(
                            text: 'سجل الآن',
                            style: TextStyle(
                              color: primaryColor,
                              fontWeight: FontWeight.bold,
                              decoration: TextDecoration.underline,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    bool isPassword = false,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      obscureText: isPassword,
      decoration: InputDecoration(
        hintText: hint,
        prefixIcon: Icon(icon, color: primaryColor),
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(15),
          borderSide: BorderSide.none,
        ),
      ),
      validator: validator,
    );
  }

  void _handleLogin(AuthController controller) {
    if (_formKey.currentState!.validate()) {
      controller.login(
        _nameController.text.trim(),
        _passwordController.text.trim(),
      );
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }
}
