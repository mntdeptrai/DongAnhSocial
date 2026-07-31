<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $connections = ['mysql_market', 'mysql', 'mysql_stay', 'mysql_wellness', 'mysql_education', 'mysql_culture'];

    public function up(): void
    {
        $default = config('database.default');
        $conns = config("database.connections.{$default}.driver") === 'sqlite' ? [$default] : $this->connections;

        foreach ($conns as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('ocop_products')) {
                    DB::connection($conn)->statement("ALTER TABLE `ocop_products` MODIFY `description` TEXT NULL");
                    DB::connection($conn)->statement("ALTER TABLE `ocop_products` MODIFY `image_path` TEXT NULL");
                }
            } catch (\Exception $e) {
                // Ignore if connection or table does not exist
            }
        }
    }

    public function down(): void
    {
        // No revert needed
    }
};
