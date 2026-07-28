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

    public function quickSearch(Request $request)
    {
        $q = trim($request->query('q', ''));
        $cat = $request->query('cat', 'all');

        $query = Eatery::query();

        if ($cat === 'truong-hoc') {
            $query->where(function($b) {
                $b->where('name', 'LIKE', '%Trường%')
                  ->orWhere('name', 'LIKE', '%Mầm non%')
                  ->orWhere('name', 'LIKE', '%Tiểu học%')
                  ->orWhere('name', 'LIKE', '%THCS%');
            });
        } elseif ($cat === 'food') {
            $query->where('name', 'NOT LIKE', '%Trường%')
                  ->where('name', 'NOT LIKE', '%Mầm non%')
                  ->where('name', 'NOT LIKE', '%Tiểu học%')
                  ->where('name', 'NOT LIKE', '%THCS%');
        } elseif ($cat === 'y-te') {
            $query->where(function($b) {
                $b->where('name', 'LIKE', '%Bệnh viện%')
                  ->orWhere('name', 'LIKE', '%Y tế%')
                  ->orWhere('name', 'LIKE', '%VNVC%');
            });
        } elseif ($cat === 'cho') {
            $query->where('name', 'LIKE', '%Chợ%');
        }

        if ($q) {
            $query->where(function($b) use ($q) {
                $b->where('name', 'LIKE', "%{$q}%")
                  ->orWhere('address', 'LIKE', "%{$q}%")
                  ->orWhere('slug', 'LIKE', "%{$q}%");
            });
        }

        $results = $query->take(8)->get()->map(function($item) {
            $isSchool = str_contains($item->name, 'Trường') || str_contains($item->name, 'Mầm non') || str_contains($item->name, 'Tiểu học') || str_contains($item->name, 'THCS');
            $badge = 'Địa điểm';
            if ($isSchool) {
                $badge = 'Giáo Dục Sáp Nhập';
            } elseif (str_contains($item->name, 'Chợ')) {
                $badge = 'Chợ OCOP';
            } elseif (str_contains($item->name, 'Bệnh viện') || str_contains($item->name, 'Y tế')) {
                $badge = 'Y Tế';
            } else {
                $badge = 'Ẩm Thực & Đặc Sản';
            }

            return [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'address' => $item->address,
                'category' => $isSchool ? 'smart-education-map' : 'food',
                'badge' => $badge,
                'url' => '/dia-diem/' . $item->slug
            ];
        });

        return response()->json($results);
    }
}

