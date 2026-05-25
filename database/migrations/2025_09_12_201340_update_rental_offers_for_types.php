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
            // Verificar se as colunas não existem antes de adicionar
            if (!Schema::hasColumn('rental_offers', 'offer_type')) {
                $table->enum('offer_type', ['rent', 'exchange', 'sell', 'buy'])->default('rent')->after('title'); // Tipo da oferta
            }
            if (!Schema::hasColumn('rental_offers', 'is_flexible_period')) {
                $table->boolean('is_flexible_period')->default(false)->after('end_date'); // Período flexível ou exato
            }
            if (!Schema::hasColumn('rental_offers', 'flexible_dates')) {
                $table->json('flexible_dates')->nullable()->after('is_flexible_period'); // Datas flexíveis
            }
            if (!Schema::hasColumn('rental_offers', 'min_days')) {
                $table->integer('min_days')->default(2)->after('flexible_dates'); // Mínimo de dias
            }
            if (!Schema::hasColumn('rental_offers', 'max_days')) {
                $table->integer('max_days')->default(7)->after('min_days'); // Máximo de dias
            }
            
            // Campos para venda
            if (!Schema::hasColumn('rental_offers', 'sale_minimum_price')) {
                $table->decimal('sale_minimum_price', 10, 2)->nullable()->after('max_days'); // Preço mínimo para venda
            }
            if (!Schema::hasColumn('rental_offers', 'acceptable_price')) {
                $table->decimal('acceptable_price', 10, 2)->nullable()->after('sale_minimum_price'); // Preço aceitável
            }
            if (!Schema::hasColumn('rental_offers', 'desired_price')) {
                $table->decimal('desired_price', 10, 2)->nullable()->after('acceptable_price'); // Preço desejado
            }
            if (!Schema::hasColumn('rental_offers', 'auction_fee_percentage')) {
                $table->decimal('auction_fee_percentage', 5, 2)->default(10.00)->after('desired_price'); // Taxa de leilão (10%)
            }
            
            // Campos para troca
            if (!Schema::hasColumn('rental_offers', 'exchange_options')) {
                $table->json('exchange_options')->nullable()->after('auction_fee_percentage'); // Opções de troca
            }
            if (!Schema::hasColumn('rental_offers', 'max_exchange_options')) {
                $table->integer('max_exchange_options')->default(3)->after('exchange_options'); // Máximo de opções
            }
            if (!Schema::hasColumn('rental_offers', 'exchange_valid_until')) {
                $table->timestamp('exchange_valid_until')->nullable()->after('max_exchange_options'); // Validade da troca
            }
            
            // Campos para compra
            if (!Schema::hasColumn('rental_offers', 'delegate_to_manager')) {
                $table->boolean('delegate_to_manager')->default(false)->after('exchange_valid_until'); // Delegar ao gestor
            }
            if (!Schema::hasColumn('rental_offers', 'delegation_fee')) {
                $table->decimal('delegation_fee', 10, 2)->nullable()->after('delegate_to_manager'); // Taxa de delegação
            }
            
            // Campos para busca avançada
            if (!Schema::hasColumn('rental_offers', 'search_criteria')) {
                $table->json('search_criteria')->nullable()->after('delegation_fee'); // Critérios de busca
            }
            if (!Schema::hasColumn('rental_offers', 'include_partial_matches')) {
                $table->boolean('include_partial_matches')->default(true)->after('search_criteria'); // Incluir correspondências parciais
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
                'offer_type',
                'is_flexible_period',
                'flexible_dates',
                'min_days',
                'max_days',
                'sale_minimum_price',
                'acceptable_price',
                'desired_price',
                'auction_fee_percentage',
                'exchange_options',
                'max_exchange_options',
                'exchange_valid_until',
                'delegate_to_manager',
                'delegation_fee',
                'search_criteria',
                'include_partial_matches'
            ]);
        });
    }
};
