<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng checkins — Check-in độc lập từng địa điểm, không cần Food Tour
     */
    public function up(): void
    {
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('eatery_id')->nullable()->constrained()->onDelete('set null');
            $table->string('guest_name')->nullable();       // Tên khách nếu chưa đăng nhập
            $table->integer('rating')->default(5);          // Số sao 1-5
            $table->text('comment')->nullable();            // Cảm nhận
            $table->string('image_path')->nullable();       // Ảnh check-in chính
            $table->string('status')->default('published'); // published | hidden
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
