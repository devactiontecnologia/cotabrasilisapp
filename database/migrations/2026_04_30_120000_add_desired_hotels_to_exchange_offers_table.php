<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exchange_offers', function (Blueprint $table) {
            $table->json('desired_hotels')->nullable()->after('desired_hotel');
        });

        if (Schema::hasColumn('exchange_offers', 'desired_hotel')) {
            DB::table('exchange_offers')
                ->whereNotNull('desired_hotel')
                ->where('desired_hotel', '!=', '')
                ->orderBy('id')
                ->chunkById(100, function ($rows) {
                    foreach ($rows as $row) {
                        $name = trim((string) $row->desired_hotel);
                        if ($name === '') {
                            continue;
                        }
                        DB::table('exchange_offers')
                            ->where('id', $row->id)
                            ->update(['desired_hotels' => json_encode([$name])]);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('exchange_offers', function (Blueprint $table) {
            $table->dropColumn('desired_hotels');
        });
    }
};
