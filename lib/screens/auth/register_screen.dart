import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../controllers/auth_controller.dart';

class AppColors {
  static const Color primary = Color(0xFF00838F);
  static const Color darkTeal = Color(0xFF006064);
  static const Color background = Color(0xFFE0F7FA);
  static const Color white = Colors.white;
}

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({Key? key}) : super(key: key);

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();

  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _dateController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _confirmPasswordController =
      TextEditingController();

  @override
  Widget build(BuildContext context) {
    final authController = Get.find<AuthController>();

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 20),
                const Text(
                  "إنشاء حساب جديد",
                  style: TextStyle(
                    color: AppColors.darkTeal,
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 10),

                _buildTextField(
                  controller: _nameController,
                  hint: "الاسم الكامل",
                  icon: Icons.person,
                  validator: (v) => v!.isEmpty ? "يرجى إدخال الاسم" : null,
                ),
                _buildTextField(
                  controller: _phoneController,
                  hint: "الرقم",
                  icon: Icons.phone,
                  type: TextInputType.phone,
                  validator: (v) => v!.isEmpty ? "يرجى إدخال رقم الهاتف" : null,
                ),
                _buildTextField(
                  controller: _emailController,
                  hint: "الايميل",
                  icon: Icons.email,
                  type: TextInputType.emailAddress,
                  validator: (v) =>
                      GetUtils.isEmail(v!) ? null : "ايميل غير صالح",
                ),
                _buildTextField(
                  controller: _dateController,
                  hint: "تاريخ الميلاد",
                  icon: Icons.cake,
                  readOnly: true,
                  onTap: () => _selectDate(context),
                  validator: (v) => v!.isEmpty ? "يرجى تحديد التاريخ" : null,
                ),
                _buildTextField(
                  controller: _passwordController,
                  hint: "باسورد",
                  icon: Icons.lock,
                  isPass: true,
                  validator: (v) => v!.length < 6 ? "كلمة السر ضعيفة" : null,
                ),
                _buildTextField(
                  controller: _confirmPasswordController,
                  hint: "تاكيد الباسورد",
                  icon: Icons.lock_outline,
                  isPass: true,
                  validator: (v) => v != _passwordController.text
                      ? "كلمات السر غير متطابقة"
                      : null,
                ),

                const SizedBox(height: 30),

                SizedBox(
                  width: double.infinity,
                  height: 55,
                  child: Obx(
                    () => ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      onPressed: authController.isLoading.value
                          ? null
                          : () => _handleRegister(authController),
                      child: authController.isLoading.value
                          ? const CircularProgressIndicator(color: Colors.white)
                          : const Text(
                              "متابعة",
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 18,
                              ),
                            ),
                    ),
                  ),
                ),

                Center(
                  child: TextButton(
                    onPressed: () => Get.back(),
                    child: const Text(
                      "لديك حساب؟ سجل دخول الآن",
                      style: TextStyle(color: AppColors.darkTeal),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _handleRegister(AuthController controller) {
    if (_formKey.currentState!.validate()) {
      controller.register(
        name: _nameController.text.trim(),
        email: _emailController.text.trim(),
        password: _passwordController.text.trim(),
        phone: _phoneController.text.trim(),
        birthdate: _dateController.text.trim(),
      );
    }
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    bool isPass = false,
    TextInputType type = TextInputType.text,
    bool readOnly = false,
    VoidCallback? onTap,
    String? Function(String?)? validator,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 15),
      child: TextFormField(
        controller: controller,
        obscureText: isPass,
        keyboardType: type,
        readOnly: readOnly,
        onTap: onTap,
        decoration: InputDecoration(
          hintText: hint,
          prefixIcon: Icon(icon, color: AppColors.primary),
          filled: true,
          fillColor: AppColors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide.none,
          ),
        ),
        validator: validator,
      ),
    );
  }

  Future<void> _selectDate(BuildContext context) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: DateTime(2000),
      firstDate: DateTime(1950),
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      setState(() {
        _dateController.text =
            "${picked.year}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}";
      });
    }
  }
}
