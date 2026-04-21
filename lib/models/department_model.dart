class DepartmentModel {
  final int? id;
  final int? authorityId;
  final String name;
  final String? description;
  final bool isActive;
  final DateTime? createdAt;

  const DepartmentModel({
    this.id,
    this.authorityId,
    required this.name,
    this.description,
    required this.isActive,
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

  static bool _readBool(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value is bool) return value;
    if (value is int) return value == 1;
    if (value is String) {
      final normalized = value.trim().toLowerCase();
      if (normalized == 'true' || normalized == '1') return true;
      if (normalized == 'false' || normalized == '0') return false;
    }
    throw FormatException('Expected bool-like for "$key", got: $value');
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

  factory DepartmentModel.fromJson(Map<String, dynamic> json) {
    return DepartmentModel(
      id: _readNullableInt(json, 'department_id'),
      authorityId: _readNullableInt(json, 'authority_id'),
      name: _readString(json, 'name'),
      description: _readNullableString(json, 'description'),
      isActive: _readBool(json, 'is_active'),
      createdAt: _readNullableDateTime(json, 'created_at'),
    );
  }

  Map<String, dynamic> toJson() => {
    'department_id': id,
    'authority_id': authorityId,
    'name': name,
    'description': description,
    'is_active': isActive ? 1 : 0,
    'created_at': createdAt?.toIso8601String(),
  };
}
