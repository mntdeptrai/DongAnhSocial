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
            try {
                if (!Schema::connection($connection)->hasTable('posts')) {
                    Schema::connection($connection)->create('posts', function (Blueprint $table) {
                        $table->id();
                        $table->unsignedBigInteger('user_id')->index();
                        $table->unsignedBigInteger('eatery_id')->nullable()->index();
                        $table->string('name')->nullable();
                        $table->longText('description')->nullable();
                        $table->string('image_path')->nullable();
                        $table->text('images')->nullable();
                        $table->unsignedInteger('likes_count')->default(0);
                        $table->unsignedInteger('shares_count')->default(0);
                        $table->unsignedInteger('comments_count')->default(0);
                        $table->string('status')->default('active');
                        $table->timestamps();
                    });
                }
            } catch (\Exception $e) {
                // Ignore connection errors if database connection is shared or not separately configured
            }
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
                Schema::connection($connection)->dropIfExists('posts');
            } catch (\Exception $e) {
                // Ignore
            }
        }
    }
};
