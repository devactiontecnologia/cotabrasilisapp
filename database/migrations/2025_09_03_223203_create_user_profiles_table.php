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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('profile_type', ['curioso', 'inteligente', 'sabio']);
            $table->string('full_name');
            $table->string('cpf', 14)->unique();
            $table->string('phone', 20);
            $table->string('address');
            $table->string('cnh_photo_path')->nullable();
            $table->string('rg_photo_path')->nullable();
            $table->string('user_photo_path')->nullable();
            $table->string('quota_contract_photo_path')->nullable();
            $table->boolean('quota_paid_off')->default(false);
            $table->boolean('hotel_operational')->default(true);
            $table->boolean('terms_accepted')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();
            $table->json('digital_signature')->nullable();
            $table->integer('auctions_used')->default(0);
            $table->integer('search_views_used')->default(0);
            $table->timestamp('last_search_view')->nullable();
            $table->json('alert_cities')->nullable();
            $table->boolean('has_quota')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
