<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wishlist_searches', function (Blueprint $table) {
            if (! Schema::hasColumn('wishlist_searches', 'transaction_type')) {
                $table->enum('transaction_type', ['rental', 'exchange', 'purchase'])
                    ->default('rental')
                    ->after('user_id');
            }
            if (! Schema::hasColumn('wishlist_searches', 'list_type')) {
                $table->enum('list_type', ['state', 'city', 'hotel'])
                    ->nullable()
                    ->after('transaction_type');
            }
        });

        Schema::table('user_wishlist_quotas', function (Blueprint $table) {
            if (! Schema::hasColumn('user_wishlist_quotas', 'transaction_type')) {
                $table->enum('transaction_type', ['rental', 'exchange', 'purchase'])
                    ->default('rental')
                    ->after('quota_id');
            }
            if (! Schema::hasColumn('user_wishlist_quotas', 'list_type')) {
                $table->enum('list_type', ['state', 'city', 'hotel'])
                    ->default('city')
                    ->after('transaction_type');
            }
        });

        if (! Schema::hasTable('wishlist_owner_alerts')) {
            Schema::create('wishlist_owner_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('quota_id')->constrained()->cascadeOnDelete();
                $table->enum('transaction_type', ['rental', 'exchange', 'purchase']);
                $table->foreignId('interested_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('wishlist_search_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedSmallInteger('interested_count')->default(1);
                $table->timestamp('notified_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['owner_user_id', 'quota_id', 'transaction_type', 'interested_user_id'],
                    'wishlist_owner_alerts_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_owner_alerts');

        Schema::table('user_wishlist_quotas', function (Blueprint $table) {
            if (Schema::hasColumn('user_wishlist_quotas', 'list_type')) {
                $table->dropColumn('list_type');
            }
            if (Schema::hasColumn('user_wishlist_quotas', 'transaction_type')) {
                $table->dropColumn('transaction_type');
            }
        });

        Schema::table('wishlist_searches', function (Blueprint $table) {
            if (Schema::hasColumn('wishlist_searches', 'list_type')) {
                $table->dropColumn('list_type');
            }
            if (Schema::hasColumn('wishlist_searches', 'transaction_type')) {
                $table->dropColumn('transaction_type');
            }
        });
    }
};
