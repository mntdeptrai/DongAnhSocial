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
        if (!Schema::hasTable('live_streams')) {
            Schema::create('live_streams', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('cover_image')->nullable();
                $table->enum('status', ['scheduled', 'live', 'ended'])->default('live');
                $table->string('category')->default('general'); // ocop, food, travel, culture, general
                $table->foreignId('pinned_product_id')->nullable()->constrained('ocop_products')->nullOnDelete();
                $table->unsignedInteger('viewer_count')->default(0);
                $table->unsignedInteger('peak_viewers')->default(0);
                $table->unsignedInteger('likes_count')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
                $table->index('user_id');
            });
        }

        if (!Schema::hasTable('live_stream_comments')) {
            Schema::create('live_stream_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('message');
                $table->timestamps();

                $table->index(['live_stream_id', 'created_at']);
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_stream_comments');
        Schema::dropIfExists('live_streams');
    }
};
