<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cotas marcadas com estrela em "Buscar cotas" — persistidas para a página Desejados.
     */
    public function up(): void
    {
        Schema::create('user_wishlist_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quota_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'quota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_wishlist_quotas');
    }
};
