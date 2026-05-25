<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotas', function (Blueprint $table) {
            // Adicionar status 'negotiating' ao enum existente
            // Nota: MySQL não permite alterar ENUM diretamente, então vamos usar uma abordagem diferente
            if (!Schema::hasColumn('quotas', 'negotiation_deadline')) {
                $table->timestamp('negotiation_deadline')->nullable()->after('status');
            }
            if (!Schema::hasColumn('quotas', 'current_transaction_id')) {
                $table->unsignedBigInteger('current_transaction_id')->nullable()->after('negotiation_deadline');
            }
        });

        // Adicionar foreign key separadamente para evitar problemas
        Schema::table('quotas', function (Blueprint $table) {
            if (Schema::hasColumn('quotas', 'current_transaction_id')) {
                try {
                    $table->foreign('current_transaction_id')->references('id')->on('quota_transactions')->onDelete('set null');
                } catch (\Exception $e) {
                    // Se a foreign key já existir, ignorar
                }
            }
        });

        // Atualizar o enum de status via SQL direto (MySQL)
        // Verificar se é MySQL antes de executar
        try {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE quotas MODIFY COLUMN status ENUM('available', 'negotiating', 'rented', 'exchanged', 'cancelled') DEFAULT 'available'");
            }
        } catch (\Exception $e) {
            // Se falhar, continuar - pode ser que o enum já tenha sido atualizado
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
                    // Ignorar se não existir
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
