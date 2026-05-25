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
        // Adicionar campo quota_type na tabela quotas
        Schema::table('quotas', function (Blueprint $table) {
            if (!Schema::hasColumn('quotas', 'quota_type')) {
                $table->enum('quota_type', ['fixa', 'flexivel', 'fix_flexivel'])->nullable()->after('is_fractioned');
            }
        });

        // Adicionar campos owner_quota_type e gestor_quota_type na tabela user_profiles
        Schema::table('user_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('user_profiles', 'owner_quota_type')) {
                $table->enum('owner_quota_type', ['fixa', 'flexivel', 'fix_flexivel'])->nullable()->after('owner_quota_observations');
            }
            if (!Schema::hasColumn('user_profiles', 'gestor_quota_type')) {
                $table->enum('gestor_quota_type', ['fixa', 'flexivel', 'fix_flexivel'])->nullable()->after('gestor_quota_observations');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotas', function (Blueprint $table) {
            if (Schema::hasColumn('quotas', 'quota_type')) {
                $table->dropColumn('quota_type');
            }
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('user_profiles', 'owner_quota_type')) {
                $table->dropColumn('owner_quota_type');
            }
            if (Schema::hasColumn('user_profiles', 'gestor_quota_type')) {
                $table->dropColumn('gestor_quota_type');
            }
        });
    }
};
