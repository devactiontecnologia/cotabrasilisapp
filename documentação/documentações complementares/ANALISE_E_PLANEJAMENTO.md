# 📋 ANÁLISE E PLANEJAMENTO - MELHORIAS DO SISTEMA COTAS BRASILIS

## 🎯 OBJETIVO
Melhorar e incrementar funcionalidades existentes sem alterar layouts, seguindo fielmente o escopo fornecido.

---

## 📊 ANÁLISE DA ESTRUTURA ATUAL

### ✅ O QUE JÁ EXISTE

#### Modelos (Models)
- ✅ `User` - Usuários do sistema
- ✅ `UserProfile` - Perfis com tipos (curioso, inteligente, sábio)
- ✅ `Quota` - Cotas hoteleiras
- ✅ `QuotaTransaction` - Transações (aluguel/troca)
- ✅ `RentalOffer` - Ofertas de aluguel
- ✅ `Auction` - Leilões
- ✅ `DigitalContract` - Contratos digitais
- ✅ `HospitalityAuthorization` - Autorizações de hospedagem
- ✅ `WishlistRequest` - Solicitações de desejo
- ✅ `Hotel` - Hotéis
- ✅ `Notification` - Notificações

#### Controladores (Controllers)
- ✅ `QuotaController` - Gestão de cotas
- ✅ `TransactionController` - Transações (aluguel/troca básica)
- ✅ `RentalOfferController` - Ofertas de aluguel
- ✅ `AuctionController` - Leilões
- ✅ `QuotaManagementController` - Gestão avançada de cotas

#### Funcionalidades Existentes
- ✅ Sistema de perfis (Tipo 1, 2, 3)
- ✅ Cadastro de cotas
- ✅ Ofertas de aluguel básicas
- ✅ Leilões básicos
- ✅ Transações de aluguel e troca básicas
- ✅ Contratos digitais
- ✅ Autorizações de hospedagem

---

## 🔧 O QUE PRECISA SER MELHORADO/IMPLEMENTADO

### 🔵 MÓDULO 1: ALUGAR - Ofertar e Buscar

#### Para quem oferece:
- ✅ Cadastro de cota existe, mas precisa melhorar:
  - ⚠️ Adicionar: múltiplas cotas em lote
  - ⚠️ Adicionar: período flexível com calendário semanal
  - ⚠️ Adicionar: faixa de preço
  - ⚠️ Melhorar: leilão com regras por tipo de cadastro
  - ⚠️ Adicionar: campo observações melhorado

#### Para quem busca:
- ✅ Busca existe, mas precisa melhorar:
  - ⚠️ Adicionar: filtros avançados (dias específicos: 2,3,4,5,7)
  - ⚠️ Adicionar: seleção por mês
  - ⚠️ Melhorar: exibição de resultados com mais informações
  - ⚠️ Adicionar: sistema de "não há oferta" com alertas
  - ⚠️ Adicionar: regras automáticas (14 dias, redução 20%)

---

### 🔵 MÓDULO 2: PAGAMENTO FICTÍCIO

#### Status: ⚠️ PARCIALMENTE IMPLEMENTADO
- ✅ Transações existem
- ❌ Falta: fluxo completo de pagamento fictício
- ❌ Falta: alertas email + SMS
- ❌ Falta: documento oficial de autorização
- ❌ Falta: vídeo selfie guiado
- ❌ Falta: opção "NA HORA" (12h)
- ❌ Falta: bloqueios por não cumprimento

---

### 🔵 MÓDULO 3: TROCAR

#### Status: ⚠️ BÁSICO IMPLEMENTADO
- ✅ Troca básica existe
- ❌ Falta: filtros avançados
- ❌ Falta: opções (trocar semana/titularidade)
- ❌ Falta: lógica de troca MAIS
- ❌ Falta: regras por tipo de cadastro (3, 5, 10 opções)
- ❌ Falta: validade por tipo (48h, 72h)
- ❌ Falta: alertas automáticos
- ❌ Falta: fluxo completo de negociação

---

### 🔵 MÓDULO 4: VENDER

