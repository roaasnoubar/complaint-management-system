class RolePermissionModel {
  final int? id;
  final int? roleId;
  final int? permissionId;

  const RolePermissionModel({this.id, this.roleId, this.permissionId});

  static int? _asInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is String) return int.tryParse(value);
    return null;
  }

  factory RolePermissionModel.fromJson(Map<String, dynamic> json) {
    return RolePermissionModel(
      id: _asInt(json['role_permission_id']),
      roleId: _asInt(json['role_id']),
      permissionId: _asInt(json['permission_id']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'role_permission_id': id,
      'role_id': roleId,
      'permission_id': permissionId,
    };
  }
}
