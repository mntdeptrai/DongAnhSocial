<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
                    DB::connection($conn)->statement("ALTER TABLE users ADD FULLTEXT INDEX ft_users_search (name, username, email, phone)");
                }
            } catch (\Throwable $e) {
                // Tránh lỗi nếu chỉ mục đã tồn tại
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
                if (Schema::connection($conn)->hasTable('users')) {
                    DB::connection($conn)->statement("ALTER TABLE users DROP INDEX ft_users_search");
                }
            } catch (\Throwable $e) {}
        }
    }
};
