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
        Schema::create('checkin_reactions', function (Blueprint $table) {
            $table->id();
            $table->string('reactionable_type'); // 'checkin' hoặc 'diary'
            $table->unsignedBigInteger('reactionable_id');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->string('emoji'); // ❤️, 🔥, 👍, 😂, 😍, 🤤
            $table->timestamps();

            $table->index(['reactionable_type', 'reactionable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkin_reactions');
    }
};
