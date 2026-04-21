class RatingModel {
  final int? id;
  final int complainId;
  final int userId;
  final int authorityId;
  final int responseSpeedScore;

  const RatingModel({
    this.id,
    required this.complainId,
    required this.userId,
    required this.authorityId,
    required this.responseSpeedScore,
  });

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

  factory RatingModel.fromJson(Map<String, dynamic> json) {
    return RatingModel(
      id: _readNullableInt(json, 'rating_id'),
      complainId: _readInt(json, 'complain_id'),
      userId: _readInt(json, 'user_id'),
      authorityId: _readInt(json, 'authority_id'),
      responseSpeedScore: _readInt(json, 'response_speed_score'),
    );
  }

  Map<String, dynamic> toJson() => {
    'rating_id': id,
    'complain_id': complainId,
    'user_id': userId,
    'authority_id': authorityId,
    'response_speed_score': responseSpeedScore,
  };
}
