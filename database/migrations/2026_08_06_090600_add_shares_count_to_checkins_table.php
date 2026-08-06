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
        $connections = ['mysql', 'mysql_education'];
        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('checkins') && !Schema::connection($conn)->hasColumn('checkins', 'shares_count')) {
                    Schema::connection($conn)->table('checkins', function (Blueprint $table) {
                        $table->unsignedInteger('shares_count')->default(0)->after('image_path');
                    });
                }
            } catch (\Throwable $ex) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = ['mysql', 'mysql_education'];
        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('checkins') && Schema::connection($conn)->hasColumn('checkins', 'shares_count')) {
                    Schema::connection($conn)->table('checkins', function (Blueprint $table) {
                        $table->dropColumn('shares_count');
                    });
                }
            } catch (\Throwable $ex) {}
        }
    }
};
