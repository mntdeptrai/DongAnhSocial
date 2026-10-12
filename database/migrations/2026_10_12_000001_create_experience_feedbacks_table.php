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
        if (!Schema::hasTable('experience_feedbacks')) {
            Schema::create('experience_feedbacks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('author_name');
                $table->string('activity_name');
                $table->unsignedTinyInteger('rating')->default(5);
                $table->text('comment');
                $table->string('image_url', 500)->nullable();
                $table->boolean('is_verified')->default(true);
                $table->unsignedInteger('helpful_count')->default(0);
                $table->json('host_reply')->nullable();
                $table->string('status', 20)->default('approved'); // 'approved', 'pending', 'hidden'
                $table->timestamps();

                // Performance indexes for fast querying and filtering
                $table->index(['status', 'created_at']);
                $table->index(['activity_name', 'status']);
                $table->index(['rating', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experience_feedbacks');
    }
};
