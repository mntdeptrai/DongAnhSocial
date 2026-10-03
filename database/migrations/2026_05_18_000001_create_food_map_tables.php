<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('name');
        });

        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();

            // Indexes
            $table->index('name');
        });

        Schema::create('eateries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('commune_id')->constrained()->onDelete('cascade');
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('phone')->nullable();
            $table->string('opening_hours')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->string('price_range')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->string('status')->default('active'); // active, inactive
            $table->json('announcements')->nullable();
            $table->timestamps();


            // Indexes
            $table->index('name');
            $table->index('is_featured');
            $table->index('status');
            $table->index(['latitude', 'longitude']); // Composite index for ultra fast geo-location
        });

        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eatery_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_signature')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('name');
            $table->index('is_signature');
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eatery_id')->constrained()->onDelete('cascade');
            $table->string('user_name');
            $table->integer('rating');
            $table->text('comment');
            $table->timestamps();

            // Indexes
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('dishes');
        Schema::dropIfExists('eateries');
        Schema::dropIfExists('communes');
        Schema::dropIfExists('categories');
    }
};
