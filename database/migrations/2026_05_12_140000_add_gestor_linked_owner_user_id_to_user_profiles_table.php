<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('user_profiles', 'gestor_linked_owner_user_id')) {
                $table->unsignedBigInteger('gestor_linked_owner_user_id')->nullable()->after('gestor_delegate_cpf');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('user_profiles', 'gestor_linked_owner_user_id')) {
                $table->dropColumn('gestor_linked_owner_user_id');
            }
        });
    }
};
