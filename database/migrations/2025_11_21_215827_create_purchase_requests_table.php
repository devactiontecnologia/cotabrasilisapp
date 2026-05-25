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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('hotel_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('weeks')->nullable();
            $table->integer('month')->nullable(); // 1-12
            $table->enum('period_type', ['fixo', 'flexivel'])->default('fixo');
            $table->string('city')->nullable();
            $table->string('company')->nullable();
            $table->decimal('price_range_min', 10, 2)->nullable();
            $table->decimal('price_range_max', 10, 2)->nullable();
            $table->text('observations')->nullable();
            $table->enum('status', ['active', 'matched', 'purchased', 'cancelled'])->default('active');
            $table->boolean('delegated_to_admin')->default(false);
            $table->decimal('max_price', 10, 2)->nullable(); // Se delegado
            $table->decimal('purchase_fee_percentage', 5, 2)->default(10.00); // Taxa inicial 10%
            $table->timestamps();
            
            $table->index(['status', 'delegated_to_admin']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
