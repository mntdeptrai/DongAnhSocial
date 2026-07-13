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
        if (!Schema::hasTable('cultural_activities')) {
            Schema::create('cultural_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('eatery_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('type')->nullable(); // 'experience', 'ticket', 'service', etc.
                $table->decimal('price', 12, 2)->nullable();
                $table->string('unit')->nullable(); // e.g. 'đoàn (10 người)', 'vé', 'lượt'
                $table->string('discount_note')->nullable(); // e.g. 'học sinh, người già giảm 50%'
                $table->text('description')->nullable();
                $table->string('image_path')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cultural_activities');
    }
};
