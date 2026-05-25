<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Quota;
use App\Models\QuotaTransaction;
use App\Models\Hotel;
use Illuminate\Support\Facades\Hash;

class UsersQuotasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar o hotel de exemplo
        $hotel = Hotel::where('name', 'Hotel Exemplo')->first();
        
        if (!$hotel) {
            $this->command->error('Hotel Exemplo não encontrado. Execute o HotelSeeder primeiro.');
            return;
        }

        // Verificar se os usuários já existem
        $user1 = User::where('email', 'joao.silva@example.com')->first();
        $user2 = User::where('email', 'maria.santos@example.com')->first();

        // Criar usuário 1 - João Silva
        if (!$user1) {
            $user1 = User::create([
                'name' => 'João Silva',
                'email' => 'joao.silva@example.com',
                'password' => Hash::make('password123'),
                'whatsapp' => '(11) 98765-4321',
                'is_active' => true,
                'is_blocked' => false,
                'role' => 'user',
                'is_admin' => false,
                'ingress_date' => now()->subMonths(6),
            ]);
            $this->command->info('✓ Usuário João Silva criado');
        }

        // Criar usuário 2 - Maria Santos
        if (!$user2) {
            $user2 = User::create([
                'name' => 'Maria Santos',
                'email' => 'maria.santos@example.com',
                'password' => Hash::make('password123'),
                'whatsapp' => '(21) 97654-3210',
                'is_active' => true,
                'is_blocked' => false,
                'role' => 'user',
                'is_admin' => false,
                'ingress_date' => now()->subMonths(4),
            ]);
            $this->command->info('✓ Usuário Maria Santos criado');
        }

        // Criar cota para o João
        $quota1 = Quota::where('user_id', $user1->id)
            ->where('hotel_name', $hotel->name)
            ->first();

        if (!$quota1) {
            $quota1 = Quota::create([
                'user_id' => $user1->id,
                'hotel_name' => $hotel->name,
                'location' => $hotel->location,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(335), // 1 ano
                'number_of_guests' => 4,
                'rental_price' => 2500.00,
                'is_exchange' => false,
                'observations' => 'Cota semanal no Hotel Exemplo. Período de alta temporada.',
                'contract_photo_path' => 'storage/exemplo_contrato_1.jpg',
                'status' => 'available',
                'is_fractioned' => false,
                'weeks' => 52,
                'number_of_rooms' => 2,
                'seasonality' => 'high',
                'payment_status' => 'paid',
                'is_owner' => true,
                'is_published' => true,
                'published_at' => now()->subDays(25),
            ]);
            $this->command->info('✓ Cota 1 criada para João Silva');
        }

        // Criar cota para a Maria
        $quota2 = Quota::where('user_id', $user2->id)
            ->where('hotel_name', $hotel->name)
            ->first();

        if (!$quota2) {
            $quota2 = Quota::create([
                'user_id' => $user2->id,
                'hotel_name' => $hotel->name,
                'location' => $hotel->location,
                'start_date' => now()->addDays(30),
                'end_date' => now()->addDays(395),
                'number_of_guests' => 6,
                'rental_price' => 3500.00,
                'is_exchange' => true,
                'observations' => 'Cota disponível para troca. Acepta troca por cota em resort de praia.',
                'contract_photo_path' => 'storage/exemplo_contrato_2.jpg',
                'status' => 'available',
                'is_fractioned' => true,
                'fraction_details' => [
                    'total_fractions' => 4,
                    'available_fractions' => 2,
                    'price_per_fraction' => 875.00
                ],
                'weeks' => 52,
                'number_of_rooms' => 3,
                'seasonality' => 'high',
                'payment_status' => 'paid',
                'is_owner' => true,
                'is_published' => true,
                'published_at' => now()->subDays(20),
            ]);
            $this->command->info('✓ Cota 2 criada para Maria Santos');
        }

        // Verificar se a transação já existe
        $existingTransaction = QuotaTransaction::where('quota_id', $quota1->id)
            ->where('renter_id', $user2->id)
            ->first();

        if (!$existingTransaction) {
            // Criar transação: Maria alugando a cota do João
            $transaction = QuotaTransaction::create([
                'quota_id' => $quota1->id,
                'renter_id' => $user2->id,
                'owner_id' => $user1->id,
                'transaction_type' => 'rental',
                'total_amount' => 2500.00,
                'owner_amount' => 2250.00, // 10% de taxa da plataforma
                'platform_fee' => 250.00,
                'status' => 'completed',
                'contract_signed_at' => now()->subDays(8),
                'payment_completed_at' => now()->subDays(5),
                'payment_reference' => 'PIX-' . strtoupper(uniqid()),
                'created_at' => now()->subDays(10),
            ]);
            $this->command->info('✓ Transação 1 criada: Maria alugou a cota do João');

            // Criar segunda transação: João alugando a cota da Maria
            $transaction2 = QuotaTransaction::create([
                'quota_id' => $quota2->id,
                'renter_id' => $user1->id,
                'owner_id' => $user2->id,
                'transaction_type' => 'exchange',
                'total_amount' => 3500.00,
                'owner_amount' => 0.00, // Troca sem pagamento
                'platform_fee' => 0.00,
                'status' => 'contract_signed',
                'contract_signed_at' => now()->subDays(2),
                'payment_reference' => 'TROCA-' . strtoupper(uniqid()),
                'created_at' => now()->subDays(5),
            ]);
            $this->command->info('✓ Transação 2 criada: João trocou com a Maria (em andamento)');

            $this->command->info('');
            $this->command->info('✓ Resumo dos dados criados:');
            $this->command->info('  • 2 usuários de exemplo');
            $this->command->info('  • 2 cotas ligadas ao Hotel Exemplo');
            $this->command->info('  • 2 transações fictícias');
            $this->command->info('');
            $this->command->info('Credenciais dos usuários:');
            $this->command->info('  • João Silva: joao.silva@example.com / password123');
            $this->command->info('  • Maria Santos: maria.santos@example.com / password123');
        } else {
            $this->command->info('Dados já existem no banco de dados.');
        }
    }
}

