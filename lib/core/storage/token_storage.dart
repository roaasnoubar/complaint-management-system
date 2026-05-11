import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class TokenStorage {
  TokenStorage._();

  static const _key = 'auth_token';

  static const FlutterSecureStorage _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );

  static String? _cachedToken;

  /// Call once during startup to allow sync [hasToken] checks.
  static Future<void> init() async {
    _cachedToken = await _storage.read(key: _key);
  }

  static bool get hasToken => (_cachedToken ?? '').trim().isNotEmpty;

  static Future<String?> read() async {
    _cachedToken ??= await _storage.read(key: _key);
    return _cachedToken;
  }

  static Future<void> save(String token) async {
    final t = token.trim();
    _cachedToken = t.isEmpty ? null : t;
    if (t.isEmpty) {
      await _storage.delete(key: _key);
      return;
    }
    await _storage.write(key: _key, value: t);
  }

  static Future<void> clear() async {
    _cachedToken = null;
    await _storage.delete(key: _key);
  }
}

