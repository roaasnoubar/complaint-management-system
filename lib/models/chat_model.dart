class ChatModel {
  final int? id;
  final int complainId;
  final int userId;
  final bool isOpen;
  final DateTime? closedAt;

  const ChatModel({
    this.id,
    required this.complainId,
    required this.userId,
    required this.isOpen,
    this.closedAt,
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

  factory ChatModel.fromJson(Map<String, dynamic> json) {
    return ChatModel(
      id: json['chat_id'] == null ? null : _readInt(json, 'chat_id'),
      complainId: _readInt(json, 'complain_id'),
      userId: _readInt(json, 'user_id'),
      isOpen: _readBool(json, 'is_open'),
      closedAt: _readNullableDateTime(json, 'closed_at'),
    );
  }

  Map<String, dynamic> toJson() => {
    'chat_id': id,
    'complain_id': complainId,
    'user_id': userId,
    'is_open': isOpen ? 1 : 0,
    'closed_at': closedAt?.toIso8601String(),
  };
}
