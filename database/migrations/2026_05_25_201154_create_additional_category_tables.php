<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = Schema::getConnection()->getName();

        // 1. Rooms for Stay in Đông Anh
        if (($connection === 'mysql_stay' || $connection === 'mysql') && !Schema::hasTable('rooms')) {
            Schema::create('rooms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('eatery_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->decimal('price', 12, 2);
                $table->string('description')->nullable();
                $table->string('image_path')->nullable();
                $table->string('bed_type')->nullable(); // Standard, Double, King...
                $table->integer('capacity')->default(2);
                $table->timestamps();
            });
        }

        // 2. Wellness Services for Wellness & Care
        if (($connection === 'mysql_wellness' || $connection === 'mysql') && !Schema::hasTable('wellness_services')) {
            Schema::create('wellness_services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('eatery_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->decimal('price', 12, 2)->nullable();
                $table->string('description')->nullable();
                $table->string('image_path')->nullable();
                $table->string('duration')->nullable(); // e.g. "60 phút"
                $table->timestamps();
            });
        }

        // 3. OCOP Products for Đông Anh Market
        if (($connection === 'mysql_market' || $connection === 'mysql') && !Schema::hasTable('ocop_products')) {
            Schema::create('ocop_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('eatery_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->decimal('price', 12, 2)->nullable();
                $table->string('description')->nullable();
                $table->string('image_path')->nullable();
                $table->string('star_rating')->nullable(); // e.g. "3 sao", "4 sao"
                $table->timestamps();
            });
        }

        // 4. Education Programs for Smart Education Map
        if (($connection === 'mysql_education' || $connection === 'mysql') && !Schema::hasTable('education_programs')) {
            Schema::create('education_programs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('eatery_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('description')->nullable();
                $table->string('image_path')->nullable();
                $table->string('duration')->nullable(); // e.g. "3 năm", "12 tháng"
                $table->string('tuition_fee')->nullable(); // e.g. "Học phí công lập", "500.000đ/tháng"
                $table->timestamps();
            });
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('education_programs');
        Schema::dropIfExists('ocop_products');
        Schema::dropIfExists('wellness_services');
        Schema::dropIfExists('rooms');
    }
};
