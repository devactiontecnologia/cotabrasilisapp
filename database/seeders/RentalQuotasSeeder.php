<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Quota;
use App\Models\RentalOffer;
use Carbon\Carbon;

class RentalQuotasSeeder extends Seeder
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

        // Buscar hotéis ativos
        $hotels = Hotel::where('is_active', true)->get();

        if ($hotels->isEmpty()) {
            $this->command->error('Nenhum hotel encontrado. Execute o BrazilHotelsSeeder primeiro.');
            return;
        }

        $this->command->info("Criando cotas para aluguel...");

        // Definir meses e períodos
        $months = [
            ['month' => 1, 'name' => 'Janeiro'],
            ['month' => 6, 'name' => 'Junho'],
            ['month' => 9, 'name' => 'Setembro'],
            ['month' => 11, 'name' => 'Novembro'],
            ['month' => 12, 'name' => 'Dezembro'],
        ];

        $year = now()->year;
        $createdQuotas = 0;
        $createdOffers = 0;
        $user1Quotas = 0;
        $user2Quotas = 0;

        // Distribuir hotéis entre os usuários
        $hotelsArray = $hotels->toArray();
        shuffle($hotelsArray);

        foreach ($months as $monthData) {
            $month = $monthData['month'];
            $monthName = $monthData['name'];
            
            // Criar 2-3 cotas por mês, alternando entre os usuários
            $quotasPerMonth = rand(2, 3);
            
            for ($i = 0; $i < $quotasPerMonth; $i++) {
                // Alternar entre os usuários
                $user = ($i % 2 == 0) ? $user1 : $user2;
                
                // Selecionar hotel aleatório
                $hotel = Hotel::find($hotelsArray[array_rand($hotelsArray)]['id']);
                
                // Determinar se será fracionada (50% de chance)
                $isFractioned = rand(0, 1) == 1;
                
                // Definir período baseado se é fracionada ou não
                if ($isFractioned) {
                    // Cotas fracionadas: 7, 14 ou 21 dias
                    $duration = [7, 14, 21][array_rand([7, 14, 21])];
                    $startDay = rand(1, 15); // Começar no início ou meio do mês
                } else {
                    // Cotas não fracionadas: 7 ou 14 dias completos
                    $duration = [7, 14][array_rand([7, 14])];
                    $startDay = rand(1, 20);
                }
                
                // Calcular datas
                $startDate = Carbon::create($year, $month, $startDay);
                $endDate = $startDate->copy()->addDays($duration - 1);
                
                // Ajustar se ultrapassar o mês
                if ($endDate->month != $month) {
                    $endDate = Carbon::create($year, $month, $startDate->daysInMonth);
                    $duration = $startDate->diffInDays($endDate) + 1;
                }
                
                // Calcular preço baseado nas estrelas do hotel
                $stars = $hotel->stars ?? 4;
                $pricePerDay = [
                    3 => rand(80, 120),
                    4 => rand(120, 200),
                    5 => rand(200, 350),
                ][$stars] ?? 150;
                
                $rentalPrice = $pricePerDay * $duration;
                
                // Número de hóspedes e quartos
                $numberOfGuests = [2, 3, 4, 5, 6][array_rand([2, 3, 4, 5, 6])];
                $numberOfRooms = [1, 2][array_rand([1, 2])];
                
                // Sazonalidade
                $seasonalities = ['low', 'medium', 'high'];
                $seasonality = $seasonalities[array_rand($seasonalities)];
                
                // Detalhes de fração (se aplicável)
                $fractionDetails = null;
                if ($isFractioned) {
                    $fractionDetails = [
                        'total_days' => $duration,
                        'fraction_type' => 'weekly',
                        'weeks' => ceil($duration / 7),
                    ];
                }
                
                // Criar a cota
                $quota = Quota::create([
                    'user_id' => $user->id,
                    'hotel_name' => $hotel->name,
                    'location' => $hotel->location ?? $hotel->city ?? 'Brasil',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'number_of_guests' => $numberOfGuests,
                    'rental_price' => $rentalPrice,
                    'is_exchange' => false, // Cotas para aluguel
                    'observations' => $this->generateObservations($hotel, $monthName, $isFractioned, $duration),
                    'contract_photo_path' => 'demo/contract' . rand(1, 4) . '.jpg',
                    'status' => 'available',
                    'is_fractioned' => $isFractioned,
                    'fraction_details' => $fractionDetails,
                    'weeks' => ceil($duration / 7),
                    'number_of_rooms' => $numberOfRooms,
                    'seasonality' => $seasonality,
                    'payment_status' => 'paid',
                    'is_owner' => true,
                    'is_published' => true,
                    'published_at' => Carbon::now()->subDays(rand(0, 30)),
                    'quota_status' => 'active',
                    'allowed_uses' => ['rent'],
                ]);
                
                $createdQuotas++;
                if ($user->id == $user1->id) {
                    $user1Quotas++;
                } else {
                    $user2Quotas++;
                }
                
                // Criar oferta de aluguel vinculada à cota
                $offerPrice = $rentalPrice * 0.95; // Preço ligeiramente menor na oferta
                
                $rentalOffer = RentalOffer::create([
                    'user_id' => $user->id,
                    'quota_id' => $quota->id,
                    'hotel_id' => $hotel->id,
                    'title' => "Aluguel de Cota - {$hotel->name} - {$monthName}",
                    'description' => "Oferta de aluguel de cota no {$hotel->name} durante o mês de {$monthName}. " . 
                                    ($isFractioned ? "Cota fracionada de {$duration} dias." : "Período completo de {$duration} dias."),
                    'city' => $hotel->city ?? 'Não informado',
                    'state' => $hotel->state ?? 'Não informado',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'number_of_days' => $duration,
                    'number_of_people' => $numberOfGuests,
                    'price' => $offerPrice,
                    'original_price' => $rentalPrice,
                    'status' => 'active',
                    'is_fractioned' => $isFractioned,
                    'fraction_details' => $fractionDetails,
                    'is_auction' => false,
                    'photos' => [],
                    'observations' => $quota->observations,
                    'views_count' => rand(0, 50),
                    'favorites_count' => rand(0, 10),
                    'period_type' => $isFractioned ? 'flexible' : 'exact',
                    'accepts_exchange' => false,
                    'accepts_sale' => false,
                    'created_at' => $quota->published_at ?? Carbon::now()->subDays(rand(0, 30)),
                ]);
                
                $createdOffers++;
                
                $this->command->info("  ✓ Cota criada para {$user->email} - {$hotel->name} - {$monthName} ({$duration} dias)" . 
                                    ($isFractioned ? " [FRACIONADA]" : ""));
            }
        }

        $this->command->info("\n✓ {$createdQuotas} cotas criadas com sucesso!");
        $this->command->info("  • {$user1Quotas} cotas para cliente@cotasbrasilis.com");
        $this->command->info("  • {$user2Quotas} cotas para cliente2@cotasbrasilis.com");
        $this->command->info("✓ {$createdOffers} ofertas de aluguel criadas com sucesso!");
    }

    /**
     * Gerar observações para as cotas
     */
    private function generateObservations($hotel, $monthName, $isFractioned, $duration): string
    {
        $observations = [];
        
        // Observação sobre o hotel
        $observations[] = "Cota disponível no {$hotel->name} durante o mês de {$monthName}";
        
        // Observação sobre fração
        if ($isFractioned) {
            $observations[] = "Cota fracionada de {$duration} dias";
            $observations[] = "Período flexível para negociação";
        } else {
            $observations[] = "Período completo de {$duration} dias";
        }
        
        // Observações adicionais
        $extraObs = [
            "Cota em perfeito estado",
            "Documentação completa e regularizada",
            "Localização privilegiada",
            "Ideal para família ou casal",
            "Estrutura completa do hotel disponível",
        ];
        
        $observations[] = $extraObs[array_rand($extraObs)];
        
        return implode('. ', $observations) . '.';
    }
}













