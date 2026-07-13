<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eatery_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('video_url');
            $table->string('video_type')->default('local'); // local, tiktok, youtube_shorts
            $table->string('thumbnail_path')->nullable();
            $table->integer('likes_count')->default(0);
            $table->string('status')->default('pending'); // approved, pending, rejected
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('video_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_videos');
    }
};
