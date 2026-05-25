<?php

use App\Models\SitePage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_pages')) {
            return;
        }

        SitePage::where('slug', 'perguntas-frequentes')->delete();
    }

    public function down(): void
    {
        // Recria apenas o registro vazio; o conteúdo não é restaurado.
        SitePage::firstOrCreate(
            ['slug' => 'perguntas-frequentes'],
            [
                'title' => 'Perguntas Frequentes e Respostas',
                'category' => SitePage::CATEGORY_PLATFORM,
                'sort_order' => 30,
                'body' => '<p class="lead text-muted">Conteúdo em elaboração.</p>',
            ]
        );
    }
};
