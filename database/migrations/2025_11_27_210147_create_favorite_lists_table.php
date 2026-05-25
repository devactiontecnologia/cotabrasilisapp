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
        Schema::create('favorite_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Nome da lista (cidade ou hotel)
            $table->enum('type', ['city', 'hotel']); // Tipo: cidade ou hotel
            $table->timestamps();
        });

        Schema::create('favorite_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('favorite_list_id')->constrained()->onDelete('cascade');
            $table->foreignId('quota_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['favorite_list_id', 'quota_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_list_items');
        Schema::dropIfExists('favorite_lists');
    }
};
