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
            if (!Schema::hasColumn('user_profiles', 'quota_status')) {
                $table->enum('quota_status', ['paid', 'unpaid'])->nullable()->after('has_quota');
            }
            if (!Schema::hasColumn('user_profiles', 'quota_payment_deadline')) {
                $table->date('quota_payment_deadline')->nullable()->after('quota_status');
            }
            if (!Schema::hasColumn('user_profiles', 'is_quota_owner')) {
                $table->boolean('is_quota_owner')->default(true)->after('quota_payment_deadline');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['quota_status', 'quota_payment_deadline', 'is_quota_owner']);
        });
    }
};
