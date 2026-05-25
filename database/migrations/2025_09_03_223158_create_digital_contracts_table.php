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
        Schema::create('digital_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('quota_transactions')->onDelete('cascade');
            $table->string('contract_type');
            $table->text('contract_content');
            $table->string('contract_file_path')->nullable();
            $table->json('owner_signature')->nullable();
            $table->json('renter_signature')->nullable();
            $table->timestamp('owner_signed_at')->nullable();
            $table->timestamp('renter_signed_at')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->string('hotel_email')->nullable();
            $table->boolean('sent_to_hotel')->default(false);
            $table->timestamp('sent_to_hotel_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_contracts');
    }
};
