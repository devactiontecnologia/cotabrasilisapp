<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SLUG = 'third_party_hospitality_voucher_authorization';

    public function up(): void
    {
        if (DB::table('platform_authorization_documents')->where('slug', self::SLUG)->exists()) {
            return;
        }

        $now = now();
        DB::table('platform_authorization_documents')->insert([
            'slug' => self::SLUG,
            'title' => 'Termo de Autorização de Hospedagem de Terceiros/Voucher',
            'file_path' => null,
            'sort_order' => 40,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('platform_authorization_documents')->where('slug', self::SLUG)->delete();
    }
};
