<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('user_profiles', 'gestor_quota_number')) {
                $table->string('gestor_quota_number', 50)->nullable()->after('gestor_authorization_document_path');
            }
            if (!Schema::hasColumn('user_profiles', 'gestor_quota_block')) {
                $table->string('gestor_quota_block', 50)->nullable()->after('gestor_quota_number');
            }
            if (!Schema::hasColumn('user_profiles', 'gestor_apartment_number')) {
                $table->string('gestor_apartment_number', 50)->nullable()->after('gestor_quota_block');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $cols = ['gestor_quota_number', 'gestor_quota_block', 'gestor_apartment_number'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('user_profiles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
