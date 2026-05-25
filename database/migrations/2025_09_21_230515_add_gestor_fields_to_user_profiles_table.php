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
            $table->boolean('gestor_hotel_operational')->nullable()->after('quota_contracts');
            $table->string('gestor_quota_status')->nullable()->after('gestor_hotel_operational');
            $table->date('gestor_quota_payment_deadline')->nullable()->after('gestor_quota_status');
            $table->string('gestor_authorization_document_path')->nullable()->after('gestor_quota_payment_deadline');
            $table->unsignedBigInteger('gestor_hotel_id')->nullable()->after('gestor_authorization_document_path');
            $table->foreign('gestor_hotel_id')->references('id')->on('hotels')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropForeign(['gestor_hotel_id']);
            $table->dropColumn([
                'gestor_hotel_operational',
                'gestor_quota_status',
                'gestor_quota_payment_deadline',
                'gestor_authorization_document_path',
                'gestor_hotel_id'
            ]);
        });
    }
};
