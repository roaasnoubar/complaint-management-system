class AttachmentModel {
  final int? id;
  final int? userId;
  final String filePath;
  final String fileType;

  const AttachmentModel({
    this.id,
    this.userId,
    required this.filePath,
    required this.fileType,
  });

  static int? _asInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is String) return int.tryParse(value);
    return null;
  }

  factory AttachmentModel.fromJson(Map<String, dynamic> json) {
    return AttachmentModel(
      id: _asInt(json['attachment_id']),
      userId: _asInt(json['user_id']),
      filePath: json['file_path'] as String,
      fileType: json['file_type'] as String,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'attachment_id': id,
      'user_id': userId,
      'file_path': filePath,
      'file_type': fileType,
    };
  }
}
