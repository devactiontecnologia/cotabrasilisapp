import 'package:flutter/material.dart';
import '../models/quota_model.dart';
import '../services/quota_service.dart';
import 'quota_detail_screen.dart';
import '../utils/formatters.dart';
import '../widgets/logo_image.dart';

const _primary = Color(0xFF198754);

class QuotasScreen extends StatefulWidget {
  const QuotasScreen({super.key});

  @override
  State<QuotasScreen> createState() => _QuotasScreenState();
}

class _QuotasScreenState extends State<QuotasScreen> {
  final _service = QuotaService();
  List<QuotaModel> _quotas = [];
  bool _loading = true;
  final _searchController = TextEditingController();

  Future<void> _load() async {
    try {
      final list = await _service.search(hotelName: _searchController.text.isEmpty ? null : _searchController.text);
      if (mounted) setState(() => _quotas = list);
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
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            const LogoImage(height: 32),
            const SizedBox(width: 10),
            const Text('Cotas'),
          ],
        ),
        backgroundColor: _primary,
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              decoration: const InputDecoration(
                hintText: 'Buscar por hotel...',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.search),
              ),
              onSubmitted: (_) => _load(),
            ),
          ),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator(color: _primary))
                : RefreshIndicator(
                    onRefresh: _load,
                    color: _primary,
                    child: _quotas.isEmpty
                        ? ListView(
                            children: const [
                              SizedBox(height: 80),
                              Center(
                                child: Text('Nenhuma cota encontrada. Use os filtros para buscar.', textAlign: TextAlign.center, style: TextStyle(color: Colors.grey)),
                              ),
                            ],
                          )
                        : ListView.builder(
                            padding: const EdgeInsets.fromLTRB(16, 0, 16, 100),
                            itemCount: _quotas.length,
                            itemBuilder: (context, i) {
                              final q = _quotas[i];
                              return Card(
                                margin: const EdgeInsets.only(bottom: 12),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                child: ListTile(
                                  contentPadding: const EdgeInsets.all(16),
                                  title: Text(q.hotelName, style: const TextStyle(fontWeight: FontWeight.w600)),
                                  subtitle: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(q.location ?? '-'),
                                      Text('${formatDate(q.startDate)} - ${formatDate(q.endDate)}'),
                                      Text(formatPrice(q.rentalPrice), style: const TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                                    ],
                                  ),
                                  onTap: () => Navigator.push(context, MaterialPageRoute(
                                    builder: (_) => QuotaDetailScreen(quotaId: q.id),
                                  )),
                                ),
                              );
                            },
                          ),
                  ),
          ),
        ],
      ),
    );
  }
}
