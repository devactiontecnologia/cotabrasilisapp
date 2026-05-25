<?php

use App\Models\SitePage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const OLD_SLUGS = [
        'painel-visao-geral',
        'painel-minhas-cotas',
        'painel-transacoes',
        'painel-favoritos-desejados',
    ];

    public function up(): void
    {
        SitePage::whereIn('slug', self::OLD_SLUGS)->delete();

        $placeholder = '<p class="lead text-muted">Conteúdo em elaboração. Use o painel administrativo em <strong>Informações do site</strong> para editar este texto.</p>';

        $rows = [
            ['slug' => 'painel-de-controle', 'title' => 'Painel de controle', 'sort_order' => 10],
            ['slug' => 'cadastrar-nova-cota', 'title' => 'Cadastrar nova cota', 'sort_order' => 20],
            ['slug' => 'aluguel', 'title' => 'Aluguel', 'sort_order' => 30],
            ['slug' => 'troca', 'title' => 'Troca', 'sort_order' => 40],
            ['slug' => 'compra', 'title' => 'Compra', 'sort_order' => 50],
            ['slug' => 'venda', 'title' => 'Venda', 'sort_order' => 60],
            ['slug' => 'favoritos', 'title' => 'Favoritos', 'sort_order' => 70],
            ['slug' => 'desejados', 'title' => 'Desejados', 'sort_order' => 80],
            ['slug' => 'bora-la-cota-brasilis', 'title' => 'Bora lá! Cota Brasilis', 'sort_order' => 90],
            ['slug' => 'conteudo-educativo', 'title' => 'Conteúdo educativo', 'sort_order' => 100],
            ['slug' => 'termo-de-autorizacao', 'title' => 'Termo de autorização', 'sort_order' => 110],
            ['slug' => 'meu-perfil', 'title' => 'Meu perfil', 'sort_order' => 120],
        ];

        foreach ($rows as $row) {
            SitePage::firstOrCreate(
                ['slug' => $row['slug']],
                [
                    'title' => $row['title'],
                    'category' => SitePage::CATEGORY_CONTROL_PANEL,
                    'sort_order' => $row['sort_order'],
                    'body' => $placeholder,
                ]
            );
        }
    }

    public function down(): void
    {
        $newSlugs = [
            'painel-de-controle',
            'cadastrar-nova-cota',
            'aluguel',
            'troca',
            'compra',
            'venda',
            'favoritos',
            'desejados',
            'bora-la-cota-brasilis',
            'conteudo-educativo',
            'termo-de-autorizacao',
            'meu-perfil',
        ];

        SitePage::whereIn('slug', $newSlugs)->delete();

        $placeholder = '<p class="lead text-muted">Conteúdo em elaboração. Use o painel administrativo em <strong>Informações do site</strong> para editar este texto.</p>';

        $legacy = [
            ['slug' => 'painel-visao-geral', 'title' => 'Visão geral do painel', 'sort_order' => 10],
            ['slug' => 'painel-minhas-cotas', 'title' => 'Minhas cotas', 'sort_order' => 20],
            ['slug' => 'painel-transacoes', 'title' => 'Reservas e transações', 'sort_order' => 30],
            ['slug' => 'painel-favoritos-desejados', 'title' => 'Favoritos e desejados', 'sort_order' => 40],
        ];

        foreach ($legacy as $row) {
            SitePage::firstOrCreate(
                ['slug' => $row['slug']],
                [
                    'title' => $row['title'],
                    'category' => SitePage::CATEGORY_CONTROL_PANEL,
                    'sort_order' => $row['sort_order'],
                    'body' => $placeholder,
                ]
            );
        }
    }
};
