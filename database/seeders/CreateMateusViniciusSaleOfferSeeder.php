<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Quota;
use App\Models\SaleOffer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CreateMateusViniciusSaleOfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()
            ->where('email', 'finnohumano110@gmail.com')
            ->orWhere('name', 'Mateus Vinicius')
            ->first();

        if (! $user) {
            $this->command?->warn('Usuario Mateus Vinicius nao encontrado.');

            return;
        }

        $quota = Quota::query()
            ->where('user_id', $user->id)
            ->where('status', Quota::STATUS_AVAILABLE)
            ->orderByDesc('id')
            ->first();

        if (! $quota) {
            $quota = Quota::query()
                ->where('user_id', $user->id)
                ->orderByDesc('id')
                ->first();
        }

        if (! $quota) {
            $this->command?->warn('Nenhuma cota encontrada para Mateus Vinicius.');

            return;
        }

        $allowedUses = collect($quota->allowed_uses ?? [])->map(fn ($u) => (string) $u)->filter()->values();
        if (! $allowedUses->contains('sell')) {
            $allowedUses->push('sell');
        }

        $fractionDetails = $quota->fraction_details;
        if (is_array($fractionDetails) && isset($fractionDetails['fraction_weeks'])) {
            foreach ($fractionDetails['fraction_weeks'] as $wi => $weekData) {
                foreach ($weekData['periods'] ?? [] as $pi => $period) {
                    if (! is_array($period)) {
                        continue;
                    }
                    if (empty($period['action']) || ! in_array($period['action'], ['sell'], true)) {
                        $fractionDetails['fraction_weeks'][$wi]['periods'][$pi]['action'] = 'sell';
                        $fractionDetails['fraction_weeks'][$wi]['periods'][$pi]['enabled'] = $period['enabled'] ?? true;
                    }
                }
            }
        }

        $quota->update([
            'status' => Quota::STATUS_AVAILABLE,
            'allowed_uses' => $allowedUses->unique()->values()->all(),
            'fraction_details' => $fractionDetails ?? $quota->fraction_details,
        ]);

        $hotel = Hotel::query()->firstOrCreate(
            ['name' => $quota->hotel_name],
            [
                'location' => $quota->location,
                'address' => 'Endereco de teste - ' . $quota->location,
                'is_active' => true,
            ]
        );

        $saleOffer = SaleOffer::query()->firstOrNew([
            'user_id' => $user->id,
            'quota_id' => $quota->id,
            'company' => 'Seeder de teste compra',
        ]);

        $saleOffer->fill([
            'hotel_id' => $hotel->id,
            'weeks' => $quota->weeks ?? 1,
            'number_of_rooms' => $quota->number_of_rooms ?? 1,
            'city' => $hotel->city ?: $quota->location,
            'minimum_price' => 15000.00,
            'acceptable_price' => 18000.00,
            'desired_price' => 22000.00,
            'observations_by_price' => [
                'minimum' => 'Preco minimo aceito para teste do fluxo de compra.',
                'acceptable' => 'Preco aceitavel para teste do fluxo de compra.',
                'desired' => 'Preco desejado para teste do fluxo de compra.',
            ],
            'status' => 'pending',
            'negotiation_status' => 'direct',
            'app_commission' => 10.00,
        ]);

        $saleOffer->save();

        $this->command?->info(sprintf(
            'Oferta de venda preparada. Quota #%d, SaleOffer #%d. Teste o filtro Compra com outro usuario (nao o dono).',
            $quota->id,
            $saleOffer->id
        ));
    }
}
