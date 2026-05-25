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
        Schema::create('sale_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('quota_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->integer('weeks')->default(1); // 1-4 semanas
            $table->integer('number_of_rooms');
            $table->string('city');
            $table->string('company')->nullable();
            $table->decimal('minimum_price', 10, 2);
            $table->decimal('acceptable_price', 10, 2);
            $table->decimal('desired_price', 10, 2);
            $table->json('observations_by_price')->nullable(); // Observações por preço
            $table->enum('status', ['pending', 'negotiating', 'sold', 'cancelled'])->default('pending');
            $table->enum('negotiation_status', ['direct', 'admin', 'auction'])->default('direct');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('auction_id')->nullable()->constrained('auctions')->onDelete('set null');
            $table->decimal('app_commission', 10, 2)->default(0); // 10% se via leilão
            $table->timestamps();
            
            $table->index(['status', 'negotiation_status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_offers');
    }
};
