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
        Schema::table('quota_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('quota_transactions', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'completed', 'failed'])->default('pending')->after('status');
            }
            if (!Schema::hasColumn('quota_transactions', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('quota_transactions', 'payment_id')) {
                $table->string('payment_id')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('quota_transactions', 'transaction_date')) {
                $table->timestamp('transaction_date')->nullable()->after('payment_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quota_transactions', function (Blueprint $table) {
            $columns = ['payment_status', 'payment_method', 'payment_id', 'transaction_date'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('quota_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
