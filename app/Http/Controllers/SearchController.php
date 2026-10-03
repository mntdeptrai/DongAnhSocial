<?php

namespace App\Http\Controllers;

use App\Models\Eatery;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->query('q');
        
        // API phục vụ tính năng tự động gợi ý (Autocomplete Suggestions) khi gõ ô tìm kiếm - Truy vấn siêu nhanh có limit trực tiếp từ DB
        if ($request->query('ajax') === 'suggest' && $keyword) {
            $suggestions = Eatery::select('id', 'name', 'slug', 'address')
                ->active()
                ->where(function($q) use ($keyword) {
                    $q->where('name', 'like', "{$keyword}%")
                      ->orWhere('slug', 'like', "{$keyword}%")
                      ->orWhere('address', 'like', "{$keyword}%");
                })
                ->limit(6)
                ->get()
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
        
        // Lightweight render: chỉ trả view rỗng, data sẽ được JS fetch async từ /api/map/*
        return view('search', [
            'keyword' => $keyword,
        ]);
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

