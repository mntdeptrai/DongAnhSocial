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
        if (!Schema::hasTable('stories')) {
            Schema::create('stories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('author_name')->nullable();
                $table->string('author_avatar')->nullable();
                $table->string('media_url')->nullable();
                $table->text('caption')->nullable();
                $table->string('bg_gradient')->nullable()->default('linear-gradient(135deg, #0ea5e9, #0284c7)');
                $table->string('type')->default('image'); // image, video, text
                $table->timestamps();

                $table->index('user_id');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
