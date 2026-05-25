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
            // Campos do proprietário da cota
            $table->unsignedBigInteger('owner_hotel_id')->nullable()->after('quota_contracts');
            $table->foreign('owner_hotel_id')->references('id')->on('hotels')->onDelete('set null');
            $table->string('owner_quota_rooms')->nullable()->after('owner_hotel_id');
            $table->string('owner_quota_people')->nullable()->after('owner_quota_rooms');
            $table->string('owner_quota_double_bed')->nullable()->after('owner_quota_people');
            $table->string('owner_quota_single_bed')->nullable()->after('owner_quota_double_bed');
            $table->string('owner_quota_sofa_bed')->nullable()->after('owner_quota_single_bed');
            $table->string('owner_quota_size')->nullable()->after('owner_quota_sofa_bed');
            $table->boolean('owner_quota_jacuzzi')->nullable()->after('owner_quota_size');
            $table->boolean('owner_quota_kitchen')->nullable()->after('owner_quota_jacuzzi');
            $table->boolean('owner_quota_parking')->nullable()->after('owner_quota_kitchen');
            $table->boolean('owner_quota_breakfast')->nullable()->after('owner_quota_parking');
            $table->string('owner_quota_seasonality')->nullable()->after('owner_quota_breakfast');
            $table->text('owner_quota_observations')->nullable()->after('owner_quota_seasonality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropForeign(['owner_hotel_id']);
            $table->dropColumn([
                'owner_hotel_id',
                'owner_quota_rooms',
                'owner_quota_people',
                'owner_quota_double_bed',
                'owner_quota_single_bed',
                'owner_quota_sofa_bed',
                'owner_quota_size',
                'owner_quota_jacuzzi',
                'owner_quota_kitchen',
                'owner_quota_parking',
                'owner_quota_breakfast',
                'owner_quota_seasonality',
                'owner_quota_observations'
            ]);
        });
    }
};
