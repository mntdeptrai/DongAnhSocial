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
        $connections = ['mysql', 'mysql_education'];

        foreach ($connections as $connection) {
            try {
                if (Schema::connection($connection)->hasTable('education_programs')) {
                    DB::connection($connection)->statement('ALTER TABLE education_programs MODIFY description LONGTEXT NULL');
                    DB::connection($connection)->statement('ALTER TABLE education_programs MODIFY name TEXT NOT NULL');
                }
            } catch (\Exception $e) {
                // Ignore connection errors if DB not present in environment
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed for expanding column capacity
    }
};
