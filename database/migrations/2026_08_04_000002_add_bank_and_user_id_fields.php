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

        // Add user_id to route_businesses table if not exists
        if (!Schema::hasColumn('route_businesses', 'user_id')) {
            Schema::table('route_businesses', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->index()->after('id');
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
