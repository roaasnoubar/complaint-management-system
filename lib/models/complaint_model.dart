class ComplaintModel {
  final int? id;
  final int userId;
  final int departmentId;
  final int currentDepartmentId;
  final int? attachmentsId;
  final int authId;
  final String title;
  final String description;
  final String status;
  final bool isValid;
  final DateTime? createdAt;
  final DateTime? resolvedAt;
  final int assignedLevel;

  const ComplaintModel({
    this.id,
    required this.userId,
    required this.departmentId,
    required this.currentDepartmentId,
    this.attachmentsId,
    required this.authId,
    required this.title,
    required this.description,
    required this.status,
    required this.isValid,
    required this.createdAt,
    required this.resolvedAt,
    required this.assignedLevel,
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
    return null;
  }

  static int? _readNullableIntStrict(Map<String, dynamic> json, String key) {
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

  factory ComplaintModel.fromJson(Map<String, dynamic> json) {
    return ComplaintModel(
      id: _readNullableIntStrict(json, 'complaint_id'),
      userId: _readInt(json, 'user_id'),
      departmentId: _readInt(json, 'department_id'),
      currentDepartmentId: _readInt(json, 'current_department_id'),
      attachmentsId: _readNullableInt(json, 'attachments_id'),
      authId: _readInt(json, 'auth_id'),
      title: _readString(json, 'title'),
      description: _readString(json, 'description'),
      status: _readNullableString(json, 'status') ?? 'pending',
      isValid: _readBool(json, 'is_valid'),
      createdAt: _readNullableDateTime(json, 'created_at'),
      resolvedAt: _readNullableDateTime(json, 'resolved_at'),
      assignedLevel: _readInt(json, 'assigned_level'),
    );
  }

  Map<String, dynamic> toJson() => {
    'complaint_id': id,
    'user_id': userId,
    'department_id': departmentId,
    'current_department_id': currentDepartmentId,
    'attachments_id': attachmentsId,
    'auth_id': authId,
    'title': title,
    'description': description,
    'status': status,
    'is_valid': isValid ? 1 : 0,
    'created_at': createdAt?.toIso8601String(),
    'resolved_at': resolvedAt?.toIso8601String(),
    'assigned_level': assignedLevel,
  };
}
