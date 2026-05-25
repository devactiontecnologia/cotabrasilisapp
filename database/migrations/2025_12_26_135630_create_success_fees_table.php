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
        Schema::create('success_fees', function (Blueprint $table) {
            $table->id();
            $table->enum('profile_type', ['curioso', 'inteligente', 'sabio']);
            $table->integer('days')->comment('Número de dias do fracionamento');
            $table->decimal('fee_amount', 10, 2)->comment('Valor da taxa de êxito em R$');
            $table->boolean('is_active')->default(true)->comment('Se a taxa está ativa');
            $table->integer('order')->default(0)->comment('Ordem de exibição');
            $table->text('description')->nullable()->comment('Descrição opcional da taxa');
            $table->timestamps();
            
            // Índices para melhor performance
            $table->index(['profile_type', 'is_active']);
            $table->index(['profile_type', 'days']);
            $table->unique(['profile_type', 'days'], 'unique_profile_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('success_fees');
    }
};