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
            $table->boolean('gestor_quota_jacuzzi')->nullable()->after('gestor_quota_size');
            $table->boolean('gestor_quota_kitchen')->nullable()->after('gestor_quota_jacuzzi');
            $table->boolean('gestor_quota_parking')->nullable()->after('gestor_quota_kitchen');
            $table->boolean('gestor_quota_breakfast')->nullable()->after('gestor_quota_parking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'gestor_quota_jacuzzi',
                'gestor_quota_kitchen',
                'gestor_quota_parking',
                'gestor_quota_breakfast'
            ]);
        });
    }
};
