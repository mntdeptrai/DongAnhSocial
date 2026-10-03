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
        // Add bank fields to users table if not exists
        if (!Schema::hasColumn('users', 'bank_account')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('bank_account')->nullable()->after('phone');
                $table->string('bank_name')->nullable()->after('bank_account');
            });
        }

        if (!Schema::hasColumn('users', 'eatery_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('eatery_id')->nullable()->index()->after('id');
            });
        }

        if (!Schema::hasColumn('users', 'stall_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('stall_id')->nullable()->index()->after('eatery_id');
            });
        }

        // Add user_id to route_businesses table if not exists
        if (!Schema::hasColumn('route_businesses', 'user_id')) {
            Schema::table('route_businesses', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->index()->after('id');
            });
        }

        // Add user_id to ocop_products table if not exists
        if (Schema::hasTable('ocop_products') && !Schema::hasColumn('ocop_products', 'user_id')) {
            Schema::table('ocop_products', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->index()->after('eatery_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'bank_account')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['bank_account', 'bank_name']);
            });
        }

        if (Schema::hasColumn('route_businesses', 'user_id')) {
            Schema::table('route_businesses', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
