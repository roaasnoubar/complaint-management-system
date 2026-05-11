import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../../controllers/department_manager/department_manager_controller.dart';

/// شاشة إنشاء موظف جديد — خاصة بمدير القسم
/// تُرسل البيانات إلى /admin/create-user عبر الـ Controller
class CreateEmployeeScreen extends StatefulWidget {
  const CreateEmployeeScreen({super.key});

  @override
  State<CreateEmployeeScreen> createState() => _CreateEmployeeScreenState();
}

class _CreateEmployeeScreenState extends State<CreateEmployeeScreen> {
  // ── الألوان ──
  static const Color _primary    = Color(0xFF00838F);
  static const Color _dark       = Color(0xFF006064);
  static const Color _background = Color(0xFFE0F7FA);

  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();

  // Controllers حقول النموذج
  final TextEditingController _nameController       = TextEditingController();
  final TextEditingController _emailController      = TextEditingController();
  final TextEditingController _usernameController   = TextEditingController();
  final TextEditingController _phoneController      = TextEditingController();
  final TextEditingController _passwordController   = TextEditingController();
  final TextEditingController _confirmPassController = TextEditingController();

  // حالة إظهار/إخفاء كلمة المرور
  bool _obscurePassword        = true;
  bool _obscureConfirmPassword = true;

  // القسم المختار من الـ Dropdown
  int? _selectedDepartmentId;

  // role_id ثابت للموظف = 4 حسب الـ API
  static const int _employeeRoleId = 4;

  late final DepartmentManagerController _controller;

