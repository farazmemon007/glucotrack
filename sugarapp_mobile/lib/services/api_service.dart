import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';
import '../models/daily_log_model.dart';
import '../models/patient_model.dart';
import '../models/user_model.dart';

class ApiService {
  static const String _keyToken = 'auth_token';
  static const String _keyActivePatient = 'active_patient_id';

  // Token management
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_keyToken);
  }

  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyToken, token);
  }

  static Future<void> clearAuth() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keyToken);
    await prefs.remove(_keyActivePatient);
  }

  // Active Patient ID
  static Future<int?> getActivePatientId() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getInt(_keyActivePatient);
  }

  static Future<void> setActivePatientId(int patientId) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(_keyActivePatient, patientId);
  }

  // Standard Headers
  static Future<Map<String, String>> _headers() async {
    final token = await getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // 1. Auth: Login
  static Future<Map<String, dynamic>> login(String login, String password) async {
    final baseUrl = await ApiConfig.getBaseUrl();
    final url = Uri.parse('$baseUrl/login');

    final response = await http.post(
      url,
      headers: await _headers(),
      body: jsonEncode({'login': login, 'password': password}),
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 200 && data['success'] == true) {
      await saveToken(data['token']);
      if (data['patients'] != null && (data['patients'] as List).isNotEmpty) {
        final firstPatient = data['patients'][0];
        await setActivePatientId(firstPatient['id']);
      }
      return data;
    } else {
      throw Exception(data['message'] ?? 'Login failed');
    }
  }

  // 2. Auth: Register
  static Future<Map<String, dynamic>> register({
    required String name,
    required String phone,
    String? email,
    required String password,
  }) async {
    final baseUrl = await ApiConfig.getBaseUrl();
    final url = Uri.parse('$baseUrl/register');

    final response = await http.post(
      url,
      headers: await _headers(),
      body: jsonEncode({
        'name': name,
        'phone': phone,
        'email': email,
        'password': password,
        'role': 'caregiver',
      }),
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 201 && data['success'] == true) {
      await saveToken(data['token']);
      if (data['patients'] != null && (data['patients'] as List).isNotEmpty) {
        await setActivePatientId(data['patients'][0]['id']);
      }
      return data;
    } else {
      throw Exception(data['message'] ?? 'Registration failed');
    }
  }

  // 3. User Profile
  static Future<UserModel> getProfile() async {
    final baseUrl = await ApiConfig.getBaseUrl();
    final url = Uri.parse('$baseUrl/me');

    final response = await http.get(url, headers: await _headers());
    final data = jsonDecode(response.body);

    if (response.statusCode == 200 && data['success'] == true) {
      return UserModel.fromJson(data['user']);
    } else {
      throw Exception(data['message'] ?? 'Failed to load profile');
    }
  }

  // 4. Patients
  static Future<List<PatientModel>> getPatients() async {
    final baseUrl = await ApiConfig.getBaseUrl();
    final url = Uri.parse('$baseUrl/patients');

    final response = await http.get(url, headers: await _headers());
    final data = jsonDecode(response.body);

    if (response.statusCode == 200 && data['success'] == true) {
      return (data['patients'] as List)
          .map((p) => PatientModel.fromJson(p))
          .toList();
    } else {
      throw Exception(data['message'] ?? 'Failed to load patients');
    }
  }

  static Future<PatientModel> addPatient(Map<String, dynamic> patientData) async {
    final baseUrl = await ApiConfig.getBaseUrl();
    final url = Uri.parse('$baseUrl/patients');

    final response = await http.post(
      url,
      headers: await _headers(),
      body: jsonEncode(patientData),
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 201 && data['success'] == true) {
      return PatientModel.fromJson(data['patient']);
    } else {
      throw Exception(data['message'] ?? 'Failed to add patient');
    }
  }

  // 5. Daily Logs
  static Future<DailyLogModel?> getTodayLog(int patientId, [String? date]) async {
    final baseUrl = await ApiConfig.getBaseUrl();
    final query = date != null ? '?date=$date' : '';
    final url = Uri.parse('$baseUrl/patients/$patientId/logs/date$query');

    final response = await http.get(url, headers: await _headers());
    final data = jsonDecode(response.body);

    if (response.statusCode == 200 && data['success'] == true) {
      if (data['log'] != null) {
        return DailyLogModel.fromJson(data['log']);
      }
      return null;
    } else {
      throw Exception(data['message'] ?? 'Failed to load daily log');
    }
  }

  static Future<List<DailyLogModel>> getLogsHistory(int patientId) async {
    final baseUrl = await ApiConfig.getBaseUrl();
    final url = Uri.parse('$baseUrl/patients/$patientId/logs?per_page=30');

    final response = await http.get(url, headers: await _headers());
    final data = jsonDecode(response.body);

    if (response.statusCode == 200 && data['success'] == true) {
      return (data['data'] as List)
          .map((item) => DailyLogModel.fromJson(item))
          .toList();
    } else {
      throw Exception(data['message'] ?? 'Failed to load history');
    }
  }

  static Future<DailyLogModel> saveLog(int patientId, Map<String, dynamic> logData) async {
    final baseUrl = await ApiConfig.getBaseUrl();
    final url = Uri.parse('$baseUrl/patients/$patientId/logs');

    final response = await http.post(
      url,
      headers: await _headers(),
      body: jsonEncode(logData),
    );

    final data = jsonDecode(response.body);
    if ((response.statusCode == 200 || response.statusCode == 201) && data['success'] == true) {
      return DailyLogModel.fromJson(data['log']);
    } else {
      throw Exception(data['message'] ?? 'Failed to save daily log');
    }
  }
}
