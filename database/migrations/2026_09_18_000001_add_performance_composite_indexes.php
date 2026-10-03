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
                if (Schema::connection($conn)->hasTable('eateries')) {
                    $this->addIndexIfNotExists($conn, 'eateries', 'idx_eateries_feed', "ALTER TABLE eateries ADD INDEX idx_eateries_feed (status, is_featured, rating, id)");
                    $this->addIndexIfNotExists($conn, 'eateries', 'idx_eateries_commune_feed', "ALTER TABLE eateries ADD INDEX idx_eateries_commune_feed (status, commune_id, is_featured, rating, id)");
                    $this->addIndexIfNotExists($conn, 'eateries', 'idx_eateries_category_feed', "ALTER TABLE eateries ADD INDEX idx_eateries_category_feed (status, category_id, is_featured, rating, id)");
                    $this->addIndexIfNotExists($conn, 'eateries', 'idx_eateries_address', "ALTER TABLE eateries ADD INDEX idx_eateries_address (address)");
                    $this->addIndexIfNotExists($conn, 'eateries', 'ft_eateries_search', "ALTER TABLE eateries ADD FULLTEXT INDEX ft_eateries_search (name, address)");
                }

                if (Schema::connection($conn)->hasTable('friendships')) {
                    $this->addIndexIfNotExists($conn, 'friendships', 'idx_friendships_user_status', "ALTER TABLE friendships ADD INDEX idx_friendships_user_status (user_id, status)");
                    $this->addIndexIfNotExists($conn, 'friendships', 'idx_friendships_friend_status', "ALTER TABLE friendships ADD INDEX idx_friendships_friend_status (friend_id, status)");
                }
            } catch (\Throwable $e) {
                // Tiếp tục nếu có lỗi cục bộ
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
                if (Schema::connection($conn)->hasTable('eateries')) {
                    $this->dropIndexIfExists($conn, 'eateries', 'idx_eateries_feed');
                    $this->dropIndexIfExists($conn, 'eateries', 'idx_eateries_commune_feed');
                    $this->dropIndexIfExists($conn, 'eateries', 'idx_eateries_category_feed');
                    $this->dropIndexIfExists($conn, 'eateries', 'idx_eateries_address');
                    $this->dropIndexIfExists($conn, 'eateries', 'ft_eateries_search');
                }

                if (Schema::connection($conn)->hasTable('friendships')) {
                    $this->dropIndexIfExists($conn, 'friendships', 'idx_friendships_user_status');
                    $this->dropIndexIfExists($conn, 'friendships', 'idx_friendships_friend_status');
                }
            } catch (\Throwable $e) {}
        }
    }

    private function addIndexIfNotExists(string $conn, string $table, string $indexName, string $statement): void
    {
        $results = DB::connection($conn)->select("
            SELECT INDEX_NAME 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND INDEX_NAME = ?
        ", [$table, $indexName]);

        if (empty($results)) {
            DB::connection($conn)->statement($statement);
        }
    }

    private function dropIndexIfExists(string $conn, string $table, string $indexName): void
    {
        $results = DB::connection($conn)->select("
            SELECT INDEX_NAME 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND INDEX_NAME = ?
        ", [$table, $indexName]);

        if (!empty($results)) {
            DB::connection($conn)->statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
        }
    }
};
