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
        Schema::create('market_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('eatery_id'); // Market ID
            $table->unsignedBigInteger('user_id')->nullable(); // Logged-in user ID
            $table->string('sender_name'); // Display name
            $table->string('sender_role')->default('user'); // user, merchant, admin
            $table->string('stall_name')->nullable(); // Stall name if merchant
            $table->text('message_text');
            $table->unsignedBigInteger('product_id')->nullable(); // Optional OCOP product ID
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_messages');
    }
};
