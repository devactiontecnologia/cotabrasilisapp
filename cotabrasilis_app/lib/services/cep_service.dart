import 'dart:convert';
import 'package:http/http.dart' as http;

/// Resposta do ViaCEP (https://viacep.com.br/)
class CepResult {
  final String? cep;
  final String? logradouro;
  final String? complemento;
  final String? bairro;
  final String? localidade;
  final String? uf;
  final String? erro;

  CepResult({
    this.cep,
    this.logradouro,
    this.complemento,
    this.bairro,
    this.localidade,
    this.uf,
    this.erro,
  });

  factory CepResult.fromJson(Map<String, dynamic> json) {
    return CepResult(
      cep: json['cep'] as String?,
      logradouro: json['logradouro'] as String?,
      complemento: json['complemento'] as String?,
      bairro: json['bairro'] as String?,
      localidade: json['localidade'] as String?,
      uf: json['uf'] as String?,
      erro: json['erro'] as String?,
    );
  }
}

class CepService {
  static const _baseUrl = 'https://viacep.com.br/ws';

  /// Busca endereço pelo CEP (apenas dígitos, 8 caracteres).
  static Future<CepResult?> fetchByCep(String cepDigits) async {
    final digits = cepDigits.replaceAll(RegExp(r'[^0-9]'), '');
    if (digits.length != 8) return null;
    try {
      final res = await http.get(Uri.parse('$_baseUrl/$digits/json/')).timeout(
        const Duration(seconds: 10),
      );
      if (res.statusCode != 200) return null;
      final map = json.decode(res.body) as Map<String, dynamic>?;
      if (map == null) return null;
      final result = CepResult.fromJson(map);
      if (result.erro == 'true') return null;
      return result;
    } catch (_) {
      return null;
    }
  }
}
