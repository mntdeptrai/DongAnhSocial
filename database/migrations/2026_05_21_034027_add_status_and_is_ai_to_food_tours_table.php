<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_tours', function (Blueprint $table) {
            // 'saved' = tour chính thức (Admin tạo hoặc user đã chủ động lưu)
            // 'draft' = tour AI tạo tạm, chờ user xác nhận lưu
            $table->string('status')->default('saved')->after('story');
            $table->boolean('is_ai_generated')->default(false)->after('status');
            $table->index('status');
            $table->index('is_ai_generated');
        });
    }

    public function down(): void
    {
        Schema::table('food_tours', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['is_ai_generated']);
            $table->dropColumn(['status', 'is_ai_generated']);
        });
    }
};
