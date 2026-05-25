<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Quota;
use App\Models\QuotaTransaction;
use Carbon\Carbon;

class RentalTransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar os usuários
        $user1 = User::where('email', 'cliente@cotasbrasilis.com')->first();
        $user2 = User::where('email', 'cliente2@cotasbrasilis.com')->first();

        if (!$user1 || !$user2) {
            $this->command->error('Usuários não encontrados. Execute o DemoAccountsSeeder primeiro.');
            return;
        }

        // Buscar cotas disponíveis de outros usuários para criar transações
        // Usuário 1 alugando cotas do usuário 2
        $quotasUser2 = Quota::where('user_id', $user2->id)
            ->where('status', 'available')
            ->where('is_exchange', false)
            ->get()
            ->filter(function($quota) {
                return $quota->allowed_uses && in_array('rent', $quota->allowed_uses);
            })
            ->take(5);

        // Usuário 2 alugando cotas do usuário 1
        $quotasUser1 = Quota::where('user_id', $user1->id)
            ->where('status', 'available')
            ->where('is_exchange', false)
            ->get()
            ->filter(function($quota) {
                return $quota->allowed_uses && in_array('rent', $quota->allowed_uses);
            })
            ->take(5);

        $created = 0;
        $statuses = [
            'pending',
            'contract_signed',
            'payment_pending',
            'payment_completed',
            'completed',
        ];

        // Criar transações onde usuário 1 aluga cotas do usuário 2
        foreach ($quotasUser2 as $index => $quota) {
            $status = $statuses[array_rand($statuses)];
            
            $transactionDate = Carbon::now()->subDays(rand(1, 60));
            $contractSignedAt = null;
            $paymentDueAt = null;
            $paymentCompletedAt = null;
            
            if (in_array($status, ['contract_signed', 'payment_pending', 'payment_completed', 'completed'])) {
                $contractSignedAt = $transactionDate->copy()->addDays(rand(1, 3));
            }
            
            if (in_array($status, ['payment_pending', 'payment_completed', 'completed'])) {
                $paymentDueAt = $contractSignedAt ? $contractSignedAt->copy()->addDays(5) : $transactionDate->copy()->addDays(5);
            }
            
            if (in_array($status, ['payment_completed', 'completed'])) {
                $paymentCompletedAt = $paymentDueAt ? $paymentDueAt->copy()->addDays(rand(0, 2)) : $transactionDate->copy()->addDays(rand(5, 7));
            }

            $totalAmount = $quota->rental_price;
            $platformFee = $totalAmount * 0.05; // 5% taxa da plataforma
            $ownerAmount = $totalAmount - $platformFee; // 95% para o proprietário

            $paymentMethods = ['credit_card', 'debit_card', 'pix', 'bank_transfer'];

            QuotaTransaction::create([
                'quota_id' => $quota->id,
                'renter_id' => $user1->id,
                'owner_id' => $user2->id,
                'transaction_type' => 'rental',
                'total_amount' => $totalAmount,
                'owner_amount' => $ownerAmount,
                'platform_fee' => $platformFee,
                'status' => $status,
                'contract_signed_at' => $contractSignedAt,
                'payment_due_at' => $paymentDueAt,
                'payment_completed_at' => $paymentCompletedAt,
                'payment_reference' => in_array($status, ['payment_completed', 'completed']) ? 'PAY-' . strtoupper(uniqid()) : null,
                'payment_details' => in_array($status, ['payment_completed', 'completed']) ? json_encode([
                    'method' => $paymentMethods[array_rand($paymentMethods)],
                    'reference' => 'PAY-' . strtoupper(uniqid()),
                    'processed_at' => $paymentCompletedAt ? $paymentCompletedAt->toDateTimeString() : now()->toDateTimeString(),
                ]) : null,
            ]);

            // Se a transação está completed, marcar a cota como rented
            if ($status === 'completed') {
                $quota->update(['status' => 'rented']);
            }

            $created++;
        }

        // Criar transações onde usuário 2 aluga cotas do usuário 1
        foreach ($quotasUser1 as $index => $quota) {
            $status = $statuses[array_rand($statuses)];
            
            $transactionDate = Carbon::now()->subDays(rand(1, 60));
            $contractSignedAt = null;
            $paymentDueAt = null;
            $paymentCompletedAt = null;
            
            if (in_array($status, ['contract_signed', 'payment_pending', 'payment_completed', 'completed'])) {
                $contractSignedAt = $transactionDate->copy()->addDays(rand(1, 3));
            }
            
            if (in_array($status, ['payment_pending', 'payment_completed', 'completed'])) {
                $paymentDueAt = $contractSignedAt ? $contractSignedAt->copy()->addDays(5) : $transactionDate->copy()->addDays(5);
            }
            
            if (in_array($status, ['payment_completed', 'completed'])) {
                $paymentCompletedAt = $paymentDueAt ? $paymentDueAt->copy()->addDays(rand(0, 2)) : $transactionDate->copy()->addDays(rand(5, 7));
            }

            $totalAmount = $quota->rental_price;
            $platformFee = $totalAmount * 0.05; // 5% taxa da plataforma
            $ownerAmount = $totalAmount - $platformFee; // 95% para o proprietário

            $paymentMethods = ['credit_card', 'debit_card', 'pix', 'bank_transfer'];

            QuotaTransaction::create([
                'quota_id' => $quota->id,
                'renter_id' => $user2->id,
                'owner_id' => $user1->id,
                'transaction_type' => 'rental',
                'total_amount' => $totalAmount,
                'owner_amount' => $ownerAmount,
                'platform_fee' => $platformFee,
                'status' => $status,
                'contract_signed_at' => $contractSignedAt,
                'payment_due_at' => $paymentDueAt,
                'payment_completed_at' => $paymentCompletedAt,
                'payment_reference' => in_array($status, ['payment_completed', 'completed']) ? 'PAY-' . strtoupper(uniqid()) : null,
                'payment_details' => in_array($status, ['payment_completed', 'completed']) ? json_encode([
                    'method' => $paymentMethods[array_rand($paymentMethods)],
                    'reference' => 'PAY-' . strtoupper(uniqid()),
                    'processed_at' => $paymentCompletedAt ? $paymentCompletedAt->toDateTimeString() : now()->toDateTimeString(),
                ]) : null,
            ]);

            // Se a transação está completed, marcar a cota como rented
            if ($status === 'completed') {
                $quota->update(['status' => 'rented']);
            }

            $created++;
        }

        // Criar algumas transações onde os usuários são proprietários (recebendo aluguéis)
        // Usuário 1 recebendo aluguéis de suas cotas (de outros usuários fictícios ou do user2)
        $quotasOwnedByUser1 = Quota::where('user_id', $user1->id)
            ->where('status', 'available')
            ->get()
            ->filter(function($quota) {
                return $quota->allowed_uses && in_array('rent', $quota->allowed_uses);
            })
            ->take(3);

        foreach ($quotasOwnedByUser1 as $quota) {
            $status = $statuses[array_rand($statuses)];
            
            $transactionDate = Carbon::now()->subDays(rand(1, 60));
            $contractSignedAt = null;
            $paymentDueAt = null;
            $paymentCompletedAt = null;
            
            if (in_array($status, ['contract_signed', 'payment_pending', 'payment_completed', 'completed'])) {
                $contractSignedAt = $transactionDate->copy()->addDays(rand(1, 3));
            }
            
            if (in_array($status, ['payment_pending', 'payment_completed', 'completed'])) {
                $paymentDueAt = $contractSignedAt ? $contractSignedAt->copy()->addDays(5) : $transactionDate->copy()->addDays(5);
            }
            
            if (in_array($status, ['payment_completed', 'completed'])) {
                $paymentCompletedAt = $paymentDueAt ? $paymentDueAt->copy()->addDays(rand(0, 2)) : $transactionDate->copy()->addDays(rand(5, 7));
            }

            $totalAmount = $quota->rental_price;
            $platformFee = $totalAmount * 0.05;
            $ownerAmount = $totalAmount - $platformFee;

            $paymentMethods = ['credit_card', 'debit_card', 'pix', 'bank_transfer'];

            // Usuário 2 alugando de usuário 1
            QuotaTransaction::create([
                'quota_id' => $quota->id,
                'renter_id' => $user2->id,
                'owner_id' => $user1->id,
                'transaction_type' => 'rental',
                'total_amount' => $totalAmount,
                'owner_amount' => $ownerAmount,
                'platform_fee' => $platformFee,
                'status' => $status,
                'contract_signed_at' => $contractSignedAt,
                'payment_due_at' => $paymentDueAt,
                'payment_completed_at' => $paymentCompletedAt,
                'payment_reference' => in_array($status, ['payment_completed', 'completed']) ? 'PAY-' . strtoupper(uniqid()) : null,
                'payment_details' => in_array($status, ['payment_completed', 'completed']) ? json_encode([
                    'method' => $paymentMethods[array_rand($paymentMethods)],
                    'reference' => 'PAY-' . strtoupper(uniqid()),
                    'processed_at' => $paymentCompletedAt ? $paymentCompletedAt->toDateTimeString() : now()->toDateTimeString(),
                ]) : null,
            ]);

            if ($status === 'completed') {
                $quota->update(['status' => 'rented']);
            }

            $created++;
        }

        // Usuário 2 recebendo aluguéis de suas cotas
        $quotasOwnedByUser2 = Quota::where('user_id', $user2->id)
            ->where('status', 'available')
            ->get()
            ->filter(function($quota) {
                return $quota->allowed_uses && in_array('rent', $quota->allowed_uses);
            })
            ->take(3);

        foreach ($quotasOwnedByUser2 as $quota) {
            $status = $statuses[array_rand($statuses)];
            
            $transactionDate = Carbon::now()->subDays(rand(1, 60));
            $contractSignedAt = null;
            $paymentDueAt = null;
            $paymentCompletedAt = null;
            
            if (in_array($status, ['contract_signed', 'payment_pending', 'payment_completed', 'completed'])) {
                $contractSignedAt = $transactionDate->copy()->addDays(rand(1, 3));
            }
            
            if (in_array($status, ['payment_pending', 'payment_completed', 'completed'])) {
                $paymentDueAt = $contractSignedAt ? $contractSignedAt->copy()->addDays(5) : $transactionDate->copy()->addDays(5);
            }
            
            if (in_array($status, ['payment_completed', 'completed'])) {
                $paymentCompletedAt = $paymentDueAt ? $paymentDueAt->copy()->addDays(rand(0, 2)) : $transactionDate->copy()->addDays(rand(5, 7));
            }

            $totalAmount = $quota->rental_price;
            $platformFee = $totalAmount * 0.05;
            $ownerAmount = $totalAmount - $platformFee;

            $paymentMethods = ['credit_card', 'debit_card', 'pix', 'bank_transfer'];

            // Usuário 1 alugando de usuário 2
            QuotaTransaction::create([
                'quota_id' => $quota->id,
                'renter_id' => $user1->id,
                'owner_id' => $user2->id,
                'transaction_type' => 'rental',
                'total_amount' => $totalAmount,
                'owner_amount' => $ownerAmount,
                'platform_fee' => $platformFee,
                'status' => $status,
                'contract_signed_at' => $contractSignedAt,
                'payment_due_at' => $paymentDueAt,
                'payment_completed_at' => $paymentCompletedAt,
                'payment_reference' => in_array($status, ['payment_completed', 'completed']) ? 'PAY-' . strtoupper(uniqid()) : null,
                'payment_details' => in_array($status, ['payment_completed', 'completed']) ? json_encode([
                    'method' => $paymentMethods[array_rand($paymentMethods)],
                    'reference' => 'PAY-' . strtoupper(uniqid()),
                    'processed_at' => $paymentCompletedAt ? $paymentCompletedAt->toDateTimeString() : now()->toDateTimeString(),
                ]) : null,
            ]);

            if ($status === 'completed') {
                $quota->update(['status' => 'rented']);
            }

            $created++;
        }

        $this->command->info("✓ {$created} transações de aluguel criadas com sucesso!");
        
        // Estatísticas
        $user1AsRenter = QuotaTransaction::where('renter_id', $user1->id)->count();
        $user1AsOwner = QuotaTransaction::where('owner_id', $user1->id)->count();
        $user2AsRenter = QuotaTransaction::where('renter_id', $user2->id)->count();
        $user2AsOwner = QuotaTransaction::where('owner_id', $user2->id)->count();
        
        $this->command->info("  • cliente@cotasbrasilis.com:");
        $this->command->info("    - Alugando: {$user1AsRenter} cotas");
        $this->command->info("    - Recebendo aluguéis: {$user1AsOwner} cotas");
        $this->command->info("  • cliente2@cotasbrasilis.com:");
        $this->command->info("    - Alugando: {$user2AsRenter} cotas");
        $this->command->info("    - Recebendo aluguéis: {$user2AsOwner} cotas");
    }
}
