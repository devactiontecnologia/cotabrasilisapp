<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotas', function (Blueprint $table) {
            // Verificar se as colunas não existem antes de adicionar
            if (!Schema::hasColumn('quotas', 'weeks')) {
                $table->integer('weeks')->default(1)->after('hotel_name'); // 1-4 semanas
            }
            if (!Schema::hasColumn('quotas', 'number_of_rooms')) {
                $table->integer('number_of_rooms')->default(1)->after('weeks'); // Número de quartos
            }
            if (!Schema::hasColumn('quotas', 'seasonality')) {
                $table->enum('seasonality', ['low', 'medium', 'high', 'peak'])->default('medium')->after('number_of_rooms'); // Sazonalidade
            }
            if (!Schema::hasColumn('quotas', 'payment_status')) {
                $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid')->after('seasonality'); // Status de pagamento
            }
            if (!Schema::hasColumn('quotas', 'is_owner')) {
                $table->boolean('is_owner')->default(true)->after('payment_status'); // Se é proprietário
            }
            if (!Schema::hasColumn('quotas', 'authorizations')) {
                $table->json('authorizations')->nullable()->after('is_owner'); // Autorizações anexadas
            }
            if (!Schema::hasColumn('quotas', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('authorizations'); // Se está publicada
            }
            if (!Schema::hasColumn('quotas', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('is_published'); // Data de publicação
            }
            if (!Schema::hasColumn('quotas', 'quota_status')) {
                $table->enum('quota_status', ['active', 'inactive', 'suspended', 'transferred'])->default('active')->after('published_at'); // Status da cota
            }
            if (!Schema::hasColumn('quotas', 'transferred_at')) {
                $table->timestamp('transferred_at')->nullable()->after('quota_status'); // Data de transferência
            }
            if (!Schema::hasColumn('quotas', 'previous_owner_id')) {
                $table->foreignId('previous_owner_id')->nullable()->constrained('users')->onDelete('set null')->after('transferred_at'); // Proprietário anterior
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotas', function (Blueprint $table) {
            $table->dropColumn([
                'weeks',
                'number_of_rooms',
                'seasonality',
                'payment_status',
                'is_owner',
                'authorizations',
                'is_published',
                'published_at',
                'quota_status',
                'transferred_at',
                'previous_owner_id'
            ]);
        });
    }
};
