import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'screens/login_screen.dart';
import 'screens/home_screen.dart';
import 'screens/quotas_screen.dart';
import 'screens/offers_screen.dart';
import 'screens/profile_screen.dart';

const _primary = Color(0xFF198754);

void main() {
  runApp(const CotaBrasilisApp());
}

class CotaBrasilisApp extends StatelessWidget {
  const CotaBrasilisApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => AuthProvider()..init(),
      child: MaterialApp(
        title: 'Cota Brasilis',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          colorScheme: ColorScheme.fromSeed(seedColor: _primary, primary: _primary),
          useMaterial3: true,
        ),
        home: Consumer<AuthProvider>(
          builder: (context, auth, _) {
            if (auth.loading) {
              return const Scaffold(
                body: Center(child: CircularProgressIndicator(color: _primary)),
              );
            }
            if (!auth.isAuthenticated) {
              return const LoginScreen();
            }
            return const MainTabs();
          },
        ),
      ),
    );
  }
}

class MainTabs extends StatelessWidget {
  const MainTabs({super.key});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 4,
      child: Scaffold(
        bottomNavigationBar: Container(
          decoration: BoxDecoration(
            color: Colors.white,
            boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 8, offset: const Offset(0, -2))],
          ),
          child: TabBar(
            labelColor: _primary,
            unselectedLabelColor: Colors.grey,
            indicatorColor: _primary,
            tabs: const [
              Tab(icon: Icon(Icons.home), text: 'Início'),
              Tab(icon: Icon(Icons.apartment), text: 'Cotas'),
              Tab(icon: Icon(Icons.hotel), text: 'Ofertas'),
              Tab(icon: Icon(Icons.person), text: 'Perfil'),
            ],
          ),
        ),
        body: const TabBarView(
          children: [
            HomeScreen(),
            QuotasScreen(),
            OffersScreen(),
            ProfileScreen(),
          ],
        ),
      ),
    );
  }
}
