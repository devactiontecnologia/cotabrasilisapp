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
        Schema::table('wishlist_requests', function (Blueprint $table) {
            // Campos para filtros específicos
            if (!Schema::hasColumn('wishlist_requests', 'specific_days')) {
                $table->json('specific_days')->nullable()->after('number_of_rooms'); // Dias específicos: 2,3,4,5,7
            }
            if (!Schema::hasColumn('wishlist_requests', 'desired_month')) {
                $table->integer('desired_month')->nullable()->after('desired_end_date'); // Mês desejado (1-12)
            }
            if (!Schema::hasColumn('wishlist_requests', 'desired_year')) {
                $table->integer('desired_year')->nullable()->after('desired_month');
            }
            if (!Schema::hasColumn('wishlist_requests', 'desired_hotel')) {
                $table->string('desired_hotel')->nullable()->after('city');
            }
            if (!Schema::hasColumn('wishlist_requests', 'price_range_min')) {
                $table->decimal('price_range_min', 10, 2)->nullable()->after('max_price');
            }
            if (!Schema::hasColumn('wishlist_requests', 'price_range_max')) {
                $table->decimal('price_range_max', 10, 2)->nullable()->after('price_range_min');
            }
            
            // Campos para observações e alertas
            if (!Schema::hasColumn('wishlist_requests', 'demand_observations')) {
                $table->text('demand_observations')->nullable()->after('description'); // Campo de observação para explicar demanda
            }
            if (!Schema::hasColumn('wishlist_requests', 'alert_sent_to_owner')) {
                $table->boolean('alert_sent_to_owner')->default(false)->after('admin_notes');
            }
            if (!Schema::hasColumn('wishlist_requests', 'alert_sent_to_admin')) {
                $table->boolean('alert_sent_to_admin')->default(false)->after('alert_sent_to_owner');
            }
            if (!Schema::hasColumn('wishlist_requests', 'alert_sent_at')) {
                $table->timestamp('alert_sent_at')->nullable()->after('alert_sent_to_admin');
            }
            if (!Schema::hasColumn('wishlist_requests', 'matched_offer_id')) {
                $table->foreignId('matched_offer_id')->nullable()->constrained('rental_offers')->after('fulfilled_by_offer_id');
            }
            if (!Schema::hasColumn('wishlist_requests', 'matched_at')) {
                $table->timestamp('matched_at')->nullable()->after('matched_offer_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wishlist_requests', function (Blueprint $table) {
            $table->dropForeign(['matched_offer_id']);
            $table->dropColumn([
                'specific_days',
                'desired_month',
                'desired_year',
                'desired_hotel',
                'price_range_min',
                'price_range_max',
                'demand_observations',
                'alert_sent_to_owner',
                'alert_sent_to_admin',
                'alert_sent_at',
                'matched_offer_id',
                'matched_at',
            ]);
        });
    }
};
