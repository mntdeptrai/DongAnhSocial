<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $connections = [
        'mysql',
        'mysql_stay',
        'mysql_wellness',
        'mysql_market',
        'mysql_education',
        'mysql_culture'
    ];

    public function up(): void
    {
        $default = config('database.default');
        $conns = config("database.connections.{$default}.driver") === 'sqlite' ? [$default] : $this->connections;

        foreach ($conns as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('eateries') && !Schema::connection($conn)->hasColumn('eateries', 'storytelling_data')) {
                    Schema::connection($conn)->table('eateries', function (Blueprint $table) {
                        $table->json('storytelling_data')->nullable()->after('status');
                    });
                }
            } catch (\Exception $e) {
                // Ignore connection errors if database is missing
            }
        }
    }

    public function down(): void
    {
        $default = config('database.default');
        $conns = config("database.connections.{$default}.driver") === 'sqlite' ? [$default] : $this->connections;

        foreach ($conns as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('eateries') && Schema::connection($conn)->hasColumn('eateries', 'storytelling_data')) {
                    Schema::connection($conn)->table('eateries', function (Blueprint $table) {
                        $table->dropColumn('storytelling_data');
                    });
                }
            } catch (\Exception $e) {
                // Ignore connection errors
            }
        }
    }
};
