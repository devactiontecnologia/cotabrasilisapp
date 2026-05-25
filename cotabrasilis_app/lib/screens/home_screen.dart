import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/quota_model.dart';
import '../models/offer_model.dart';
import '../providers/auth_provider.dart';
import '../services/quota_service.dart';
import '../services/offer_service.dart';
import 'quota_detail_screen.dart';
import 'offer_detail_screen.dart';
import '../utils/formatters.dart';
import '../widgets/logo_image.dart';

const _primary = Color(0xFF198754);

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final _quotaService = QuotaService();
  final _offerService = OfferService();
  List<QuotaModel> _featured = [];
  List<OfferModel> _offers = [];
  bool _loading = true;

  Future<void> _load() async {
    try {
      final featuredList = await _quotaService.getFeatured();
      final offersList = await _offerService.getOffers(perPage: 10);
      if (mounted) {
        setState(() {
          _featured = featuredList;
          _offers = offersList;
        });
      }
    } catch (e) {
      debugPrint('Erro ao carregar: $e');
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
    }
  }

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final firstName = user?.profile?.fullName?.split(' ').first ?? '';

    if (_loading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator(color: _primary)),
      );
    }

    return Scaffold(
      body: RefreshIndicator(
        onRefresh: _load,
        color: _primary,
        child: CustomScrollView(
          slivers: [
            SliverToBoxAdapter(
              child: Container(
                padding: const EdgeInsets.fromLTRB(20, 48, 20, 24),
                color: _primary,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const LogoImage(height: 44),
                    const SizedBox(height: 12),
                    Text('Olá${firstName.isNotEmpty ? ', $firstName' : ''}!', style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    )),
                    const SizedBox(height: 4),
                    Text('Encontre cotas e ofertas de aluguel', style: TextStyle(
                      fontSize: 14,
                      color: Colors.white.withValues(alpha: 0.9),
                    )),
                  ],
                ),
              ),
            ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 20, 16, 12),
                child: Text('Em destaque', style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                  color: Colors.grey[800],
                )),
              ),
            ),
            if (_featured.isEmpty)
              const SliverToBoxAdapter(child: SizedBox(height: 80))
            else
              SliverToBoxAdapter(
                child: SizedBox(
                  height: 180,
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                    itemCount: _featured.length,
                    itemBuilder: (context, i) {
                      final q = _featured[i];
                      return _QuotaCard(
                        quota: q,
                        onTap: () => Navigator.push(context, MaterialPageRoute(
                          builder: (_) => QuotaDetailScreen(quotaId: q.id),
                        )),
                      );
                    },
                  ),
                ),
              ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 20, 16, 12),
                child: Text('Ofertas de aluguel', style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                  color: Colors.grey[800],
                )),
              ),
            ),
            if (_offers.isEmpty)
              const SliverToBoxAdapter(child: Padding(
                padding: EdgeInsets.all(40),
                child: Center(child: Text('Nenhuma oferta encontrada', style: TextStyle(color: Colors.grey))),
              ))
            else
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(12, 0, 12, 24),
                sliver: SliverGrid(
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    childAspectRatio: 0.75,
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                  ),
                  delegate: SliverChildBuilderDelegate(
                    (context, i) {
                      final o = _offers[i];
                      return _OfferCard(
                        offer: o,
                        onTap: () => Navigator.push(context, MaterialPageRoute(
                          builder: (_) => OfferDetailScreen(offerId: o.id),
                        )),
                      );
                    },
                    childCount: _offers.length,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _QuotaCard extends StatelessWidget {
  final QuotaModel quota;
  final VoidCallback onTap;

  const _QuotaCard({required this.quota, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 160,
        margin: const EdgeInsets.only(right: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 4, offset: const Offset(0, 2)),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
              child: quota.firstImageUrl != null
                  ? Image.network(quota.firstImageUrl!, height: 100, fit: BoxFit.cover)
                  : Container(height: 100, color: Colors.grey[300], child: const Center(child: Text('Sem foto', style: TextStyle(color: Colors.grey)))),
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(quota.hotelName, maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                  const SizedBox(height: 4),
                  Text('${formatDate(quota.startDate)} - ${formatDate(quota.endDate)}', style: TextStyle(fontSize: 12, color: Colors.grey[600])),
                  const SizedBox(height: 4),
                  Text(formatPrice(quota.rentalPrice), style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: _primary)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _OfferCard extends StatelessWidget {
  final OfferModel offer;
  final VoidCallback onTap;

  const _OfferCard({required this.offer, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 4, offset: const Offset(0, 2)),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
              child: offer.firstImageUrl != null
                  ? Image.network(offer.firstImageUrl!, height: 100, fit: BoxFit.cover)
                  : Container(height: 100, color: Colors.grey[300], child: const Center(child: Text('Sem foto', style: TextStyle(color: Colors.grey)))),
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(offer.title, maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                  const SizedBox(height: 4),
                  Text('${offer.city ?? ''}, ${offer.state ?? ''}', style: TextStyle(fontSize: 12, color: Colors.grey[600])),
                  const SizedBox(height: 4),
                  Text(formatPrice(offer.price), style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: _primary)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