#### Status: ❌ NÃO IMPLEMENTADO
- ❌ Criar: modelo SaleOffer
- ❌ Criar: controlador SaleController
- ❌ Criar: views de venda
- ❌ Implementar: regras por tipo de cadastro
- ❌ Implementar: negociação com administrador
- ❌ Implementar: venda via leilão (10% app)

---

### 🔵 MÓDULO 5: COMPRAR

#### Status: ❌ NÃO IMPLEMENTADO
- ❌ Criar: modelo PurchaseRequest
- ❌ Criar: controlador PurchaseController
- ❌ Criar: views de compra
- ❌ Implementar: filtros avançados
- ❌ Implementar: delegação ao admin
- ❌ Implementar: taxas (início 10%)

---

### 🔵 MÓDULO 6: CAMADA EDUCATIVA

#### Status: ❌ NÃO IMPLEMENTADO
- ❌ Criar: modelo EducationalContent
- ❌ Criar: modelo EducationalVideo
- ❌ Criar: controlador EducationalController
- ❌ Criar: views de vídeos
- ❌ Implementar: filtros por tipo de cadastro
- ❌ Implementar: comentários
- ❌ Implementar: registro de visualizações

---

### 🔵 MÓDULO 7: ALERTAS E NOTIFICAÇÕES

#### Status: ⚠️ PARCIALMENTE IMPLEMENTADO
- ✅ Modelo Notification existe
- ❌ Falta: integração email
- ❌ Falta: integração WhatsApp
- ❌ Falta: alertas automáticos por eventos
- ❌ Falta: templates de notificação

---

### 🔵 MÓDULO 8: MÉTRICAS

#### Status: ⚠️ PARCIALMENTE IMPLEMENTADO
- ✅ Algumas métricas no dashboard
- ❌ Falta: métricas completas (alugada, trocada, vendida)
- ❌ Falta: relatórios detalhados
- ❌ Falta: gráficos e estatísticas

---

### 🔵 MÓDULO 9: PROCESSOS AUTOMÁTICOS

#### Status: ❌ NÃO IMPLEMENTADO
- ❌ Criar: Commands para cron jobs
- ❌ Implementar: redução automática de preço (14 dias)
- ❌ Implementar: alertas automáticos
- ❌ Implementar: expiração de ofertas
- ❌ Implementar: bloqueios automáticos

---

## 📐 ARQUITETURA DE IMPLEMENTAÇÃO

### Estrutura de Pastas
```
app/
├── Models/
│   ├── SaleOffer.php (NOVO)
│   ├── PurchaseRequest.php (NOVO)
│   ├── EducationalContent.php (NOVO)
│   ├── EducationalVideo.php (NOVO)
│   └── ... (modelos existentes)
├── Http/Controllers/
│   ├── SaleController.php (NOVO)
│   ├── PurchaseController.php (NOVO)
│   ├── EducationalController.php (NOVO)
│   ├── PaymentController.php (NOVO - melhorar)
│   └── ... (controladores existentes)
├── Services/
│   ├── NotificationService.php (NOVO)
│   ├── PaymentService.php (NOVO - simulado)
│   ├── VideoService.php (NOVO)
│   └── ... (serviços existentes)
└── Console/Commands/
    ├── ProcessAutomaticDiscounts.php (NOVO)
    ├── ProcessExpiredOffers.php (NOVO)
    ├── SendAlerts.php (NOVO)
    └── ... (comandos existentes)
```

---

## 🗄️ BANCO DE DADOS - NOVAS TABELAS NECESSÁRIAS

### 1. sale_offers
- id, user_id, quota_id, hotel_id
- weeks (1-4), number_of_rooms, city, company
- minimum_price, acceptable_price, desired_price
- observations_by_price (JSON)
- status, negotiation_status
- admin_id (negociação)
- auction_id (se via leilão)
- app_commission (10%)
- timestamps

### 2. purchase_requests
- id, user_id
- hotel_id, weeks, month, period_type (fixo/flexível)
- city, company, price_range_min, price_range_max
- observations
- status, delegated_to_admin
- max_price (se delegado)
- timestamps

