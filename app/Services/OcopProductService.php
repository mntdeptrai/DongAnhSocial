<?php

namespace App\Services;

use App\Contracts\OcopProductServiceInterface;
use App\Domain\OcopProduct\OcopProductData;
use App\Helpers\R2Helper;
use App\Models\Dish;
use App\Models\Eatery;
use App\Models\OcopProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OcopProductService implements OcopProductServiceInterface
{
    /**
     * Lấy danh sách sản phẩm OCOP từ database kèm quan hệ cơ sở sản xuất.
     */
    public function getOcopProducts(array $filters = []): Collection
    {
        $products = collect();

        try {
            $dbProducts = OcopProduct::whereHas('eatery.category', function($q) {
                    $q->where('slug', 'dong-anh-market');
                })
                ->with(['eatery.commune', 'eatery.category'])
                ->get();

            foreach ($dbProducts as $p) {
                if ($p->eatery && $p->eatery->category && $p->eatery->category->slug === 'dong-anh-market') {
                    if (isset($filters['commune_id']) && $filters['commune_id']) {
                        if ($p->eatery->commune_id != $filters['commune_id']) continue;
                    }
                    if (isset($filters['q']) && $filters['q']) {
                        $q = strtolower($filters['q']);
                        $text = strtolower(($p->name ?? '') . ' ' . ($p->seller_name ?? '') . ' ' . ($p->description ?? ''));
                        if (!str_contains($text, $q)) continue;
                    }
                    $products->push($p);
                }
            }
        } catch (\Exception $e) {
            Log::warning("Lỗi khi truy vấn ocop_products: " . $e->getMessage());
        }

        return $products;
    }

    /**
     * Resolve product by slug or numeric ID (handles OcopProduct or Dish fallback).
     */
    public function resolveProduct(string|int $slugOrId): ?object
    {
        $relations = ['eatery.commune', 'eatery.category', 'eatery.foodSafetyCertificate', 'eatery.reviews'];

        if (is_numeric($slugOrId)) {
            return OcopProduct::with($relations)->find($slugOrId)
                ?: Dish::with($relations)->find($slugOrId);
        }

        // 1. Exact slug match
        $product = OcopProduct::with($relations)->where('slug', $slugOrId)->first();
        if ($product) return $product;

        // 2. Legacy slug with trailing ID e.g. "tom-2696"
        if (preg_match('/-(\d+)$/', $slugOrId, $matches)) {
            $product = OcopProduct::with($relations)->find($matches[1])
                ?: Dish::with($relations)->find($matches[1]);
            if ($product) return $product;
        }

        // 3. Fallback to Dish slug
        $dish = Dish::with($relations)->where('slug', $slugOrId)->first();
        if ($dish) return $dish;

        // 4. Fallback search by slugified name
        $all = OcopProduct::with($relations)->take(100)->get();
        return $all->first(fn($p) => Str::slug($p->name) === $slugOrId);
    }

    /**
     * Get related products in the same commune / area.
     */
    public function getRelatedProducts(object $product, int $limit = 6): Collection
    {
        $isOcop = !empty($product->star_rating);
        $eatery = $product->eatery ?? null;
        $related = collect();

        if ($eatery && $eatery->commune_id) {
            $query = OcopProduct::where('id', '!=', $product->id)
                ->whereHas('eatery', fn($q) => $q->where('commune_id', $eatery->commune_id));

            if ($isOcop) {
                $query->orderByRaw('star_rating IS NULL, star_rating DESC');
            }

            $related = $query->take($limit)->get();
        }

        return $related;
    }

    /**
     * Get other products from the same establishment.
     */
    public function getEstablishmentProducts(object $product, int $limit = 6): Collection
    {
        $eatery = $product->eatery ?? null;
        if (!$eatery) return collect();

        $products = OcopProduct::where('eatery_id', $eatery->id)
            ->where('id', '!=', $product->id)
            ->take($limit)
            ->get();

        if ($products->count() < $limit) {
            $moreDishes = Dish::where('eatery_id', $eatery->id)
                ->where('id', '!=', $product->id)
                ->take($limit - $products->count())
                ->get();
            $products = $products->concat($moreDishes);
        }

        return $products;
    }

    public function create(OcopProductData|array $data): ?OcopProduct
    {
        if ($data instanceof OcopProductData) {
            $imagePath = $this->resolveImagePath($data->image, $data->image_url);
            $attributes = [
                'eatery_id' => $data->eatery_id,
                'name' => $data->name,
                'price' => $data->price,
                'unit' => $data->unit,
                'star_rating' => $data->star_rating,
                'year_awarded' => $data->year_awarded,
                'origin' => $data->origin,
                'seller_name' => $data->seller_name,
                'seller_phone' => $data->seller_phone,
                'description' => $data->description,
                'image_path' => $imagePath,
            ];
        } else {
            $attributes = $data;
        }

        return $this->storeOcopProduct($attributes);
    }

    public function update($id, OcopProductData|array $data): ?OcopProduct
    {
        $product = OcopProduct::find($id);
        if (!$product) return null;

        if ($data instanceof OcopProductData) {
            $imagePath = $this->resolveImagePath($data->image, $data->image_url) ?? $product->image_path;
            $attributes = [
                'eatery_id' => $data->eatery_id,
                'name' => $data->name,
                'price' => $data->price,
                'unit' => $data->unit,
                'star_rating' => $data->star_rating,
                'year_awarded' => $data->year_awarded,
                'origin' => $data->origin,
                'seller_name' => $data->seller_name,
                'seller_phone' => $data->seller_phone,
                'description' => $data->description,
                'image_path' => $imagePath,
            ];
        } else {
            $attributes = $data;
        }

        $product->update($attributes);
        return $product;
    }

    public function storeOcopProduct(array $data): ?OcopProduct
    {
        $eatery = Eatery::find($data['eatery_id'] ?? null);
        if (!$eatery) return null;

        return OcopProduct::create($data);
    }

    public function updateOcopProduct($id, array $data): ?OcopProduct
    {
        $product = OcopProduct::find($id);
        if (!$product) return null;

        $product->update($data);
        return $product;
    }

    public function delete($id): bool
    {
        $product = OcopProduct::find($id);
        if (!$product) return false;

        return (bool) $product->delete();
    }

    public function deleteOcopProduct($id): bool
    {
        return $this->delete($id);
    }

    protected function resolveImagePath($imageFile, ?string $imageUrl): ?string
    {
        if ($imageFile) {
            return R2Helper::upload($imageFile, 'ocop');
        }

        if ($imageUrl) {
            if (preg_match('/(?:drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=))([a-zA-Z0-9_-]{25,50})/i', $imageUrl, $matches)) {
                return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            }
            return $imageUrl;
        }

        return null;
    }
}
