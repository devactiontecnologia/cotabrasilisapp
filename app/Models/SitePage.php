<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitePage extends Model
{
    use HasFactory;

    public const CATEGORY_PLATFORM = 'platform';

    public const CATEGORY_RESOURCES = 'resources';

    public const CATEGORY_CONTROL_PANEL = 'control_panel';

    public const CATEGORY_LEGAL = 'legal';

    protected $fillable = [
        'slug',
        'title',
        'body',
        'category',
        'sort_order',
    ];

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            self::CATEGORY_PLATFORM => 'Plataforma',
            self::CATEGORY_RESOURCES => 'Recursos',
            self::CATEGORY_CONTROL_PANEL => 'Painel de controle',
            self::CATEGORY_LEGAL => 'Legal',
            default => $category,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            self::CATEGORY_PLATFORM => 'Plataforma',
            self::CATEGORY_RESOURCES => 'Recursos',
            self::CATEGORY_CONTROL_PANEL => 'Painel de controle',
            self::CATEGORY_LEGAL => 'Legal',
        ];
    }

    /**
     * Ícone Bootstrap Icons para o botão no painel (Informações do site).
     */
    public static function adminIconForSlug(string $slug): string
    {
        return match ($slug) {
            'historia' => 'bi-book',
            'como-funciona' => 'bi-lightbulb',
            'fracionamento' => 'bi-grid-3x3-gap',
            'destacar-oferta' => 'bi-star-fill',
            'alerta-publicacao' => 'bi-bell',
            'leilao' => 'bi-hammer',
            'sofamais' => 'bi-house-heart',
            'superdesconto' => 'bi-tag-fill',
            'megaoferta' => 'bi-lightning-charge-fill',
            'oferta-unica' => 'bi-gem',
            'troca-simples' => 'bi-arrow-left-right',
            'troca-justa' => 'bi-scales',
            'torei-na-vespera' => 'bi-moon-stars-fill',
            'torei-no-dia' => 'bi-sun-fill',
            'painel-de-controle' => 'bi-speedometer2',
            'cadastrar-nova-cota' => 'bi-plus-circle',
            'aluguel' => 'bi-key-fill',
            'troca' => 'bi-arrow-left-right',
            'compra' => 'bi-cart3',
            'venda' => 'bi-currency-dollar',
            'favoritos' => 'bi-heart-fill',
            'desejados' => 'bi-star-fill',
            'bora-la-cota-brasilis' => 'bi-gift-fill',
            'conteudo-educativo' => 'bi-mortarboard-fill',
            'termo-de-autorizacao' => 'bi-file-earmark-medical-fill',
            'meu-perfil' => 'bi-person-circle',
            'termos-uso' => 'bi-file-earmark-text',
            'termos-autorizacao' => 'bi-file-earmark-check',
            'politicas' => 'bi-journal-text',
            'cookies' => 'bi-cookie',
            'lgpd' => 'bi-shield-lock',
            default => 'bi-file-earmark-richtext',
        };
    }
}
