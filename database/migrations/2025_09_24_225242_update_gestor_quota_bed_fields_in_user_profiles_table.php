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
            // Rename the existing beds field to double_bed
            $table->renameColumn('gestor_quota_beds', 'gestor_quota_double_bed');
            
            // Add new bed fields
            $table->string('gestor_quota_single_bed')->nullable()->after('gestor_quota_double_bed');
            $table->string('gestor_quota_sofa_bed')->nullable()->after('gestor_quota_single_bed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            // Drop the new fields
            $table->dropColumn(['gestor_quota_single_bed', 'gestor_quota_sofa_bed']);
            
            // Rename back to original name
            $table->renameColumn('gestor_quota_double_bed', 'gestor_quota_beds');
        });
    }
};
