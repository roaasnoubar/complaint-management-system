class EmployeeModel {
  final int id;
  final String name;
  final String email;
  final String username;
  final String phone;
  final int roleId;
  final int authorityId;
  final int departmentId;
  final bool isActive;
  final bool isVerified;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  final RoleInfo? role;
  final AuthorityInfo? authority;
  final DepartmentInfo? department;

  const EmployeeModel({
    required this.id,
    required this.name,
    required this.email,
    required this.username,
    required this.phone,
    required this.roleId,
    required this.authorityId,
    required this.departmentId,
    required this.isActive,
    required this.isVerified,
    this.createdAt,
    this.updatedAt,
    this.role,
    this.authority,
    this.department,
  });

  // --- Helpers ---

  static int _readInt(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  static bool _readBool(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value is bool) return value;
    if (value is int) return value == 1;
    return false;
  }

  static String _readString(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value is String) return value;
    return value?.toString() ?? '';
  }

  static DateTime? _readDateTime(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value == null) return null;
    if (value is DateTime) return value;
    return DateTime.tryParse(value.toString());
  }

  factory EmployeeModel.fromJson(Map<String, dynamic> json) {
    return EmployeeModel(
      id: _readInt(json, 'id'),
      name: _readString(json, 'name'),
      email: _readString(json, 'email'),
      username: _readString(json, 'username'),
      phone: _readString(json, 'phone'),
      roleId: _readInt(json, 'role_id'),
      authorityId: _readInt(json, 'authority_id'),
      departmentId: _readInt(json, 'department_id'),
      isActive: _readBool(json, 'is_active'),
      isVerified: _readBool(json, 'is_verified'),
      createdAt: _readDateTime(json, 'created_at'),
      updatedAt: _readDateTime(json, 'updated_at'),
      role: json['role'] != null
          ? RoleInfo.fromJson(json['role'] as Map<String, dynamic>)
          : null,
      authority: json['authority'] != null
          ? AuthorityInfo.fromJson(json['authority'] as Map<String, dynamic>)
          : null,
      department: json['department'] != null
          ? DepartmentInfo.fromJson(json['department'] as Map<String, dynamic>)
          : null,
    );
  }

  Map<String, dynamic> toJson() => {
    'name': name,
    'email': email,
    'username': username,
    'phone': phone,
    'role_id': roleId,
    'authority_id': authorityId,
    'department_id': departmentId,
  };
}

// ──────────────────────────────────────────────
// Nested Model: بيانات الدور
// ──────────────────────────────────────────────

class RoleInfo {
  final int id;
  final String name;
  final int level;

  const RoleInfo({required this.id, required this.name, required this.level});

  factory RoleInfo.fromJson(Map<String, dynamic> json) {
    return RoleInfo(
      id: json['id'] as int? ?? 0,
      name: json['name']?.toString() ?? '',
      level: json['level'] as int? ?? 0,
    );
  }

  String get displayName {
    switch (name.toLowerCase()) {
      case 'employee':
        return 'موظف';
      case 'manager':
        return 'مدير قسم';
      case 'official':
        return 'مدير جهة';
      default:
        return name;
    }
  }
}

// ──────────────────────────────────────────────
// Nested Model: بيانات الجهة
// ──────────────────────────────────────────────

class AuthorityInfo {
  final int id;
  final String name;
  final String? description;
  final bool isActive;

  const AuthorityInfo({
    required this.id,
    required this.name,
    this.description,
    required this.isActive,
  });

  factory AuthorityInfo.fromJson(Map<String, dynamic> json) {
    return AuthorityInfo(
      id: json['id'] as int? ?? 0,
      name: json['name']?.toString() ?? '',
      description: json['description']?.toString(),
      isActive: json['is_active'] == true || json['is_active'] == 1,
    );
  }
}

// ──────────────────────────────────────────────
// Nested Model: بيانات القسم
// ──────────────────────────────────────────────

class DepartmentInfo {
  final int id;
  final int authorityId;
  final String name;
  final String? description;
  final bool isActive;

  const DepartmentInfo({
    required this.id,
    required this.authorityId,
    required this.name,
    this.description,
    required this.isActive,
  });

  factory DepartmentInfo.fromJson(Map<String, dynamic> json) {
    return DepartmentInfo(
      id: json['id'] as int? ?? 0,
      authorityId: json['authority_id'] as int? ?? 0,
      name: json['name']?.toString() ?? '',
      description: json['description']?.toString(),
      isActive: json['is_active'] == true || json['is_active'] == 1,
    );
  }
}
