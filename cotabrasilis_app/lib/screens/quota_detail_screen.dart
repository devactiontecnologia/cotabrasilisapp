import 'package:flutter/material.dart';
import '../models/quota_model.dart';
import '../services/quota_service.dart';
import '../utils/formatters.dart';
import '../widgets/logo_image.dart';

const _primary = Color(0xFF198754);

class QuotaDetailScreen extends StatelessWidget {
  final int quotaId;

  const QuotaDetailScreen({super.key, required this.quotaId});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            const LogoImage(height: 32),
            const SizedBox(width: 10),
            const Text('Detalhe da Cota'),
          ],
        ),
        backgroundColor: _primary,
        foregroundColor: Colors.white,
      ),
      body: FutureBuilder<QuotaModel?>(
        future: QuotaService().getById(quotaId),
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator(color: _primary));
          }
          final quota = snapshot.data;
          if (quota == null) {
            return const Center(child: Text('Cota não encontrada'));
          }
          return SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                if (quota.firstImageUrl != null)
                  Image.network(quota.firstImageUrl!, height: 220, fit: BoxFit.cover)
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
                      Text(quota.hotelName, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 4),
                      Text(quota.location ?? '-', style: TextStyle(color: Colors.grey[600])),
                      _DetailRow('Período', '${formatDate(quota.startDate)} a ${formatDate(quota.endDate)}'),
                      _DetailRow('Hóspedes', '${quota.numberOfGuests ?? '-'}'),
                      _DetailRow('Quartos', '${quota.numberOfRooms ?? '-'}'),
                      const SizedBox(height: 20),
                      Text(formatPrice(quota.rentalPrice), style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: _primary)),
                      if (quota.observations != null && quota.observations!.isNotEmpty) ...[
                        const SizedBox(height: 16),
                        const Divider(),
                        const Text('Observações', style: TextStyle(fontSize: 14, color: Colors.grey)),
                        const SizedBox(height: 4),
                        Text(quota.observations!, style: const TextStyle(fontSize: 14)),
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
