<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_tour_diaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_tour_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('rating')->default(5);
            $table->text('comment')->nullable();
            $table->string('image_path')->nullable();
            $table->json('completed_stops')->nullable();
            $table->json('stop_reviews')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_tour_diaries');
    }
};
