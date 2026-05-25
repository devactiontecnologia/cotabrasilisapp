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
            // Campos obrigatórios para KYC
            $table->string('user_photo_path')->nullable()->change(); // Já existe, mas tornando obrigatório
            $table->string('rg_photo_path')->nullable()->change(); // Já existe, mas tornando obrigatório
            $table->string('cnh_photo_path')->nullable()->change(); // Já existe, mas tornando obrigatório
            $table->string('quota_contract_photo_path')->nullable()->change(); // Já existe, mas tornando obrigatório
            
            // Novos campos para KYC
            $table->boolean('is_authorized_user')->default(false)->after('has_quota');
            $table->string('authorization_document_path')->nullable()->after('is_authorized_user');
            $table->json('gov_br_signature')->nullable()->after('authorization_document_path');
            $table->timestamp('gov_br_signature_at')->nullable()->after('gov_br_signature');
            $table->boolean('kyc_completed')->default(false)->after('gov_br_signature_at');
            $table->timestamp('kyc_completed_at')->nullable()->after('kyc_completed');
            $table->enum('kyc_status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending')->after('kyc_completed_at');
            $table->text('kyc_rejection_reason')->nullable()->after('kyc_status');
            $table->json('quota_contracts')->nullable()->after('kyc_rejection_reason'); // Para múltiplos contratos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'is_authorized_user',
                'authorization_document_path',
                'gov_br_signature',
                'gov_br_signature_at',
                'kyc_completed',
                'kyc_completed_at',
                'kyc_status',
                'kyc_rejection_reason',
                'quota_contracts'
            ]);
        });
    }
};
