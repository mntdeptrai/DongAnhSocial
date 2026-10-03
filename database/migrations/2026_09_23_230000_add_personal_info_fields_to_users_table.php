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
        $connections = ['mysql_education', 'mysql'];

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('users')) {
                    Schema::connection($conn)->table('users', function (Blueprint $table) use ($conn) {
                        if (!Schema::connection($conn)->hasColumn('users', 'address')) {
                            $table->text('address')->nullable()->after('phone');
                        }
                        if (!Schema::connection($conn)->hasColumn('users', 'commune')) {
                            $table->string('commune', 100)->nullable()->after('address');
                        }
                        if (!Schema::connection($conn)->hasColumn('users', 'bio')) {
                            $table->text('bio')->nullable()->after('commune');
                        }
                        if (!Schema::connection($conn)->hasColumn('users', 'gender')) {
                            $table->string('gender', 20)->nullable()->after('bio');
                        }
                        if (!Schema::connection($conn)->hasColumn('users', 'birthday')) {
                            $table->date('birthday')->nullable()->after('gender');
                        }
                    });
                }
            } catch (\Throwable $e) {
                // Ignore connection if not configured
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = ['mysql_education', 'mysql'];

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('users')) {
                    Schema::connection($conn)->table('users', function (Blueprint $table) use ($conn) {
                        $columns = ['address', 'commune', 'bio', 'gender', 'birthday'];
                        $existing = [];
                        foreach ($columns as $col) {
                            if (Schema::connection($conn)->hasColumn('users', $col)) {
                                $existing[] = $col;
                            }
                        }
                        if (!empty($existing)) {
                            $table->dropColumn($existing);
                        }
                    });
                }
            } catch (\Throwable $e) {}
        }
    }
};
