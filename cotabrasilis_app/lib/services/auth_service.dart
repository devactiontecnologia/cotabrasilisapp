import '../models/user_model.dart';
import 'api_client.dart';

class AuthService {
  final _api = ApiClient();

  Future<LoginResult> login(String email, String password) async {
    final body = await _api.postJson('/login', body: {
      'email': email,
      'password': password,
    }, auth: false);

    if (body['success'] == true && body['token'] != null) {
      await _api.setToken(body['token'] as String);
      final user = UserModel.fromJson(body['user'] as Map<String, dynamic>);
      return LoginResult.success(user: user);
    }
    return LoginResult.failure(message: body['message'] as String? ?? 'Credenciais inválidas');
  }

  Future<void> logout() async {
    try {
      await _api.postJson('/logout');
    } catch (_) {}
    await _api.removeToken();
  }

  Future<UserModel?> getUser() async {
    final body = await _api.getJson('/user');
    if (body['success'] == true && body['user'] != null) {
      return UserModel.fromJson(body['user'] as Map<String, dynamic>);
    }
    return null;
  }

  Future<bool> hasToken() async {
    return await _api.getToken() != null;
  }
}

class LoginResult {
  final bool success;
  final UserModel? user;
  final String? message;

  LoginResult._({required this.success, this.user, this.message});

  factory LoginResult.success({required UserModel user}) =>
      LoginResult._(success: true, user: user);

  factory LoginResult.failure({String? message}) =>
      LoginResult._(success: false, message: message);
}
