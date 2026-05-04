class RoleModel {
  final int? id;
  final int? permissionId;
  final int? userId;
  final int level;

  const RoleModel({
    this.id,
    this.permissionId,
    this.userId,
    required this.level,
  });

  static int? _asInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is String) return int.tryParse(value);
    return null;
  }

  factory RoleModel.fromJson(Map<String, dynamic> json) {
    return RoleModel(
      id: _asInt(json['role_id']),
      permissionId: _asInt(json['permission_id']),
      userId: _asInt(json['user_id']),
      level: _asInt(json['level']) ?? 0,
    );
  }

  Map<String, dynamic> toJson() => {
        'role_id': id,
        'permission_id': permissionId,
        'user_id': userId,
        'level': level,
      };
}
