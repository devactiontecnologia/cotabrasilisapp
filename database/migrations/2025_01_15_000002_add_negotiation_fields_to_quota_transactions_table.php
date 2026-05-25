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
        Schema::table('quota_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('quota_transactions', 'negotiation_started_at')) {
                $table->timestamp('negotiation_started_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('quota_transactions', 'negotiation_deadline')) {
                $table->timestamp('negotiation_deadline')->nullable()->after('negotiation_started_at');
            }
            if (!Schema::hasColumn('quota_transactions', 'document_upload_deadline')) {
                $table->timestamp('document_upload_deadline')->nullable()->after('negotiation_deadline');
            }
            if (!Schema::hasColumn('quota_transactions', 'document_uploaded_at')) {
                $table->timestamp('document_uploaded_at')->nullable()->after('document_upload_deadline');
            }
            if (!Schema::hasColumn('quota_transactions', 'document_path')) {
                $table->string('document_path')->nullable()->after('document_uploaded_at');
            }
            if (!Schema::hasColumn('quota_transactions', 'payment_deadline_hours')) {
                $table->integer('payment_deadline_hours')->default(24)->after('document_path');
            }
            if (!Schema::hasColumn('quota_transactions', 'document_deadline_hours')) {
                $table->integer('document_deadline_hours')->default(24)->after('payment_deadline_hours');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quota_transactions', function (Blueprint $table) {
            $columns = [
                'negotiation_started_at',
                'negotiation_deadline',
                'document_upload_deadline',
                'document_uploaded_at',
                'document_path',
                'payment_deadline_hours',
                'document_deadline_hours'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('quota_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
