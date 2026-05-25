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
        Schema::create('hospitality_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('quota_id')->constrained()->onDelete('cascade');
            $table->foreignId('guest_user_id')->constrained('users')->onDelete('cascade');
            $table->string('authorization_code', 20)->unique();
            $table->string('guest_name');
            $table->string('guest_document', 20);
            $table->string('guest_phone', 20);
            $table->string('guest_email');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('number_of_guests');
            $table->text('special_requests')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'used', 'expired'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->dateTime('expires_at');
            $table->text('rejection_reason')->nullable();
            $table->text('hotel_notes')->nullable();
            $table->boolean('is_transferable')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitality_authorizations');
    }
};
