<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QuotaTransaction;
use App\Models\Quota;
use Carbon\Carbon;

class CheckExpiredNegotiations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'negotiations:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica e encerra negociações expiradas que não foram pagas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando negociações expiradas...');

        // Buscar transações em negociação que expiraram e não foram pagas
        $expiredTransactions = QuotaTransaction::where('status', QuotaTransaction::STATUS_NEGOTIATING)
            ->where('payment_status', QuotaTransaction::PAYMENT_PENDING)
            ->where('negotiation_deadline', '<', now())
            ->with(['quota'])
            ->get();

        $count = 0;

        foreach ($expiredTransactions as $transaction) {
            // Atualizar transação
            $transaction->update([
                'status' => QuotaTransaction::STATUS_EXPIRED,
                'payment_status' => QuotaTransaction::PAYMENT_FAILED,
            ]);

            // Retornar cota para disponível
            if ($transaction->quota) {
                $transaction->quota->update([
                    'status' => Quota::STATUS_AVAILABLE,
                    'negotiation_deadline' => null,
                    'current_transaction_id' => null,
                ]);
            }

            $count++;
        }

        $this->info("Processadas {$count} negociações expiradas.");

        // Verificar também documentos que não foram enviados no prazo
        $this->checkExpiredDocuments();

        return Command::SUCCESS;
    }

    /**
     * Verifica documentos que não foram enviados no prazo.
     */
    private function checkExpiredDocuments()
    {
        $expiredDocuments = QuotaTransaction::where('status', QuotaTransaction::STATUS_PAYMENT_PENDING)
            ->where('payment_status', QuotaTransaction::PAYMENT_COMPLETED)
            ->where('document_upload_deadline', '<', now())
            ->whereNull('document_uploaded_at')
            ->with(['quota'])
            ->get();

        $count = 0;

        foreach ($expiredDocuments as $transaction) {
            // Notificar sobre documento atrasado
            // Aqui você pode adicionar lógica de notificação
            
            $count++;
        }

        if ($count > 0) {
            $this->warn("Encontrados {$count} documentos não enviados no prazo.");
        }
    }
}
