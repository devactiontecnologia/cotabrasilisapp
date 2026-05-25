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
        Schema::table('user_profiles', function (Blueprint $table) {
            // Verificar se o campo address existe antes de removê-lo
            if (Schema::hasColumn('user_profiles', 'address')) {
                $table->dropColumn('address');
            }
            
            // Adicionar os novos campos de endereço
            $table->string('cep', 9)->after('phone'); // 07980-000
            $table->string('street')->after('cep'); // Rua
            $table->string('neighborhood')->after('street'); // Bairro
            $table->string('city')->after('neighborhood'); // Cidade
            $table->string('state')->after('city'); // Estado
            $table->string('house_number')->after('state'); // Número da residência
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            // Remover os novos campos
            $table->dropColumn(['cep', 'street', 'neighborhood', 'city', 'state', 'house_number']);
            
            // Restaurar o campo address antigo
            $table->string('address')->after('phone');
        });
    }
};
