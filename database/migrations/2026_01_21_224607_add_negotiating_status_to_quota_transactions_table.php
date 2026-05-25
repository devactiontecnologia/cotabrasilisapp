<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL não permite alterar ENUM diretamente, então precisamos usar DB::statement
        DB::statement("ALTER TABLE quota_transactions MODIFY COLUMN status ENUM('pending', 'contract_signed', 'negotiating', 'payment_pending', 'document_pending', 'payment_completed', 'completed', 'cancelled', 'expired') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para os valores originais
        DB::statement("ALTER TABLE quota_transactions MODIFY COLUMN status ENUM('pending', 'contract_signed', 'payment_pending', 'payment_completed', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
