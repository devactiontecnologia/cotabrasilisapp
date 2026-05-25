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
        Schema::table('quotas', function (Blueprint $table) {
            if (!Schema::hasColumn('quotas', 'allowed_uses')) {
                $table->json('allowed_uses')->nullable()->after('quota_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotas', function (Blueprint $table) {
            if (Schema::hasColumn('quotas', 'allowed_uses')) {
                $table->dropColumn('allowed_uses');
            }
        });
    }
};