  @override
  void initState() {
    super.initState();
    _controller = Get.find<DepartmentManagerController>();
    // جلب الأقسام لعرضها في الـ Dropdown
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _controller.fetchMyDepartments();
    });
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _usernameController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmPassController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _background,
      appBar: _buildAppBar(),
      body: GestureDetector(
        // إغلاق الكيبورد عند الضغط خارج الحقول
        onTap: () => FocusScope.of(context).unfocus(),
        child: SingleChildScrollView(
          physics: const BouncingScrollPhysics(),
          padding: const EdgeInsets.fromLTRB(16, 24, 16, 40),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                // ── رأس الصفحة ──
                _buildPageHeader(),
                const SizedBox(height: 24),

                // ── البيانات الشخصية ──
                _buildSectionTitle('البيانات الشخصية', Icons.person_outline_rounded),
                const SizedBox(height: 12),
                _buildNameField(),
                const SizedBox(height: 14),
                _buildUsernameField(),
                const SizedBox(height: 14),
                _buildPhoneField(),
                const SizedBox(height: 24),

                // ── بيانات الحساب ──
                _buildSectionTitle('بيانات الحساب', Icons.lock_outline_rounded),
                const SizedBox(height: 12),
                _buildEmailField(),
                const SizedBox(height: 14),
                _buildPasswordField(),
                const SizedBox(height: 14),
                _buildConfirmPasswordField(),
                const SizedBox(height: 24),

                // ── بيانات التعيين ──
                _buildSectionTitle('بيانات التعيين', Icons.business_outlined),
                const SizedBox(height: 12),
                _buildDepartmentDropdown(),
                const SizedBox(height: 32),

                // ── زر الإنشاء ──
                _buildSubmitButton(),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ──────────────────────────────────────────────
  // AppBar
  // ──────────────────────────────────────────────
  PreferredSizeWidget _buildAppBar() {
    return AppBar(
      backgroundColor: _dark,
      foregroundColor: Colors.white,
      elevation: 0,
      centerTitle: true,
      title: const Text(
        'إضافة موظف جديد',
        style: TextStyle(
          color: Colors.white,
          fontSize: 18,
          fontWeight: FontWeight.bold,
        ),
      ),
      leading: IconButton(
        icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white),
        onPressed: () => Get.back(),
      ),
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(4),
        child: Container(
          height: 4,
          decoration: const BoxDecoration(
            gradient: LinearGradient(colors: [_dark, _primary]),
          ),
        ),
      ),
    );
  }

  // ──────────────────────────────────────────────
  // رأس الصفحة
  // ──────────────────────────────────────────────
  Widget _buildPageHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [_dark, _primary],
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: _primary.withOpacity(0.25),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.end,
        children: [
          const Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                'إضافة موظف جديد',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 17,
                  fontWeight: FontWeight.bold,
                ),
              ),
              SizedBox(height: 4),
              Text(
                'أدخل بيانات الموظف الجديد بدقة',
                style: TextStyle(
                  color: Colors.white70,
                  fontSize: 12,
                ),
              ),
            ],
          ),
          const SizedBox(width: 16),
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.15),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.person_add_alt_1_rounded,
              color: Colors.white,
              size: 28,
            ),
          ),
        ],
      ),
    );
  }

  // ──────────────────────────────────────────────
  // عنوان القسم
  // ──────────────────────────────────────────────
  Widget _buildSectionTitle(String title, IconData icon) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.end,
      children: [
        Text(
          title,
          style: const TextStyle(
            color: _dark,
            fontSize: 15,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(width: 8),
        Container(
          width: 32,
          height: 32,
          decoration: BoxDecoration(
            color: _primary.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: _primary, size: 18),
        ),
      ],
    );
  }

  // ──────────────────────────────────────────────
  // حقول النموذج
  // ──────────────────────────────────────────────

  Widget _buildNameField() {
    return _FormField(
      controller: _nameController,
      hint: 'الاسم الكامل للموظف',
      icon: Icons.badge_outlined,
      textDirection: TextDirection.rtl,
      validator: (v) {
        if (v == null || v.trim().isEmpty) return 'الاسم الكامل مطلوب';
        if (v.trim().length < 3) return 'الاسم قصير جداً';
        return null;
      },
    );
  }

  Widget _buildUsernameField() {
    return _FormField(
      controller: _usernameController,
      hint: 'اسم المستخدم (بالإنجليزية)',
      icon: Icons.alternate_email_rounded,
      textDirection: TextDirection.ltr,
      keyboardType: TextInputType.emailAddress,
      validator: (v) {
        if (v == null || v.trim().isEmpty) return 'اسم المستخدم مطلوب';
        if (v.trim().length < 4) return 'اسم المستخدم قصير جداً';
        if (v.contains(' ')) return 'لا يجوز استخدام المسافات';
        return null;
      },
    );
  }

  Widget _buildPhoneField() {
    return _FormField(
      controller: _phoneController,
      hint: 'رقم الهاتف',
      icon: Icons.phone_outlined,
      textDirection: TextDirection.ltr,
      keyboardType: TextInputType.phone,
      validator: (v) {
        if (v == null || v.trim().isEmpty) return 'رقم الهاتف مطلوب';
        if (v.trim().length < 9) return 'رقم الهاتف غير صحيح';
        return null;
      },
    );
  }

  Widget _buildEmailField() {
    return _FormField(
      controller: _emailController,
      hint: 'البريد الإلكتروني',
      icon: Icons.email_outlined,
      textDirection: TextDirection.ltr,
      keyboardType: TextInputType.emailAddress,
      validator: (v) {
        if (v == null || v.trim().isEmpty) return 'البريد الإلكتروني مطلوب';
        if (!GetUtils.isEmail(v.trim())) return 'البريد الإلكتروني غير صحيح';
        return null;
      },
    );
  }

  Widget _buildPasswordField() {
    return _FormField(
      controller: _passwordController,
      hint: 'كلمة المرور',
      icon: Icons.lock_outline_rounded,
      textDirection: TextDirection.ltr,
      obscureText: _obscurePassword,
      suffixIcon: IconButton(
        icon: Icon(
          _obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
          color: _primary,
          size: 20,
        ),
        onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
      ),
      validator: (v) {
        if (v == null || v.isEmpty) return 'كلمة المرور مطلوبة';
        if (v.length < 8) return 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
        return null;
      },
    );
  }

  Widget _buildConfirmPasswordField() {
    return _FormField(
      controller: _confirmPassController,
      hint: 'تأكيد كلمة المرور',
      icon: Icons.lock_reset_outlined,
      textDirection: TextDirection.ltr,
      obscureText: _obscureConfirmPassword,
      suffixIcon: IconButton(
        icon: Icon(
          _obscureConfirmPassword
              ? Icons.visibility_off_outlined
              : Icons.visibility_outlined,
          color: _primary,
          size: 20,
        ),
        onPressed: () =>
            setState(() => _obscureConfirmPassword = !_obscureConfirmPassword),
      ),
      validator: (v) {
        if (v == null || v.isEmpty) return 'تأكيد كلمة المرور مطلوب';
        if (v != _passwordController.text) return 'كلمتا المرور غير متطابقتين';
        return null;
      },
    );
  }

  Widget _buildDepartmentDropdown() {
    return Obx(() {
      final departments = _controller.myDepartments;

      return Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(
              color: _primary.withOpacity(0.08),
              blurRadius: 8,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: DropdownButtonFormField<int>(
          value: _selectedDepartmentId,
          isExpanded: true,
          decoration: InputDecoration(
            hintText: departments.isEmpty ? 'جاري تحميل الأقسام...' : 'اختر القسم',
            hintStyle: const TextStyle(
              color: Color(0xFFB0BEC5),
              fontSize: 13,
            ),
            prefixIcon: const Icon(Icons.business_outlined, color: _primary, size: 20),
            filled: true,
            fillColor: Colors.white,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: BorderSide(color: _primary.withOpacity(0.2)),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: BorderSide(color: _primary.withOpacity(0.2)),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: _primary, width: 1.5),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: Colors.red),
            ),
            contentPadding:
                const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          ),
          items: departments.map((dept) {
            return DropdownMenuItem<int>(
              value: dept['id'] as int?,
              child: Text(
                dept['name']?.toString() ?? '',
                textAlign: TextAlign.right,
                style: const TextStyle(
                  color: Color(0xFF37474F),
                  fontSize: 14,
                ),
              ),
            );
          }).toList(),
          onChanged: (val) => setState(() => _selectedDepartmentId = val),
          validator: (v) => v == null ? 'يرجى اختيار القسم' : null,
          icon: const Icon(Icons.keyboard_arrow_down_rounded, color: _primary),
          dropdownColor: Colors.white,
        ),
      );
    });
  }

  // ──────────────────────────────────────────────
  // زر الإنشاء
  // ──────────────────────────────────────────────
  Widget _buildSubmitButton() {
    return Obx(() {
      final isLoading = _controller.isLoadingAction.value;

      return GestureDetector(
        onTap: isLoading ? null : _onSubmit,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          width: double.infinity,
          height: 56,
          decoration: BoxDecoration(
            gradient: isLoading
                ? const LinearGradient(
                    colors: [Color(0xFF80CBC4), Color(0xFF80CBC4)],
                  )
                : const LinearGradient(
                    colors: [_dark, _primary],
                    begin: Alignment.centerRight,
                    end: Alignment.centerLeft,
                  ),
            borderRadius: BorderRadius.circular(16),
            boxShadow: isLoading
                ? []
                : [
                    BoxShadow(
                      color: _primary.withOpacity(0.35),
                      blurRadius: 12,
                      offset: const Offset(0, 5),
                    ),
                  ],
          ),
          child: Center(
            child: isLoading
                ? const SizedBox(
                    width: 24,
                    height: 24,
                    child: CircularProgressIndicator(
                      color: Colors.white,
                      strokeWidth: 2.5,
                    ),
                  )
                : const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        'إنشاء الحساب',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          letterSpacing: 0.5,
                        ),
                      ),
                      SizedBox(width: 10),
                      Icon(
                        Icons.person_add_rounded,
                        color: Colors.white,
                        size: 20,
                      ),
                    ],
                  ),
          ),
        ),
      );
    });
  }

  // ──────────────────────────────────────────────
  // Submit
  // ──────────────────────────────────────────────
  void _onSubmit() {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedDepartmentId == null) return;

    // جلب authority_id من القسم المختار
    final selectedDept = _controller.myDepartments.firstWhereOrNull(
      (d) => d['id'] == _selectedDepartmentId,
    );
    final authorityId = selectedDept?['authority_id'] as int? ?? 1;

    _controller.createEmployee(
      name: _nameController.text.trim(),
      email: _emailController.text.trim(),
      username: _usernameController.text.trim(),
      phone: _phoneController.text.trim(),
      password: _passwordController.text,
      passwordConfirmation: _confirmPassController.text,
      roleId: _employeeRoleId,
      authorityId: authorityId,
      departmentId: _selectedDepartmentId!,
    );
  }
}

