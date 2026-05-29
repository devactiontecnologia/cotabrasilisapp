<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('quota_transactions')) {
            return;
        }

        DB::statement("ALTER TABLE quota_transactions MODIFY COLUMN transaction_type ENUM('rental', 'exchange', 'purchase') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('quota_transactions')) {
            return;
        }

        DB::table('quota_transactions')->where('transaction_type', 'purchase')->update(['transaction_type' => 'rental']);

        DB::statement("ALTER TABLE quota_transactions MODIFY COLUMN transaction_type ENUM('rental', 'exchange') NOT NULL");
    }
};
