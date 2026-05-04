class PermissionModel {
  final int? id;
  final String name;
  final String description;

  const PermissionModel({
    this.id,
    required this.name,
    required this.description,
  });

  static int? _asInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is String) return int.tryParse(value);
    return null;
  }

  factory PermissionModel.fromJson(Map<String, dynamic> json) {
    return PermissionModel(
      id: _asInt(json['permission_id']),
      name: json['name'] as String,
      description: json['description'] as String,
    );
  }

  Map<String, dynamic> toJson() {
    return {'permission_id': id, 'name': name, 'description': description};
  }
}
