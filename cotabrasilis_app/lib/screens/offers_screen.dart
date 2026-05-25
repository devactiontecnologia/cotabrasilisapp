import 'package:flutter/material.dart';
import '../models/offer_model.dart';
import '../services/offer_service.dart';
import 'offer_detail_screen.dart';
import '../utils/formatters.dart';
import '../widgets/logo_image.dart';

const _primary = Color(0xFF198754);

class OffersScreen extends StatefulWidget {
  const OffersScreen({super.key});

  @override
  State<OffersScreen> createState() => _OffersScreenState();
}

class _OffersScreenState extends State<OffersScreen> {
  final _service = OfferService();
  List<OfferModel> _offers = [];
  bool _loading = true;

  Future<void> _load() async {
    try {
      final list = await _service.getOffers(perPage: 20);
      if (mounted) setState(() => _offers = list);
    } catch (e) {
      debugPrint('Erro: $e');
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            const LogoImage(height: 32),
            const SizedBox(width: 10),
            const Text('Ofertas'),
          ],
        ),
        backgroundColor: _primary,
        foregroundColor: Colors.white,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: _primary))
          : RefreshIndicator(
              onRefresh: _load,
              color: _primary,
              child: _offers.isEmpty
                  ? ListView(
                      children: const [
                        SizedBox(height: 80),
                        Center(
                          child: Text('Nenhuma oferta encontrada.', style: TextStyle(color: Colors.grey)),
                        ),
                      ],
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
                      itemCount: _offers.length,
                      itemBuilder: (context, i) {
                        final o = _offers[i];
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          child: ListTile(
                            contentPadding: const EdgeInsets.all(16),
                            title: Text(o.title, style: const TextStyle(fontWeight: FontWeight.w600)),
                            subtitle: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('${o.city ?? ''}, ${o.state ?? ''}'),
                                Text('${o.numberOfDays ?? '-'} noites • ${o.numberOfPeople ?? '-'} hóspedes'),
                                Text(formatPrice(o.price), style: const TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                              ],
                            ),
                            onTap: () => Navigator.push(context, MaterialPageRoute(
                              builder: (_) => OfferDetailScreen(offerId: o.id),
                            )),
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
