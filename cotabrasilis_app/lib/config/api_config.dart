/// Configuração da API Cota Brasilis
///
/// Escolha a URL conforme onde você está testando:
///
/// 1) ANDROID EMULADOR: use 10.0.2.2 (mapeia para o localhost do PC)
/// 2) DISPOSITIVO FÍSICO (celular): use o IP do seu PC na rede (ex: 192.168.1.10)
///    Descubra com: ipconfig (Windows) ou ifconfig (Mac/Linux)
/// 3) iOS SIMULADOR: localhost costuma funcionar
///
/// IMPORTANTE: O Laravel (php artisan serve) precisa estar rodando no PC!
const String _apiHost = '127.0.0.1'; // Mude para '192.168.x.x' no celular físico
const String _apiPort = '8000';
const String apiBaseUrl = 'http://$_apiHost:$_apiPort/api';
