<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CidadeCapital extends Model
{
    protected $table = 'cidades_capitais';

    protected $fillable = [
        'codigo_ibge',
        'nome',
        'uf',
        'is_capital',
    ];

    protected function casts(): array
    {
        return [
            'codigo_ibge' => 'integer',
            'is_capital' => 'boolean',
        ];
    }

    /**
     * Lista ordenada para o campo “Informe de ofertas disponíveis por cidade”.
     *
     * @return Collection<int, self>
     */
    public static function orderedForInforme(): Collection
    {
        return static::query()
            ->orderBy('uf')
            ->orderBy('nome')
            ->get(['id', 'codigo_ibge', 'nome', 'uf', 'is_capital']);
    }

    public static function labelForPromotionValue(string $value): string
    {
        $t = trim($value);
        if ($t !== '' && ctype_digit($t)) {
            $row = static::query()->where('codigo_ibge', (int) $t)->first(['nome', 'uf']);
            if ($row) {
                return $row->nome.'/'.$row->uf;
            }
        }

        return $t;
    }

    /**
     * Converte códigos IBGE (ou textos legados) em termos para busca em campos tipo “localização”.
     *
     * @param  list<string>  $values
     * @return list<string>
     */
    public static function locationTermsForPromotionValues(array $values): array
    {
        $terms = [];
        foreach ($values as $v) {
            $v = trim((string) $v);
            if ($v === '') {
                continue;
            }
            if (ctype_digit($v)) {
                $row = static::query()->where('codigo_ibge', (int) $v)->first(['nome', 'uf']);
                if ($row) {
                    $terms[] = $row->nome;
                    $terms[] = $row->nome.'/'.$row->uf;
                    $terms[] = $row->nome.' - '.$row->uf;
                }
            } else {
                $terms[] = $v;
            }
        }

        return array_values(array_unique(array_filter($terms)));
    }
}
