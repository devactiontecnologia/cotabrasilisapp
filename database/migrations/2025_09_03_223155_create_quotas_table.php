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
        Schema::create('quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('hotel_name');
            $table->string('location');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('number_of_guests');
            $table->decimal('rental_price', 10, 2)->nullable();
            $table->boolean('is_exchange')->default(false);
            $table->text('observations')->nullable();
            $table->string('contract_photo_path');
            $table->enum('status', ['available', 'rented', 'exchanged', 'cancelled'])->default('available');
            $table->boolean('is_fractioned')->default(false);
            $table->json('fraction_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotas');
    }
};
