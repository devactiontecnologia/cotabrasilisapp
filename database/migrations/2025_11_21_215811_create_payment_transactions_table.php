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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('quota_transactions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('payment_method', ['credit_card', 'debit_card', 'pix', 'bank_transfer'])->default('pix');
            $table->decimal('amount', 10, 2);
            $table->decimal('fees', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->json('payment_details')->nullable();
            $table->string('authorization_document_path')->nullable();
            $table->string('video_path')->nullable(); // Vídeo selfie
            $table->boolean('sent_at_hour')->default(false); // NA HORA (12h)
            $table->timestamp('payment_due_at')->nullable(); // Prazo de 12h
            $table->timestamp('payment_completed_at')->nullable();
            $table->timestamp('blocked_until')->nullable(); // Bloqueio se não cumpriu
            $table->text('block_reason')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'payment_due_at']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
