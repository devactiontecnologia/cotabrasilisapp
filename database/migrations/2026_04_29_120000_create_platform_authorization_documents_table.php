<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_authorization_documents', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('platform_authorization_documents')->insert([
            [
                'slug' => 'third_party_quota_management',
                'title' => 'Autorização para Gestão de Cota de Terceiro',
                'file_path' => null,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'ownership_transfer_guidance',
                'title' => 'Orientação para Conclusão da Troca de Titularidade de Cota',
                'file_path' => null,
                'sort_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'quota_usage_calendar',
                'title' => 'Calendário de Uso de Cotas',
                'file_path' => null,
                'sort_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_authorization_documents');
    }
};
