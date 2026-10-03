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
        $connections = ['mysql_market', 'mysql'];

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('ocop_products')) {
                    if (!Schema::connection($conn)->hasColumn('ocop_products', 'stall_image')) {
                        Schema::connection($conn)->table('ocop_products', function (Blueprint $table) {
                            $table->string('stall_image', 500)->nullable()->after('seller_phone');
                        });
                    }
                }
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = ['mysql_market', 'mysql'];

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('ocop_products') && Schema::connection($conn)->hasColumn('ocop_products', 'stall_image')) {
                    Schema::connection($conn)->table('ocop_products', function (Blueprint $table) {
                        $table->dropColumn('stall_image');
                    });
                }
            } catch (\Throwable $e) {}
        }
    }
};
