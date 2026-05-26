<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asaas_subaccounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('asaas_account_id')->nullable()->index();
            $table->string('wallet_id')->nullable()->index();
            $table->text('api_key')->nullable();
            $table->string('status', 32)->default('pending');
            $table->text('last_error')->nullable();
            $table->foreignId('created_from_transaction_id')->nullable()->constrained('quota_transactions')->nullOnDelete();
            $table->decimal('cached_balance', 12, 2)->nullable();
            $table->timestamp('balance_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('asaas_wallet_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asaas_subaccount_id')->constrained('asaas_subaccounts')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('destination_wallet_id');
            $table->string('asaas_transfer_id')->nullable()->index();
            $table->string('status', 32)->default('pending');
            $table->text('error_message')->nullable();
            $table->json('asaas_response')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asaas_wallet_transfers');
        Schema::dropIfExists('asaas_subaccounts');
    }
};
