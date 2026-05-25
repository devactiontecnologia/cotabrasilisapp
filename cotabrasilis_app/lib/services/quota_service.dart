import '../models/quota_model.dart';
import 'api_client.dart';

class QuotaService {
  final _api = ApiClient();

  Future<List<QuotaModel>> search({String? hotelName, int perPage = 15, int page = 1}) async {
    final params = <String, String>{
      'per_page': perPage.toString(),
      'page': page.toString(),
    };
    if (hotelName != null && hotelName.isNotEmpty) {
      params['hotel_name'] = hotelName;
    }
    final body = await _api.getJson('/quotas/search', params: params, auth: false);
    final data = body['data'];
    if (data is List) {
      return data.map((e) => QuotaModel.fromJson(e as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<List<QuotaModel>> getFeatured() async {
    final body = await _api.getJson('/quotas/featured', auth: false);
    final data = body['data'];
    if (data is List) {
      return data.map((e) => QuotaModel.fromJson(e as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<QuotaModel?> getById(int id) async {
    final body = await _api.getJson('/quotas/$id', auth: false);
    final data = body['data'];
    if (data is Map<String, dynamic>) {
      return QuotaModel.fromJson(data);
    }
    return null;
  }

  Future<List<QuotaModel>> getMyQuotas() async {
    final body = await _api.getJson('/quotas/my');
    final data = body['data'];
    if (data is List) {
      return data.map((e) => QuotaModel.fromJson(e as Map<String, dynamic>)).toList();
    }
    return [];
  }
}
