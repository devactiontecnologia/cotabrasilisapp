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
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('gestor_quota_rooms')->nullable()->after('gestor_quota_people');
            $table->string('gestor_quota_size')->nullable()->after('gestor_quota_rooms');
            $table->string('gestor_quota_seasonality')->nullable()->after('gestor_quota_size');
            $table->text('gestor_quota_observations')->nullable()->after('gestor_quota_seasonality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'gestor_quota_rooms',
                'gestor_quota_size', 
                'gestor_quota_seasonality',
                'gestor_quota_observations'
            ]);
        });
    }
};
