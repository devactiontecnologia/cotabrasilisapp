<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quota_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('quota_transactions', 'owner_pix')) {
                $table->string('owner_pix', 255)->nullable()->after('document_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quota_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('quota_transactions', 'owner_pix')) {
                $table->dropColumn('owner_pix');
            }
        });
    }
};
