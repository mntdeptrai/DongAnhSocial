<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bảng giấy chứng nhận VSATTP
        Schema::create('food_safety_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eatery_id')->constrained()->onDelete('cascade');
            $table->string('certificate_number');
            $table->string('issued_by');
            $table->date('issued_at');
            $table->date('expired_at');
            $table->string('image_path');
            $table->string('status')->default('active'); // active, expired
            $table->timestamps();
        });

        // 2. Bảng hợp đồng cung cấp thực phẩm
        Schema::create('food_supply_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eatery_id')->constrained()->onDelete('cascade');
            $table->string('supplier_name');
            $table->string('items_supplied');
            $table->date('signed_at');
            $table->date('expired_at');
            $table->string('image_path');
            $table->timestamps();
        });

        // 3. Bảng hóa đơn mua thực phẩm
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eatery_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->nullable();
            $table->string('supplier_name');
            $table->date('invoice_date');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('items_summary');
            $table->string('image_path');
            $table->timestamps();
        });

        // 4. Bảng nhật ký thực phẩm hàng ngày
        Schema::create('daily_food_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eatery_id')->constrained()->onDelete('cascade');
            $table->date('log_date');
            $table->string('checker_name');
            $table->text('ingredients_origin');
            $table->string('storage_condition');
            $table->string('status')->default('compliant'); // compliant, non_compliant
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_food_logs');
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('food_supply_contracts');
        Schema::dropIfExists('food_safety_certificates');
    }
};
