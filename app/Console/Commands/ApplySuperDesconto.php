<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RentalOffer;

class ApplySuperDesconto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:apply-super-desconto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aplica SuperDesconto automaticamente em ofertas ativas há 14 dias';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Aplicando SuperDesconto em ofertas elegíveis...');

        $offers = RentalOffer::eligibleForSuperDesconto()->get();
        $appliedCount = 0;

        foreach ($offers as $offer) {
            if ($offer->applySuperDesconto()) {
                $appliedCount++;
                $this->line("SuperDesconto aplicado na oferta: {$offer->title} (ID: {$offer->id})");
            }
        }

        $this->info("SuperDesconto aplicado em {$appliedCount} ofertas.");
        
        return Command::SUCCESS;
    }
}
