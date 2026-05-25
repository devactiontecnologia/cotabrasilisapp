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
        Schema::create('wishlist_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('city');
            $table->string('state', 2);
            $table->date('desired_start_date');
            $table->date('desired_end_date');
            $table->integer('number_of_people');
            $table->integer('number_of_rooms');
            $table->decimal('max_price', 10, 2)->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['active', 'fulfilled', 'cancelled', 'expired'])->default('active');
            $table->timestamp('fulfilled_at')->nullable();
            $table->foreignId('fulfilled_by_offer_id')->nullable()->constrained('rental_offers');
            $table->text('admin_notes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist_requests');
    }
};
