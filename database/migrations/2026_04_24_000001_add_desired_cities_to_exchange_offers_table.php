<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exchange_offers', function (Blueprint $table) {
            $table->json('desired_cities')->nullable()->after('desired_city');
        });
    }

    public function down(): void
    {
        Schema::table('exchange_offers', function (Blueprint $table) {
            $table->dropColumn('desired_cities');
        });
    }
};
