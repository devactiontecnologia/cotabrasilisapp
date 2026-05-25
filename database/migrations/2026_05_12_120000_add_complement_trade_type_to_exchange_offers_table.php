<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exchange_offers', function (Blueprint $table) {
            if (! Schema::hasColumn('exchange_offers', 'complement_trade_type')) {
                $table->string('complement_trade_type', 32)->nullable()->after('exchange_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exchange_offers', function (Blueprint $table) {
            if (Schema::hasColumn('exchange_offers', 'complement_trade_type')) {
                $table->dropColumn('complement_trade_type');
            }
        });
    }
};
