# Documentação — Banco de Dados (Cota Brasilis)

Este documento descreve a estrutura do banco de dados do projeto: tabelas principais, relacionamentos, campos relevantes e observações de implementação.

## 1. Visão geral
- Banco principal: MySQL (configurado em `.env`)  
- Driver principal usado: `pdo_mysql`  
- As migrations estão em `database/migrations/` e definem a estrutura do esquema.

## 2. Tabelas principais (resumo)
Listagem das tabelas mais importantes (nomes de migration aproximados / criação):

- `users`  
  - Campos principais: `id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`  
  - Observação: coluna `role` foi adicionada (migrations posteriores) para distinguir perfis (owner, gestor, admin).

- `user_profiles`  
  - Campos: `id`, `user_id` (FK), `full_name`, `cpf`, `phone`, `owner_quota_rooms`, `owner_quota_size`, `owner_quota_weeks_count`, `owner_hotel_id`, `owner_allowed_uses` (json), `hospitality_authorization_term` (file path), `...`  
  - Observação: armazena detalhes da cota e campos específicos para proprietários e gestores. Muitas colunas foram adicionadas por migrações (`add_owner_quota_fields_to_user_profiles_table`, etc).

- `hotels`  
  - Campos: `id`, `name`, `city`, `state`, `address`, `images` (json), `amenities` (json), `stars`, `is_active`, `created_at`, `updated_at`.

- `quotas`  
  - Campos: `id`, `user_id`, `hotel_id`, `number_of_rooms`, `size`, `seasonality`, `type`, `breakfast_included`, `weeks_count`, `allowed_uses` (json), `status`, `created_at`, `updated_at`.
  - Observação: armazena cota hoteleira que pode ser fracionada e ofertada.

- `quota_transactions` / `payment_transactions`  
  - Campos: `id`, `quota_id`, `user_id`, `amount`, `status`, `gateway_id`, `asaas_*` fields, `created_at`, `updated_at`.
  - Observação: integrações com gateway de pagamento (Asaas); há migrations para campos Asaas.

- `rental_offers`, `sale_offers`, `exchange_offers`, `purchase_requests`  
  - Tabelas para ofertas de aluguel, venda, troca e requisições de compra.

- `favorite_lists`, `wishlist_searches`  
  - Listas de favoritos e buscas salvas pelo usuário.

- `success_fees`  
  - Configuração de taxas de sucesso, usada em cálculo de preços.

- `personal_access_tokens`  
  - Tabela para tokens de API (Laravel Sanctum migration criada).

## 3. Relacionamentos (principais)
- `users` 1:N `user_profiles` (um usuário tem um profile).  
- `users` 1:N `quotas` (um usuário pode possuir/quitar várias cotas).  
- `hotels` 1:N `quotas` (hotel possui diversas quotas).  
- `quotas` 1:N `quota_transactions` (transações associadas a uma cota).  

## 4. Campos JSON e arrays
- `images` e `amenities` em `hotels` são armazenados como JSON (array).  
- `allowed_uses` pode ser JSON/array em `quotas` e em alguns campos de `user_profiles`.

## 5. Regras e validações no banco
- Muitas validações ocorrem via controllers e requests. Exemplos:
  - `owner_hotel_id` usa `exists:hotels,id` no controller para validação.  
  - Campos de quartos e semanas têm validações dinâmicas (migrations adicionam fields, controller valida condicionalmente).

## 6. Índices e performance
- Adicionar índices em colunas usadas para busca/filtros: `hotels.name`, `hotels.city`, `quotas.user_id`, `quotas.hotel_id`.  
- Usar `limit(50)` nas queries de listagem de hotéis (já implementado na API).

## 7. Observações operacionais
- Se `SESSION_DRIVER=database` está configurado, certifique-se de rodar a migration `create_sessions_table` e que a tabela `sessions` existe.  
- Permissões: garantir que `storage/` e `bootstrap/cache` estejam graváveis.  
- Backup: exportar DB regularmente; para ambiente dev use `mysqldump`.

## 8. Consultas de exemplo
- Buscar hotéis ativos com filtro de nome/cidade:
```php
$query = Hotel::where('is_active', true);
if ($search) {
  $query->where(fn($q) => $q->where('name','like',\"%{$search}%\")->orWhere('city','like',\"%{$search}%\"));
}
$hotels = $query->orderBy('name')->limit(50)->get();
```

---
Documentação das migrations: ver `database/migrations/` (cada arquivo contém a definição completa dos campos).

