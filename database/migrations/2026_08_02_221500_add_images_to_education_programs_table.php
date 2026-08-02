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

        foreach ($connections as $connection) {
            try {
                if (Schema::connection($connection)->hasTable('education_programs')) {
                    Schema::connection($connection)->table('education_programs', function (Blueprint $table) use ($connection) {
                        if (!Schema::connection($connection)->hasColumn('education_programs', 'images')) {
                            $table->text('images')->nullable()->after('image_path');
                        }
                        if (!Schema::connection($connection)->hasColumn('education_programs', 'likes_count')) {
                            $table->unsignedInteger('likes_count')->default(0)->after('images');
                        }
                        if (!Schema::connection($connection)->hasColumn('education_programs', 'shares_count')) {
                            $table->unsignedInteger('shares_count')->default(0)->after('likes_count');
                        }
                    });
                }
            } catch (\Exception $e) {
                // Ignore connection error if mysql_education is not separately configured
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = ['mysql_education', 'mysql'];

        foreach ($connections as $connection) {
            try {
                if (Schema::connection($connection)->hasTable('education_programs')) {
                    Schema::connection($connection)->table('education_programs', function (Blueprint $table) use ($connection) {
                        $columnsToDrop = [];
                        if (Schema::connection($connection)->hasColumn('education_programs', 'images')) {
                            $columnsToDrop[] = 'images';
                        }
                        if (Schema::connection($connection)->hasColumn('education_programs', 'likes_count')) {
                            $columnsToDrop[] = 'likes_count';
                        }
                        if (Schema::connection($connection)->hasColumn('education_programs', 'shares_count')) {
                            $columnsToDrop[] = 'shares_count';
                        }
                        if (!empty($columnsToDrop)) {
                            $table->dropColumn($columnsToDrop);
                        }
                    });
                }
            } catch (\Exception $e) {
                // Ignore
            }
        }
    }
};
