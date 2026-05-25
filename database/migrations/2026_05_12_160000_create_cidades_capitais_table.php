<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cidades_capitais', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('codigo_ibge')->unique();
            $table->string('nome', 120);
            $table->string('uf', 2);
            $table->boolean('is_capital')->default(false);
            $table->timestamps();

            $table->index(['uf', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cidades_capitais');
    }
};
