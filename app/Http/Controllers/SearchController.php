<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use Illuminate\Http\Request;

use App\Services\EateryApiService;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $categories = EateryApiService::getCategories();
        $communes = EateryApiService::getCommunes();
        
        $keyword = $request->query('q');
        $catId = $request->query('category_id');
        $comId = $request->query('commune_id');
        
        $selectedCategorySlug = null;
        if ($catId) {
            $category = $categories->firstWhere('id', $catId);
            if ($category) {
                $selectedCategorySlug = $category->slug;
            }
        }
        
        // Tìm địa điểm thông qua API Service
        $eateries = EateryApiService::getEateries($selectedCategorySlug, [
            'commune_id' => $comId,
            'q' => $keyword
        ]);
        
        // API phục vụ tính năng tự động gợi ý (Autocomplete Suggestions) khi gõ ô tìm kiếm
        if ($request->query('ajax') === 'suggest' && $keyword) {
            $suggestions = EateryApiService::getEateries(null, ['q' => $keyword])
                ->take(6)
                ->map(function($e) {
                    return [
                        'id' => $e->id,
                        'name' => $e->name,
                        'slug' => $e->slug,
                        'address' => $e->address
                    ];
                });
            return response()->json($suggestions);
        }
        
        // Trả về JSON nếu yêu cầu API (cập nhật marker bản đồ thời gian thực)
        if ($request->expectsJson() || $request->query('json') === '1') {
            return response()->json($eateries);
        }
        
        return view('search', compact('categories', 'communes', 'eateries', 'keyword', 'catId', 'comId'));
    }
}
