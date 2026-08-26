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
        if (!Schema::hasTable('live_stream_products')) {
            Schema::create('live_stream_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('cascade');
                $table->foreignId('ocop_product_id')->constrained('ocop_products')->onDelete('cascade');
                $table->boolean('is_pinned')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['live_stream_id', 'ocop_product_id'], 'stream_product_unique');
                $table->index(['live_stream_id', 'is_pinned']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_stream_products');
    }
};
