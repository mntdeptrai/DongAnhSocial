<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Eatery;
use App\Models\OcopProduct;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BusinessProductController extends Controller
{
    /**
     * Display the dedicated detail page for a product of a business establishment (Dish/Product).
     */
    public function show($slugOrId)
    {
        $product = null;

        // 1. Tìm sản phẩm theo slug hoặc ID
        if (is_numeric($slugOrId)) {
            $product = OcopProduct::with(['eatery.commune', 'eatery.category', 'eatery.foodSafetyCertificate', 'eatery.reviews'])
                ->find($slugOrId)
                ?: Dish::with(['eatery.commune', 'eatery.category', 'eatery.foodSafetyCertificate', 'eatery.reviews'])
                ->find($slugOrId);
        } else {
            $product = OcopProduct::with(['eatery.commune', 'eatery.category', 'eatery.foodSafetyCertificate', 'eatery.reviews'])
                ->where('slug', $slugOrId)
                ->first();

            if (!$product) {
                // Tách ID ở đuôi slug nếu truy cập bằng URL cũ dạng "tom-2696"
                if (preg_match('/-(\d+)$/', $slugOrId, $matches)) {
                    $product = OcopProduct::with(['eatery.commune', 'eatery.category', 'eatery.foodSafetyCertificate', 'eatery.reviews'])->find($matches[1])
                            ?: Dish::with(['eatery.commune', 'eatery.category', 'eatery.foodSafetyCertificate', 'eatery.reviews'])->find($matches[1]);
                }
            }

            if (!$product) {
                // Fallback: Tìm theo tên sản phẩm dạng slug
                $allProducts = OcopProduct::with(['eatery.commune', 'eatery.category', 'eatery.foodSafetyCertificate', 'eatery.reviews'])->get();
                $product = $allProducts->first(function($p) use ($slugOrId) {
                    return Str::slug($p->name) === $slugOrId;
                });
            }
        }

        if (!$product) {
            abort(404, 'Không tìm thấy sản phẩm này.');
        }

        // 2. Chuyển hướng nếu sản phẩm có sao chứng nhận OCOP (sang route OCOP chuẩn)
        if (!empty($product->star_rating)) {
            return redirect()->route('ocop.product.show', $product->slug ?: $product->id, 301);
        }

        // 3. Chuẩn hóa Slug thuần (KHÔNG kèm ID số): Tự động tạo & lưu slug đẹp nếu chưa có
        if (empty($product->slug)) {
            $baseSlug = Str::slug($product->name);
            $product->slug = $baseSlug;
            try { $product->save(); } catch (\Throwable $e) {}
        }

        $canonicalSlug = $product->slug ?: Str::slug($product->name);

        // Nếu người dùng truy cập bằng ID số hoặc slug cũ dính ID số (-2696), Redirect 301 sang Slug thuần chuẩn SEO (/san-pham/tom)
        if (is_numeric($slugOrId) || ($slugOrId !== $canonicalSlug && preg_match('/-\d+$/', $slugOrId))) {
            return redirect()->route('business.product.show', $canonicalSlug, 301);
        }

        $eatery = $product->eatery;

        // 4. Tối ưu truy vấn các sản phẩm khác cùng cơ sở kinh doanh (sử dụng Index eatery_id)
        $otherEstablishmentProducts = collect();
        if ($eatery) {
            $otherEstablishmentProducts = OcopProduct::where('eatery_id', $eatery->id)
                ->where('id', '!=', $product->id)
                ->take(6)
                ->get();

            if ($otherEstablishmentProducts->count() < 6) {
                $moreDishes = Dish::where('eatery_id', $eatery->id)
                    ->where('id', '!=', $product->id)
                    ->take(6 - $otherEstablishmentProducts->count())
                    ->get();
                $otherEstablishmentProducts = $otherEstablishmentProducts->concat($moreDishes);
            }
        }

        // 5. Tối ưu truy vấn các sản phẩm liên quan cùng xã/huyện
        $relatedProducts = collect();
        if ($eatery && $eatery->commune_id) {
            $relatedProducts = OcopProduct::where('id', '!=', $product->id)
                ->where('eatery_id', '!=', $eatery->id)
                ->whereHas('eatery', function ($q) use ($eatery) {
                    $q->where('commune_id', $eatery->commune_id);
                })
                ->take(6)
                ->get();
        }

        // 6. Xử lý thông tin thanh toán VietQR (Chỉ tạo khi cơ sở có khai báo tài khoản ngân hàng thực tế trong DB)
        $sellerPhone = $eatery?->phone ?: '';
        $bankName   = $eatery?->storytelling_data['bank_name'] ?? null;
        $bankAcct   = $eatery?->storytelling_data['bank_account'] ?? null;
        $bankHolder = $eatery?->storytelling_data['bank_holder'] ?? ($eatery?->name ? mb_strtoupper($eatery->name) : null);

        $qrCodeUrl = null;
        if (!empty($bankName) && !empty($bankAcct)) {
            $bankCodeMap = [
                'MBBANK' => 'MB', 'MB' => 'MB', 'VIETCOMBANK' => 'VCB', 'VCB' => 'VCB',
                'AGRIBANK' => 'VBA', 'VBA' => 'VBA', 'TECHCOMBANK' => 'TCB', 'TCB' => 'TCB',
                'BIDV' => 'BIDV', 'VPBANK' => 'VPB', 'VPB' => 'VPB', 'VIETINBANK' => 'CTG',
            ];
            $bankCode = $bankCodeMap[strtoupper($bankName)] ?? 'MB';
            $qrCodeUrl = "https://img.vietqr.io/image/{$bankCode}-{$bankAcct}-compact.png?accountName=" . urlencode($bankHolder) . "&addInfo=" . urlencode("Dat mua " . Str::limit($product->name, 20, ''));
        }

        // 7. Load đánh giá cơ sở
        $reviews = $eatery ? $eatery->reviews()->take(10)->get() : collect();
        $avgRating = $eatery ? $eatery->average_rating : 5.0;

        return view('product-detail', compact(
            'product',
            'eatery',
            'otherEstablishmentProducts',
            'relatedProducts',
            'sellerPhone',
            'bankName',
            'bankAcct',
            'bankHolder',
            'qrCodeUrl',
            'reviews',
            'avgRating'
        ));
    }
}
