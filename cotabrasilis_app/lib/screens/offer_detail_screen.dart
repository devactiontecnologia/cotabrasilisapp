import 'package:flutter/material.dart';
import '../models/offer_model.dart';
import '../services/offer_service.dart';
import '../utils/formatters.dart';
import '../widgets/logo_image.dart';

const _primary = Color(0xFF198754);

class OfferDetailScreen extends StatelessWidget {
  final int offerId;

  const OfferDetailScreen({super.key, required this.offerId});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            const LogoImage(height: 32),
            const SizedBox(width: 10),
            const Text('Detalhe da Oferta'),
          ],
        ),
        backgroundColor: _primary,
        foregroundColor: Colors.white,
      ),
      body: FutureBuilder<OfferModel?>(
        future: OfferService().getById(offerId),
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator(color: _primary));
          }
          final offer = snapshot.data;
          if (offer == null) {
            return const Center(child: Text('Oferta não encontrada'));
          }
          return SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                if (offer.firstImageUrl != null)
                  Image.network(offer.firstImageUrl!, height: 220, fit: BoxFit.cover)
                else
                  Container(
                    height: 220,
                    color: Colors.grey[300],
                    child: const Center(child: Text('Sem foto', style: TextStyle(color: Colors.grey))),
                  ),
                Container(
                  margin: const EdgeInsets.all(16),
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    boxShadow: [
                      BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 8, offset: const Offset(0, 2)),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(offer.title, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 4),
                      Text('${offer.city ?? ''}, ${offer.state ?? ''}', style: TextStyle(color: Colors.grey[600])),
                      _DetailRow('Período', '${formatDate(offer.startDate)} a ${formatDate(offer.endDate)}'),
                      _DetailRow('Noites', '${offer.numberOfDays ?? '-'}'),
                      _DetailRow('Hóspedes', '${offer.numberOfPeople ?? '-'}'),
                      const SizedBox(height: 20),
                      Text(formatPrice(offer.price), style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: _primary)),
                      if (offer.description != null && offer.description!.isNotEmpty) ...[
                        const SizedBox(height: 16),
                        const Divider(),
                        const Text('Descrição', style: TextStyle(fontSize: 14, color: Colors.grey)),
                        const SizedBox(height: 4),
                        Text(offer.description!, style: const TextStyle(fontSize: 14)),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  final String label;
  final String value;

  const _DetailRow(this.label, this.value);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: Colors.grey[600])),
          Text(value, style: const TextStyle(fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}
