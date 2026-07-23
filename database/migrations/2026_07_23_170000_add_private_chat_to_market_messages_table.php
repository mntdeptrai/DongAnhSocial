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
        Schema::table('market_messages', function (Blueprint $table) {
            $table->string('private_stall_name')->nullable()->after('product_id'); // Target stall name for private chat
            $table->unsignedBigInteger('private_user_id')->nullable()->after('private_stall_name'); // Target user ID for private replies
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_messages', function (Blueprint $table) {
            $table->dropColumn(['private_stall_name', 'private_user_id']);
        });
    }
};
