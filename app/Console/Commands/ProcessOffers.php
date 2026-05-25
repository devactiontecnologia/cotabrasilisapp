<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RentalOffer;
use App\Models\WishlistRequest;
use App\Models\HospitalityAuthorization;

class ProcessOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa ofertas: aplica SuperDesconto, verifica expirações e processa wishlist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processando ofertas...');

        // Aplicar SuperDesconto em ofertas elegíveis
        $this->applySuperDesconto();

        // Verificar ofertas expiradas
        $this->checkExpiredOffers();

        // Processar wishlist
        $this->processWishlist();

        // Verificar autorizações de hospedagem expiradas
        $this->checkExpiredAuthorizations();

        $this->info('Processamento concluído!');
        
        return Command::SUCCESS;
    }

    /**
     * Aplicar SuperDesconto em ofertas elegíveis
     */
    private function applySuperDesconto()
    {
        $this->info('Aplicando SuperDesconto...');

        $offers = RentalOffer::eligibleForSuperDesconto()->get();
        $appliedCount = 0;

        foreach ($offers as $offer) {
            if ($offer->applySuperDesconto()) {
                $appliedCount++;
                $this->line("SuperDesconto aplicado na oferta: {$offer->title} (ID: {$offer->id})");
            }
        }

        $this->info("SuperDesconto aplicado em {$appliedCount} ofertas.");
    }

    /**
     * Verificar ofertas expiradas
     */
    private function checkExpiredOffers()
    {
        $this->info('Verificando ofertas expiradas...');

        $expiredOffers = RentalOffer::where('status', 'active')
            ->where('end_date', '<', now()->toDateString())
            ->get();

        $expiredCount = 0;

        foreach ($expiredOffers as $offer) {
            $offer->update(['status' => 'expired']);
            $expiredCount++;
            $this->line("Oferta expirada: {$offer->title} (ID: {$offer->id})");
        }

        $this->info("{$expiredCount} ofertas marcadas como expiradas.");
    }

    /**
     * Processar wishlist
     */
    private function processWishlist()
    {
        $this->info('Processando wishlist...');

        $activeRequests = WishlistRequest::active()->get();
        $matchedCount = 0;

        foreach ($activeRequests as $request) {
            // Buscar ofertas que correspondem ao pedido
            $matchingOffers = RentalOffer::where('status', 'active')
                ->where('city', $request->city)
                ->where('state', $request->state)
                ->where('start_date', '<=', $request->desired_end_date)
                ->where('end_date', '>=', $request->desired_start_date)
                ->where('number_of_people', '>=', $request->number_of_people)
                ->when($request->max_price, function($query) use ($request) {
                    return $query->where('price', '<=', $request->max_price);
                })
                ->get();

            foreach ($matchingOffers as $offer) {
                if ($request->matchesOffer($offer)) {
                    $request->markAsFulfilled($offer);
                    $matchedCount++;
                    $this->line("Wishlist atendida: {$request->title} -> {$offer->title}");
                    break; // Apenas uma oferta por pedido
                }
            }
        }

        $this->info("{$matchedCount} pedidos de wishlist atendidos.");
    }

    /**
     * Verificar autorizações de hospedagem expiradas
     */
    private function checkExpiredAuthorizations()
    {
        $this->info('Verificando autorizações expiradas...');

        $expiredAuthorizations = HospitalityAuthorization::where('status', 'approved')
            ->where('expires_at', '<=', now())
            ->get();

        $expiredCount = 0;

        foreach ($expiredAuthorizations as $authorization) {
            $authorization->update(['status' => 'expired']);
            $expiredCount++;
            $this->line("Autorização expirada: {$authorization->authorization_code}");
        }

        $this->info("{$expiredCount} autorizações marcadas como expiradas.");
    }
}
