<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exchange_offers', function (Blueprint $table) {
            $table->unsignedTinyInteger('desired_period_month')->nullable()->after('desired_period_end');
            $table->unsignedSmallInteger('desired_period_year')->nullable()->after('desired_period_month');
        });
    }

    public function down(): void
    {
        Schema::table('exchange_offers', function (Blueprint $table) {
            $table->dropColumn(['desired_period_month', 'desired_period_year']);
        });
    }
};
