<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quota_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('quota_transactions', 'exchange_quota_id')) {
                $table->unsignedBigInteger('exchange_quota_id')->nullable()->after('quota_id');
                $table->index('exchange_quota_id');
            }
            if (!Schema::hasColumn('quota_transactions', 'is_fair_exchange')) {
                $table->boolean('is_fair_exchange')->default(false)->after('exchange_quota_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quota_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('quota_transactions', 'is_fair_exchange')) {
                $table->dropColumn('is_fair_exchange');
            }
            if (Schema::hasColumn('quota_transactions', 'exchange_quota_id')) {
                $table->dropIndex(['exchange_quota_id']);
                $table->dropColumn('exchange_quota_id');
            }
        });
    }
};

