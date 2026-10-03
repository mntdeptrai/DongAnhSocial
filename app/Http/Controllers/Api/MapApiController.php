<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Eatery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * MapApiController — API chuyên dụng cho trang Bản đồ số (/tim-kiem)
 * 
 * Thiết kế lazy-load: trả data tối thiểu cho marker, load chi tiết on-demand.
 */
class MapApiController extends Controller
{
    /**
     * GET /api/map/categories
     * Trả về danh sách categories kèm số lượng eateries active.
     * Response nhẹ (~1-2KB) dùng để render sidebar header.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::withCount(['eateries' => function ($q) {
            $q->where('status', 'active');
        }])
            ->having('eateries_count', '>', 0)
            ->orderByDesc('eateries_count')
            ->get(['id', 'name', 'slug', 'icon']);

        return response()->json($categories);
    }

    /**
     * GET /api/map/markers?category_slug=co-so-kinh-doanh&page=1
     * Trả về markers tối thiểu (id, name, slug, lat, lng) cho 1 category.
     * Cursor pagination 50 items/page để tránh load 3,918 records cùng lúc.
     */
    public function markers(Request $request): JsonResponse
    {
        $categorySlug = $request->query('category_slug');
        $keyword = $request->query('q');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 50;

        $query = Eatery::active()
            ->select('id', 'name', 'slug', 'category_id', 'latitude', 'longitude', 'is_featured')
            ->with('category:id,slug,icon');

        if ($categorySlug) {
            $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
        }

        if ($keyword && mb_strlen($keyword) >= 2) {
            $query->where(function ($q) use ($keyword) {
                // Ưu tiên FULLTEXT index (name, address)
                $q->whereFullText(['name', 'address'], $keyword)
                    ->orWhere('name', 'like', "{$keyword}%")
                    ->orWhere('address', 'like', "{$keyword}%");
            });
        }

        $total = $query->count();
        $eateries = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'slug' => $e->slug,
                'lat' => $e->latitude,
                'lng' => $e->longitude,
                'cat' => $e->category?->slug,
                'icon' => $e->category?->icon,
                'featured' => $e->is_featured,
            ]);

        return response()->json([
            'data' => $eateries,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * GET /api/map/sidebar?category_slug=co-so-kinh-doanh&page=1
     * Trả về data cho sidebar list (name, address, image) — nhiều hơn markers nhưng vẫn gọn.
     */
    public function sidebar(Request $request): JsonResponse
    {
        $categorySlug = $request->query('category_slug');
        $keyword = $request->query('q');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;

        $query = Eatery::active()
            ->select('id', 'name', 'slug', 'category_id', 'address', 'image_path', 'latitude', 'longitude')
            ->with('category:id,slug');

        if ($categorySlug) {
            $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
        }

        if ($keyword && mb_strlen($keyword) >= 2) {
            $query->where(function ($q) use ($keyword) {
                $q->whereFullText(['name', 'address'], $keyword)
                    ->orWhere('name', 'like', "{$keyword}%")
                    ->orWhere('address', 'like', "{$keyword}%");
            });
        }

        $paginated = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'page' => $paginated->currentPage(),
                'per_page' => $perPage,
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/map/detail/{slug}
     * Trả về full data cho popup khi click marker — load on-demand.
     */
    public function detail(string $slug): JsonResponse
    {
        $eatery = Eatery::active()
            ->where('slug', $slug)
            ->with('category:id,name,slug,icon')
            ->select('id', 'name', 'slug', 'category_id', 'address', 'phone', 'opening_hours', 'latitude', 'longitude', 'rating', 'image_path', 'price_range')
            ->first();

        if (!$eatery) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json($eatery);
    }
}