### 3. educational_contents
- id, title, description, content_type
- profile_type_required (curioso/inteligente/sabio)
- category, tags
- is_active, order
- timestamps

### 4. educational_videos
- id, educational_content_id
- title, description, video_url, thumbnail_url
- duration, profile_type_required
- category, tags
- views_count, likes_count
- is_active, order
- timestamps

### 5. video_comments
- id, educational_video_id, user_id
- comment, parent_id (respostas)
- is_approved
- timestamps

### 6. video_views
- id, educational_video_id, user_id
- viewed_at, duration_watched
- completed (boolean)
- timestamps

### 7. exchange_offers (melhorar)
- id, user_id, quota_id
- exchange_type (semana/titularidade)
- desired_city, desired_period_start, desired_period_end
- desired_hotel, desired_people, desired_rooms
- price_range_min, price_range_max
- exchange_mode (simples/mais)
- additional_value (se MAIS)
- days_difference (se diárias diferentes)
- observations
- status, validity_until
- selected_options (JSON - até 3, 5 ou 10)
- timestamps

### 8. payment_transactions (melhorar)
- id, transaction_id, user_id
- payment_method, amount, fees
- status, payment_reference
- authorization_document_path
- video_path (selfie)
- sent_at_hour (boolean - NA HORA)
- payment_due_at, payment_completed_at
- blocked_until (se não cumpriu)
- timestamps

---

## 🔄 FLUXOS PRINCIPAIS

### FLUXO 1: ALUGAR (Melhorado)
1. Usuário cria oferta de aluguel
   - Preenche dados da cota
   - Escolhe período exato ou flexível
   - Define preço ou leilão
   - Upload foto contrato
2. Sistema valida e publica
3. Buscador filtra e encontra
4. Clica "QUERO"
5. Fluxo de pagamento (Módulo 2)
6. Cota fica indisponível
7. Move para métricas "Alugada"

### FLUXO 2: PAGAMENTO FICTÍCIO
1. Interessado clica "QUERO"
2. Ofertante recebe alerta (email + SMS)
3. Sistema gera documento oficial
4. Ofertante grava vídeo selfie
5. Ofertante escolhe "NA HORA" (12h)
6. Interessado recebe alerta para pagar (12h)
7. Sistema exibe preço + taxas
8. Interessado paga (simulado)
9. Sistema libera:
   - Vídeo
   - Documento oficial
   - Envio automático para hotel
10. Bloqueios se não cumprir

### FLUXO 3: TROCAR (Melhorado)
1. Usuário cria oferta de troca
   - Escolhe tipo (semana/titularidade)
   - Define critérios desejados
   - Escolhe opções (3, 5 ou 10)
   - Define validade
2. Sistema busca matches
3. Quando encontra, alerta ambos
4. Negociação
5. Ambos assinam digitalmente
6. Interessado paga taxa
7. Sistema envia documentos para hotéis
8. Move para métricas "Trocada"

### FLUXO 4: VENDER
1. Usuário cria oferta de venda
   - Define hotel, semanas, preços
   - Observações por preço
2. Sistema valida tipo de cadastro
3. Negociação com admin (se tipo permitir)
4. Venda direta ou via leilão
5. Pagamento (10% app)
6. Transferência de titularidade
7. Move para métricas "Vendida"

### FLUXO 5: COMPRAR
1. Usuário cria solicitação de compra
   - Define critérios
   - Preço máximo
2. Sistema busca matches
3. Usuário escolhe ou delega ao admin
4. Negociação
5. Pagamento (taxas)
6. Contrato compra/venda
7. Transferência de titularidade

---

## 📋 REGRAS DE NEGÓCIO POR TIPO DE CADASTRO

### TIPO 1 (Curioso)
- Alugar: ✅ Pode
- Trocar: ✅ Pode (até 3 opções, 48h)
- Vender: ⚠️ Vê lista sem preços/nomes
- Comprar: ✅ Pode
- Leilões: 3 por ano corrente
- Vídeos: Geral

