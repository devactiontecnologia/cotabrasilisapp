<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Quota;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AddTurbinaQuotasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Adicionando 6 novas cotas para a seção Turbina...');

        // Buscar os dois usuários
        $user1 = User::where('email', 'cliente@cotasbrasilis.com')->first();
        $user2 = User::where('email', 'cliente2@cotasbrasilis.com')->first();

        if (!$user1 || !$user2) {
            $this->command->error('Usuários não encontrados. Execute o DemoAccountsSeeder primeiro.');
            return;
        }

        // Buscar hotéis que já foram usados
        $usedHotels = Quota::distinct()->pluck('hotel_name')->toArray();

        // Buscar hotéis reais do banco que tenham imagens, estado e NÃO tenham sido usados
        $availableHotels = Hotel::where('is_active', true)
            ->whereNotNull('images')
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->whereNotIn('name', $usedHotels)
            ->get();

        if ($availableHotels->count() < 6) {
            $this->command->warning("Apenas {$availableHotels->count()} hotéis disponíveis. Usando os hotéis encontrados.");
        }

        // Selecionar 6 hotéis diferentes
        $selectedHotels = $availableHotels->shuffle()->take(6);
        
        if ($selectedHotels->isEmpty()) {
            $this->command->error('Nenhum hotel disponível com imagens encontrado.');
            return;
        }

        $hotelIndex = 0;

        // COTA 1 - INTEGRAL (User 1) - TURBINA
        $hotel1 = $selectedHotels[$hotelIndex++];
        Quota::create([
            'user_id' => $user1->id,
            'hotel_name' => $hotel1->name,
            'location' => $hotel1->city . ', ' . $hotel1->state,
            'start_date' => Carbon::now()->addDays(20),
            'end_date' => Carbon::now()->addDays(26),
            'number_of_guests' => 4,
            'rental_price' => 3800.00,
            'is_exchange' => false,
            'observations' => 'Cota integral de 7 dias no ' . $hotel1->name . '. Perfeita para turbinar sua divulgação! ' . ($hotel1->description ?? 'Período de alta temporada com todas as comodidades.'),
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
            'published_at' => Carbon::now()->subDays(1),
            'quota_status' => 'active',
            'allowed_uses' => ['rent', 'exchange', 'sell', 'buy'],
            'negotiation_deadline' => null,
            'current_transaction_id' => null,
            'created_at' => Carbon::now()->subDays(1),
        ]);

        // COTA 2 - FRACIONADA 3+4 (User 1) - TURBINA
        if ($hotelIndex < $selectedHotels->count()) {
            $hotel2 = $selectedHotels[$hotelIndex++];
            Quota::create([
                'user_id' => $user1->id,
                'hotel_name' => $hotel2->name,
                'location' => $hotel2->city . ', ' . $hotel2->state,
                'start_date' => Carbon::now()->addDays(35),
                'end_date' => Carbon::now()->addDays(41),
                'number_of_guests' => 3,
                'rental_price' => 2900.00,
                'is_exchange' => false,
                'observations' => 'Cota fracionada 3+4 dias no ' . $hotel2->name . '. Primeira fração de 3 dias e segunda de 4 dias. Ideal para turbinar sua divulgação! ' . ($hotel2->description ?? 'Localização privilegiada.'),
                'contract_photo_path' => 'demo/contract2.jpg',
                'status' => 'available',
                'is_fractioned' => true,
                'fraction_details' => [
                    'type' => '3_4',
                    'fractions' => [
                        [
                            'days' => 3,
                            'start_date' => Carbon::now()->addDays(35)->format('Y-m-d'),
                            'end_date' => Carbon::now()->addDays(37)->format('Y-m-d'),
                        ],
                        [
                            'days' => 4,
                            'start_date' => Carbon::now()->addDays(38)->format('Y-m-d'),
                            'end_date' => Carbon::now()->addDays(41)->format('Y-m-d'),
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
                'published_at' => Carbon::now()->subDays(2),
                'quota_status' => 'active',
                'allowed_uses' => ['rent', 'exchange'],
                'negotiation_deadline' => null,
                'current_transaction_id' => null,
                'created_at' => Carbon::now()->subDays(2),
            ]);
        }

        // COTA 3 - INTEGRAL (User 1) - TURBINA
        if ($hotelIndex < $selectedHotels->count()) {
            $hotel3 = $selectedHotels[$hotelIndex++];
            Quota::create([
                'user_id' => $user1->id,
                'hotel_name' => $hotel3->name,
                'location' => $hotel3->city . ', ' . $hotel3->state,
                'start_date' => Carbon::now()->addDays(50),
                'end_date' => Carbon::now()->addDays(56),
                'number_of_guests' => 2,
                'rental_price' => 4500.00,
                'is_exchange' => false,
                'observations' => 'Cota integral de 7 dias no ' . $hotel3->name . '. Suíte de luxo perfeita para turbinar sua divulgação! ' . ($hotel3->description ?? 'Vista panorâmica. Ideal para casal.'),
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
                'published_at' => Carbon::now()->subDays(3),
                'quota_status' => 'active',
                'allowed_uses' => ['rent', 'sell'],
                'negotiation_deadline' => null,
                'current_transaction_id' => null,
                'created_at' => Carbon::now()->subDays(3),
            ]);
        }

        // COTA 4 - FRACIONADA 2+2+3 (User 2) - TURBINA
        if ($hotelIndex < $selectedHotels->count()) {
            $hotel4 = $selectedHotels[$hotelIndex++];
            Quota::create([
                'user_id' => $user2->id,
                'hotel_name' => $hotel4->name,
                'location' => $hotel4->city . ', ' . $hotel4->state,
                'start_date' => Carbon::now()->addDays(65),
                'end_date' => Carbon::now()->addDays(71),
                'number_of_guests' => 5,
                'rental_price' => 5200.00,
                'is_exchange' => false,
                'observations' => 'Cota fracionada 2+2+3 dias no ' . $hotel4->name . '. Três frações: 2 dias, 2 dias e 3 dias. Excelente para turbinar sua divulgação! ' . ($hotel4->description ?? 'Resort exclusivo.'),
                'contract_photo_path' => 'demo/contract4.jpg',
                'status' => 'available',
                'is_fractioned' => true,
                'fraction_details' => [
                    'type' => '2_2_3',
                    'fractions' => [
                        [
                            'days' => 2,
                            'start_date' => Carbon::now()->addDays(65)->format('Y-m-d'),
                            'end_date' => Carbon::now()->addDays(66)->format('Y-m-d'),
                        ],
                        [
                            'days' => 2,
                            'start_date' => Carbon::now()->addDays(67)->format('Y-m-d'),
                            'end_date' => Carbon::now()->addDays(68)->format('Y-m-d'),
                        ],
                        [
                            'days' => 3,
                            'start_date' => Carbon::now()->addDays(69)->format('Y-m-d'),
                            'end_date' => Carbon::now()->addDays(71)->format('Y-m-d'),
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
                'published_at' => Carbon::now()->subDays(4),
                'quota_status' => 'active',
                'allowed_uses' => ['rent', 'exchange', 'sell', 'buy'],
                'negotiation_deadline' => null,
                'current_transaction_id' => null,
                'created_at' => Carbon::now()->subDays(4),
            ]);
        }

        // COTA 5 - FRACIONADA 4+3 (User 2) - TURBINA
        if ($hotelIndex < $selectedHotels->count()) {
            $hotel5 = $selectedHotels[$hotelIndex++];
            Quota::create([
                'user_id' => $user2->id,
                'hotel_name' => $hotel5->name,
                'location' => $hotel5->city . ', ' . $hotel5->state,
                'start_date' => Carbon::now()->addDays(80),
                'end_date' => Carbon::now()->addDays(86),
                'number_of_guests' => 3,
                'rental_price' => 3400.00,
                'is_exchange' => false,
                'observations' => 'Cota fracionada 4+3 dias no ' . $hotel5->name . '. Primeira fração de 4 dias e segunda de 3 dias. Perfeita para turbinar sua divulgação! ' . ($hotel5->description ?? 'Hotel de luxo.'),
                'contract_photo_path' => 'demo/contract1.jpg',
                'status' => 'available',
                'is_fractioned' => true,
                'fraction_details' => [
                    'type' => '4_3',
                    'fractions' => [
                        [
                            'days' => 4,
                            'start_date' => Carbon::now()->addDays(80)->format('Y-m-d'),
                            'end_date' => Carbon::now()->addDays(83)->format('Y-m-d'),
                        ],
                        [
                            'days' => 3,
                            'start_date' => Carbon::now()->addDays(84)->format('Y-m-d'),
                            'end_date' => Carbon::now()->addDays(86)->format('Y-m-d'),
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
                'created_at' => Carbon::now()->subDays(5),
            ]);
        }

        // COTA 6 - INTEGRAL (User 2) - TURBINA
        if ($hotelIndex < $selectedHotels->count()) {
            $hotel6 = $selectedHotels[$hotelIndex++];
            Quota::create([
                'user_id' => $user2->id,
                'hotel_name' => $hotel6->name,
                'location' => $hotel6->city . ', ' . $hotel6->state,
                'start_date' => Carbon::now()->addDays(95),
                'end_date' => Carbon::now()->addDays(101),
                'number_of_guests' => 6,
                'rental_price' => 5500.00,
                'is_exchange' => false,
                'observations' => 'Cota integral de 7 dias no ' . $hotel6->name . '. Suíte familiar perfeita para turbinar sua divulgação! ' . ($hotel6->description ?? 'Período de alta temporada. Ideal para família.'),
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
                'created_at' => Carbon::now()->subDays(6),
            ]);
        }

        $createdCount = min($selectedHotels->count(), 6);
        $this->command->info("✓ {$createdCount} cotas criadas com sucesso para a seção Turbina!");
        $this->command->info('  • Todas vinculadas a hotéis com imagens');
        $this->command->info('  • Todas publicadas e pagas');
        $this->command->info('  • Prontas para aparecer na seção "Para quem quer turbinar sua divulgação"');
    }
}
