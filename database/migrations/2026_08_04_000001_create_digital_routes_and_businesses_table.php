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
        // 1. Table Digital Routes (Tuyến đường 4.0)
        Schema::create('digital_routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_key')->unique();
            $table->string('name');
            $table->string('village_key');
            $table->string('village_name');
            $table->string('length')->default('1.0km');
            $table->string('color')->default('#059669');
            $table->string('anim_class')->default('route-path-animated-1');
            $table->json('path_coords');
            $table->timestamps();
        });

        // 2. Table Route Businesses (Hộ kinh doanh trên tuyến đường 4.0)
        Schema::create('route_businesses', function (Blueprint $table) {
            $table->id();
            $table->string('route_key')->nullable()->index();
            $table->string('name');
            $table->string('owner')->nullable();
            $table->string('village_key');
            $table->string('village_name');
            $table->string('type')->default('dich-vu'); // quan-an, nha-hang, tap-hoa, thoi-trang, y-te, dich-vu
            $table->decimal('rating', 3, 1)->default(4.8);
            $table->text('address');
            $table->string('phone')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_name')->nullable();
            $table->boolean('is_open')->default(true);
            $table->json('menu')->nullable();
            $table->text('image_url')->nullable();
            $table->double('lat')->nullable();
            $table->double('lng')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_businesses');
        Schema::dropIfExists('digital_routes');
    }
};
