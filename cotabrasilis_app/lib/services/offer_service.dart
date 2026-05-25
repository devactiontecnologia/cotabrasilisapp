import '../models/offer_model.dart';
import 'api_client.dart';

class OfferService {
  final _api = ApiClient();

  Future<List<OfferModel>> getOffers({int perPage = 20}) async {
    final body = await _api.getJson('/rental-offers', params: {'per_page': perPage.toString()}, auth: false);
    final data = body['data'];
    if (data is List) {
      return data.map((e) => OfferModel.fromJson(e as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<OfferModel?> getById(int id) async {
    final body = await _api.getJson('/rental-offers/$id', auth: false);
    final data = body['data'];
    if (data is Map<String, dynamic>) {
      return OfferModel.fromJson(data);
    }
    return null;
  }

  Future<List<OfferModel>> getMyOffers() async {
    final body = await _api.getJson('/rental-offers/my');
    final data = body['data'];
    if (data is List) {
      return data.map((e) => OfferModel.fromJson(e as Map<String, dynamic>)).toList();
    }
    return [];
  }
}
