<?php

namespace App\Console\Commands;

use App\Services\WishlistMatchingService;
use Illuminate\Console\Command;

class ProcessWishlistMatches extends Command
{
    protected $signature = 'wishlist:process-matches';

    protected $description = 'Processa desejados: alerta donos sem oferta publicada e avisa interessados quando houver match';

    public function handle(WishlistMatchingService $matching): int
    {
        $this->info('Processando buscas nos Desejados...');
        $count = $matching->processPendingSearches();
        $this->info("{$count} busca(s) processada(s).");

        return Command::SUCCESS;
    }
}
