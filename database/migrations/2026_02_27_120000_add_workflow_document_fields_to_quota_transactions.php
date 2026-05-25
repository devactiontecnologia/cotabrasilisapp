<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quota_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('quota_transactions', 'workflow_step')) {
                $table->string('workflow_step', 40)->default('awaiting_owner_doc')->after('document_path');
            }
            if (!Schema::hasColumn('quota_transactions', 'renter_signed_document_path')) {
                $table->string('renter_signed_document_path')->nullable()->after('workflow_step');
            }
            if (!Schema::hasColumn('quota_transactions', 'owner_signed_document_path')) {
                $table->string('owner_signed_document_path')->nullable()->after('renter_signed_document_path');
            }
            if (!Schema::hasColumn('quota_transactions', 'payment_receipt_path')) {
                $table->string('payment_receipt_path')->nullable()->after('owner_signed_document_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quota_transactions', function (Blueprint $table) {
            foreach (['workflow_step', 'renter_signed_document_path', 'owner_signed_document_path', 'payment_receipt_path'] as $col) {
                if (Schema::hasColumn('quota_transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
