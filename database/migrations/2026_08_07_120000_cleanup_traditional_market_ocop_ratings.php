<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

                // 1. Lấy danh sách ID gian hàng Chợ truyền thống (traditional-market) bằng PHP JSON decode
                $traditionalMarketEateryIds = [];
                if (Schema::connection($conn)->hasTable('eateries')) {
                    $eateries = DB::connection($conn)->table('eateries')->get();
                    $traditionalMarketEateryIds = $eateries->filter(function ($e) {
                        $catData = is_string($e->category) ? json_decode($e->category, true) : (array)$e->category;
                        $slug = $catData['slug'] ?? '';
                        $catName = mb_strtolower($catData['name'] ?? '');
                        $name = mb_strtolower($e->name ?? '');
                        return $slug === 'traditional-market' || str_contains($catName, 'chợ truyền thống') || str_starts_with($name, 'chợ ');
                    })->pluck('id')->toArray();
                }

                // 2. Lọc danh sách sản phẩm thuộc Chợ truyền thống / Chợ dân sinh (không phải sản phẩm OCOP) bằng PHP collection
                $products = DB::connection($conn)->table('ocop_products')->get();
                $idsToClean = $products->filter(function ($p) use ($traditionalMarketEateryIds) {
                    $pName  = mb_strtolower($p->name ?? '');
                    $sName  = mb_strtolower($p->stall_name ?? '');
                    $seller = mb_strtolower($p->seller_name ?? '');
                    $desc   = mb_strtolower($p->description ?? '');

                    // Kiểm tra chủ thể / chứng nhận OCOP
                    $isOcop = !empty($p->heritage_year) ||
                              str_contains($pName, 'ocop') || str_contains($sName, 'ocop') || str_contains($seller, 'ocop') || str_contains($desc, 'ocop') ||
                              str_contains($seller, 'htx') || str_contains($seller, 'hợp tác xã') || str_contains($sName, 'htx') || str_contains($sName, 'hợp tác xã') ||
                              str_contains($seller, 'hộ kinh doanh') || str_contains($seller, 'hkd') || str_contains($seller, 'công ty') || str_contains($seller, 'tnhh') || str_contains($seller, 'doanh nghiệp') ||
                              str_contains($desc, 'chủ thể') || str_contains($desc, 'qđ số') || str_contains($desc, 'quuyết định');

                    if ($isOcop) {
                        return false;
                    }

                    $isTraditionalMarket = in_array($p->eatery_id, $traditionalMarketEateryIds) ||
                                           str_starts_with($pName, 'bán rau') ||
                                           str_starts_with($pName, 'bán thịt') ||
                                           str_starts_with($pName, 'bán cá') ||
                                           str_starts_with($pName, 'bán đồ') ||
                                           str_starts_with($sName, 'gian hàng bán');

                    return $isTraditionalMarket;
                })->pluck('id')->toArray();

                // 3. Cập nhật star_rating = null bằng query whereIn theo ID
                if (!empty($idsToClean)) {
                    DB::connection($conn)->table('ocop_products')
                        ->whereIn('id', $idsToClean)
                        ->update(['star_rating' => null]);
                }

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
        // No rollback needed
    }
};
