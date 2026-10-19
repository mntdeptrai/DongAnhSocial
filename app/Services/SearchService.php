<?php

namespace App\Services;

use App\Contracts\SearchServiceInterface;
use App\Models\Eatery;
use Illuminate\Support\Collection;

class SearchService implements SearchServiceInterface
{
    /**
     * Get autocomplete search suggestions.
     */
    public function getSuggestions(string $keyword, int $limit = 6): Collection
    {
        return Eatery::select('id', 'name', 'slug', 'address')
            ->active()
            ->where(function($q) use ($keyword) {
                if (mb_strlen($keyword) >= 2) {
                    try {
                        $q->whereFullText(['name', 'address'], $keyword);
                        return;
                    } catch (\Throwable $e) {}
                }
                $q->where('name', 'like', "{$keyword}%")
                  ->orWhere('slug', 'like', "{$keyword}%")
                  ->orWhere('address', 'like', "{$keyword}%");
            })
            ->limit($limit)
            ->get()
            ->map(fn($e) => [
                'id'      => $e->id,
                'name'    => $e->name,
                'slug'    => $e->slug,
                'address' => $e->address
            ]);
    }

    /**
     * Perform quick categorized search.
     */
    public function quickSearch(string $query, string $category = 'all', int $limit = 8): Collection
    {
        $builder = Eatery::query();

        if ($category === 'truong-hoc') {
            $builder->whereHas('category', fn($c) => $c->where('slug', 'smart-education-map'));
        } elseif ($category === 'y-te') {
            $builder->whereHas('category', fn($c) => $c->where('slug', 'co-so-y-te-benh-vien'));
        } elseif ($category === 'cho') {
            $builder->whereHas('category', fn($c) => $c->whereIn('slug', ['cho-truyen-thong', 'market', 'traditional-market', 'ocop-products']));
        } elseif ($category === 'food') {
            $builder->whereHas('category', fn($c) => $c->whereNotIn('slug', ['smart-education-map', 'co-so-y-te-benh-vien']));
        }

        if (!empty($query)) {
            $builder->where(function($b) use ($query) {
                if (mb_strlen($query) >= 2) {
                    try {
                        $b->whereFullText(['name', 'address'], $query);
                        return;
                    } catch (\Throwable $e) {}
                }
                $b->where('name', 'LIKE', "{$query}%")
                  ->orWhere('slug', 'LIKE', "{$query}%")
                  ->orWhere('address', 'LIKE', "{$query}%");
            });
        }

        return $builder->take($limit)->get()->map(function($item) {
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
                'id'       => $item->id,
                'name'     => $item->name,
                'slug'     => $item->slug,
                'address'  => $item->address,
                'category' => $isSchool ? 'smart-education-map' : 'food',
                'badge'    => $badge,
                'url'      => '/dia-diem/' . $item->slug
            ];
        });
    }
}
