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
            if (!Schema::hasColumn('user_profiles', 'hospitality_authorization_term_path')) {
                $table->string('hospitality_authorization_term_path')->nullable()->after('owner_quota_observations');
            }
            if (!Schema::hasColumn('user_profiles', 'gestor_hospitality_authorization_term_path')) {
                $table->string('gestor_hospitality_authorization_term_path')->nullable()->after('gestor_quota_observations');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $columns = ['hospitality_authorization_term_path', 'gestor_hospitality_authorization_term_path'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('user_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
