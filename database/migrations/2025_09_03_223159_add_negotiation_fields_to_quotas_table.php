<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $database = Schema::getConnection()->getDatabaseName();

        $row = DB::selectOne(
            'SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?
             AND CONSTRAINT_TYPE = ? LIMIT 1',
            [$database, $table, $constraintName, 'FOREIGN KEY']
        );

        return $row !== null;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotas', function (Blueprint $table) {
            if (! Schema::hasColumn('quotas', 'negotiation_deadline')) {
                $table->timestamp('negotiation_deadline')->nullable()->after('status');
            }
            if (! Schema::hasColumn('quotas', 'current_transaction_id')) {
                $table->unsignedBigInteger('current_transaction_id')->nullable()->after('negotiation_deadline');
            }
        });

        if (
            Schema::hasColumn('quotas', 'current_transaction_id')
            && ! $this->foreignKeyExists('quotas', 'quotas_current_transaction_id_foreign')
        ) {
            Schema::table('quotas', function (Blueprint $table) {
                $table->foreign('current_transaction_id')->references('id')->on('quota_transactions')->onDelete('set null');
            });
        }

        // Atualizar o enum de status via SQL direto (MySQL)
        // Verificar se Ã© MySQL antes de executar
        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE quotas MODIFY COLUMN status ENUM('available', 'negotiating', 'rented', 'exchanged', 'cancelled') DEFAULT 'available'");
            }
        } catch (\Exception $e) {
            // Se falhar, continuar - pode ser que o enum jÃ¡ tenha sido atualizado
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotas', function (Blueprint $table) {
            if (Schema::hasColumn('quotas', 'current_transaction_id')) {
                try {
                    $table->dropForeign(['current_transaction_id']);
                } catch (\Exception $e) {
                    // Ignorar se nÃ£o existir
                }
                $table->dropColumn('current_transaction_id');
            }
            if (Schema::hasColumn('quotas', 'negotiation_deadline')) {
                $table->dropColumn('negotiation_deadline');
            }
        });

        // Reverter o enum
        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE quotas MODIFY COLUMN status ENUM('available', 'rented', 'exchanged', 'cancelled') DEFAULT 'available'");
            }
        } catch (\Exception $e) {
            // Ignorar erro
        }
    }
};
