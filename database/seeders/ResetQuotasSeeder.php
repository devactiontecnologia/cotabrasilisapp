<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Quota;
use App\Models\QuotaTransaction;
use App\Models\RentalOffer;
use App\Models\ExchangeOffer;
use App\Models\SaleOffer;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ResetQuotasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Limpando todas as cotas e dados relacionados...');

        // Desabilitar verificação de foreign keys temporariamente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Deletar transações relacionadas
        QuotaTransaction::query()->delete();
        $this->command->info('✓ Transações deletadas');

        // Deletar ofertas relacionadas
        RentalOffer::query()->delete();
        ExchangeOffer::query()->delete();
        SaleOffer::query()->delete();
        $this->command->info('✓ Ofertas deletadas');

        // Deletar todas as cotas
        Quota::query()->delete();
        $this->command->info('✓ Todas as cotas deletadas');

        // Reabilitar verificação de foreign keys
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Buscar os dois usuários
        $user1 = User::where('email', 'cliente@cotasbrasilis.com')->first();
        $user2 = User::where('email', 'cliente2@cotasbrasilis.com')->first();

        if (!$user1 || !$user2) {
            $this->command->error('Usuários não encontrados. Execute o DemoAccountsSeeder primeiro.');
            return;
        }

        // Buscar hotéis reais do banco que tenham imagens e estado
        $availableHotels = Hotel::where('is_active', true)
            ->whereNotNull('images')
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->get();

        if ($availableHotels->isEmpty()) {
            $this->command->error('Nenhum hotel com imagens encontrado. Execute o BrazilHotelsSeeder primeiro.');
            return;
        }

        // Selecionar 6 hotéis diferentes
        $selectedHotels = $availableHotels->shuffle()->take(6);
        
        if ($selectedHotels->count() < 6) {
            $this->command->warning("Apenas {$selectedHotels->count()} hotéis disponíveis. Criando cotas para os hotéis encontrados.");
        }

        $this->command->info("Criando 6 novas cotas usando hotéis reais do banco...");

        $hotelIndex = 0;

        // COTA 1 - INTEGRAL (User 1)
        $hotel1 = $selectedHotels[$hotelIndex++];
        Quota::create([
            'user_id' => $user1->id,
            'hotel_name' => $hotel1->name,
            'location' => $hotel1->city . ', ' . $hotel1->state,
            'start_date' => Carbon::now()->addDays(30),
            'end_date' => Carbon::now()->addDays(36),
            'number_of_guests' => 4,
            'rental_price' => 3500.00,
            'is_exchange' => false,
            'observations' => 'Cota integral de 7 dias no ' . $hotel1->name . '. ' . ($hotel1->description ?? 'Período de alta temporada.'),
            'contract_photo_path' => 'demo/contract1.jpg',
            'status' => 'available',
            'is_fractioned' => false,
            'fraction_details' => null,
            'weeks' => 1,
            'number_of_rooms' => 1,
            'seasonality' => 'high',
            'payment_status' => 'paid',
            'is_owner' => true,
            'authorizations' => null,
            'is_published' => true,
            'published_at' => Carbon::now()->subDays(2),
            'quota_status' => 'active',
            'allowed_uses' => ['rent', 'exchange', 'sell', 'buy'],
            'negotiation_deadline' => null,
            'current_transaction_id' => null,
        ]);

        // COTA 2 - FRACIONADA 3+4 (User 1)
        $hotel2 = $selectedHotels[$hotelIndex++];
        Quota::create([
            'user_id' => $user1->id,
            'hotel_name' => $hotel2->name,
            'location' => $hotel2->city . ', ' . $hotel2->state,
            'start_date' => Carbon::now()->addDays(45),
            'end_date' => Carbon::now()->addDays(51),
            'number_of_guests' => 3,
            'rental_price' => 2800.00,
            'is_exchange' => false,
            'observations' => 'Cota fracionada 3+4 dias no ' . $hotel2->name . '. Primeira fração de 3 dias e segunda de 4 dias. ' . ($hotel2->description ?? 'Localização privilegiada.'),
            'contract_photo_path' => 'demo/contract2.jpg',
            'status' => 'available',
            'is_fractioned' => true,
            'fraction_details' => [
                'type' => '3_4',
                'fractions' => [
                    [
                        'days' => 3,
                        'start_date' => Carbon::now()->addDays(45)->format('Y-m-d'),
                        'end_date' => Carbon::now()->addDays(47)->format('Y-m-d'),
                    ],
                    [
                        'days' => 4,
                        'start_date' => Carbon::now()->addDays(48)->format('Y-m-d'),
                        'end_date' => Carbon::now()->addDays(51)->format('Y-m-d'),
                    ],
                ],
            ],
            'weeks' => 1,
            'number_of_rooms' => 2,
            'seasonality' => 'medium',
            'payment_status' => 'paid',
            'is_owner' => true,
            'authorizations' => null,
            'is_published' => true,
            'published_at' => Carbon::now()->subDays(5),
            'quota_status' => 'active',
            'allowed_uses' => ['rent', 'exchange'],
            'negotiation_deadline' => null,
            'current_transaction_id' => null,
        ]);

        // COTA 3 - INTEGRAL (User 1)
        $hotel3 = $selectedHotels[$hotelIndex++];
        Quota::create([
            'user_id' => $user1->id,
            'hotel_name' => $hotel3->name,
            'location' => $hotel3->city . ', ' . $hotel3->state,
            'start_date' => Carbon::now()->addDays(60),
            'end_date' => Carbon::now()->addDays(66),
            'number_of_guests' => 2,
            'rental_price' => 4200.00,
            'is_exchange' => false,
            'observations' => 'Cota integral de 7 dias no ' . $hotel3->name . '. ' . ($hotel3->description ?? 'Suíte de luxo com vista panorâmica. Ideal para casal.'),
            'contract_photo_path' => 'demo/contract3.jpg',
            'status' => 'available',
            'is_fractioned' => false,
            'fraction_details' => null,
            'weeks' => 1,
            'number_of_rooms' => 1,
            'seasonality' => 'high',
            'payment_status' => 'paid',
            'is_owner' => true,
            'authorizations' => null,
            'is_published' => true,
            'published_at' => Carbon::now()->subDays(1),
            'quota_status' => 'active',
            'allowed_uses' => ['rent', 'sell'],
            'negotiation_deadline' => null,
            'current_transaction_id' => null,
        ]);

        // COTA 4 - FRACIONADA 2+2+3 (User 2)
        $hotel4 = $selectedHotels[$hotelIndex++];
        Quota::create([
            'user_id' => $user2->id,
            'hotel_name' => $hotel4->name,
            'location' => $hotel4->city . ', ' . $hotel4->state,
            'start_date' => Carbon::now()->addDays(75),
            'end_date' => Carbon::now()->addDays(81),
            'number_of_guests' => 4,
            'rental_price' => 5500.00,
            'is_exchange' => false,
            'observations' => 'Cota fracionada 2+2+3 dias no ' . $hotel4->name . '. Três frações: 2 dias, 2 dias e 3 dias. ' . ($hotel4->description ?? 'Resort exclusivo.'),
            'contract_photo_path' => 'demo/contract4.jpg',
            'status' => 'available',
            'is_fractioned' => true,
            'fraction_details' => [
                'type' => '2_2_3',
                'fractions' => [
                    [
                        'days' => 2,
                        'start_date' => Carbon::now()->addDays(75)->format('Y-m-d'),
                        'end_date' => Carbon::now()->addDays(76)->format('Y-m-d'),
                    ],
                    [
                        'days' => 2,
                        'start_date' => Carbon::now()->addDays(77)->format('Y-m-d'),
                        'end_date' => Carbon::now()->addDays(78)->format('Y-m-d'),
                    ],
                    [
                        'days' => 3,
                        'start_date' => Carbon::now()->addDays(79)->format('Y-m-d'),
                        'end_date' => Carbon::now()->addDays(81)->format('Y-m-d'),
                    ],
                ],
            ],
            'weeks' => 1,
            'number_of_rooms' => 1,
            'seasonality' => 'peak',
            'payment_status' => 'paid',
            'is_owner' => true,
            'authorizations' => null,
            'is_published' => true,
            'published_at' => Carbon::now()->subDays(3),
            'quota_status' => 'active',
            'allowed_uses' => ['rent', 'exchange', 'sell', 'buy'],
            'negotiation_deadline' => null,
            'current_transaction_id' => null,
        ]);

        // COTA 5 - FRACIONADA 4+3 (User 2)
        $hotel5 = $selectedHotels[$hotelIndex++];
        Quota::create([
            'user_id' => $user2->id,
            'hotel_name' => $hotel5->name,
            'location' => $hotel5->city . ', ' . $hotel5->state,
            'start_date' => Carbon::now()->addDays(90),
            'end_date' => Carbon::now()->addDays(96),
            'number_of_guests' => 3,
            'rental_price' => 3200.00,
            'is_exchange' => false,
            'observations' => 'Cota fracionada 4+3 dias no ' . $hotel5->name . '. Primeira fração de 4 dias e segunda de 3 dias. ' . ($hotel5->description ?? 'Hotel de luxo.'),
            'contract_photo_path' => 'demo/contract1.jpg',
            'status' => 'available',
            'is_fractioned' => true,
            'fraction_details' => [
                'type' => '4_3',
                'fractions' => [
                    [
                        'days' => 4,
                        'start_date' => Carbon::now()->addDays(90)->format('Y-m-d'),
                        'end_date' => Carbon::now()->addDays(93)->format('Y-m-d'),
                    ],
                    [
                        'days' => 3,
                        'start_date' => Carbon::now()->addDays(94)->format('Y-m-d'),
                        'end_date' => Carbon::now()->addDays(96)->format('Y-m-d'),
                    ],
                ],
            ],
            'weeks' => 1,
            'number_of_rooms' => 2,
            'seasonality' => 'medium',
            'payment_status' => 'paid',
            'is_owner' => true,
            'authorizations' => null,
            'is_published' => true,
            'published_at' => Carbon::now()->subDays(4),
            'quota_status' => 'active',
            'allowed_uses' => ['rent', 'exchange'],
            'negotiation_deadline' => null,
            'current_transaction_id' => null,
        ]);

        // COTA 6 - INTEGRAL (User 2)
        $hotel6 = $selectedHotels[$hotelIndex++];
        Quota::create([
            'user_id' => $user2->id,
            'hotel_name' => $hotel6->name,
            'location' => $hotel6->city . ', ' . $hotel6->state,
            'start_date' => Carbon::now()->addDays(105),
            'end_date' => Carbon::now()->addDays(111),
            'number_of_guests' => 5,
            'rental_price' => 4800.00,
            'is_exchange' => false,
            'observations' => 'Cota integral de 7 dias no ' . $hotel6->name . '. ' . ($hotel6->description ?? 'Suíte familiar com 2 quartos. Período de alta temporada.'),
            'contract_photo_path' => 'demo/contract2.jpg',
            'status' => 'available',
            'is_fractioned' => false,
            'fraction_details' => null,
            'weeks' => 1,
            'number_of_rooms' => 2,
            'seasonality' => 'high',
            'payment_status' => 'paid',
            'is_owner' => true,
            'authorizations' => null,
            'is_published' => true,
            'published_at' => Carbon::now()->subDays(6),
            'quota_status' => 'active',
            'allowed_uses' => ['rent', 'exchange', 'sell', 'buy'],
            'negotiation_deadline' => null,
            'current_transaction_id' => null,
        ]);

        $this->command->info('✓ 6 cotas criadas com sucesso!');
        $this->command->info('  • 3 cotas fracionadas (3+4, 2+2+3, 4+3)');
        $this->command->info('  • 3 cotas integrais (7 dias cada)');
        $this->command->info('  • 3 cotas para cliente@cotasbrasilis.com');
        $this->command->info('  • 3 cotas para cliente2@cotasbrasilis.com');
    }
}
