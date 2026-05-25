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
        Schema::table('hotels', function (Blueprint $table) {
            $table->boolean('is_functioning')->default(true)->after('is_active');
            $table->string('city')->nullable()->after('location');
            $table->string('state', 2)->nullable()->after('city');
            $table->text('status_reason')->nullable()->after('is_functioning');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['is_functioning', 'city', 'state', 'status_reason']);
        });
    }
};
