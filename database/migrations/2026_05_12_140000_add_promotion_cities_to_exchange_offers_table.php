<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exchange_offers', function (Blueprint $table) {
            if (! Schema::hasColumn('exchange_offers', 'promotion_cities')) {
                $table->json('promotion_cities')->nullable()->after('desired_hotels');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exchange_offers', function (Blueprint $table) {
            if (Schema::hasColumn('exchange_offers', 'promotion_cities')) {
                $table->dropColumn('promotion_cities');
            }
        });
    }
};
