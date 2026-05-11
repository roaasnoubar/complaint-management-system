class ComplaintModel {
  final int? id;
  final String? complainNumber;
  final int userId;
  final int? authorityId;
  final int departmentId;
  final int? currentDepartmentId;
  final int? attachmentsId;
  final List<String>? attachments;
  final String? fullName;
  final String title;
  final String description;
  final String status;
  final bool isValid;
  final DateTime? createdAt;
  final DateTime? resolvedAt;
  final int assignedLevel;
  final bool canChat;
  final String? currentLevelName;
  final String? levelName;
  final String? priority;

  const ComplaintModel({
    this.id,
    this.complainNumber,
    required this.userId,
    required this.authorityId,
    required this.departmentId,
    required this.currentDepartmentId,
    this.attachmentsId,
    this.attachments,
    required this.fullName,
    required this.title,
    required this.description,
    required this.status,
    required this.isValid,
    required this.createdAt,
    required this.resolvedAt,
    required this.assignedLevel,
    required this.canChat,
    required this.currentLevelName,
    this.levelName,
    this.priority,
  });

  static DateTime? _readDateTime(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value == null) return null;
    if (value is DateTime) return value;
    return DateTime.tryParse(value.toString());
  }

  static int _readInt(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  static int? _readNullableInt(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value == null) return null;
    if (value is int) return value;
    return int.tryParse(value.toString());
  }

  static bool _readBool(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value == null) return false;
    if (value is bool) return value;
    if (value is int) return value == 1;
    if (value is String) {
      final n = value.trim().toLowerCase();
      return n == 'true' || n == '1';
    }
    return false;
  }

  static String _readString(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value is String) return value;
    return value?.toString() ?? '';
  }

  static String? _readNullableString(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value == null) return null;
    return value.toString();
  }

  static String _levelNameFromAssignedLevel(int level) {
    switch (level) {
      case 3:
        return 'موظف مختص';
      case 2:
        return 'مدير قسم';
      case 1:
        return 'إدارة عليا';
      default:
        return 'تحت المعالجة';
    }
  }

  factory ComplaintModel.fromJson(Map<String, dynamic> json) {
    final assignedLevel = _readNullableInt(json, 'assigned_level') ?? 3;

    final serverLevelName =
        _readNullableString(json, 'current_level_name') ??
        _readNullableString(json, 'level_name');

    List<String>? attachmentsList;
    if (json['attachments'] is List) {
      attachmentsList = List<String>.from(
        (json['attachments'] as List).map((e) => e.toString()),
      );
    }

    return ComplaintModel(
      id:
          _readNullableInt(json, 'complaint_id') ??
          _readNullableInt(json, 'id'),
      complainNumber: _readNullableString(json, 'complain_number'),
      userId: _readInt(json, 'user_id'),
      authorityId:
          _readNullableInt(json, 'authority_id') ??
          _readNullableInt(json, 'auth_id'),
      departmentId: _readInt(json, 'department_id'),
      currentDepartmentId: _readNullableInt(json, 'current_department_id'),
      attachmentsId: _readNullableInt(json, 'attachments_id'),
      attachments: attachmentsList,
      fullName: _readNullableString(json, 'full_name'),
      title: _readString(json, 'title'),
      description: _readString(json, 'description'),
      status: _readNullableString(json, 'status') ?? 'Pending',
      isValid: _readBool(json, 'is_valid'),
      createdAt: _readDateTime(json, 'created_at'),
      resolvedAt: _readDateTime(json, 'resolved_at'),
      assignedLevel: assignedLevel,
      canChat: _readBool(json, 'can_chat'),
      currentLevelName:
          serverLevelName ?? _levelNameFromAssignedLevel(assignedLevel),
      levelName: _readNullableString(json, 'level_name'),
      priority: _readNullableString(json, 'priority'),
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'complain_number': complainNumber,
    'user_id': userId,
    'authority_id': authorityId,
    'department_id': departmentId,
    'current_department_id': currentDepartmentId,
    'attachments_id': attachmentsId,
    'attachments': attachments,
    'full_name': fullName,
    'title': title,
    'description': description,
    'status': status,
    'is_valid': isValid ? 1 : 0,
    'created_at': createdAt?.toIso8601String(),
    'resolved_at': resolvedAt?.toIso8601String(),
    'assigned_level': assignedLevel,
    'can_chat': canChat ? 1 : 0,
    'current_level_name': currentLevelName,
    'level_name': levelName,
    'priority': priority,
  };

  String get statusLabel {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'جديدة';
      case 'in progress':
        return 'قيد المعالجة';
      case 'resolved':
        return 'مغلقة';
      case 'rejected':
        return 'مرفوضة';
      default:
        return status;
    }
  }

  int get statusStepIndex {
    switch (status.toLowerCase()) {
      case 'in progress':
        return 1;
      case 'resolved':
        return 2;
      default:
        return 0; // pending
    }
  }

  bool get isClosed =>
      status.toLowerCase() == 'resolved' || status.toLowerCase() == 'rejected';
}
