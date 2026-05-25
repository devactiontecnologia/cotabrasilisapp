# Implementação das Regras de Negócio - Cota Brasilis

## Resumo das Regras Implementadas

### 1. ✅ Cadastro Inválido → Bloqueio Total
- **Middleware**: `CheckValidRegistration`
- **Funcionalidade**: Bloqueia acesso total até regularização
- **Validações**:
  - Campos obrigatórios preenchidos
  - CPF válido e único
  - Documentos obrigatórios anexados
  - KYC não rejeitado
  - Contrato de cota se declarou possuir cota
  - Autorização se for usuário autorizado

### 2. ✅ Hotel Não Funcionando
- **Modelo**: `Hotel` com campos `is_functioning`, `status_reason`
- **Regras**:
  - Hotel não funcionando → proíbe alugar
  - Hotel não funcionando → permite vender/trocar
  - Troca de titularidade ainda permitida
- **Métodos**: `isFunctioning()`, `allowsRentals()`, `allowsSales()`

### 3. ✅ Transferência de Titularidade
- **Modelo**: `Quota` método `transferOwnership()`
- **Funcionalidade**: Cancela automaticamente todas as ofertas ativas
- **Campos**: `previous_owner_id`, `transferred_at`, `quota_status`

### 4. ✅ SuperDesconto Automático
- **Aplicação**: Após 14 dias de oferta ativa
- **Comando**: `offers:apply-super-desconto`
- **Campos**: `super_desconto_applied`, `super_desconto_percentage`, `original_price`
- **Lógica**: Aplica 10% de desconto automaticamente

### 5. ✅ MegaOferta
- **Substitui**: SuperDesconto se aplicado dentro da janela (14-21 dias)
- **Taxa Adicional**: 5% para cobrir comissão extra do app
- **Campos**: `mega_oferta_applied`, `mega_oferta_percentage`, `app_commission`
- **Lógica**: Remove SuperDesconto e aplica MegaOferta

### 6. ✅ Limites de Leilões por Perfil
- **Validação Rigorosa**: `canCreateAuction()` no `QuotaManagementController`
- **Verificações**:
  - Usuário não penalizado
  - Cota pertence ao usuário
  - Cota ativa e publicada
  - Limites por período respeitados
  - Não existe leilão ativo para a cota
- **Modelo**: `AuctionLimit` para controle de limites

### 7. ✅ Penalidades de Cancelamento
- **Campos**: `is_penalized`, `penalty_until`, `penalty_reason`
- **Regra**: Bloqueio de 24h por não cumprimento de prazos (12h)
- **Métodos**: `applyPenalty()`, `isPenalized()`, `removePenalty()`

### 8. ✅ Busca com Sobreposição de Período
- **Lógica**: Exibe ofertas contendo ao menos um dia do período desejado
- **Métodos**: `search()` e `searchOffers()` no `QuotaManagementController`
- **Query**: `start_date <= search_end_date AND end_date >= search_start_date`

### 9. ✅ Fluxo de Pagamentos e Repasses
- **Campos**: `app_commission` para retenção de comissão
- **Regra**: App retém comissão até confirmação de hospedagem
- **Implementação**: Sistema de comissões baseado em perfil e tipo de oferta

### 10. ✅ Autorização de Hospedagem
- **Modelo**: `HospitalityAuthorization`
- **Características**:
  - Pessoal e intransferível (`is_transferable = false`)
  - Não prorrogável
  - Código único de autorização
  - Validação de datas e status
- **Métodos**: `isValid()`, `canBeUsed()`, `markAsUsed()`

### 11. ✅ Sistema de Wishlist
- **Modelo**: `WishlistRequest`
- **Funcionalidade**: Usuários podem registrar desejos quando não encontram ofertas
- **Matching**: Sistema automático de correspondência com ofertas
- **Admin**: Pode buscar e cadastrar novas opções com notificações
- **Métodos**: `matchesOffer()`, `markAsFulfilled()`

## Comandos Disponíveis

### 1. Aplicar SuperDesconto
```bash
php artisan offers:apply-super-desconto
```

### 2. Processar Ofertas (Completo)
```bash
php artisan offers:process
```
- Aplica SuperDesconto
- Verifica ofertas expiradas
- Processa wishlist
- Verifica autorizações expiradas

## Middleware Implementados

### 1. CheckValidRegistration
- **Alias**: `valid.registration`
- **Função**: Bloqueia usuários com cadastro inválido
- **Aplicado**: Todas as rotas protegidas

### 2. CheckProfileComplete
- **Alias**: `profile.complete`
- **Função**: Verifica se perfil está completo
- **Aplicado**: Rotas que requerem perfil completo

## Validações de Negócio

### 1. Criação de Leilões
- Verificação de limites por perfil
- Validação de penalidades
- Verificação de propriedade da cota
- Verificação de status da cota
- Verificação de leilões ativos

### 2. Aplicação de Descontos
- SuperDesconto: 14+ dias ativo
- MegaOferta: 14-21 dias ativo
- Substituição automática do SuperDesconto

### 3. Transferência de Cota
- Cancelamento automático de ofertas
- Atualização de status
- Notificação ao novo proprietário

### 4. Busca de Ofertas
- Sobreposição de períodos
- Filtros por localização
- Filtros por preço e características

## Status dos Sistemas

- ✅ **Cadastro e KYC**: Implementado
- ✅ **Gestão de Cotas**: Implementado
- ✅ **Ofertas de Aluguel**: Implementado
- ✅ **Sistema de Leilões**: Implementado
- ✅ **SuperDesconto/MegaOferta**: Implementado
- ✅ **Autorização de Hospedagem**: Implementado
- ✅ **Sistema de Wishlist**: Implementado
- ✅ **Validações de Negócio**: Implementado
- ✅ **Penalidades**: Implementado
- ✅ **Busca Avançada**: Implementado

## Próximos Passos Sugeridos

1. **Testes**: Implementar testes unitários e de integração
2. **Notificações**: Sistema de notificações em tempo real
3. **Relatórios**: Dashboard administrativo com analytics
4. **Integração Gov.br**: Implementar assinatura digital
5. **OCR**: Integração real para validação de documentos
6. **WebSocket**: Notificações em tempo real para leilões
7. **API**: Endpoints para aplicativo mobile
8. **Logs**: Sistema de auditoria completo