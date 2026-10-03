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
        $connections = ['mysql', 'mysql_education', 'sqlite'];

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('live_streams')) {
                    Schema::connection($conn)->table('live_streams', function (Blueprint $table) {
                        if (!Schema::hasColumn('live_streams', 'recording_url')) {
                            $table->text('recording_url')->nullable()->after('cover_image');
                        }
                        if (!Schema::hasColumn('live_streams', 'youtube_video_id')) {
                            $table->string('youtube_video_id', 64)->nullable()->after('recording_url');
                        }
                    });
                }
            } catch (\Throwable $ex) {
                // Ignore if connection or column already exists
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = ['mysql', 'mysql_education', 'sqlite'];

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('live_streams')) {
                    Schema::connection($conn)->table('live_streams', function (Blueprint $table) {
                        if (Schema::hasColumn('live_streams', 'youtube_video_id')) {
                            $table->dropColumn('youtube_video_id');
                        }
                        if (Schema::hasColumn('live_streams', 'recording_url')) {
                            $table->dropColumn('recording_url');
                        }
                    });
                }
            } catch (\Throwable $ex) {}
        }
    }
};
