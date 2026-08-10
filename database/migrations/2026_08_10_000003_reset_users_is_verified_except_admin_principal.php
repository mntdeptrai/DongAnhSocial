<?php

use Illuminate\Database\Migrations\Migration;
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
                if (Schema::connection($conn)->hasTable('users') && Schema::connection($conn)->hasColumn('users', 'is_verified')) {
                    DB::connection($conn)
                        ->table('users')
                        ->where(function($query) {
                            $query->whereNotIn('role', ['admin', 'principal'])
                                  ->orWhereNull('role');
                        })
                        ->update(['is_verified' => 0]);
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
        // No action needed on rollback
    }
};
