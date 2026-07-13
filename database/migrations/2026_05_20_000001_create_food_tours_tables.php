<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_tours', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('duration');       // e.g. "2.5 giờ"
            $table->string('distance');       // e.g. "4.8 km"
            $table->string('budget');         // e.g. "250.000đ"
            $table->string('difficulty');     // e.g. "🔥 Ăn no", "☕ Chill", "🏛 Văn hóa", "🌙 Ăn đêm"
            $table->string('best_time');      // e.g. "18:00 - 22:00"
            $table->string('popularity')->default('Cao');
            $table->string('mood')->default('chill'); // chill, night, cheap, specialty, date
            $table->string('thumbnail')->nullable();
            $table->text('story')->nullable(); // Rich description storytelling
            $table->timestamps();

            $table->index('slug');
            $table->index('mood');
        });

        Schema::create('food_tour_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_tour_id')->constrained()->onDelete('cascade');
            $table->foreignId('eatery_id')->constrained()->onDelete('cascade');
            $table->integer('stop_order')->default(1);
            $table->text('stop_story')->nullable(); // Unique storytelling for this eatery inside this tour
            $table->string('estimated_time')->nullable(); // e.g. "45 phút"
            $table->timestamps();

            $table->index(['food_tour_id', 'stop_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_tour_stops');
        Schema::dropIfExists('food_tours');
    }
};
