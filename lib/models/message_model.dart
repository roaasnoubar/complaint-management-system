class MessageModel {
  final int? id;
  final int chatId;
  final int senderId;
  final String message;
  final DateTime? sentAt;

  const MessageModel({
    this.id,
    required this.chatId,
    required this.senderId,
    required this.message,
    this.sentAt,
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

  static String _readString(Map<String, dynamic> json, String key) {
    final value = json[key];
    if (value is String) return value;
    if (value == null) return '';
    return value.toString();
  }

  factory MessageModel.fromJson(Map<String, dynamic> json) {
    return MessageModel(
      id: json['message_id'] == null ? null : _readInt(json, 'message_id'),
      chatId: _readInt(json, 'chat_id'),
      senderId: _readInt(json, 'sender_id'),
      message: _readString(json, 'message'),
      sentAt: _readNullableDateTime(json, 'sent_at'),
    );
  }

  Map<String, dynamic> toJson() => {
    'message_id': id,
    'chat_id': chatId,
    'sender_id': senderId,
    'message': message,
    'sent_at': sentAt?.toIso8601String(),
  };
}
