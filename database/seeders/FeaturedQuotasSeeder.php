<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Quota;
use Carbon\Carbon;

class FeaturedQuotasSeeder extends Seeder
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

        // Buscar hotéis que têm imagens
        $hotels = Hotel::where('is_active', true)
            ->whereNotNull('images')
            ->get()
            ->filter(function($hotel) {
                return is_array($hotel->images) && count($hotel->images) > 0;
            });

        if ($hotels->isEmpty()) {
            $this->command->error('Nenhum hotel com imagens encontrado. Execute o AddHotelImagesSeeder primeiro.');
            return;
        }

        $users = [$user1, $user2];
        $created = 0;
        $userIndex = 0;

        // Criar 30-40 cotas pagas e publicadas para diferentes hotéis
        $numQuotas = 35;
        
        foreach ($hotels->shuffle()->take($numQuotas) as $index => $hotel) {
            $user = $users[$userIndex];
            $userIndex = ($userIndex + 1) % count($users);

            // Variar as datas
            $startDate = Carbon::now()->addDays(rand(20, 200));
            $endDate = $startDate->copy()->addDays(rand(5, 14));
            
            $numberOfGuests = rand(2, 6);
            $numberOfRooms = rand(1, 3);
            
            $seasonalityOptions = ['low', 'medium', 'high'];
            $seasonality = $seasonalityOptions[array_rand($seasonalityOptions)];
            
            // Preço baseado nas estrelas do hotel
            $rentalPrice = match ($hotel->stars) {
                3 => rand(800, 1200) * $numberOfGuests,
                4 => rand(1200, 2000) * $numberOfGuests,
                5 => rand(2000, 3500) * $numberOfGuests,
                default => rand(1000, 1500) * $numberOfGuests,
            };
            
            // Usos permitidos variados
            $allowedUsesOptions = [
                ['rent'],
                ['rent', 'exchange'],
                ['rent', 'exchange', 'sell'],
                ['rent', 'exchange', 'sell', 'buy'],
            ];
            $allowedUses = $allowedUsesOptions[array_rand($allowedUsesOptions)];
            
            $isExchange = in_array('exchange', $allowedUses) && rand(0, 1) == 1;
            
            // Criar observações
            $observations = $this->generateObservations($hotel, $seasonality, $isExchange);
            
            // Variar published_at para ter algumas mais recentes
            $publishedAt = Carbon::now()->subDays(rand(0, 15));
            $createdAt = $publishedAt->copy()->subDays(rand(0, 5));
            
            // Criar a cota como PAGA e PUBLICADA
            Quota::create([
                'user_id' => $user->id,
                'hotel_name' => $hotel->name,
                'location' => $hotel->city . ', ' . $hotel->state,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'number_of_guests' => $numberOfGuests,
                'rental_price' => $rentalPrice,
                'is_exchange' => $isExchange,
                'observations' => $observations,
                'contract_photo_path' => 'demo/contract' . rand(1, 4) . '.jpg',
                'status' => 'available',
                'is_fractioned' => false,
                'weeks' => $endDate->diffInWeeks($startDate),
                'number_of_rooms' => $numberOfRooms,
                'seasonality' => $seasonality,
                'payment_status' => 'paid', // PAGA
                'is_owner' => true,
                'is_published' => true, // PUBLICADA
                'published_at' => $publishedAt,
                'quota_status' => 'active',
                'allowed_uses' => $allowedUses,
                'created_at' => $createdAt,
            ]);
            
            $created++;
        }

        $this->command->info("✓ {$created} cotas em destaque (pagas e publicadas) criadas com sucesso!");
        
        // Estatísticas
        $totalFeatured = Quota::where('is_published', true)
            ->where('payment_status', 'paid')
            ->where('status', 'available')
            ->count();
        
        $this->command->info("  • Total de cotas em destaque: {$totalFeatured}");
        
        // Contar por hotel
        $hotelsWithFeatured = Quota::where('is_published', true)
            ->where('payment_status', 'paid')
            ->where('status', 'available')
            ->distinct('hotel_name')
            ->count('hotel_name');
        
        $this->command->info("  • Hotéis com cotas em destaque: {$hotelsWithFeatured}");
    }

    private function generateObservations(Hotel $hotel, string $seasonality, bool $isExchange): string
    {
        $obs = "Hospedagem no {$hotel->name}. ";
        $obs .= match ($seasonality) {
            'low' => 'Baixa temporada - ideal para quem busca tranquilidade. ',
            'medium' => 'Média temporada - bom equilíbrio entre movimento e sossego. ',
            'high' => 'Alta temporada - período muito procurado. ',
            default => '',
        };
        if ($isExchange) {
            $obs .= 'Aceita troca por outras cotas.';
        } else {
            $obs .= 'Disponível para aluguel imediato.';
        }
        return $obs;
    }
}


