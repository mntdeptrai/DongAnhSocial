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
                if (Schema::connection($conn)->hasTable('comments')) {
                    DB::connection($conn)->statement("ALTER TABLE comments ADD FULLTEXT INDEX ft_comments_content (content)");
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
                if (Schema::connection($conn)->hasTable('comments')) {
                    DB::connection($conn)->statement("ALTER TABLE comments DROP INDEX ft_comments_content");
                }
            } catch (\Throwable $e) {}
        }
    }
};
