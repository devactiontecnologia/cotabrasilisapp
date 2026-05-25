<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quota_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('quota_transactions', 'guest_names')) {
                $table->json('guest_names')->nullable()->after('document_deadline_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quota_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('quota_transactions', 'guest_names')) {
                $table->dropColumn('guest_names');
            }
        });
    }
};
