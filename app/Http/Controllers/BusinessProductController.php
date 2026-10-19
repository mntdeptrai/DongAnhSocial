<?php

namespace App\Http\Controllers;

use App\Contracts\OcopProductServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class BusinessProductController extends Controller
{
    public function __construct(
        protected OcopProductServiceInterface $productService
    ) {}

    /**
     * Display the dedicated detail page for a product of a business establishment (Dish/Product).
     */
    public function show(string|int $slugOrId): View|RedirectResponse
    {
        $product = $this->productService->resolveProduct($slugOrId);

        if (!$product) {
            abort(404, 'Không tìm thấy sản phẩm này.');
        }

        // Chuyển hướng nếu sản phẩm có sao chứng nhận OCOP
        $categorySlug = $product->eatery?->category?->slug;
        if (!empty($product->star_rating) && $categorySlug !== 'co-so-kinh-doanh') {
            return redirect()->route('ocop.product.show', $product->slug ?: $product->id, 301);
        }

        // Đảm bảo canonical slug đẹp không kèm ID số
        if (empty($product->slug)) {
            $product->slug = Str::slug($product->name);
            try { $product->save(); } catch (\Throwable $e) {}
        }

        $canonicalSlug = $product->slug ?: Str::slug($product->name);

        if (is_numeric($slugOrId) || ($slugOrId !== $canonicalSlug && preg_match('/-\d+$/', (string) $slugOrId))) {
            return redirect()->route('business.product.show', $canonicalSlug, 301);
        }

        $eatery = $product->eatery;
        $otherEstablishmentProducts = $this->productService->getEstablishmentProducts($product, 6);
        $relatedProducts = $this->productService->getRelatedProducts($product, 6);

        // Reviews & Ratings
        $reviews = $product->reviews ?? collect();
        $averageRating = $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : null;
        $totalReviews = $reviews->count();

        return view('business-product-detail', compact(
            'product',
            'eatery',
            'otherEstablishmentProducts',
            'relatedProducts',
            'reviews',
            'averageRating',
            'totalReviews'
        ));
    }
}
