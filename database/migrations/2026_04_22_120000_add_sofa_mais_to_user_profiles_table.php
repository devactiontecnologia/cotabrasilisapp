<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('user_profiles', 'owner_quota_sofa_mais')) {
                $table->boolean('owner_quota_sofa_mais')->nullable()->after('owner_quota_breakfast');
            }
            if (! Schema::hasColumn('user_profiles', 'gestor_quota_sofa_mais')) {
                $table->boolean('gestor_quota_sofa_mais')->nullable()->after('gestor_quota_breakfast');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('user_profiles', 'owner_quota_sofa_mais')) {
                $table->dropColumn('owner_quota_sofa_mais');
            }
            if (Schema::hasColumn('user_profiles', 'gestor_quota_sofa_mais')) {
                $table->dropColumn('gestor_quota_sofa_mais');
            }
        });
    }
};
