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
        Schema::create('quota_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quota_id')->constrained()->onDelete('cascade');
            $table->foreignId('renter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->enum('transaction_type', ['rental', 'exchange']);
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal('owner_amount', 10, 2)->nullable();
            $table->decimal('platform_fee', 10, 2)->nullable();
            $table->enum('status', ['pending', 'contract_signed', 'payment_pending', 'payment_completed', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('contract_signed_at')->nullable();
            $table->timestamp('payment_due_at')->nullable();
            $table->timestamp('payment_completed_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('payment_details')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quota_transactions');
    }
};
