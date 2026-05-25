<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('user_profiles', 'owner_quota_number')) {
                $table->string('owner_quota_number', 50)->nullable()->after('owner_quota_observations');
            }
            if (!Schema::hasColumn('user_profiles', 'owner_quota_block')) {
                $table->string('owner_quota_block', 50)->nullable()->after('owner_quota_number');
            }
            if (!Schema::hasColumn('user_profiles', 'owner_apartment_number')) {
                $table->string('owner_apartment_number', 50)->nullable()->after('owner_quota_block');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $cols = ['owner_quota_number', 'owner_quota_block', 'owner_apartment_number'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('user_profiles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
