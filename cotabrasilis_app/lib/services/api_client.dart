import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';

const _tokenKey = 'cotabrasilis_token';

class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;

  ApiClient._internal();

  String? _token;

  Future<String?> getToken() async {
    if (_token != null) return _token;
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_tokenKey);
    return _token;
  }

  Future<void> setToken(String token) async {
    _token = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
  }

  Future<void> removeToken() async {
    _token = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
  }

  Future<Map<String, String>> _headers({bool auth = true}) async {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (auth) {
      final token = await getToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }
    }
    return headers;
  }

  Future<http.Response> get(String path, {Map<String, String>? params, bool auth = true}) async {
    var uri = Uri.parse('$apiBaseUrl$path');
    if (params != null && params.isNotEmpty) {
      uri = uri.replace(queryParameters: params);
    }
    return http.get(uri, headers: await _headers(auth: auth));
  }

  Future<http.Response> post(String path, {Map<String, dynamic>? body, bool auth = true}) async {
    final uri = Uri.parse('$apiBaseUrl$path');
    return http.post(
      uri,
      headers: await _headers(auth: auth),
      body: body != null ? jsonEncode(body) : null,
    );
  }

  dynamic _handleResponse(http.Response response) {
    if (response.body.isEmpty) return null;
    return jsonDecode(response.body);
  }

  Future<Map<String, dynamic>> getJson(String path, {Map<String, String>? params, bool auth = true}) async {
    final response = await get(path, params: params, auth: auth);
    return _handleResponse(response) as Map<String, dynamic>? ?? {};
  }

  Future<Map<String, dynamic>> postJson(String path, {Map<String, dynamic>? body, bool auth = true}) async {
    final response = await post(path, body: body, auth: auth);
    return _handleResponse(response) as Map<String, dynamic>? ?? {};
  }
}
