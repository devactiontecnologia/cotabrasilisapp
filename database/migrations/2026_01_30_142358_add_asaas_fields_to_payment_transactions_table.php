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
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('asaas_payment_id')->nullable()->after('payment_reference');
            $table->json('asaas_webhook_data')->nullable()->after('asaas_payment_id');
            
            $table->index('asaas_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropIndex(['asaas_payment_id']);
            $table->dropColumn(['asaas_payment_id', 'asaas_webhook_data']);
        });
    }
};
