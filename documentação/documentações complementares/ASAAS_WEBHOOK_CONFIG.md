# Configuração de Webhooks do Asaas - Cota Brasilis

## Endpoint do Webhook

O endpoint fixo para receber webhooks do Asaas é:

```
https://seudominio.com.br/webhooks/asaas
```

ou em desenvolvimento:

```
http://localhost/cotabrasilis/public/webhooks/asaas
```

## Configuração no Painel do Asaas

1. Acesse o painel do Asaas
2. Vá em **Configurações** > **Webhooks**
3. Adicione uma nova URL de webhook:
   - **URL**: `https://seudominio.com.br/webhooks/asaas`
   - **Eventos**: Selecione os eventos que deseja receber:
     - `PAYMENT_CREATED` - Pagamento criado
     - `PAYMENT_UPDATED` - Pagamento atualizado
     - `PAYMENT_CONFIRMED` - Pagamento confirmado
     - `PAYMENT_RECEIVED` - Pagamento recebido
     - `PAYMENT_OVERDUE` - Pagamento vencido
     - `PAYMENT_DELETED` - Pagamento deletado
     - `PAYMENT_RESTORED` - Pagamento restaurado
     - `PAYMENT_REFUNDED` - Pagamento reembolsado
     - `PAYMENT_CHARGEBACK_REQUESTED` - Chargeback solicitado

## Configuração no .env

Adicione as seguintes variáveis no arquivo `.env`:

```env
# Asaas Configuration
ASAAS_API_KEY=your_asaas_api_key_here
ASAAS_ENVIRONMENT=production # ou 'sandbox' para testes
ASAAS_WEBHOOK_TOKEN=your_webhook_token_here
```

### Obtendo o Token do Webhook

1. No painel do Asaas, vá em **Configurações** > **Webhooks**
2. Copie o **Token de Segurança** do webhook configurado
3. Cole no arquivo `.env` na variável `ASAAS_WEBHOOK_TOKEN`

## Eventos Processados

O sistema processa os seguintes eventos do Asaas:

### PAYMENT_CREATED / PAYMENT_UPDATED
- Atualiza o status do pagamento para `processing`
- Armazena o ID do pagamento do Asaas
- Salva os dados do webhook

### PAYMENT_CONFIRMED / PAYMENT_RECEIVED
- Atualiza o status do pagamento para `completed`
- Atualiza a transação relacionada para `payment_completed`
- Envia notificação por email ao usuário
- Define o próximo status como `document_pending` (aguardando documento)

### PAYMENT_OVERDUE
- Atualiza o status do pagamento para `failed`
- Cancela a transação relacionada

### PAYMENT_DELETED
- Atualiza o status do pagamento para `cancelled`

### PAYMENT_RESTORED
- Atualiza o status do pagamento para `pending`

### PAYMENT_REFUNDED
- Atualiza o status do pagamento para `cancelled`
- Cancela a transação relacionada

### PAYMENT_CHARGEBACK_REQUESTED
- Mantém o status como `processing` para revisão manual
- Registra o evento para análise

## Validação de Segurança

O webhook valida a assinatura usando o token configurado em `ASAAS_WEBHOOK_TOKEN`. 

**Importante**: Se o token não estiver configurado, o sistema aceitará todos os webhooks (não recomendado para produção).

## Estrutura de Dados

Os dados do webhook são armazenados no campo `asaas_webhook_data` da tabela `payment_transactions` em formato JSON.

## Logs

Todos os webhooks recebidos são registrados no log do Laravel em:
- `storage/logs/laravel.log`

Os logs incluem:
- Evento recebido
- ID do pagamento
- Dados completos do webhook
- Erros de processamento

## Testando o Webhook

### Usando o Asaas Sandbox

1. Configure `ASAAS_ENVIRONMENT=sandbox` no `.env`
2. Use a URL de webhook do sandbox no painel do Asaas
3. Crie um pagamento de teste
4. Verifique os logs em `storage/logs/laravel.log`

### Teste Manual (cURL)

```bash
curl -X POST http://localhost/cotabrasilis/public/webhooks/asaas \
  -H "Content-Type: application/json" \
  -H "asaas-access-token: seu_token_aqui" \
  -d '{
    "event": "PAYMENT_CONFIRMED",
    "payment": {
      "id": "pay_123456789",
      "customer": "cus_123456789",
      "value": 100.00,
      "status": "CONFIRMED"
    }
  }'
```

## Troubleshooting

### Webhook não está sendo recebido

1. Verifique se a URL está correta no painel do Asaas
2. Verifique se o servidor está acessível publicamente (use ngrok para desenvolvimento local)
3. Verifique os logs do Laravel para erros
4. Verifique se o CSRF está desabilitado para esta rota (já está configurado)

### Erro de assinatura inválida

1. Verifique se o `ASAAS_WEBHOOK_TOKEN` está correto no `.env`
2. Verifique se o token no header `asaas-access-token` corresponde ao configurado
3. Limpe o cache: `php artisan config:clear`

### Pagamento não está sendo atualizado

1. Verifique se o `asaas_payment_id` está sendo salvo corretamente
2. Verifique se a transação existe no banco de dados
3. Verifique os logs para erros de processamento

## Segurança

- O endpoint é público, mas valida a assinatura do webhook
- Todos os webhooks são logados para auditoria
- Erros são capturados e logados sem expor informações sensíveis
- O token do webhook deve ser mantido em segredo

## Próximos Passos

1. Configure o webhook no painel do Asaas
2. Adicione as variáveis de ambiente no `.env`
3. Execute a migration: `php artisan migrate`
4. Teste criando um pagamento de teste
5. Monitore os logs para verificar o funcionamento
