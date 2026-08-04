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
        $connections = ['mysql'];
        if (config('database.connections.mysql_education')) {
            $connections[] = 'mysql_education';
        }

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('users')) {
                    Schema::connection($conn)->table('users', function (Blueprint $table) use ($conn) {
                        if (!Schema::connection($conn)->hasColumn('users', 'is_verified')) {
                            $table->boolean('is_verified')->default(false)->after('status');
                        }
                    });
                }
            } catch (\Throwable $e) {
                // Ignore if connection or table does not exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = ['mysql'];
        if (config('database.connections.mysql_education')) {
            $connections[] = 'mysql_education';
        }

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('users') && Schema::connection($conn)->hasColumn('users', 'is_verified')) {
                    Schema::connection($conn)->table('users', function (Blueprint $table) {
                        $table->dropColumn('is_verified');
                    });
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }
};
