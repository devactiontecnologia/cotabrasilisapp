<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exchange_offers', function (Blueprint $table) {
            $table->string('nights_plus_money', 500)->nullable()->after('observations');
        });

        DB::table('exchange_offers')
            ->whereNotNull('observations')
            ->where('observations', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('exchange_offers')
                        ->where('id', $row->id)
                        ->whereNull('nights_plus_money')
                        ->update(['nights_plus_money' => Str::limit((string) $row->observations, 500)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('exchange_offers', function (Blueprint $table) {
            $table->dropColumn('nights_plus_money');
        });
    }
};
