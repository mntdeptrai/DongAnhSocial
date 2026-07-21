<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $connections = ['mysql_market', 'mysql'];

    public function up(): void
    {
        $default = config('database.default');
        $conns = config("database.connections.{$default}.driver") === 'sqlite' ? [$default] : $this->connections;

        foreach ($conns as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('ocop_products')) {
                    Schema::connection($conn)->table('ocop_products', function (Blueprint $table) use ($conn) {
                        if (!Schema::connection($conn)->hasColumn('ocop_products', 'stall_name')) {
                            $table->string('stall_name')->nullable()->after('eatery_id');
                        }
                        if (!Schema::connection($conn)->hasColumn('ocop_products', 'seller_name')) {
                            $table->string('seller_name')->nullable()->after('stall_name');
                        }
                        if (!Schema::connection($conn)->hasColumn('ocop_products', 'seller_phone')) {
                            $table->string('seller_phone')->nullable()->after('seller_name');
                        }
                    });
                }
            } catch (\Exception $e) {}
        }
    }

    public function down(): void
    {
        $default = config('database.default');
        $conns = config("database.connections.{$default}.driver") === 'sqlite' ? [$default] : $this->connections;

        foreach ($conns as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('ocop_products')) {
                    Schema::connection($conn)->table('ocop_products', function (Blueprint $table) use ($conn) {
                        $columnsToDrop = [];
                        if (Schema::connection($conn)->hasColumn('ocop_products', 'stall_name')) {
                            $columnsToDrop[] = 'stall_name';
                        }
                        if (Schema::connection($conn)->hasColumn('ocop_products', 'seller_name')) {
                            $columnsToDrop[] = 'seller_name';
                        }
                        if (Schema::connection($conn)->hasColumn('ocop_products', 'seller_phone')) {
                            $columnsToDrop[] = 'seller_phone';
                        }
                        if (!empty($columnsToDrop)) {
                            $table->dropColumn($columnsToDrop);
                        }
                    });
                }
            } catch (\Exception $e) {}
        }
    }
};
