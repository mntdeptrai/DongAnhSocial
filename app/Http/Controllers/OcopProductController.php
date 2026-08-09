<?php

namespace App\Http\Controllers;

use App\Models\OcopProduct;
use Illuminate\Http\Request;

class OcopProductController extends Controller
{
    /**
     * Display the dedicated detail page for a specific OCOP product
     */
    public function show($slugOrId)
    {
        // 1. Tìm theo Slug sản phẩm (URL thân thiện SEO)
        $product = OcopProduct::with(['eatery.commune', 'eatery.category', 'eatery.foodSafetyCertificate'])
            ->where('slug', $slugOrId)
            ->first();

        // 2. Nếu không thấy slug và tham số là ID số -> Tìm theo ID và chuyển hướng 301 về URL Slug chuẩn
        if (!$product && is_numeric($slugOrId)) {
            $product = OcopProduct::with(['eatery.commune', 'eatery.category', 'eatery.foodSafetyCertificate'])->find($slugOrId);
            if ($product && !empty($product->slug)) {
                return redirect()->route('ocop.product.show', $product->slug, 301);
            }
        }

        if (!$product) {
            // Fallback check in Dish model
            $dish = \App\Models\Dish::find($slugOrId);
            if ($dish) {
                return redirect()->route('business.product.show', $dish->id, 301);
            }
            abort(404);
        }

        // Tự động chuyển hướng nếu sản phẩm không phải OCOP chứng nhận (sản phẩm cơ sở kinh doanh/chợ)
        // Chuyển từ /san-pham-ocop/{id} sang đường dẫn chuẩn Cơ sở kinh doanh: /san-pham/{id}
        $categorySlug = $product->eatery?->category?->slug;
        if (empty($product->star_rating) || $categorySlug === 'co-so-kinh-doanh') {
            return redirect()->route('business.product.show', $product->id, 301);
        }

        $eatery = $product->eatery;
        
        // Get related products (Prioritize certified OCOP items if current product is OCOP certified)
        $isOcop = !empty($product->star_rating);

        $query = OcopProduct::where('id', '!=', $product->id);
        if ($eatery) {
            $query->where('eatery_id', $eatery->id);
        }
        if ($isOcop) {
            $query->orderByRaw('star_rating IS NULL, star_rating DESC');
        }

        $relatedProducts = $query->take(6)->get();

        if ($relatedProducts->count() < 6 && $eatery && $eatery->commune_id) {
            $moreQuery = OcopProduct::where('id', '!=', $product->id)
                ->whereHas('eatery', function($q) use ($eatery) {
                    $q->where('commune_id', $eatery->commune_id);
                })
                ->whereNotIn('id', $relatedProducts->pluck('id'));

            if ($isOcop) {
                $moreQuery->orderByRaw('star_rating IS NULL, star_rating DESC');
            }

            $moreProducts = $moreQuery->take(6 - $relatedProducts->count())->get();
            $relatedProducts = $relatedProducts->concat($moreProducts);
        }

        return view('ocop-detail', compact('product', 'eatery', 'relatedProducts'));
    }
}