// ══════════════════════════════════════════════════════
// Widget مساعد — حقل نموذج موحد
// ══════════════════════════════════════════════════════
class _FormField extends StatelessWidget {
  final TextEditingController controller;
  final String hint;
  final IconData icon;
  final TextDirection textDirection;
  final TextInputType keyboardType;
  final bool obscureText;
  final Widget? suffixIcon;
  final String? Function(String?)? validator;

  static const Color _primary = Color(0xFF00838F);

  const _FormField({
    required this.controller,
    required this.hint,
    required this.icon,
    required this.textDirection,
    this.keyboardType = TextInputType.text,
    this.obscureText = false,
    this.suffixIcon,
    this.validator,
  });

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      textAlign: textDirection == TextDirection.rtl
          ? TextAlign.right
          : TextAlign.left,
      textDirection: textDirection,
      keyboardType: keyboardType,
      obscureText: obscureText,
      style: const TextStyle(
        color: Color(0xFF37474F),
        fontSize: 14,
      ),
      decoration: InputDecoration(
        hintText: hint,
        hintTextDirection: TextDirection.rtl,
        hintStyle: const TextStyle(
          color: Color(0xFFB0BEC5),
          fontSize: 13,
        ),
        prefixIcon: Icon(icon, color: _primary, size: 20),
        suffixIcon: suffixIcon,
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: _primary.withOpacity(0.2)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: _primary.withOpacity(0.2)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: _primary, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: Colors.red),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: Colors.red, width: 1.5),
        ),
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        errorStyle: const TextStyle(fontSize: 11),
      ),
      validator: validator,
    );
  }
}
