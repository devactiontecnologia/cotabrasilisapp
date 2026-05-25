<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

class PlatformDataExchangeService
{
    /** Tabelas de sistema / fila que não entram no backup de negócio. */
    private const EXCLUDED_TABLES = [
        'migrations',
        'failed_jobs',
        'job_batches',
        'jobs',
        'cache',
        'cache_locks',
        'password_reset_tokens',
        'sessions',
    ];

    /** Nunca importar (evita quebrar histórico de migrações). */
    private const NEVER_IMPORT_TABLES = [
        'migrations',
    ];

    public function getExportableTables(): array
    {
        $all = $this->listBaseTables();
        $filtered = array_values(array_filter($all, function (string $name) {
            return !in_array($name, self::EXCLUDED_TABLES, true);
        }));
        sort($filtered);

        return $filtered;
    }

    /**
     * Gera ZIP em disco com um CSV UTF-8 (BOM) por tabela.
     *
     * @return string caminho absoluto do arquivo ZIP
     */
    public function buildExportZip(): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('A extensão PHP zip (ZipArchive) não está habilitada.');
        }

        $tables = $this->getExportableTables();
        $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cotabrasilis_export_' . uniqid('', true) . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Não foi possível criar o arquivo ZIP.');
        }

        $meta = [
            'generated_at' => now()->toIso8601String(),
            'app' => config('app.name'),
            'tables' => $tables,
        ];
        $zip->addFromString('manifest.json', json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cb_csv_' . uniqid('', true);
        File::ensureDirectoryExists($tempDir);

        try {
            foreach ($tables as $table) {
                $csvPath = $tempDir . DIRECTORY_SEPARATOR . $table . '.csv';
                $this->exportTableToCsv($table, $csvPath);
                $zip->addFile($csvPath, 'csv/' . $table . '.csv');
            }
            $zip->close();
        } finally {
            File::deleteDirectory($tempDir);
        }

        return $zipPath;
    }

    /**
     * Importa a partir de um ZIP contendo csv/*.csv (mesmo layout da exportação).
     */
    public function importFromZip(string $zipAbsolutePath): array
    {
        if (!is_readable($zipAbsolutePath)) {
            throw new InvalidArgumentException('Arquivo ZIP inválido ou ilegível.');
        }

        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('A extensão PHP zip (ZipArchive) não está habilitada.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipAbsolutePath) !== true) {
            throw new InvalidArgumentException('Não foi possível abrir o ZIP.');
        }

        $extractDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cb_import_' . uniqid('', true);
        File::ensureDirectoryExists($extractDir);

        try {
            $zip->extractTo($extractDir);
            $zip->close();

            $csvDir = $extractDir . DIRECTORY_SEPARATOR . 'csv';
            if (!is_dir($csvDir)) {
                // Aceita CSVs na raiz do ZIP (fallback)
                $csvDir = $extractDir;
            }

            $files = glob($csvDir . DIRECTORY_SEPARATOR . '*.csv') ?: [];
            if ($files === []) {
                throw new InvalidArgumentException('Nenhum arquivo .csv encontrado no ZIP (esperado em pasta csv/).');
            }

            $imported = [];
            $driver = Schema::getConnection()->getDriverName();

            $this->withoutForeignKeyChecks($driver, function () use ($files, &$imported) {
                foreach ($this->getExportableTables() as $t) {
                    $this->truncateTable($t);
                }

                foreach ($files as $csvPath) {
                    $base = basename($csvPath, '.csv');
                    if (!preg_match('/^[a-z0-9_]+$/', $base)) {
                        continue;
                    }
                    if (in_array($base, self::NEVER_IMPORT_TABLES, true)) {
                        continue;
                    }
                    if (!Schema::hasTable($base)) {
                        continue;
                    }
                    if (in_array($base, self::EXCLUDED_TABLES, true)) {
                        continue;
                    }

                    $rows = $this->readCsvIntoRows($csvPath, $base);
                    foreach (array_chunk($rows, 300) as $chunk) {
                        if ($chunk !== []) {
                            DB::table($base)->insert($chunk);
                        }
                    }
                    $imported[] = $base;
                }
            });

            sort($imported);

            return [
                'tables_imported' => $imported,
                'count' => count($imported),
            ];
        } finally {
            File::deleteDirectory($extractDir);
        }
    }

    private function listBaseTables(): array
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'");

            return collect($rows)->pluck('name')->map(fn ($n) => (string) $n)->all();
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $db = $connection->getDatabaseName();
            $rows = DB::select(
                'SELECT TABLE_NAME AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ?',
                [$db, 'BASE TABLE']
            );

            return collect($rows)->pluck('n')->map(fn ($n) => (string) $n)->all();
        }

        // Fallback: nomes conhecidos do projeto
        return $this->fallbackTableList();
    }

    private function fallbackTableList(): array
    {
        return [
            'users', 'user_profiles', 'hotels', 'quotas', 'quota_transactions', 'digital_contracts',
            'notifications', 'kyc_validations', 'rental_offers', 'auctions', 'advanced_auctions',
            'auction_limits', 'wishlist_requests', 'hospitality_authorizations', 'admin_logs',
            'exchange_offers', 'sale_offers', 'purchase_requests', 'payment_transactions',
            'favorite_lists', 'favorite_list_items', 'wishlist_searches', 'educational_contents',
            'educational_videos', 'video_comments', 'video_views', 'success_fees',
            'personal_access_tokens', 'site_pages', 'faqs', 'bora_la_posts', 'user_wishlist_quotas',
            'platform_authorization_documents',
        ];
    }

    private function exportTableToCsv(string $table, string $csvPath): void
    {
        $handle = fopen($csvPath, 'wb');
        if ($handle === false) {
            throw new RuntimeException('Não foi possível gravar CSV temporário.');
        }

        fwrite($handle, "\xEF\xBB\xBF");

        $first = true;
        foreach (DB::table($table)->cursor() as $row) {
            $arr = (array) $row;
            if ($first) {
                $this->putCsvRow($handle, array_keys($arr));
                $first = false;
            }
            $this->putCsvRow($handle, array_map([$this, 'stringifyCell'], $arr));
        }

        if ($first) {
            $columns = Schema::getColumnListing($table);
            $this->putCsvRow($handle, $columns);
        }

        fclose($handle);
    }

    private function stringifyCell(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        if ($v === true) {
            return '1';
        }
        if ($v === false) {
            return '0';
        }
        if (is_array($v) || is_object($v)) {
            return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        return (string) $v;
    }

    private function putCsvRow($handle, array $row): void
    {
        if (PHP_VERSION_ID >= 80100) {
            fputcsv($handle, $row, ';', '"', '\\');
        } else {
            fputcsv($handle, $row, ';', '"');
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readCsvIntoRows(string $csvPath, string $table): array
    {
        $handle = fopen($csvPath, 'rb');
        if ($handle === false) {
            return [];
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = $this->getCsvLine($handle);
        if ($headers === false || $headers === [null] || $headers === []) {
            fclose($handle);

            return [];
        }
        $headers = array_map(fn ($h) => is_string($h) ? trim($h) : '', $headers);

        $validColumns = array_flip(Schema::getColumnListing($table));
        $headers = array_values(array_filter($headers, fn ($h) => $h !== '' && isset($validColumns[$h])));

        $rows = [];
        while (($data = $this->getCsvLine($handle)) !== false) {
            if ($data === [null] || $data === []) {
                continue;
            }
            $row = [];
            foreach ($headers as $i => $col) {
                $val = $data[$i] ?? null;
                $row[$col] = $this->normalizeImportedValue($table, $col, $val);
            }
            if ($row !== []) {
                $rows[] = $row;
            }
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeImportedValue(string $table, string $column, mixed $raw): mixed
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (!is_string($raw)) {
            return $raw;
        }

        try {
            $type = Schema::getColumnType($table, $column);
        } catch (\Throwable) {
            return $raw;
        }

        $t = strtolower((string) $type);
        if (str_contains($t, 'json')) {
            $decoded = json_decode($raw, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
        }

        return $raw;
    }

    /**
     * Esvazia a tabela (DELETE) para evitar erros de TRUNCATE com FKs no MySQL.
     */
    private function truncateTable(string $table): void
    {
        DB::table($table)->delete();
    }

    /**
     * @return array<int, string|null>|false
     */
    private function getCsvLine($handle): array|false
    {
        if (PHP_VERSION_ID >= 80100) {
            return fgetcsv($handle, 0, ';', '"', '\\');
        }

        return fgetcsv($handle, 0, ';', '"');
    }

    private function withoutForeignKeyChecks(string $driver, callable $callback): void
    {
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            try {
                $callback();
            } finally {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            try {
                $callback();
            } finally {
                DB::statement('PRAGMA foreign_keys = ON');
            }

            return;
        }

        $callback();
    }
}
