<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Quota;
use App\Models\RentalOffer;
use App\Models\ExchangeOffer;
use App\Models\SaleOffer;
use App\Models\PurchaseRequest;
use App\Models\Hotel;
use Carbon\Carbon;

class CreateTestOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-offers {user_id : ID do usuário para buscar seus hotéis}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria ofertas de teste (aluguel, troca, venda, compra) para cliente2@cotasbrasilis.com nos mesmos hotéis do usuário informado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $currentUser = User::find($userId);
        
        if (!$currentUser) {
            $this->error("Usuário com ID {$userId} não encontrado!");
            return 1;
        }

        $this->info("Buscando hotéis do usuário: {$currentUser->name} ({$currentUser->email})");

        // Buscar hotéis onde o usuário tem cotas
        $userHotelNames = $currentUser->quotas()
            ->whereNotNull('hotel_name')
            ->distinct()
            ->pluck('hotel_name')
            ->toArray();

        $userCities = $currentUser->quotas()
            ->whereNotNull('location')
            ->distinct()
            ->pluck('location')
            ->toArray();

        if (empty($userHotelNames) && empty($userCities)) {
            $this->error("O usuário não possui cotas cadastradas!");
            return 1;
        }

        $this->info("Hotéis encontrados: " . implode(', ', $userHotelNames));
        $this->info("Cidades encontradas: " . implode(', ', $userCities));

        // Buscar ou criar usuário cliente2
        $testUser = User::firstOrCreate(
            ['email' => 'cliente2@cotasbrasilis.com'],
            [
                'name' => 'Cliente Teste 2',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $this->info("Usuário de teste: {$testUser->name} (ID: {$testUser->id})");

        // Criar cotas para o cliente2 nos mesmos hotéis
        $createdQuotas = [];
        foreach ($userHotelNames as $hotelName) {
            $quota = Quota::create([
                'user_id' => $testUser->id,
                'hotel_name' => $hotelName,
                'location' => $userCities[0] ?? 'São Paulo',
                'start_date' => Carbon::now()->addDays(30),
                'end_date' => Carbon::now()->addDays(37),
                'number_of_guests' => 4,
                'rental_price' => 1500.00,
                'is_exchange' => false,
                'status' => 'available',
                'contract_photo_path' => 'test/contract.jpg', // Caminho fictício para teste
                'quota_status' => 'active',
                'payment_status' => 'paid',
                'is_owner' => true,
                'is_published' => true,
                'published_at' => now(),
                'weeks' => 1,
                'number_of_rooms' => 2,
                'seasonality' => 'medium',
            ]);
            $createdQuotas[] = $quota;
            $this->info("Cota criada: {$hotelName}");
        }

        if (empty($createdQuotas)) {
            $this->error("Nenhuma cota foi criada!");
            return 1;
        }

        // Buscar hotel_id se existir na tabela hotels
        $hotelIds = [];
        foreach ($userHotelNames as $hotelName) {
            $hotel = Hotel::where('name', 'like', "%{$hotelName}%")->first();
            if ($hotel) {
                $hotelIds[] = $hotel->id;
            }
        }

        // Criar ofertas de ALUGUEL
        foreach ($createdQuotas as $quota) {
            $hotelId = null;
            $hotel = Hotel::where('name', 'like', "%{$quota->hotel_name}%")->first();
            if ($hotel) {
                $hotelId = $hotel->id;
            } else {
                // Criar hotel se não existir
                $hotel = Hotel::create([
                    'name' => $quota->hotel_name,
                    'location' => $quota->location,
                    'address' => "Endereço de teste - {$quota->location}",
                    'is_active' => true,
                ]);
                $hotelId = $hotel->id;
            }

            RentalOffer::create([
                'user_id' => $testUser->id,
                'quota_id' => $quota->id,
                'hotel_id' => $hotelId,
                'title' => "Oferta de Aluguel - {$quota->hotel_name}",
                'description' => "Oferta de aluguel para {$quota->hotel_name}",
                'city' => $quota->location,
                'state' => 'SP',
                'start_date' => $quota->start_date,
                'end_date' => $quota->end_date,
                'number_of_days' => 7,
                'number_of_people' => $quota->number_of_guests,
                'price' => 1500.00,
                'original_price' => 2000.00,
                'status' => 'active',
                'observations' => 'Oferta de teste criada automaticamente',
                'period_type' => 'exact',
            ]);
        }
        $this->info("✓ Ofertas de ALUGUEL criadas: " . count($createdQuotas));

        // Criar ofertas de TROCA
        foreach ($createdQuotas as $quota) {
            ExchangeOffer::create([
                'user_id' => $testUser->id,
                'quota_id' => $quota->id,
                'exchange_type' => 'semana',
                'desired_city' => $quota->location,
                'desired_period_start' => Carbon::now()->addDays(60),
                'desired_period_end' => Carbon::now()->addDays(67),
                'desired_hotel' => $quota->hotel_name,
                'desired_hotels' => $quota->hotel_name ? [$quota->hotel_name] : null,
                'desired_people' => $quota->number_of_guests,
                'desired_rooms' => $quota->number_of_rooms ?? 2,
                'price_range_min' => 1000.00,
                'price_range_max' => 2000.00,
                'exchange_mode' => 'simples',
                'nights_plus_money' => '1 diária + R$ 100 (teste)',
                'status' => 'active',
                'validity_until' => Carbon::now()->addHours(48),
                'max_options' => 3,
            ]);
        }
        $this->info("✓ Ofertas de TROCA criadas: " . count($createdQuotas));

        // Criar ofertas de VENDA
        foreach ($createdQuotas as $quota) {
            $hotelId = null;
            $hotel = Hotel::where('name', 'like', "%{$quota->hotel_name}%")->first();
            if ($hotel) {
                $hotelId = $hotel->id;
            } else {
                // Criar hotel se não existir
                $hotel = Hotel::create([
                    'name' => $quota->hotel_name,
                    'location' => $quota->location,
                    'address' => "Endereço de teste - {$quota->location}",
                    'is_active' => true,
                ]);
                $hotelId = $hotel->id;
            }

            SaleOffer::create([
                'user_id' => $testUser->id,
                'quota_id' => $quota->id,
                'hotel_id' => $hotelId,
                'weeks' => $quota->weeks ?? 1,
                'number_of_rooms' => $quota->number_of_rooms ?? 2,
                'city' => $quota->location,
                'company' => 'Teste',
                'minimum_price' => 5000.00,
                'acceptable_price' => 7000.00,
                'desired_price' => 10000.00,
                'observations_by_price' => [
                    'minimum' => 'Preço mínimo aceito',
                    'acceptable' => 'Preço aceitável',
                    'desired' => 'Preço desejado',
                ],
                'status' => 'pending',
                'negotiation_status' => 'direct',
                'app_commission' => 10.00,
            ]);
        }
        $this->info("✓ Ofertas de VENDA criadas: " . count($createdQuotas));

        // Criar solicitações de COMPRA
        foreach ($createdQuotas as $quota) {
            $hotelId = null;
            $hotel = Hotel::where('name', 'like', "%{$quota->hotel_name}%")->first();
            if ($hotel) {
                $hotelId = $hotel->id;
            } else {
                // Criar hotel se não existir
                $hotel = Hotel::create([
                    'name' => $quota->hotel_name,
                    'location' => $quota->location,
                    'address' => "Endereço de teste - {$quota->location}",
                    'is_active' => true,
                ]);
                $hotelId = $hotel->id;
            }

            PurchaseRequest::create([
                'user_id' => $testUser->id,
                'hotel_id' => $hotelId,
                'weeks' => $quota->weeks ?? 1,
                'month' => Carbon::now()->addMonths(2)->month,
                'period_type' => 'fixo',
                'city' => $quota->location,
                'company' => 'Teste',
                'price_range_min' => 5000.00,
                'price_range_max' => 15000.00,
                'observations' => 'Solicitação de compra de teste',
                'status' => 'active',
                'delegated_to_admin' => false,
                'purchase_fee_percentage' => 10.00,
            ]);
        }
        $this->info("✓ Solicitações de COMPRA criadas: " . count($createdQuotas));

        $this->newLine();
        $this->info("✓ Dados de teste criados com sucesso!");
        $this->info("Total de cotas criadas: " . count($createdQuotas));
        $this->info("Usuário de teste: cliente2@cotasbrasilis.com (ID: {$testUser->id})");

        return 0;
    }
}
