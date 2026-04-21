class AuthorityModel {
  final int? id;
  final int? complainId;
  final int? departmentId;
  final String name;
  final String? description;
  final DateTime? createdAt;

  const AuthorityModel({
    this.id,
    this.complainId,
    this.departmentId,
    required this.name,
    this.description,
    this.createdAt,
  });

  static DateTime? _readNullableDateTime(
    Map<String, dynamic> json,
    String key,
  ) {
    final value = json[key];
    if (value == null) return null;
    if (value is DateTime) return value;
    if (value is String) return DateTime.tryParse(value);
    return DateTime.tryParse(value.toString());
  }

  static int _readInt(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value is int) return value;
    if (value is String) {
      final parsed = int.tryParse(value);
      if (parsed != null) return parsed;
    }
    throw FormatException('Expected int for "$key", got: $value');
  }

  static int? _readNullableInt(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value == null) return null;
    if (value is int) return value;
    if (value is String) return int.tryParse(value);
    return int.tryParse(value.toString());
  }

  static String _readString(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value is String) return value;
    if (value == null) return '';
    return value.toString();
  }

  static String? _readNullableString(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value == null) return null;
    if (value is String) return value;
    return value.toString();
  }

  factory AuthorityModel.fromJson(Map<String, dynamic> json) {
    return AuthorityModel(
      id: _readNullableInt(json, 'auth_id'),
      complainId: _readNullableInt(json, 'complain_id'),
      departmentId: _readNullableInt(json, 'department_id'),
      name: _readString(json, 'name'),
      description: _readNullableString(json, 'description'),
      createdAt: _readNullableDateTime(json, 'created_at'),
    );
  }

  Map<String, dynamic> toJson() => {
    'auth_id': id,
    'complain_id': complainId,
    'department_id': departmentId,
    'name': name,
    'description': description,
    'created_at': createdAt?.toIso8601String(),
  };
}
