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
        Schema::table('reviews', function (Blueprint $table) {
            $table->integer('rating')->nullable()->change();
            $table->text('comment')->nullable()->change();
        });

        Schema::create('review_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_type')->default('image'); // image, video
            $table->timestamps();

            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_media');

        Schema::table('reviews', function (Blueprint $table) {
            $table->integer('rating')->nullable(false)->change();
            $table->text('comment')->nullable(false)->change();
        });
    }
};
