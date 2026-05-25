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
        Schema::table('favorite_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('favorite_lists', 'transaction_type')) {
                $table->enum('transaction_type', ['rental', 'purchase', 'exchange'])->default('rental')->after('type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favorite_lists', function (Blueprint $table) {
            if (Schema::hasColumn('favorite_lists', 'transaction_type')) {
                $table->dropColumn('transaction_type');
            }
        });
    }
};
