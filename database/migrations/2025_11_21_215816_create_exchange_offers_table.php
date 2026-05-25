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
        Schema::create('exchange_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('quota_id')->constrained()->onDelete('cascade');
            $table->enum('exchange_type', ['semana', 'titularidade'])->default('semana');
            $table->string('desired_city')->nullable();
            $table->date('desired_period_start')->nullable();
            $table->date('desired_period_end')->nullable();
            $table->string('desired_hotel')->nullable();
            $table->integer('desired_people')->nullable();
            $table->integer('desired_rooms')->nullable();
            $table->decimal('price_range_min', 10, 2)->nullable();
            $table->decimal('price_range_max', 10, 2)->nullable();
            $table->enum('exchange_mode', ['simples', 'mais'])->default('simples');
            $table->decimal('additional_value', 10, 2)->nullable(); // Se MAIS
            $table->integer('days_difference')->nullable(); // Se diárias diferentes
            $table->text('observations')->nullable();
            $table->enum('status', ['active', 'negotiating', 'completed', 'cancelled', 'expired'])->default('active');
            $table->timestamp('validity_until')->nullable(); // Validade (48h ou 72h)
            $table->json('selected_options')->nullable(); // Até 3, 5 ou 10 opções
            $table->integer('max_options')->default(3); // Máximo de opções por tipo
            $table->timestamps();
            
            $table->index(['status', 'validity_until']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_offers');
    }
};
