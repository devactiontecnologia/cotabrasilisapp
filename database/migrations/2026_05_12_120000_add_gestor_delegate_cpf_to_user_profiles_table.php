<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('user_profiles', 'gestor_delegate_cpf')) {
                $table->string('gestor_delegate_cpf', 14)->nullable()->after('gestor_authorization_document_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('user_profiles', 'gestor_delegate_cpf')) {
                $table->dropColumn('gestor_delegate_cpf');
            }
        });
    }
};
