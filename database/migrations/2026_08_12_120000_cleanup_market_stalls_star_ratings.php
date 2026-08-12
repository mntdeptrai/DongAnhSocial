<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connections = ['mysql', 'mysql_market'];

        foreach ($connections as $conn) {
            try {
                if (!Schema::connection($conn)->hasTable('ocop_products')) {
                    continue;
                }

                // Cập nhật tất cả các sản phẩm chợ dân sinh/chợ truyền thống không có chứng nhận OCOP chuẩn
                DB::connection($conn)->table('ocop_products')
                    ->where(function ($query) {
                        $query->whereNull('heritage_year')->orWhere('heritage_year', '');
                    })
                    ->whereRaw("LOWER(COALESCE(name, '')) NOT LIKE '%ocop%'")
                    ->whereRaw("LOWER(COALESCE(stall_name, '')) NOT LIKE '%ocop%'")
                    ->whereRaw("LOWER(COALESCE(seller_name, '')) NOT LIKE '%htx%'")
                    ->whereRaw("LOWER(COALESCE(seller_name, '')) NOT LIKE '%hợp tác xã%'")
                    ->whereRaw("LOWER(COALESCE(stall_name, '')) NOT LIKE '%htx%'")
                    ->whereRaw("LOWER(COALESCE(stall_name, '')) NOT LIKE '%hợp tác xã%'")
                    ->whereRaw("LOWER(COALESCE(description, '')) NOT LIKE '%chủ thể%'")
                    ->whereRaw("LOWER(COALESCE(description, '')) NOT LIKE '%qđ số%'")
                    ->whereRaw("LOWER(COALESCE(description, '')) NOT LIKE '%quuyết định%'")
                    ->update(['star_rating' => null]);

            } catch (\Throwable $e) {
                // Skip connection if not available
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cleanup migration reversal not required
    }
};
