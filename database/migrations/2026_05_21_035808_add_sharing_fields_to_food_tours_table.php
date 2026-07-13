<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('food_tours', function (Blueprint $table) {
            // Thời điểm user cho phép chia sẻ lên cộng đồng (null = riêng tư)
            $table->timestamp('shared_at')->nullable()->after('is_ai_generated');
            // Thời điểm hết hiệu lực công khai (shared_at + 72h)
            $table->timestamp('expires_at')->nullable()->after('shared_at');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('food_tours', function (Blueprint $table) {
            $table->dropIndex(['expires_at']);
            $table->dropColumn(['shared_at', 'expires_at']);
        });
    }
};
