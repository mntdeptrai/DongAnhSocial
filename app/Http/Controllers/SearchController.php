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
        
        // API phục vụ tính năng tự động gợi ý (Autocomplete Suggestions) khi gõ ô tìm kiếm - Truy vấn siêu nhanh có limit trực tiếp từ DB
        if ($request->query('ajax') === 'suggest' && $keyword) {
            $suggestions = Eatery::active()
                ->where(function($q) use ($keyword) {
                    $q->where('slug', 'like', "{$keyword}%")
                      ->orWhere('name', 'like', "{$keyword}%")
                      ->orWhere('address', 'like', "{$keyword}%");
                })
                ->select('id', 'name', 'slug', 'address')
                ->limit(6)
                ->get();
            return response()->json($suggestions);
        }
        
        // Tìm địa điểm cho bản đồ (chỉ chọn các trường cần thiết cho marker & sidebar, tránh load toàn bộ quan hệ nặng)
        $query = Eatery::active()->with(['category:id,name,slug,icon', 'commune:id,name,slug'])
            ->select('id', 'name', 'slug', 'category_id', 'commune_id', 'address', 'latitude', 'longitude', 'rating', 'image_path', 'price_range', 'opening_hours', 'phone', 'is_featured');
        
        if ($selectedCategorySlug) {
            $query->whereHas('category', function($q) use ($selectedCategorySlug) {
                $q->where('slug', $selectedCategorySlug);
            });
        }
        if ($comId) {
            $query->where('commune_id', $comId);
        }
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('slug', 'like', "{$keyword}%")
                  ->orWhere('name', 'like', "{$keyword}%")
                  ->orWhere('address', 'like', "{$keyword}%");
            });
        }
        
        $eateries = $query->orderByDesc('is_featured')->orderByDesc('rating')->get();
        
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
            $query->whereHas('category', function($c) {
                $c->where('slug', 'smart-education-map');
            });
        } elseif ($cat === 'y-te') {
            $query->whereHas('category', function($c) {
                $c->where('slug', 'co-so-y-te-benh-vien');
            });
        } elseif ($cat === 'cho') {
            $query->whereHas('category', function($c) {
                $c->whereIn('slug', ['cho-truyen-thong', 'market', 'traditional-market', 'ocop-products']);
            });
        } elseif ($cat === 'food') {
            $query->whereHas('category', function($c) {
                $c->whereNotIn('slug', ['smart-education-map', 'co-so-y-te-benh-vien']);
            });
        }

        if ($q) {
            $query->where(function($b) use ($q) {
                $b->where('slug', 'LIKE', "{$q}%")
                  ->orWhere('name', 'LIKE', "{$q}%")
                  ->orWhere('address', 'LIKE', "{$q}%");
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

