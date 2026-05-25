# Documentação — Aplicativo Mobile (Flutter)

Este documento descreve a arquitetura do aplicativo mobile Flutter que consome a API do Cota Brasilis, com lista de telas, serviços, providers, modelos e comportamento principal.

## 1. Visão geral
- Projeto Flutter em `cotabrasilis_app/`  
- Comunicação com backend via `ApiClient` (`lib/services/api_client.dart`) consumindo os endpoints do Laravel (Sanctum para rotas autenticadas).  
- State management: `provider` (ex.: `AuthProvider`).

## 2. Telas (screens) — lista e propósito
As telas estão em `lib/screens/`:

- `HomeScreen` (`home_screen.dart`) — Tela inicial / feed; mostra quotas e ofertas em destaque.  
- `LoginScreen` (`login_screen.dart`) — Tela de autenticação (email + senha).  
- `RegisterScreen` (`register_screen.dart`) — Registro multi-etapas (completa): login info, dados pessoais, endereço, documentos, cota, perfil, fracionamento, termos. (tela complexa — ver documentação inline de `register_screen.dart`)  
- `QuotasScreen` (`quotas_screen.dart`) — Listagem de cotas do usuário / pesquisa.  
- `QuotaDetailScreen` (`quota_detail_screen.dart`) — Detalhe de uma cota.  
- `OffersScreen` (`offers_screen.dart`) — Listagem de ofertas (aluguel/venda).  
- `OfferDetailScreen` (`offer_detail_screen.dart`) — Detalhe de oferta.  
- `ProfileScreen` (`profile_screen.dart`) — Perfil do usuário, logout, dados.

Total de telas principais: ~8 telas públicas + telas de detalhe e formulários específicos (~12 a 15 widgets de tela).

## 3. Providers e serviços principais
- `AuthProvider` (`lib/providers/auth_provider.dart`) — ChangeNotifier que mantém `UserModel? _user`, `loading`, métodos: `init()`, `login(email,password)`, `logout()`, `setUser(...)`. Usa `AuthService`.  
- `AuthService` (`lib/services/auth_service.dart`) — Faz chamadas à API: `/login`, `/register`, `/logout`, `/user`. Trata token via `ApiClient`.  
- `ApiClient` (`lib/services/api_client.dart`) — HTTP client wrapper (usa `http` package). Métodos: `get`, `post`, `getJson`, `postJson`, `getToken`, `setToken`, `removeToken`. Lê `apiBaseUrl` de `lib/config/api_config.dart`.  
- `QuotaService`, `OfferService`, `CepService` (em `lib/services/*`) — serviços que encapsulam endpoints específicos: quotas, offers, CEP lookup (ViaCEP).

## 4. Modelos (exemplos)
- `UserModel` (`lib/models/user_model.dart`) — campos: `id`, `name`, `email`, `token?`, `profile` etc. (usado por AuthProvider).  
- `Quota`, `Offer` models — representações simples dos objetos retornados pela API (ver `lib/models/` se existir).

## 5. Fluxos importantes
- Login: `LoginScreen` -> `AuthProvider.login()` -> `AuthService.login()` -> `/login` (API) -> guarda token com `ApiClient.setToken()` -> `AuthProvider.setUser()`.  
- Registro: `RegisterScreen` coleta dados e submete `POST /register` via `ApiClient.postJson('/register', body: {...})` (envia imagens em base64 conforme implementado no controller).  
- Hotéis: `HotelSearchSheet` (modal) -> chama `/hotels` e `/hotels?search=...` (implementação do modal faz fetch por API com debounce).

## 6. Arquivos-chaves (onde procurar)
- `lib/screens/register_screen.dart` — fluxo mais complexo (masks, image_picker, validations, weeks/fracionamento).  
- `lib/services/api_client.dart` — cliente HTTP e gestão de token local.  
- `lib/providers/auth_provider.dart` e `lib/services/auth_service.dart`.

## 7. Variáveis e estado importantes
- Token de autenticação: gerenciado por `ApiClient` (armazenado com `shared_preferences`) — método `getToken()` e `setToken(token)`.  
- Em `RegisterScreen`: muitos controllers `TextEditingController` (nome, email, cpf, cep, street, city, state, etc.), arquivos temporários `File? _userPhoto`, `_documentPhoto`, `_quotaContractFile`, `_hospitalityAuthorizationTermFile`.  
- Em `AuthProvider`: `_user`, `_loading`, `_initialized`.

## 8. Observações para próximo desenvolvedor (mobile)
- Ao trabalhar com uploads: a API do backend aceita fotos codificadas em base64 no corpo do JSON (o controller decodifica e grava). Para arquivos PDF, a versão mobile atual não usa `file_picker` por problemas nativos — recomenda-se enviar fotos (JPG/PNG) ou usar website para PDFs.  
- Ajuste `lib/config/api_config.dart` para apontar para o host correto (emulador/device).  
- Testar em real device: use IP do PC e verifique CORS/Firewalls.

