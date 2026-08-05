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
            // 1. Add videos & video_path to posts table if exists
            try {
                if (Schema::connection($connection)->hasTable('posts')) {
                    Schema::connection($connection)->table('posts', function (Blueprint $table) use ($connection) {
                        if (!Schema::connection($connection)->hasColumn('posts', 'video_path')) {
                            $table->string('video_path')->nullable()->after('image_path');
                        }
                        if (!Schema::connection($connection)->hasColumn('posts', 'videos')) {
                            $table->longText('videos')->nullable()->after('images');
                        }
                    });
                }
            } catch (\Exception $e) {}

            // 2. Add videos & video_path to education_programs table if exists
            try {
                if (Schema::connection($connection)->hasTable('education_programs')) {
                    Schema::connection($connection)->table('education_programs', function (Blueprint $table) use ($connection) {
                        if (!Schema::connection($connection)->hasColumn('education_programs', 'video_path')) {
                            $table->string('video_path')->nullable()->after('image_path');
                        }
                        if (!Schema::connection($connection)->hasColumn('education_programs', 'videos')) {
                            $table->longText('videos')->nullable()->after('images');
                        }
                    });
                }
            } catch (\Exception $e) {}
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
                if (Schema::connection($connection)->hasTable('posts')) {
                    Schema::connection($connection)->table('posts', function (Blueprint $table) use ($connection) {
                        if (Schema::connection($connection)->hasColumn('posts', 'video_path')) {
                            $table->dropColumn('video_path');
                        }
                        if (Schema::connection($connection)->hasColumn('posts', 'videos')) {
                            $table->dropColumn('videos');
                        }
                    });
                }
            } catch (\Exception $e) {}

            try {
                if (Schema::connection($connection)->hasTable('education_programs')) {
                    Schema::connection($connection)->table('education_programs', function (Blueprint $table) use ($connection) {
                        if (Schema::connection($connection)->hasColumn('education_programs', 'video_path')) {
                            $table->dropColumn('video_path');
                        }
                        if (Schema::connection($connection)->hasColumn('education_programs', 'videos')) {
                            $table->dropColumn('videos');
                        }
                    });
                }
            } catch (\Exception $e) {}
        }
    }
};
