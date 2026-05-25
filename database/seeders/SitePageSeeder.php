<?php

namespace Database\Seeders;

use App\Models\SitePage;
use Illuminate\Database\Seeder;

class SitePageSeeder extends Seeder
{
    public function run(): void
    {
        $placeholder = '<p class="lead text-muted">Conteúdo em elaboração. Use o painel administrativo em <strong>Informações do site</strong> para editar este texto.</p>';

        $rows = [
            ['slug' => 'historia', 'title' => 'História', 'category' => SitePage::CATEGORY_PLATFORM, 'sort_order' => 10],
            ['slug' => 'como-funciona', 'title' => 'Como funciona', 'category' => SitePage::CATEGORY_PLATFORM, 'sort_order' => 20],

            ['slug' => 'fracionamento', 'title' => 'Fracionamento', 'category' => SitePage::CATEGORY_RESOURCES, 'sort_order' => 10],
            ['slug' => 'destacar-oferta', 'title' => 'Destacar oferta', 'category' => SitePage::CATEGORY_RESOURCES, 'sort_order' => 20],
            ['slug' => 'alerta-publicacao', 'title' => 'Alerta de publicação', 'category' => SitePage::CATEGORY_RESOURCES, 'sort_order' => 30],
            ['slug' => 'leilao', 'title' => 'Leilão', 'category' => SitePage::CATEGORY_RESOURCES, 'sort_order' => 40],
            ['slug' => 'sofamais', 'title' => 'SofáMais', 'category' => SitePage::CATEGORY_RESOURCES, 'sort_order' => 50],
            ['slug' => 'superdesconto', 'title' => 'SuperDesconto', 'category' => SitePage::CATEGORY_RESOURCES, 'sort_order' => 60],
            ['slug' => 'megaoferta', 'title' => 'MegaOferta', 'category' => SitePage::CATEGORY_RESOURCES, 'sort_order' => 70],
            ['slug' => 'oferta-unica', 'title' => 'OfertaÚnica', 'category' => SitePage::CATEGORY_RESOURCES, 'sort_order' => 80],
            ['slug' => 'troca-simples', 'title' => 'Troca Simples', 'category' => SitePage::CATEGORY_RESOURCES, 'sort_order' => 90],
            ['slug' => 'troca-justa', 'title' => 'Troca Justa', 'category' => SitePage::CATEGORY_RESOURCES, 'sort_order' => 100],
            ['slug' => 'torei-na-vespera', 'title' => 'Torei na Véspera', 'category' => SitePage::CATEGORY_RESOURCES, 'sort_order' => 110],
            ['slug' => 'torei-no-dia', 'title' => 'Torei no Dia', 'category' => SitePage::CATEGORY_RESOURCES, 'sort_order' => 120],

            ['slug' => 'painel-de-controle', 'title' => 'Painel de controle', 'category' => SitePage::CATEGORY_CONTROL_PANEL, 'sort_order' => 10],
            ['slug' => 'cadastrar-nova-cota', 'title' => 'Cadastrar nova cota', 'category' => SitePage::CATEGORY_CONTROL_PANEL, 'sort_order' => 20],
            ['slug' => 'aluguel', 'title' => 'Aluguel', 'category' => SitePage::CATEGORY_CONTROL_PANEL, 'sort_order' => 30],
            ['slug' => 'troca', 'title' => 'Troca', 'category' => SitePage::CATEGORY_CONTROL_PANEL, 'sort_order' => 40],
            ['slug' => 'compra', 'title' => 'Compra', 'category' => SitePage::CATEGORY_CONTROL_PANEL, 'sort_order' => 50],
            ['slug' => 'venda', 'title' => 'Venda', 'category' => SitePage::CATEGORY_CONTROL_PANEL, 'sort_order' => 60],
            ['slug' => 'favoritos', 'title' => 'Favoritos', 'category' => SitePage::CATEGORY_CONTROL_PANEL, 'sort_order' => 70],
            ['slug' => 'desejados', 'title' => 'Desejados', 'category' => SitePage::CATEGORY_CONTROL_PANEL, 'sort_order' => 80],
            ['slug' => 'bora-la-cota-brasilis', 'title' => 'Bora lá! Cota Brasilis', 'category' => SitePage::CATEGORY_CONTROL_PANEL, 'sort_order' => 90],
            ['slug' => 'conteudo-educativo', 'title' => 'Conteúdo educativo', 'category' => SitePage::CATEGORY_CONTROL_PANEL, 'sort_order' => 100],
            ['slug' => 'termo-de-autorizacao', 'title' => 'Termo de autorização', 'category' => SitePage::CATEGORY_CONTROL_PANEL, 'sort_order' => 110],
            ['slug' => 'meu-perfil', 'title' => 'Meu perfil', 'category' => SitePage::CATEGORY_CONTROL_PANEL, 'sort_order' => 120],

            ['slug' => 'termos-uso', 'title' => 'Termos de Uso', 'category' => SitePage::CATEGORY_LEGAL, 'sort_order' => 10],
            ['slug' => 'termos-autorizacao', 'title' => 'Termos de Autorização', 'category' => SitePage::CATEGORY_LEGAL, 'sort_order' => 20],
            ['slug' => 'politicas', 'title' => 'Políticas', 'category' => SitePage::CATEGORY_LEGAL, 'sort_order' => 30],
            ['slug' => 'cookies', 'title' => 'Cookies', 'category' => SitePage::CATEGORY_LEGAL, 'sort_order' => 40],
            ['slug' => 'lgpd', 'title' => 'LGPD', 'category' => SitePage::CATEGORY_LEGAL, 'sort_order' => 50],
        ];

        foreach ($rows as $row) {
            SitePage::firstOrCreate(
                ['slug' => $row['slug']],
                [
                    'title' => $row['title'],
                    'category' => $row['category'],
                    'sort_order' => $row['sort_order'],
                    'body' => $placeholder,
                ]
            );
        }
    }
}
