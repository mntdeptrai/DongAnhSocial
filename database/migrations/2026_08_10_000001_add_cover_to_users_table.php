<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connections = ['mysql', 'mysql_education', 'mysql_culture'];
        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('users')) {
                    Schema::connection($conn)->table('users', function (Blueprint $table) use ($conn) {
                        if (!Schema::connection($conn)->hasColumn('users', 'cover')) {
                            $table->string('cover')->nullable()->after('avatar');
                        }
                    });
                }
            } catch (\Exception $e) {}
        }
    }

    public function down(): void
    {
        $connections = ['mysql', 'mysql_education', 'mysql_culture'];
        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('users') && Schema::connection($conn)->hasColumn('users', 'cover')) {
                    Schema::connection($conn)->table('users', function (Blueprint $table) {
                        $table->dropColumn('cover');
                    });
                }
            } catch (\Exception $e) {}
        }
    }
};
