class UserModel {
  final int? id;
  final int? roleId;
  final int? authorityId;
  final int? departmentId;
  final String? token;
  final String name;
  final String phone;
  final String email;
  final String? birthDate;
  final String? password;
  final bool isActive;
  final num? score;
  final String? verification;
  final String? roleName;

  const UserModel({
    this.id,
    this.roleId,
    this.authorityId,
    this.departmentId,
    this.token,
    required this.name,
    required this.phone,
    required this.email,
    this.birthDate,
    this.password,
    required this.isActive,
    this.score,
    this.verification,
    this.roleName,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    String? extractedRoleName;
    if (json['role'] != null && json['role'] is Map) {
      extractedRoleName = json['role']['name']?.toString();
    }

    return UserModel(
      id: _parseNullableInt(json['user_id'] ?? json['id']),
      roleId: _parseNullableInt(json['role_id']),
      authorityId: _parseNullableInt(json['authority_id']),
      departmentId: _parseNullableInt(json['department_id']),
      token: json['token']?.toString(),
      name: json['name']?.toString() ?? '',
      phone: json['phone']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      birthDate: json['birth_date']?.toString(),
      password: json['password']?.toString(),
      isActive: json['is_active'] == 1 || json['is_active'] == true,
      score: json['score'] is num
          ? json['score']
          : num.tryParse(json['score']?.toString() ?? ''),
      verification: json['verification']?.toString(),
      roleName: extractedRoleName, 
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'role_id': roleId,
    'authority_id': authorityId,
    'department_id': departmentId,
    'token': token,
    'name': name,
    'phone': phone,
    'email': email,
    'birth_date': birthDate,
    'is_active': isActive ? 1 : 0,
    'score': score,
    'role_name': roleName,
  };

  static int? _parseNullableInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is String) return int.tryParse(value);
    return null;
  }
}
