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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'cancel_reason')) {
                $table->string('cancel_reason')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('orders', 'cancelled_by')) {
                $table->string('cancelled_by')->nullable()->after('cancel_reason');
            }
            if (!Schema::hasColumn('orders', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('cancelled_by');
            }
            if (!Schema::hasColumn('orders', 'preparing_at')) {
                $table->timestamp('preparing_at')->nullable()->after('confirmed_at');
            }
            if (!Schema::hasColumn('orders', 'ready_at')) {
                $table->timestamp('ready_at')->nullable()->after('preparing_at');
            }
            if (!Schema::hasColumn('orders', 'shipping_at')) {
                $table->timestamp('shipping_at')->nullable()->after('ready_at');
            }
            if (!Schema::hasColumn('orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('shipping_at');
            }
            if (!Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = ['cancel_reason', 'cancelled_by', 'confirmed_at', 'preparing_at', 'ready_at', 'shipping_at', 'completed_at', 'cancelled_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
