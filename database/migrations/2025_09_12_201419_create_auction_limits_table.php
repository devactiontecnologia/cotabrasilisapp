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
        Schema::create('auction_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('quota_id')->constrained()->onDelete('cascade');
            $table->integer('auctions_used')->default(0); // Leilões usados
            $table->integer('auctions_limit')->default(0); // Limite de leilões
            $table->enum('limit_period', ['year', 'month', 'usage'])->default('year'); // Período do limite
            $table->date('period_start'); // Início do período
            $table->date('period_end'); // Fim do período
            $table->timestamps();
            
            $table->index(['user_id', 'limit_period', 'period_start']);
            $table->index(['quota_id', 'limit_period']);
            $table->unique(['user_id', 'quota_id', 'limit_period', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auction_limits');
    }
};
