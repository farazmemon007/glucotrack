import 'package:shared_preferences/shared_preferences.dart';

class ApiConfig {
  // Default URL - Can be your hosting subdomain (e.g. https://api.yourdomain.com/api)
  // Or your computer's local WiFi IP (e.g. http://192.168.1.100:8000/api) when testing on a physical phone
  static const String defaultBaseUrl = 'http://10.0.2.2:8000/api';
  static const String _keyBaseUrl = 'custom_base_url';

  static Future<String> getBaseUrl() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_keyBaseUrl) ?? defaultBaseUrl;
  }

  static Future<void> setBaseUrl(String newUrl) async {
    final prefs = await SharedPreferences.getInstance();
    String formattedUrl = newUrl.trim();
    if (formattedUrl.endsWith('/')) {
      formattedUrl = formattedUrl.substring(0, formattedUrl.length - 1);
    }
    if (!formattedUrl.endsWith('/api')) {
      formattedUrl = '$formattedUrl/api';
    }
    await prefs.setString(_keyBaseUrl, formattedUrl);
  }
}
