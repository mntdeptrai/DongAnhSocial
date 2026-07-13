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
        if (Schema::getConnection()->getName() !== 'mysql') {
            return;
        }

        Schema::table('food_tour_diaries', function (Blueprint $table) {
            $table->integer('rating')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_tour_diaries', function (Blueprint $table) {
            $table->integer('rating')->nullable(false)->default(5)->change();
        });
    }
};
