<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentTransaction;
use App\Models\QuotaTransaction;
use App\Services\NotificationService;

class AsaasWebhookController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle webhook from Asaas
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        try {
            // Log do webhook recebido
            Log::info('Asaas Webhook recebido', [
                'event' => $request->event,
                'payment_id' => $request->payment?->id ?? null,
                'data' => $request->all()
            ]);

            // Validar assinatura do webhook (se configurado)
            if (!$this->validateWebhookSignature($request)) {
                Log::warning('Asaas Webhook: Assinatura inválida', [
                    'ip' => $request->ip(),
                    'data' => $request->all()
                ]);
                return response()->json(['error' => 'Assinatura inválida'], 401);
            }

            // Processar evento
            $event = $request->event;
            $paymentData = $request->payment ?? $request->all();

            switch ($event) {
                case 'PAYMENT_CREATED':
                    return $this->handlePaymentCreated($paymentData);
                
                case 'PAYMENT_UPDATED':
                    return $this->handlePaymentUpdated($paymentData);
                
                case 'PAYMENT_CONFIRMED':
                case 'PAYMENT_RECEIVED':
                    return $this->handlePaymentConfirmed($paymentData);
                
                case 'PAYMENT_OVERDUE':
                    return $this->handlePaymentOverdue($paymentData);
                
                case 'PAYMENT_DELETED':
                    return $this->handlePaymentDeleted($paymentData);
                
                case 'PAYMENT_RESTORED':
                    return $this->handlePaymentRestored($paymentData);
                
                case 'PAYMENT_REFUNDED':
                    return $this->handlePaymentRefunded($paymentData);
                
                case 'PAYMENT_CHARGEBACK_REQUESTED':
                    return $this->handleChargebackRequested($paymentData);
                
                default:
                    Log::info('Asaas Webhook: Evento não tratado', [
                        'event' => $event,
                        'data' => $paymentData
                    ]);
                    return response()->json(['message' => 'Evento não tratado'], 200);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook do Asaas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return response()->json(['error' => 'Erro ao processar webhook'], 500);
        }
    }

    /**
     * Validate webhook signature
     * 
     * @param Request $request
     * @return bool
     */
    private function validateWebhookSignature(Request $request): bool
    {
        $webhookToken = env('ASAAS_WEBHOOK_TOKEN');
        
        // Se não houver token configurado, aceitar (não recomendado para produção)
        if (empty($webhookToken)) {
            Log::warning('Asaas Webhook: Token não configurado, aceitando webhook sem validação');
            return true;
        }

        // Verificar token no header (Asaas envia no header 'asaas-access-token')
        $token = $request->header('asaas-access-token');
        
        if (empty($token)) {
            Log::warning('Asaas Webhook: Token não encontrado no header');
            return false;
        }
        
        return hash_equals($webhookToken, $token);
    }

    /**
     * Handle payment created event
     */
    private function handlePaymentCreated($paymentData)
    {
        $asaasPaymentId = $paymentData['id'] ?? null;
        
        if (!$asaasPaymentId) {
            return response()->json(['error' => 'ID do pagamento não encontrado'], 400);
        }

        // Buscar transação pelo ID do Asaas ou pelo identificador externo
        $paymentTransaction = $this->findPaymentTransaction($asaasPaymentId, $paymentData);

        if ($paymentTransaction) {
            $paymentTransaction->update([
                'asaas_payment_id' => $asaasPaymentId,
                'asaas_webhook_data' => $paymentData,
                'status' => 'processing',
                'payment_reference' => $asaasPaymentId,
            ]);

            Log::info('Asaas Webhook: Pagamento criado atualizado', [
                'payment_transaction_id' => $paymentTransaction->id,
                'asaas_payment_id' => $asaasPaymentId
            ]);
        }

        return response()->json(['message' => 'Webhook processado'], 200);
    }

    /**
     * Handle payment updated event
     */
    private function handlePaymentUpdated($paymentData)
    {
        return $this->handlePaymentCreated($paymentData);
    }

    /**
     * Handle payment confirmed/received event
     */
    private function handlePaymentConfirmed($paymentData)
    {
        $asaasPaymentId = $paymentData['id'] ?? null;
        
        if (!$asaasPaymentId) {
            return response()->json(['error' => 'ID do pagamento não encontrado'], 400);
        }

        $paymentTransaction = $this->findPaymentTransaction($asaasPaymentId, $paymentData);

        if (!$paymentTransaction) {
            Log::warning('Asaas Webhook: Transação não encontrada para pagamento confirmado', [
                'asaas_payment_id' => $asaasPaymentId
            ]);
            return response()->json(['error' => 'Transação não encontrada'], 404);
        }

        // Atualizar status do pagamento
        $paymentTransaction->update([
            'status' => 'completed',
            'payment_completed_at' => now(),
            'asaas_webhook_data' => $paymentData,
        ]);

        // Atualizar transação relacionada
        if ($paymentTransaction->transaction) {
            $quotaTransaction = $paymentTransaction->transaction;
            $quotaTransaction->update([
                'payment_status' => QuotaTransaction::PAYMENT_COMPLETED,
                'status' => QuotaTransaction::STATUS_DOCUMENT_PENDING, // Próximo passo: aguardar documento
                'payment_id' => $asaasPaymentId,
            ]);

            Log::info('Asaas Webhook: Pagamento confirmado e transação atualizada', [
                'payment_transaction_id' => $paymentTransaction->id,
                'quota_transaction_id' => $quotaTransaction->id,
                'asaas_payment_id' => $asaasPaymentId
            ]);

            // Notificar usuário sobre pagamento confirmado
            try {
                $this->notificationService->sendEmail(
                    $paymentTransaction->user,
                    'Pagamento Confirmado - Cota Brasilis',
                    "Seu pagamento foi confirmado com sucesso! ID: {$asaasPaymentId}"
                );
            } catch (\Exception $e) {
                Log::error('Erro ao enviar notificação de pagamento confirmado', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json(['message' => 'Pagamento confirmado'], 200);
    }

    /**
     * Handle payment overdue event
     */
    private function handlePaymentOverdue($paymentData)
    {
        $asaasPaymentId = $paymentData['id'] ?? null;
        
        if (!$asaasPaymentId) {
            return response()->json(['error' => 'ID do pagamento não encontrado'], 400);
        }

        $paymentTransaction = $this->findPaymentTransaction($asaasPaymentId, $paymentData);

        if ($paymentTransaction) {
            $paymentTransaction->update([
                'status' => 'failed',
                'asaas_webhook_data' => $paymentData,
            ]);

            // Atualizar transação relacionada
            if ($paymentTransaction->transaction) {
                $paymentTransaction->transaction->update([
                    'payment_status' => QuotaTransaction::PAYMENT_FAILED,
                    'status' => QuotaTransaction::STATUS_CANCELLED,
                ]);
            }

            Log::info('Asaas Webhook: Pagamento vencido', [
                'payment_transaction_id' => $paymentTransaction->id,
                'asaas_payment_id' => $asaasPaymentId
            ]);
        }

        return response()->json(['message' => 'Pagamento vencido processado'], 200);
    }

    /**
     * Handle payment deleted event
     */
    private function handlePaymentDeleted($paymentData)
    {
        $asaasPaymentId = $paymentData['id'] ?? null;
        
        if (!$asaasPaymentId) {
            return response()->json(['error' => 'ID do pagamento não encontrado'], 400);
        }

        $paymentTransaction = $this->findPaymentTransaction($asaasPaymentId, $paymentData);

        if ($paymentTransaction) {
            $paymentTransaction->update([
                'status' => 'cancelled',
                'asaas_webhook_data' => $paymentData,
            ]);

            Log::info('Asaas Webhook: Pagamento deletado', [
                'payment_transaction_id' => $paymentTransaction->id,
                'asaas_payment_id' => $asaasPaymentId
            ]);
        }

        return response()->json(['message' => 'Pagamento deletado processado'], 200);
    }

    /**
     * Handle payment restored event
     */
    private function handlePaymentRestored($paymentData)
    {
        $asaasPaymentId = $paymentData['id'] ?? null;
        
        if (!$asaasPaymentId) {
            return response()->json(['error' => 'ID do pagamento não encontrado'], 400);
        }

        $paymentTransaction = $this->findPaymentTransaction($asaasPaymentId, $paymentData);

        if ($paymentTransaction) {
            $paymentTransaction->update([
                'status' => 'pending',
                'asaas_webhook_data' => $paymentData,
            ]);

            Log::info('Asaas Webhook: Pagamento restaurado', [
                'payment_transaction_id' => $paymentTransaction->id,
                'asaas_payment_id' => $asaasPaymentId
            ]);
        }

        return response()->json(['message' => 'Pagamento restaurado processado'], 200);
    }

    /**
     * Handle payment refunded event
     */
    private function handlePaymentRefunded($paymentData)
    {
        $asaasPaymentId = $paymentData['id'] ?? null;
        
        if (!$asaasPaymentId) {
            return response()->json(['error' => 'ID do pagamento não encontrado'], 400);
        }

        $paymentTransaction = $this->findPaymentTransaction($asaasPaymentId, $paymentData);

        if ($paymentTransaction) {
            $paymentTransaction->update([
                'status' => 'cancelled',
                'asaas_webhook_data' => $paymentData,
            ]);

            // Atualizar transação relacionada
            if ($paymentTransaction->transaction) {
                $paymentTransaction->transaction->update([
                    'payment_status' => QuotaTransaction::PAYMENT_FAILED,
                    'status' => QuotaTransaction::STATUS_CANCELLED,
                ]);
            }

            Log::info('Asaas Webhook: Pagamento reembolsado', [
                'payment_transaction_id' => $paymentTransaction->id,
                'asaas_payment_id' => $asaasPaymentId
            ]);
        }

        return response()->json(['message' => 'Pagamento reembolsado processado'], 200);
    }

    /**
     * Handle chargeback requested event
     */
    private function handleChargebackRequested($paymentData)
    {
        $asaasPaymentId = $paymentData['id'] ?? null;
        
        if (!$asaasPaymentId) {
            return response()->json(['error' => 'ID do pagamento não encontrado'], 400);
        }

        $paymentTransaction = $this->findPaymentTransaction($asaasPaymentId, $paymentData);

        if ($paymentTransaction) {
            // Marcar para revisão manual
            $paymentTransaction->update([
                'status' => 'processing', // Manter como processing para revisão
                'asaas_webhook_data' => $paymentData,
            ]);

            Log::warning('Asaas Webhook: Chargeback solicitado', [
                'payment_transaction_id' => $paymentTransaction->id,
                'asaas_payment_id' => $asaasPaymentId
            ]);
        }

        return response()->json(['message' => 'Chargeback processado'], 200);
    }

    /**
     * Find payment transaction by Asaas payment ID or external reference
     */
    private function findPaymentTransaction($asaasPaymentId, $paymentData)
    {
        // Tentar encontrar pelo ID do Asaas
        $paymentTransaction = PaymentTransaction::where('asaas_payment_id', $asaasPaymentId)->first();

        if ($paymentTransaction) {
            return $paymentTransaction;
        }

        // Tentar encontrar pelo identificador externo (customer, subscription, etc)
        $externalReference = $paymentData['customer'] ?? 
                           $paymentData['subscription'] ?? 
                           $paymentData['externalReference'] ?? 
                           null;

        if ($externalReference) {
            // Se o externalReference for o ID da transação
            $paymentTransaction = PaymentTransaction::where('id', $externalReference)
                ->orWhere('payment_reference', $externalReference)
                ->first();
        }

        return $paymentTransaction;
    }
}
