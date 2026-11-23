import 'package:shared_preferences/shared_preferences.dart';

class ApiConfig {
  // Production Subdomain URL
  static const String defaultBaseUrl = 'https://glucotrack.binsultansweet.com/api';
  static const String _keyBaseUrl = 'custom_base_url_v2';

  static Future<String> getBaseUrl() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_keyBaseUrl);
    if (saved != null && saved.trim().startsWith('http')) {
      return saved.trim();
    }
    return defaultBaseUrl;
  }

  static Future<void> setBaseUrl(String newUrl) async {
    final prefs = await SharedPreferences.getInstance();
    String formatted = newUrl.trim();

    // Auto-correct if user pastes server path or bare domain
    if (formatted.contains('glucotrack.binsultansweet.com')) {
      formatted = 'https://glucotrack.binsultansweet.com/api';
    } else {
      if (!formatted.startsWith('http://') && !formatted.startsWith('https://')) {
        formatted = 'https://$formatted';
      }
      if (formatted.endsWith('/')) {
        formatted = formatted.substring(0, formatted.length - 1);
      }
      if (!formatted.endsWith('/api')) {
        formatted = '$formatted/api';
      }
    }
    await prefs.setString(_keyBaseUrl, formatted);
  }
}
