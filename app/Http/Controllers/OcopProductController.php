<?php

namespace App\Http\Controllers;

use App\Models\OcopProduct;
use Illuminate\Http\Request;

class OcopProductController extends Controller
{
    /**
     * Display the dedicated detail page for a specific OCOP product
     */
    public function show($id)
    {
        $product = OcopProduct::with(['eatery.commune', 'eatery.category'])->findOrFail($id);
        $eatery = $product->eatery;
        
        // Get related OCOP products from the same seller or commune
        $relatedProducts = OcopProduct::where('id', '!=', $product->id)
            ->where(function($q) use ($product, $eatery) {
                if ($eatery) {
                    $q->where('eatery_id', $eatery->id);
                }
            })
            ->take(6)
            ->get();

        if ($relatedProducts->count() < 4 && $eatery && $eatery->commune_id) {
            $moreProducts = OcopProduct::where('id', '!=', $product->id)
                ->whereHas('eatery', function($q) use ($eatery) {
                    $q->where('commune_id', $eatery->commune_id);
                })
                ->whereNotIn('id', $relatedProducts->pluck('id'))
                ->take(6 - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->concat($moreProducts);
        }

        return view('ocop-detail', compact('product', 'eatery', 'relatedProducts'));
    }
}
