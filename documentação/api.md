# Documentação — API (Cota Brasilis)

Este documento descreve os endpoints da API (routes/api.php), autenticação, exemplos de request/response e responsabilidades dos controllers da API.

## 1. Autenticação
- O projeto usa **Laravel Sanctum** para autenticação de token. Fluxo:
  1. Mobile faz `POST /api/login` com email + password.  
  2. Em resposta, a API devolve `success: true`, `token` (Bearer) e `user` (dados do usuário).  
  3. O app guarda o token localmente (ex.: `ApiClient.setToken`) e o envia em `Authorization: Bearer {token}` nas chamadas protegidas.
- Rotas protegidas agrupadas com middleware `auth:sanctum`.

## 2. Endpoints públicos (sem auth)
Local: `routes/api.php`

- `POST /api/login`  
  - Controller: `AuthApiController@login`  
  - Body: `{ "email": "...", "password": "..." }`  
  - Response (sucesso): `{ "success": true, "token": "xxx", "user": { ... } }`

- `POST /api/register`  
  - Controller: `AuthApiController@register`  
  - Body: JSON com todos os campos do formulário (nome, email, senha, dados pessoais, base64 imagens, has_quota, quota details quando aplicável)  
  - Resposta: `{ "success": true, "token": "xxx", "user": { ... } }`

- `GET /api/quotas/search`  
  - Controller: `QuotaApiController@search` — busca quotas públicas com filtros.

- `GET /api/quotas/featured`  
  - Controller: `QuotaApiController@featured` — quotas em destaque.

- `GET /api/quotas/{quota}`  
  - Controller: `QuotaApiController@show` — detalhes de uma cota pública.

- `GET /api/rental-offers` and `GET /api/rental-offers/{rentalOffer}`  
  - Controller: `RentalOfferApiController` — listagem e detalhe de ofertas.

- `GET /api/hotels` and `GET /api/hotels/{hotel}`  
  - Controller: `HotelApiController@index` e `HotelApiController@show` — listagem e detalhes de hotéis.  
  - Parâmetro de busca: `?search=termo` (filtra por nome ou cidade). Retorna até 50 resultados por default.

## 3. Endpoints protegidos (auth:sanctum)

- `POST /api/logout` — `AuthApiController@logout`  
- `GET /api/user` — `UserApiController@me` (retorna dados do usuário autenticado)  
- `GET /api/dashboard` — `DashboardApiController@index` (dados para tela inicial protegida)  
- `GET /api/quotas/my` e `GET /api/quotas/my/list` — quotas do usuário autenticado (`QuotaApiController@myQuotas`, `myQuotasList`)  
- `GET /api/rental-offers/my` — ofertas do usuário autenticado (`RentalOfferApiController@myOffers`)

## 4. Formatos e contratos (exemplos)
- Login (request):
```json
POST /api/login
{ "email": "joao@example.com", "password": "senha123" }
```
- Login (response, success):
```json
{
  "success": true,
  "token": "eyJ...xyz",
  "user": { "id": 123, "name": "João", "email": "joao@example.com", "...": "..." }
}
```
- Hotel list:
```json
GET /api/hotels
{
  "success": true,
  "data": [
    { "id": 1, "name": "Hotel A", "city": "Cidade", "state": "SP", "images": [], "amenities": [], "stars": 4 },
    ...
  ]
}
```

## 5. Controllers da API (localização: `app/Http/Controllers/Api/`)
- `AuthApiController` — login, register, logout; decodifica imagens base64 no register e cria User + Profile + KYC; retorna token.  
- `QuotaApiController` — busca public quotas, quotas do usuário (myQuotas), detalhes.  
- `HotelApiController` — `index()` lista hotéis (`search` param), `show()` retorna detalhes.  
- `RentalOfferApiController`, `UserApiController`, `DashboardApiController` — endpoints relacionados a ofertas, dados do usuário e dashboard.

## 6. Erros e códigos de retorno
- 401: Unauthorized (token inválido/ausente)  
- 419: Page expired (CSRF) — aplicável a formulários web (não a API tokenizada)  
- 422: Unprocessable Entity — validações falharam (veja mensagens `errors` no corpo)

## 7. Boas práticas para o próximo desenvolvedor
- Documente novos endpoints em `routes/api.php` e atualize `docs/api.md`.  
- Para testes: use ferramentas como Postman ou HTTPie; quando usar Sanctum, crie token via `/login` e passe `Authorization: Bearer {token}`.  
- Valide sempre formas de upload (no mobile fotos são enviadas em base64 no register).