### TIPO 2 (Inteligente)
- Alugar: ✅ Pode
- Trocar: ✅ Pode (5 opções, 48h)
- Vender: ⚠️ Vê preços
- Comprar: ✅ Pode
- Leilões: 1 por mês
- Vídeos: Alugar, trocar, vender, comprar

### TIPO 3 (Sábio)
- Alugar: ✅ Pode
- Trocar: ✅ Pode (10 opções, 72h)
- Vender: ✅ Negociação direta com admin
- Comprar: ✅ Pode
- Leilões: 2 por mês
- Vídeos: Todos (avião, carro, turismo, hotel)

---

## 🚀 PRIORIDADE DE IMPLEMENTAÇÃO

1. **FASE 1** - Melhorias Críticas
   - Melhorar módulo ALUGAR
   - Implementar PAGAMENTO FICTÍCIO completo
   - Melhorar módulo TROCAR

2. **FASE 2** - Novos Módulos
   - Implementar VENDER
   - Implementar COMPRAR

3. **FASE 3** - Funcionalidades Complementares
   - Implementar CAMADA EDUCATIVA
   - Melhorar ALERTAS E NOTIFICAÇÕES
   - Implementar MÉTRICAS completas

4. **FASE 4** - Automações
   - Implementar PROCESSOS AUTOMÁTICOS
   - Cron jobs
   - Bloqueios automáticos

---

## ✅ CHECKLIST DE IMPLEMENTAÇÃO

### Módulo ALUGAR
- [ ] Múltiplas cotas em lote
- [ ] Período flexível com calendário
- [ ] Faixa de preço
- [ ] Leilão com regras por tipo
- [ ] Filtros avançados (dias: 2,3,4,5,7)
- [ ] Seleção por mês
- [ ] Sistema "não há oferta" com alertas
- [ ] Regras automáticas (14 dias, -20%)

### Módulo PAGAMENTO
- [ ] Fluxo completo fictício
- [ ] Alertas email + SMS
- [ ] Documento oficial
- [ ] Vídeo selfie guiado
- [ ] Opção "NA HORA" (12h)
- [ ] Bloqueios automáticos

### Módulo TROCAR
- [ ] Filtros avançados
- [ ] Opções (semana/titularidade)
- [ ] Lógica troca MAIS
- [ ] Regras por tipo (3,5,10 opções)
- [ ] Validade por tipo (48h, 72h)
- [ ] Alertas automáticos
- [ ] Fluxo completo

### Módulo VENDER
- [ ] Modelo e migrations
- [ ] Controlador e views
- [ ] Regras por tipo
- [ ] Negociação com admin
- [ ] Leilão (10% app)

### Módulo COMPRAR
- [ ] Modelo e migrations
- [ ] Controlador e views
- [ ] Filtros avançados
- [ ] Delegação ao admin
- [ ] Taxas (10%)

### Módulo EDUCATIVO
- [ ] Modelos e migrations
- [ ] Controlador e views
- [ ] Filtros por tipo
- [ ] Comentários
- [ ] Visualizações

### Alertas e Notificações
- [ ] Integração email
- [ ] Integração WhatsApp
- [ ] Templates
- [ ] Alertas automáticos

### Métricas
- [ ] Dashboard completo
- [ ] Relatórios
- [ ] Gráficos

### Processos Automáticos
- [ ] Commands
- [ ] Cron jobs
- [ ] Redução automática
- [ ] Bloqueios automáticos

---

## 📝 NOTAS IMPORTANTES

1. **NÃO ALTERAR LAYOUTS** - Manter design atual
2. **MELHORAR, NÃO RECRIAR** - Ajustar funcionalidades existentes
3. **SEGUIR PADRÕES** - Usar mesma estrutura do código atual
4. **VALIDAÇÕES** - Implementar todas as validações necessárias
5. **SEGURANÇA** - Manter autenticação e autorização
6. **PERFORMANCE** - Otimizar consultas quando necessário

---

**Data de Criação:** {{ date('Y-m-d H:i:s') }}
**Versão:** 1.0

