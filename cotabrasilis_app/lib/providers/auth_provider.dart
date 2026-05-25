import 'package:flutter/foundation.dart';
import '../models/user_model.dart';
import '../services/auth_service.dart';

class AuthProvider with ChangeNotifier {
  final AuthService _authService = AuthService();

  UserModel? _user;
  bool _loading = true;
  bool _initialized = false;

  UserModel? get user => _user;
  bool get loading => _loading;
  bool get isAuthenticated => _user != null;

  Future<void> init() async {
    if (_initialized) return;
    _initialized = true;
    _loading = true;
    notifyListeners();
    try {
      final hasToken = await _authService.hasToken();
      if (hasToken) {
        _user = await _authService.getUser();
      } else {
        _user = null;
      }
    } catch (e) {
      _user = null;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<String?> login(String email, String password) async {
    final result = await _authService.login(email, password);
    if (result.success && result.user != null) {
      _user = result.user;
      notifyListeners();
      return null;
    }
    return result.message ?? 'Erro ao fazer login';
  }

  Future<void> logout() async {
    await _authService.logout();
    _user = null;
    notifyListeners();
  }

  void setUser(UserModel? u) {
    _user = u;
    notifyListeners();
  }
}
