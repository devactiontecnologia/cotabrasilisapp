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
        Schema::create('rental_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('quota_id')->constrained()->onDelete('cascade');
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('city');
            $table->string('state');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('number_of_days');
            $table->integer('number_of_people');
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable(); // Preço original antes de descontos
            $table->enum('status', ['active', 'negotiated', 'cancelled', 'expired'])->default('active');
            $table->boolean('is_fractioned')->default(false);
            $table->json('fraction_details')->nullable(); // Detalhes do fracionamento
            $table->boolean('is_auction')->default(false);
            $table->decimal('minimum_price', 10, 2)->nullable(); // Preço mínimo para leilão
            $table->timestamp('auction_end_time')->nullable(); // Fim do leilão
            $table->json('photos')->nullable(); // URLs das fotos
            $table->text('observations')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->timestamp('negotiated_at')->nullable();
            $table->foreignId('negotiated_with')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['status', 'start_date']);
            $table->index(['city', 'state']);
            $table->index(['price', 'status']);
            $table->index(['is_auction', 'auction_end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_offers');
    }
};
