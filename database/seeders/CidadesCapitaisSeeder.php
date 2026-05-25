<?php

namespace Database\Seeders;

use App\Models\CidadeCapital;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CidadesCapitaisSeeder extends Seeder
{
    /**
     * Códigos IBGE das capitais estaduais e do Distrito Federal.
     *
     * @var list<int>
     */
    private const CAPITAIS_IBGE = [
        1100205, 1200401, 1302603, 1400100, 1501402, 1600303, 1721000, 2111300, 2211001,
        2304400, 2408102, 2507507, 2611606, 2704302, 2800308, 2927408, 3106200, 3205309,
        3304557, 3550308, 4106902, 4205407, 4314902, 5002704, 5103403, 5208707, 5300108,
    ];

    public function run(): void
    {
        $url = 'https://servicodados.ibge.gov.br/api/v1/localidades/municipios';

        try {
            $response = Http::timeout(180)->acceptJson()->get($url);
        } catch (\Throwable $e) {
            Log::error('CidadesCapitaisSeeder: falha HTTP IBGE: '.$e->getMessage());

            return;
        }

        if (! $response->successful()) {
            Log::error('CidadesCapitaisSeeder: resposta IBGE não OK: '.$response->status());

            return;
        }

        $list = $response->json();
        if (! is_array($list) || $list === []) {
            Log::error('CidadesCapitaisSeeder: JSON IBGE vazio ou inválido.');

            return;
        }

        $capitals = array_flip(self::CAPITAIS_IBGE);
        $now = now();
        $batch = [];

        foreach ($list as $m) {
            if (! is_array($m)) {
                continue;
            }
            $codigo = isset($m['id']) ? (int) $m['id'] : 0;
            $nome = isset($m['nome']) ? trim((string) $m['nome']) : '';
            $uf = null;
            if (isset($m['microrregiao']['mesorregiao']['UF']['sigla'])) {
                $uf = strtoupper(trim((string) $m['microrregiao']['mesorregiao']['UF']['sigla']));
            } elseif (isset($m['regiao-imediata']['regiao-intermediaria']['UF']['sigla'])) {
                $uf = strtoupper(trim((string) $m['regiao-imediata']['regiao-intermediaria']['UF']['sigla']));
            }
            if ($codigo <= 0 || $nome === '' || $uf === null || strlen($uf) !== 2) {
                continue;
            }

            $batch[] = [
                'codigo_ibge' => $codigo,
                'nome' => $nome,
                'uf' => $uf,
                'is_capital' => isset($capitals[$codigo]),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= 400) {
                DB::table('cidades_capitais')->upsert(
                    $batch,
                    ['codigo_ibge'],
                    ['nome', 'uf', 'is_capital', 'updated_at']
                );
                $batch = [];
            }
        }

        if ($batch !== []) {
            DB::table('cidades_capitais')->upsert(
                $batch,
                ['codigo_ibge'],
                ['nome', 'uf', 'is_capital', 'updated_at']
            );
        }

        $this->command?->info('cidades_capitais: '.CidadeCapital::query()->count().' municípios.');
    }
}
