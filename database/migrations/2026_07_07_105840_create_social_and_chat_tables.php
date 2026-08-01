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
        // 1. Bảng lưu trạng thái kết bạn
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('friend_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('pending'); // 'pending', 'accepted', 'declined'
            $table->timestamps();

            $table->unique(['user_id', 'friend_id']);
        });

        // 2. Bảng lưu tin nhắn Realtime
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // 3. Thêm vị trí địa lý (GPS) trực tiếp vào bảng users để phục vụ "tìm người ở gần"
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('status');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->timestamp('last_active_at')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'last_active_at']);
        });

        Schema::dropIfExists('messages');
        Schema::dropIfExists('friendships');
    }
};

