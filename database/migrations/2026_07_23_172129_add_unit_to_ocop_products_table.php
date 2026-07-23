<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add `unit` column (đơn vị tính: kg, bát, đĩa, mớ, quả, ...) to all ocop_products tables.
     * Run on every market/food connection.
     */
    public function up(): void
    {
        $connections = ['mysql', 'mysql_market', 'mysql_stay', 'mysql_wellness', 'mysql_education', 'mysql_culture'];

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('ocop_products')) {
                    Schema::connection($conn)->table('ocop_products', function (Blueprint $table) use ($conn) {
                        if (!Schema::connection($conn)->hasColumn('ocop_products', 'unit')) {
                            // unit = đơn vị tính: "kg", "bát", "đĩa", "mớ", "quả", "túi", "hộp", ...
                            $table->string('unit', 30)->nullable()->after('price')->comment('Đơn vị tính (kg, bát, đĩa, mớ...)');
                        }
                    });
                }
            } catch (\Exception $e) {
                // Skip connection if unavailable
            }
        }
    }

    public function down(): void
    {
        $connections = ['mysql', 'mysql_market', 'mysql_stay', 'mysql_wellness', 'mysql_education', 'mysql_culture'];

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('ocop_products')) {
                    Schema::connection($conn)->table('ocop_products', function (Blueprint $table) use ($conn) {
                        if (Schema::connection($conn)->hasColumn('ocop_products', 'unit')) {
                            $table->dropColumn('unit');
                        }
                    });
                }
            } catch (\Exception $e) {
                // Skip
            }
        }
    }
};
