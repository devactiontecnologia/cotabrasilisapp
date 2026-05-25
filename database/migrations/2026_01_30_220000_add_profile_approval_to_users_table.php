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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'profile_approval_status')) {
                $table->string('profile_approval_status', 20)->default('approved')->after('role');
            }
            if (!Schema::hasColumn('users', 'profile_approved_at')) {
                $table->timestamp('profile_approved_at')->nullable()->after('profile_approval_status');
            }
            if (!Schema::hasColumn('users', 'profile_rejected_at')) {
                $table->timestamp('profile_rejected_at')->nullable()->after('profile_approved_at');
            }
            if (!Schema::hasColumn('users', 'show_approval_success_modal')) {
                $table->boolean('show_approval_success_modal')->default(false)->after('profile_rejected_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['profile_approval_status', 'profile_approved_at', 'profile_rejected_at', 'show_approval_success_modal'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
