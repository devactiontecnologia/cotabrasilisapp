# Cota Brasilis - API e App Mobile

## API REST (Laravel)

A API foi criada em `routes/api.php` e utiliza Laravel Sanctum para autenticação por token.

### Autenticação

- **POST** `/api/login` - Login (email, password) → retorna `token` e `user`
- **POST** `/api/logout` - Logout (requer Bearer token)
- **GET** `/api/user` - Dados do usuário autenticado (requer Bearer token)

### Rotas públicas (sem autenticação)

- **GET** `/api/quotas/search` - Busca cotas (params: hotel_name, city, state, guests, year, month, per_page, page)
- **GET** `/api/quotas/featured` - Cotas em destaque
- **GET** `/api/quotas/{id}` - Detalhe de uma cota
- **GET** `/api/rental-offers` - Lista ofertas de aluguel (params: city, state, min_price, max_price, hotel_id, per_page)
- **GET** `/api/rental-offers/{id}` - Detalhe de uma oferta
- **GET** `/api/hotels` - Lista hotéis (params: search, state, city)
- **GET** `/api/hotels/{id}` - Detalhe de um hotel

### Rotas protegidas (Bearer token)

- **GET** `/api/dashboard` - Resumo (cotas, ofertas, transações)
- **GET** `/api/quotas/my` - Minhas cotas
- **GET** `/api/quotas/my/list` - Lista simplificada para dropdown
- **GET** `/api/rental-offers/my` - Minhas ofertas

### Uso do token

Enviar no header: `Authorization: Bearer {token}`

### Base URL

- Local: `http://localhost:8000/api` (ou `http://10.0.2.2:8000/api` para Android Emulator)
- Produção: `https://seudominio.com/api`

---

## App Mobile (Flutter)

O app Flutter está em `cotabrasilis_app/`.

### Estrutura

```
cotabrasilis_app/
├── lib/
│   ├── config/
│   │   └── api_config.dart    # URL base da API
│   ├── models/
│   │   ├── user_model.dart
│   │   ├── quota_model.dart
│   │   └── offer_model.dart
│   ├── services/
│   │   ├── api_client.dart
│   │   ├── auth_service.dart
│   │   ├── quota_service.dart
│   │   └── offer_service.dart
│   ├── providers/
│   │   └── auth_provider.dart
│   ├── screens/
│   │   ├── login_screen.dart
│   │   ├── home_screen.dart
│   │   ├── quotas_screen.dart
│   │   ├── offers_screen.dart
│   │   ├── profile_screen.dart
│   │   ├── quota_detail_screen.dart
│   │   └── offer_detail_screen.dart
│   └── main.dart
├── android/
├── ios/
└── pubspec.yaml
```

### Configuração da API

Edite `cotabrasilis_app/lib/config/api_config.dart`:

- **Android Emulator**: `http://10.0.2.2:8000/api`
- **iOS Simulator**: `http://localhost:8000/api`
- **Dispositivo físico**: `http://SEU_IP:8000/api` (ex: `http://192.168.1.10:8000/api`)

### Executar o app

```bash
# Na raiz do projeto Laravel
cd cotabrasilis_app

# Obter dependências
flutter pub get

# Executar
flutter run

# Ou para Android especificamente
flutter run -d android

# Ou para iOS (Mac)
flutter run -d ios
```

### Funcionalidades

- Login com email/senha
- Listagem de cotas em destaque e ofertas de aluguel
- Busca de cotas
- Detalhes de cota e oferta
- Perfil do usuário
- Logout

### Cadastro

O cadastro completo deve ser feito pelo site (formulário extenso com documentos). O app exibe essa mensagem na tela de login.
