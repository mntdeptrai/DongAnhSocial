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
        // 1. Carts table
        if (!Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('session_id')->nullable();
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('session_id');
            });
        }

        // 2. Cart Items table
        if (!Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cart_id');
                $table->unsignedBigInteger('dish_id')->nullable();
                $table->unsignedBigInteger('ocop_product_id')->nullable();
                $table->integer('quantity')->default(1);
                $table->timestamps();

                $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
                $table->index('dish_id');
                $table->index('ocop_product_id');
            });
        }

        // 3. Orders table
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('eatery_id'); // ID of restaurant or market eatery
                $table->string('category_slug'); // dong-anh-food-map / dong-anh-market
                $table->string('customer_name');
                $table->string('customer_phone');
                $table->text('shipping_address');
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->string('payment_method')->default('COD'); // COD or Online
                $table->string('status')->default('pending'); // pending, paid, processing, completed, cancelled
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('eatery_id');
                $table->index('status');
            });
        }

        // 4. Order Items table
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('dish_id')->nullable();
                $table->unsignedBigInteger('ocop_product_id')->nullable();
                $table->string('name');
                $table->decimal('price', 12, 2);
                $table->integer('quantity')->default(1);
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            });
        }

        // 5. Payments table
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->string('method'); // COD or Online
                $table->decimal('amount', 12, 2);
                $table->string('status')->default('pending'); // pending, success, failed
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
