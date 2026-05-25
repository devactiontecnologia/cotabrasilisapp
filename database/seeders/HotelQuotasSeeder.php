<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Quota;
use Carbon\Carbon;

class HotelQuotasSeeder extends Seeder
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

        // Buscar todos os hotéis ativos
        $hotels = Hotel::where('is_active', true)->get();

        if ($hotels->isEmpty()) {
            $this->command->error('Nenhum hotel encontrado. Execute o BrazilHotelsSeeder primeiro.');
            return;
        }

        $this->command->info("Encontrados {$hotels->count()} hotéis. Criando cotas...");

        $created = 0;
        $user1Quotas = 0;
        $user2Quotas = 0;

        // Definir variações de preços baseadas na estrelas do hotel
        $priceMultipliers = [
            3 => [800, 1200],
            4 => [1200, 2000],
            5 => [2000, 3500],
        ];

        // Definir variações de hóspedes
        $guestsOptions = [2, 3, 4, 5, 6];

        // Definir variações de quartos
        $roomsOptions = [1, 2, 3];

        // Definir sazonalidades
        $seasonalities = ['low', 'medium', 'high'];

        // Definir usos permitidos
        $allowedUsesOptions = [
            ['rent'],
            ['rent', 'exchange'],
            ['rent', 'exchange', 'sell'],
            ['rent', 'exchange', 'sell', 'buy'],
        ];

        // Distribuir hotéis entre os usuários
        $hotelsArray = $hotels->toArray();
        shuffle($hotelsArray);

        foreach ($hotelsArray as $index => $hotelData) {
            $hotel = Hotel::find($hotelData['id']);
            
            // Alternar entre os usuários
            $user = ($index % 2 == 0) ? $user1 : $user2;
            
            // Criar 1-3 cotas por hotel (variando)
            $quotasPerHotel = rand(1, 3);
            
            for ($q = 0; $q < $quotasPerHotel; $q++) {
                // Calcular datas (períodos futuros variados)
                $daysFromNow = rand(15, 180); // Entre 15 e 180 dias a partir de agora
                $startDate = Carbon::now()->addDays($daysFromNow);
                $duration = rand(5, 14); // Entre 5 e 14 dias
                $endDate = $startDate->copy()->addDays($duration - 1);
                
                // Calcular preço baseado nas estrelas
                $stars = $hotel->stars ?? 4;
                $multiplier = $priceMultipliers[$stars] ?? $priceMultipliers[4];
                $basePrice = rand($multiplier[0], $multiplier[1]);
                $rentalPrice = $basePrice * $duration;
                
                // Selecionar valores aleatórios
                $numberOfGuests = $guestsOptions[array_rand($guestsOptions)];
                $numberOfRooms = $roomsOptions[array_rand($roomsOptions)];
                $seasonality = $seasonalities[array_rand($seasonalities)];
                $allowedUses = $allowedUsesOptions[array_rand($allowedUsesOptions)];
                
                // Determinar se é troca ou aluguel
                $isExchange = in_array('exchange', $allowedUses) && rand(0, 1) == 1;
                
                // Criar observações variadas
                $observations = $this->generateObservations($hotel, $seasonality, $isExchange);
                
                // Determinar se será publicado e pago (70% das cotas)
                $isPublished = rand(1, 10) <= 7;
                $paymentStatus = $isPublished ? 'paid' : 'unpaid';
                $publishedAt = $isPublished ? Carbon::now()->subDays(rand(0, 30)) : null;
                
                // Criar a cota
                Quota::create([
                    'user_id' => $user->id,
                    'hotel_name' => $hotel->name,
                    'location' => $hotel->location,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'number_of_guests' => $numberOfGuests,
                    'rental_price' => $rentalPrice,
                    'is_exchange' => $isExchange,
                    'observations' => $observations,
                    'contract_photo_path' => 'demo/contract' . rand(1, 4) . '.jpg',
                    'status' => 'available',
                    'is_fractioned' => false,
                    'weeks' => ceil($duration / 7),
                    'number_of_rooms' => $numberOfRooms,
                    'seasonality' => $seasonality,
                    'payment_status' => $paymentStatus,
                    'is_owner' => true,
                    'is_published' => $isPublished,
                    'published_at' => $publishedAt,
                    'quota_status' => 'active',
                    'allowed_uses' => $allowedUses,
                    'created_at' => $publishedAt ?? Carbon::now()->subDays(rand(0, 60)),
                ]);
                
                $created++;
                if ($user->id == $user1->id) {
                    $user1Quotas++;
                } else {
                    $user2Quotas++;
                }
            }
        }

        $this->command->info("✓ {$created} cotas criadas com sucesso!");
        $this->command->info("  • {$user1Quotas} cotas para cliente@cotasbrasilis.com");
        $this->command->info("  • {$user2Quotas} cotas para cliente2@cotasbrasilis.com");
    }

    /**
     * Gerar observações variadas para as cotas
     */
    private function generateObservations($hotel, $seasonality, $isExchange): string
    {
        $observations = [];
        
        // Observação sobre o hotel
        $hotelObs = [
            "Cota no {$hotel->name}",
            "Hospedagem no {$hotel->name}",
            "Período disponível no {$hotel->name}",
        ];
        $observations[] = $hotelObs[array_rand($hotelObs)];
        
        // Observação sobre sazonalidade
        if ($seasonality === 'high') {
            $observations[] = "Alta temporada - período muito procurado";
        } elseif ($seasonality === 'medium') {
            $observations[] = "Média temporada - período ideal para viagem";
        } else {
            $observations[] = "Baixa temporada - preço especial";
        }
        
        // Observação sobre tipo
        if ($isExchange) {
            $observations[] = "Aceita troca por outras cotas";
        } else {
            $observations[] = "Disponível para aluguel";
        }
        
        // Observações adicionais aleatórias
        $extraObs = [
            "Cota em perfeito estado",
            "Documentação completa",
            "Localização privilegiada",
            "Estrutura completa do hotel disponível",
            "Ideal para família",
            "Período flexível para negociação",
        ];
        
        if (rand(0, 1)) {
            $observations[] = $extraObs[array_rand($extraObs)];
        }
        
        return implode('. ', $observations) . '.';
    }
}

