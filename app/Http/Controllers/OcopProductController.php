<?php

namespace App\Http\Controllers;

use App\Contracts\OcopProductServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OcopProductController extends Controller
{
    public function __construct(
        protected OcopProductServiceInterface $productService
    ) {}

    /**
     * Display the dedicated detail page for a specific OCOP product.
     */
    public function show(string|int $slugOrId): View|RedirectResponse
    {
        $product = $this->productService->resolveProduct($slugOrId);

        if (!$product) {
            abort(404, 'Không tìm thấy sản phẩm OCOP này.');
        }

        // Canonical 301 redirect if accessed via numeric ID and slug is present
        if (is_numeric($slugOrId) && !empty($product->slug)) {
            return redirect()->route('ocop.product.show', $product->slug, 301);
        }

        // Chuyển hướng sang cơ sở kinh doanh nếu không phải sản phẩm có sao OCOP
        $categorySlug = $product->eatery?->category?->slug;
        if (empty($product->star_rating) || $categorySlug === 'co-so-kinh-doanh') {
            return redirect()->route('business.product.show', $product->slug ?: $product->id, 301);
        }

        $eatery = $product->eatery;
        $relatedProducts = $this->productService->getRelatedProducts($product, 6);

        return view('ocop-detail', compact('product', 'eatery', 'relatedProducts'));
    }
}
