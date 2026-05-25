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
            if (!Schema::hasColumn('rental_offers', 'super_desconto_applied')) {
                $table->boolean('super_desconto_applied')->default(false)->after('is_auction');
            }
            if (!Schema::hasColumn('rental_offers', 'super_desconto_applied_at')) {
                $table->timestamp('super_desconto_applied_at')->nullable()->after('super_desconto_applied');
            }
            if (!Schema::hasColumn('rental_offers', 'super_desconto_percentage')) {
                $table->decimal('super_desconto_percentage', 5, 2)->default(0)->after('super_desconto_applied_at');
            }
            if (!Schema::hasColumn('rental_offers', 'mega_oferta_applied')) {
                $table->boolean('mega_oferta_applied')->default(false)->after('super_desconto_percentage');
            }
            if (!Schema::hasColumn('rental_offers', 'mega_oferta_applied_at')) {
                $table->timestamp('mega_oferta_applied_at')->nullable()->after('mega_oferta_applied');
            }
            if (!Schema::hasColumn('rental_offers', 'mega_oferta_percentage')) {
                $table->decimal('mega_oferta_percentage', 5, 2)->default(0)->after('mega_oferta_applied_at');
            }
            if (!Schema::hasColumn('rental_offers', 'app_commission')) {
                $table->decimal('app_commission', 10, 2)->default(0)->after('mega_oferta_percentage');
            }
            if (!Schema::hasColumn('rental_offers', 'is_penalized')) {
                $table->boolean('is_penalized')->default(false)->after('app_commission');
            }
            if (!Schema::hasColumn('rental_offers', 'penalty_until')) {
                $table->timestamp('penalty_until')->nullable()->after('is_penalized');
            }
            if (!Schema::hasColumn('rental_offers', 'penalty_reason')) {
                $table->text('penalty_reason')->nullable()->after('penalty_until');
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
                'super_desconto_applied',
                'super_desconto_applied_at',
                'super_desconto_percentage',
                'mega_oferta_applied',
                'mega_oferta_applied_at',
                'mega_oferta_percentage',
                'app_commission',
                'is_penalized',
                'penalty_until',
                'penalty_reason'
            ]);
        });
    }
};
