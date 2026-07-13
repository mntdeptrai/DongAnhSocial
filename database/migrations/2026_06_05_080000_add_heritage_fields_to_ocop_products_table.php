<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $connections = ['mysql_market'];

    public function up(): void
    {
        $default = config('database.default');
        $conns = config("database.connections.{$default}.driver") === 'sqlite' ? [$default] : $this->connections;

        foreach ($conns as $conn) {
            if (Schema::connection($conn)->hasTable('ocop_products')) {
                Schema::connection($conn)->table('ocop_products', function (Blueprint $table) use ($conn) {
                    if (!Schema::connection($conn)->hasColumn('ocop_products', 'heritage_year')) {
                        $table->string('heritage_year')->nullable();
                    }
                    if (!Schema::connection($conn)->hasColumn('ocop_products', 'story')) {
                        $table->text('story')->nullable();
                    }
                    if (!Schema::connection($conn)->hasColumn('ocop_products', 'artisans')) {
                        $table->text('artisans')->nullable();
                    }
                    if (!Schema::connection($conn)->hasColumn('ocop_products', 'fun_fact')) {
                        $table->text('fun_fact')->nullable();
                    }
                    if (!Schema::connection($conn)->hasColumn('ocop_products', 'audio_narrative')) {
                        $table->text('audio_narrative')->nullable();
                    }
                    if (!Schema::connection($conn)->hasColumn('ocop_products', 'ingredients')) {
                        $table->text('ingredients')->nullable();
                    }
                    if (!Schema::connection($conn)->hasColumn('ocop_products', 'timeline')) {
                        $table->text('timeline')->nullable();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $default = config('database.default');
        $conns = config("database.connections.{$default}.driver") === 'sqlite' ? [$default] : $this->connections;

        foreach ($conns as $conn) {
            if (Schema::connection($conn)->hasTable('ocop_products')) {
                Schema::connection($conn)->table('ocop_products', function (Blueprint $table) {
                    $table->dropColumn([
                        'heritage_year',
                        'story',
                        'artisans',
                        'fun_fact',
                        'audio_narrative',
                        'ingredients',
                        'timeline'
                    ]);
                });
            }
        }
    }
};
