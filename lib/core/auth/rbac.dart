import 'package:get_storage/get_storage.dart';

class Rbac {
  Rbac._();

  // تعريف الأدوار الرسمية التي لها صلاحيات الدخول للوحات التحكم
  static const String _roleManager = 'manager';
  static const String _roleEmployee = 'employee';
  static const String _roleOfficial = 'official';
  static const String _roleCitizen = 'citizen';

  // استخراج الدور الحالي للمستخدم من الذاكرة الدائمة
  static String? currentRole() {
    final dynamic raw = GetStorage().read('user_data');
    if (raw is! Map) return null;

    final Map<String, dynamic> data = Map<String, dynamic>.from(raw);

    return data['role_name']?.toString().toLowerCase().trim();
  }

  static bool isOfficialUser() {
    final role = currentRole();
    return role == _roleManager ||
        role == _roleEmployee ||
        role == _roleOfficial;
  }

  static bool isManager() => currentRole() == _roleManager;
  static bool isEmployee() => currentRole() == _roleEmployee;
  static bool isAuthorityOfficial() => currentRole() == _roleOfficial;
  static bool isCitizen() =>
      currentRole() == _roleCitizen || currentRole() == null;

  static bool hasAccess(List<String> allowedRoles) {
    final role = currentRole();
    return allowedRoles.contains(role);
  }
}
