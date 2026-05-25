<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_offers', function (Blueprint $table) {
            $table->decimal('minimum_price', 10, 2)->nullable()->change();
            $table->decimal('acceptable_price', 10, 2)->nullable()->change();
            $table->decimal('desired_price', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sale_offers', function (Blueprint $table) {
            $table->decimal('minimum_price', 10, 2)->nullable(false)->change();
            $table->decimal('acceptable_price', 10, 2)->nullable(false)->change();
            $table->decimal('desired_price', 10, 2)->nullable(false)->change();
        });
    }
};
