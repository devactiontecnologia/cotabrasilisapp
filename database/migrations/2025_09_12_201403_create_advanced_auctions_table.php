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
        Schema::create('advanced_auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('start_time'); // Data/hora de início
            $table->timestamp('end_time'); // Data/hora de fim
            $table->decimal('minimum_price', 10, 2); // Preço mínimo
            $table->integer('duration_minutes'); // Duração em minutos (20-1440)
            $table->integer('bid_extension_minutes')->default(1); // Minutos antes do fim para permitir lances
            $table->enum('status', ['scheduled', 'active', 'ended', 'cancelled'])->default('scheduled');
            $table->decimal('current_bid', 10, 2)->nullable(); // Lance atual
            $table->foreignId('current_winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('total_bids')->default(0); // Total de lances
            $table->json('auction_rules')->nullable(); // Regras específicas do leilão
            $table->boolean('auto_extend')->default(false); // Auto-extensão se houver lances nos últimos minutos
            $table->integer('max_extensions')->default(3); // Máximo de extensões
            $table->integer('extensions_used')->default(0); // Extensões usadas
            $table->timestamp('last_bid_at')->nullable(); // Último lance
            $table->timestamps();
            
            $table->index(['status', 'start_time']);
            $table->index(['end_time', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advanced_auctions');
    }
};
