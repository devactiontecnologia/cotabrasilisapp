<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar se a coluna já existe
        $columns = DB::select("SHOW COLUMNS FROM favorite_lists LIKE 'transaction_type'");
        
        if (empty($columns)) {
            // Adicionar a coluna usando SQL direto
            DB::statement("ALTER TABLE favorite_lists ADD COLUMN transaction_type ENUM('rental', 'purchase', 'exchange') DEFAULT 'rental' AFTER type");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = DB::select("SHOW COLUMNS FROM favorite_lists LIKE 'transaction_type'");
        
        if (!empty($columns)) {
            DB::statement("ALTER TABLE favorite_lists DROP COLUMN transaction_type");
        }
    }
};
