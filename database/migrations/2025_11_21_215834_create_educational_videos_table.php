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
        Schema::create('educational_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_content_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_url');
            $table->string('thumbnail_url')->nullable();
            $table->integer('duration')->nullable(); // em segundos
            $table->enum('profile_type_required', ['curioso', 'inteligente', 'sabio'])->nullable(); // null = todos
            $table->string('category')->nullable(); // alugar, trocar, vender, comprar, aviao, carro, turismo, hotel
            $table->json('tags')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['is_active', 'profile_type_required']);
            $table->index('category');
        });
        
        // Tabela para comentários de vídeos
        Schema::create('video_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_video_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->foreignId('parent_id')->nullable()->constrained('video_comments')->onDelete('cascade');
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
            
            $table->index(['educational_video_id', 'is_approved']);
        });
        
        // Tabela para visualizações de vídeos
        Schema::create('video_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_video_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('viewed_at');
            $table->integer('duration_watched')->default(0); // em segundos
            $table->boolean('completed')->default(false);
            $table->timestamps();
            
            $table->unique(['educational_video_id', 'user_id', 'viewed_at']);
            $table->index(['educational_video_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_views');
        Schema::dropIfExists('video_comments');
        Schema::dropIfExists('educational_videos');
    }
};
