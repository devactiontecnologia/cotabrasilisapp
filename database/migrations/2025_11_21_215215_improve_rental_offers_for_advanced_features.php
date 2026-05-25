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
        Schema::table('rental_offers', function (Blueprint $table) {
            // Campos para período flexível
            if (!Schema::hasColumn('rental_offers', 'period_type')) {
                $table->enum('period_type', ['exact', 'flexible'])->default('exact')->after('end_date');
            }
            if (!Schema::hasColumn('rental_offers', 'flexible_weeks')) {
                $table->json('flexible_weeks')->nullable()->after('period_type'); // Semanas disponíveis para período flexível
            }
            
            // Campos para faixa de preço
            if (!Schema::hasColumn('rental_offers', 'price_min')) {
                $table->decimal('price_min', 10, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('rental_offers', 'price_max')) {
                $table->decimal('price_max', 10, 2)->nullable()->after('price_min');
            }
            
            // Campos para leilão melhorado
            if (!Schema::hasColumn('rental_offers', 'auction_start_time')) {
                $table->timestamp('auction_start_time')->nullable()->after('auction_end_time');
            }
            if (!Schema::hasColumn('rental_offers', 'auction_duration_minutes')) {
                $table->integer('auction_duration_minutes')->nullable()->after('auction_start_time'); // 20 min a 24h (saltos de 30 min)
            }
            if (!Schema::hasColumn('rental_offers', 'auction_day')) {
                $table->date('auction_day')->nullable()->after('auction_duration_minutes');
            }
            if (!Schema::hasColumn('rental_offers', 'auction_start_hour')) {
                $table->time('auction_start_hour')->nullable()->after('auction_day');
            }
            
            // Campos para múltiplas cotas
            if (!Schema::hasColumn('rental_offers', 'is_batch_offer')) {
                $table->boolean('is_batch_offer')->default(false)->after('observations');
            }
            if (!Schema::hasColumn('rental_offers', 'batch_quota_ids')) {
                $table->json('batch_quota_ids')->nullable()->after('is_batch_offer'); // IDs das cotas em lote
            }
            
            // Campos para busca melhorada
            if (!Schema::hasColumn('rental_offers', 'accepts_exchange')) {
                $table->boolean('accepts_exchange')->default(false)->after('is_batch_offer');
            }
            if (!Schema::hasColumn('rental_offers', 'accepts_sale')) {
                $table->boolean('accepts_sale')->default(false)->after('accepts_exchange');
            }
            if (!Schema::hasColumn('rental_offers', 'accepts_diaria_exchange')) {
                $table->boolean('accepts_diaria_exchange')->default(false)->after('accepts_sale'); // Troca por diárias
            }
            
            // Campos para regras automáticas
            if (!Schema::hasColumn('rental_offers', 'days_until_start')) {
                $table->integer('days_until_start')->nullable()->after('accepts_diaria_exchange'); // Dias até início
            }
            if (!Schema::hasColumn('rental_offers', 'auto_discount_applied')) {
                $table->boolean('auto_discount_applied')->default(false)->after('days_until_start');
            }
            if (!Schema::hasColumn('rental_offers', 'auto_discount_percentage')) {
                $table->decimal('auto_discount_percentage', 5, 2)->nullable()->after('auto_discount_applied'); // 20% após 14 dias
            }
            if (!Schema::hasColumn('rental_offers', 'auto_discount_applied_at')) {
                $table->timestamp('auto_discount_applied_at')->nullable()->after('auto_discount_percentage');
            }
            
            // Campos para métricas
            if (!Schema::hasColumn('rental_offers', 'rented_at')) {
                $table->timestamp('rented_at')->nullable()->after('negotiated_at');
            }
            if (!Schema::hasColumn('rental_offers', 'moved_to_metrics')) {
                $table->boolean('moved_to_metrics')->default(false)->after('rented_at');
            }
            if (!Schema::hasColumn('rental_offers', 'metrics_type')) {
                $table->enum('metrics_type', ['rented', 'exchanged', 'sold'])->nullable()->after('moved_to_metrics');
            }
        });
        
        // Adicionar campos à tabela quotas para melhorar busca
        Schema::table('quotas', function (Blueprint $table) {
            if (!Schema::hasColumn('quotas', 'accepts_exchange')) {
                $table->boolean('accepts_exchange')->default(false)->after('is_exchange');
            }
            if (!Schema::hasColumn('quotas', 'accepts_sale')) {
                $table->boolean('accepts_sale')->default(false)->after('accepts_exchange');
            }
            if (!Schema::hasColumn('quotas', 'accepts_diaria_exchange')) {
                $table->boolean('accepts_diaria_exchange')->default(false)->after('accepts_sale');
            }
            if (!Schema::hasColumn('quotas', 'price_min')) {
                $table->decimal('price_min', 10, 2)->nullable()->after('rental_price');
            }
            if (!Schema::hasColumn('quotas', 'price_max')) {
                $table->decimal('price_max', 10, 2)->nullable()->after('price_min');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_offers', function (Blueprint $table) {
            $table->dropColumn([
                'period_type',
                'flexible_weeks',
                'price_min',
                'price_max',
                'auction_start_time',
                'auction_duration_minutes',
                'auction_day',
                'auction_start_hour',
                'is_batch_offer',
                'batch_quota_ids',
                'accepts_exchange',
                'accepts_sale',
                'accepts_diaria_exchange',
                'days_until_start',
                'auto_discount_applied',
                'auto_discount_percentage',
                'auto_discount_applied_at',
                'rented_at',
                'moved_to_metrics',
                'metrics_type',
            ]);
        });
        
        Schema::table('quotas', function (Blueprint $table) {
            $table->dropColumn([
                'accepts_exchange',
                'accepts_sale',
                'accepts_diaria_exchange',
                'price_min',
                'price_max',
            ]);
        });
    }
};
