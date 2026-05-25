# Documentação — Sistema Web (Cota Brasilis)

Versão: Documentação técnica e explicativa da aplicação web Laravel usada pelo projeto Cota Brasilis.

## Sumário
- Visão geral
- Telas principais (nomes e quantidade)
- Funcionalidades por área
- Controllers principais e responsabilidades
- Rotas relevantes (web)
- Variáveis e modelos importantes
- Observações para o próximo desenvolvedor

---

## 1. Visão geral
Aplicação web construída em Laravel (versão especificada no composer). Responsável por registrar usuários, gerenciar cotas hoteleiras, ofertas de aluguel/venda/troca, painéis administrativos, e páginas públicas (listagem de quotas, hotéis, ofertas).

Padrões usados:
- MVC padrão do Laravel
- Migrations para estrutura de banco
- Controllers organizados por área (Auth, Quotas, Offers, Admin, API)

## 2. Telas principais (frontend web)
Observação: a aplicação usa Blade templates em `resources/views/`.

- Tela de registro (register) — `resources/views/auth/register.blade.php`  
  - Função: cadastro completo multi-etapas (dados pessoais, documentos, cota, quartos, fracionamento, termos).  
  - Campos principais: usuário, e-mail, senha, CPF, telefone, foto do usuário, foto documento, possui cota, documentos de cota, hotel, quartos, tamanho, amenidades, semanas, observações, termos.  

- Tela de login (login) — `resources/views/auth/login.blade.php`  
- Home / Landing (welcome) — `resources/views/welcome.blade.php`  
- Páginas de cotas / listagem de cotas — `resources/views/quotas/*.blade.php`  
- Páginas de ofertas (aluguel / venda / troca) — `resources/views/rental-offers/*.blade.php`  
- Painel administrativo (hotels, users, fees) — `resources/views/admin/*`  
- Outras telas: transações, perfil de usuário, gestão de quotas, criação/edição de hotéis.

Quantidade de telas: a app é modular; as views principais relacionadas ao fluxo de cadastro e gestão representam ~20-30 templates (views/), além dos includes/partials.

## 3. Funcionalidades por área
- Autenticação: cadastro, login, recuperação de senha (CSRF protegido).  
- Cadastro completo com upload de fotos (image handling) e validações (CPF, máscara, CEP lookup).  
- Gestão de cotas: criação de cota proprietária/gestor, configuração de quartos, fracionamento por semanas, autorizações para aluguel/troca/venda.  
- Hotéis: listagem, busca (API), seleção, detalhes.  
- Ofertas e transações: criar ofertas de aluguel/venda/troca, gerenciar transações de pagamento (integração Asaas).  
- Painel admin: administrar hotéis, taxas de sucesso (success fees), usuários.  
- APIs públicas para buscar quotas/hotéis e APIs autenticadas para operações do usuário.

## 4. Controllers principais (localização: `app/Http/Controllers/`)
Lista resumida (o projeto possui muitos controllers; abaixo os mais relevantes):
- `AuthController.php` — Cadastro web, validações, recebimento de formulários, criação de users, user_profiles, validações KYC.  
- `QuotaController.php` — Lida com lógica de quotas (criação via painel, validações de fracionamento e quartos).  
- `HotelController.php` / `AdminHotelController.php` — CRUD de hotéis (admin).  
- `RentalOfferController.php`, `SaleController.php`, `ExchangeController.php` — Criar gerenciar ofertas e buscas.  
- `PaymentController.php` / `AsaasWebhookController.php` — Integração com gateway (notificações, transações).  
- `UserProfileController.php` — Edição e visualização do perfil do usuário e dados de cota.  
- `QuotaManagementController.php` — Ferramentas adicionais para gerenciamento de cotas por admin/gestor.  
- `AuthApiController`, `QuotaApiController`, `HotelApiController` (em `app/Http/Controllers/Api/`) — Endpoints públicos/privados consumidos pelo mobile e SPA.

Cada controller contém métodos padrão: index, show, create, store, edit, update, destroy (quando aplicável) e métodos específicos (ex.: processPayment, search, authorizeWeek, uploadDocument).

## 5. Rotas web importantes
- Web routes: `routes/web.php` — páginas públicas, views e forms.  
- API routes: `routes/api.php` — endpoints REST para mobile/web AJAX.  
- Middlewares: `web` (sessions, CSRF), `auth`, `admin` (quando aplicável).

## 6. Variáveis / modelos (resumo)
Principais Models (local `app/Models/`): `User`, `UserProfile`, `Quota`, `QuotaTransaction`, `Hotel`, `RentalOffer`, `SaleOffer`, `PaymentTransaction`, `FavoriteList`, `SuccessFee`.  
Para cada model importante, verifique migrations em `database/migrations/` (nomes e campos).

Exemplos de campos (quota / user_profile):  
- `user_profiles` — owner_quota_rooms, owner_quota_size, owner_quota_weeks_count, allowed_uses, hospitality_authorization_term, owner_hotel_id, owner_room_{n}_* etc.  
- `hotels` — id, name, city, state, address, images (json), amenities (json), stars.

## 7. Observações e dicas para o próximo desenvolvedor
- CSRF: certifique-se que `APP_URL` coincide com a URL acessada (evita 419).  
- Sessões: `SESSION_DRIVER=database` exige tabela `sessions` (migration).  
- Uploads: FileUploadService usa GD para imagens; atente para permissões de `storage/` e `public/storage` (rodar `php artisan storage:link`).  
- Testes: rodar migrations, seeders e verificar `APP_ENV=local`.

---
> Local dos arquivos chaves: controllers em `app/Http/Controllers/`, views em `resources/views/`, migrations em `database/migrations/`, modelos em `app/Models/`.

