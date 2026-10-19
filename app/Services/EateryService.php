<?php

namespace App\Services;

use App\Contracts\EateryServiceInterface;
use App\Domain\Eatery\EateryData;
use App\Helpers\R2Helper;
use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use App\Models\EateryPhoto;
use App\Models\Review;
use App\Models\ReviewMedia;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EateryService implements EateryServiceInterface
{
    /**
     * In-memory cache for getEateries() without filters.
     */
    protected $cachedAllEateries = null;

    /**
     * Lấy danh sách tất cả các danh mục.
     */
    public function getCategories()
    {
        return Category::select('id', 'name', 'slug', 'icon', 'description')->get();
    }

    /**
     * Lấy danh sách tất cả các xã / thị trấn.
     */
    public function getCommunes()
    {
        return Commune::select('id', 'name', 'slug')->get();
    }

    /**
     * Đếm tổng số địa điểm theo danh mục và bộ lọc.
     */
    public function countEateries(?string $categorySlug = null, array $filters = []): int
    {
        $cacheKey = 'count_eateries_' . ($categorySlug ?? 'all') . '_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, 300, function() use ($categorySlug, $filters) {
            $query = Eatery::active();

            if ($categorySlug) {
                $query->whereHas('category', function($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            }

            if (isset($filters['commune_id']) && $filters['commune_id']) {
                $query->where('commune_id', $filters['commune_id']);
            }

            if (isset($filters['q']) && $filters['q']) {
                $keyword = trim($filters['q']);
                $query->where(function($q) use ($keyword) {
                    if (mb_strlen($keyword) >= 2) {
                        $q->whereFullText(['name', 'address'], $keyword);
                    }
                    $q->orWhere('slug', 'like', "{$keyword}%")
                      ->orWhere('name', 'like', "{$keyword}%")
                      ->orWhere('address', 'like', "{$keyword}%");
                });
            }

            return $query->count();
        });
    }

    /**
     * Lấy danh sách địa điểm theo danh mục và bộ lọc có phân trang.
     */
    public function getEateries(?string $categorySlug = null, array $filters = [])
    {
        $hasFilters = !empty(array_filter($filters, fn($v) => !is_null($v) && $v !== ''));
        $hasSearch = !empty($filters['q']);
        $cacheKey = null;

        // Tối ưu Cache Server-side: Tăng tốc phản hồi danh sách chuẩn trong 5 phút
        if (!$hasSearch) {
            $cacheKey = 'get_eateries_' . ($categorySlug ?? 'all') . '_' . md5(json_encode($filters));
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        if (!$categorySlug && !$hasFilters && $this->cachedAllEateries !== null) {
            return $this->cachedAllEateries;
        }

        $query = Eatery::with([
            'category:id,name,slug,icon',
            'commune:id,name,slug',
            'ocopProducts:id,eatery_id,name,price,description,image_path,star_rating',
            'dishes:id,eatery_id,name,price,description,image_path,is_signature',
            'reviewVideos' => function($q) {
                $q->select('id', 'eatery_id', 'user_id', 'title', 'video_url', 'video_type', 'thumbnail_path', 'likes_count', 'status')
                  ->where('status', 'approved');
            }
        ])->withCount('reviews')->active();

        if ($categorySlug) {
            $query->whereHas('category', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        if (isset($filters['commune_id']) && $filters['commune_id']) {
            $query->where('commune_id', $filters['commune_id']);
        }

        if (isset($filters['q']) && $filters['q']) {
            $keyword = trim($filters['q']);
            $unaccented = \App\Helpers\VietnameseSeoHelper::stripAccents($keyword);
            $keywords = array_filter(array_unique([$keyword, $unaccented]));

            if (preg_match('/(mam non|mn)/i', $unaccented)) {
                $keywords[] = 'mầm non';
                $keywords[] = 'mn';
            }
            if (preg_match('/(tieu hoc|th)/i', $unaccented)) {
                $keywords[] = 'tiểu học';
                $keywords[] = 'th';
            }
            if (preg_match('/(benh vien|y te|phong kham)/i', $unaccented)) {
                $keywords[] = 'bệnh viện';
                $keywords[] = 'y tế';
                $keywords[] = 'phòng khám';
            }
            if (preg_match('/(cho|nong san|ocop)/i', $unaccented)) {
                $keywords[] = 'chợ';
                $keywords[] = 'ocop';
            }
            if (preg_match('/(kham pha|di san|van hoa)/i', $unaccented)) {
                $keywords[] = 'di sản';
                $keywords[] = 'văn hóa';
            }
            if (preg_match('/(dong anh|donganh|xa dong anh)/i', $unaccented)) {
                $keywords[] = 'Đông Anh';
                $keywords[] = 'Xã Đông Anh';
                $keywords[] = 'dong anh';
            }

            $query->where(function($q) use ($keywords, $keyword) {
                if (mb_strlen($keyword) >= 2) {
                    $q->whereFullText(['name', 'address'], $keyword);
                }
                foreach ($keywords as $kw) {
                    $q->orWhere('slug', 'like', "{$kw}%")
                      ->orWhere('name', 'like', "{$kw}%")
                      ->orWhere('address', 'like', "{$kw}%");
                }
            });
        }

        $query->orderByDesc('is_featured')->orderByDesc('rating')->orderBy('id', 'desc');

        if (isset($filters['page']) && isset($filters['per_page'])) {
            $page = max(1, (int)$filters['page']);
            $perPage = max(1, (int)$filters['per_page']);
            $query->skip(($page - 1) * $perPage)->take($perPage);
        } elseif (isset($filters['limit'])) {
            $query->limit((int) $filters['limit']);
        }

        $result = $query->get();

        if ($cacheKey !== null) {
            try {
                Cache::put($cacheKey, $result, 300);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Cache::put failed for key {$cacheKey}: " . $e->getMessage());
            }
        }

        if (!$categorySlug && !$hasFilters) {
            $this->cachedAllEateries = $result;
        }

        return $result;
    }

    /**
     * Lấy chi tiết địa điểm theo slug (tìm nhanh trong 1 query).
     */
    public function getEateryBySlug(string $slug): ?Eatery
    {
        return $this->fetchEateryBySlug(null, $slug);
    }

    /**
     * Lấy chi tiết địa điểm theo slug kèm relations chuyên biệt theo category.
     */
    public function fetchEateryBySlug(?string $categorySlug, string $slug): ?Eatery
    {
        $baseRelations = [
            'category',
            'commune',
            'dishes',
            'photos',
            'foodSafetyCertificate',
            'foodSupplyContracts',
            'purchaseInvoices',
            'dailyFoodLogs',
            'reviews' => function($q) {
                $q->orderBy('created_at', 'desc');
            }
        ];

        $query = Eatery::with($baseRelations)->where('slug', $slug);
        if ($categorySlug) {
            $query->whereHas('category', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        $eatery = $query->first();
        if (!$eatery) {
            return null;
        }

        $catSlug = $categorySlug ?? ($eatery->category->slug ?? null);

        $categoryRelations = [];
        if ($catSlug === 'stay-in-dong-anh') {
            $categoryRelations[] = 'rooms';
        } elseif ($catSlug === 'wellness-care') {
            $categoryRelations[] = 'wellnessServices';
        } elseif (in_array($catSlug, ['dong-anh-market', 'traditional-market', 'dong-anh-food-map', 'hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub', 'co-so-kinh-doanh'])) {
            $categoryRelations[] = 'ocopProducts';
        } elseif ($catSlug === 'smart-education-map') {
            $categoryRelations[] = 'educationPrograms';
        }

        foreach ($categoryRelations as $rel) {
            try {
                $eatery->load($rel);
            } catch (\Exception $e) {
                $eatery->setRelation($rel, collect());
            }
        }

        try {
            $eatery->load('reviewVideos');
        } catch (\Exception $e) {
            $eatery->setRelation('reviewVideos', collect());
        }

        if (in_array($catSlug, ['hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub'])) {
            try {
                $eatery->load('culturalActivities');
            } catch (\Exception $e) {
                $eatery->setRelation('culturalActivities', collect());
            }
        }

        return $eatery;
    }

    /**
     * Tạo địa điểm mới (hỗ trợ cả EateryData DTO và mảng array).
     */
    public function create(EateryData|array $data, string $categorySlug): Eatery
    {
        if ($data instanceof EateryData) {
            $imagePath = $this->resolveImagePath($data->image, $data->image_url);
            $attributes = [
                'user_id' => $data->user_id,
                'name' => $data->name,
                'category_id' => $data->category_id,
                'commune_id' => $data->commune_id,
                'address' => $data->address,
                'phone' => $data->phone,
                'opening_hours' => $data->opening_hours,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'price_range' => $data->price_range ?: (in_array($categorySlug, ['smart-education-map', 'hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub']) ? null : '30.000 - 100.000'),
                'image_path' => $imagePath,
                'is_featured' => $data->is_featured,
                'description' => $data->description,
                'rating' => 5.0,
                'status' => 'active',
                'heritage_year' => $data->heritage_year,
                'story' => $data->story,
                'artisans' => $data->artisans,
                'fun_fact' => $data->fun_fact,
                'audio_narrative' => $data->audio_narrative,
                'ocop_stars' => $data->ocop_stars,
                'ingredients' => $data->ingredients,
                'timeline' => $data->timeline,
            ];
        } else {
            $attributes = $data;
        }

        if (empty($attributes['slug'])) {
            $attributes['slug'] = Str::slug($attributes['name']) . '-' . Str::random(5);
        }

        return Eatery::create($attributes);
    }

    /**
     * Cập nhật địa điểm.
     */
    public function update($id, EateryData|array $data, string $categorySlug, ?string $currentImagePath = null): ?Eatery
    {
        $eatery = Eatery::find($id);
        if (!$eatery) {
            return null;
        }

        if ($data instanceof EateryData) {
            $imagePath = $currentImagePath ?? $eatery->image_path;
            if ($data->image) {
                if ($imagePath && Str::startsWith($imagePath, '/uploads/eateries/')) {
                    $oldPath = public_path($imagePath);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $imagePath = R2Helper::upload($data->image, 'eateries');
            } elseif ($data->image_url) {
                $imagePath = $this->parseImageUrl($data->image_url);
            }

            $attributes = [
                'user_id' => $data->user_id,
                'name' => $data->name,
                'category_id' => $data->category_id,
                'commune_id' => $data->commune_id,
                'address' => $data->address,
                'phone' => $data->phone,
                'opening_hours' => $data->opening_hours,
                'latitude' => $data->latitude,
                'longitude' => $data->longitude,
                'price_range' => $data->price_range,
                'image_path' => $imagePath,
                'is_featured' => $data->is_featured,
                'description' => $data->description,
                'heritage_year' => $data->heritage_year,
                'story' => $data->story,
                'artisans' => $data->artisans,
                'fun_fact' => $data->fun_fact,
                'audio_narrative' => $data->audio_narrative,
                'ocop_stars' => $data->ocop_stars,
                'ingredients' => $data->ingredients,
                'timeline' => $data->timeline,
            ];
        } else {
            $attributes = $data;
        }

        $eatery->update($attributes);
        return $eatery;
    }

    /**
     * Xóa địa điểm.
     */
    public function delete(string $categorySlug, $id): bool
    {
        $eatery = Eatery::find($id);
        if ($eatery) {
            return (bool) $eatery->delete();
        }
        return false;
    }

    /**
     * Lưu ảnh gallery cho địa điểm.
     */
    public function storeEateryPhoto(array $data): ?EateryPhoto
    {
        $eatery = Eatery::find($data['eatery_id'] ?? null);
        if (!$eatery) return null;

        return EateryPhoto::create($data);
    }

    /**
     * Xóa ảnh gallery.
     */
    public function deleteEateryPhoto(int $id): bool
    {
        $photo = EateryPhoto::find($id);
        if (!$photo) return false;

        return (bool) $photo->delete();
    }

    /**
     * Lưu đánh giá mới và tính lại rating trung bình.
     */
    public function storeReview(?string $categorySlug, $eateryId, array $data): Review
    {
        $review = Review::create([
            'eatery_id' => $eateryId,
            'user_name' => $data['user_name'] ?? 'Khách',
            'user_phone' => $data['user_phone'] ?? null,
            'rating' => $data['rating'] ?? 5,
            'comment' => $data['comment'] ?? '',
        ]);

        if (!empty($data['media_urls'])) {
            $mediaRows = array_map(fn($url) => [
                'review_id'  => $review->id,
                'media_url'  => $url,
                'media_type' => 'image',
                'created_at' => now(),
                'updated_at' => now(),
            ], $data['media_urls']);
            ReviewMedia::insert($mediaRows);
        }

        // Tự động cập nhật rating trung bình
        $eatery = Eatery::find($eateryId);
        if ($eatery) {
            $avg = Review::where('eatery_id', $eateryId)->avg('rating');
            $eatery->update([
                'rating' => $avg ? round($avg, 1) : 5.0
            ]);
        }

        return $review;
    }

    /**
     * Xóa đánh giá và cập nhật lại điểm rating trung bình.
     */
    public function deleteReview($id): bool
    {
        $review = Review::find($id);
        if (!$review) return false;

        $eateryId = $review->eatery_id;
        $review->media()->delete();
        $review->delete();

        if ($eateryId) {
            $eatery = Eatery::find($eateryId);
            if ($eatery) {
                $avg = Review::where('eatery_id', $eateryId)->avg('rating') ?? 5.0;
                $eatery->update([
                    'rating' => round($avg, 1)
                ]);
            }
        }

        return true;
    }

    /**
     * Trả lời đánh giá của thực khách.
     */
    public function replyReview($id, string $reply): ?Review
    {
        $review = Review::find($id);
        if (!$review) return null;

        $review->update(['seller_reply' => $reply]);
        return $review;
    }

    /**
     * Hydrate một instance Eatery kèm tất cả relations.
     */
    public function hydrateEatery($data): ?Eatery
    {
        if (!$data) return null;

        $attributes = Arr::except($data, [
            'category', 'commune', 'dishes', 'rooms', 'wellness_services', 
            'ocop_products', 'education_programs', 'reviews', 'review_videos',
            'food_safety_certificate', 'food_supply_contracts', 'purchase_invoices', 'daily_food_logs',
            'photos'
        ]);

        $eatery = new Eatery();
        $eatery->forceFill($attributes);
        $eatery->exists = true;

        if (isset($data['category'])) {
            $cat = new Category();
            $cat->forceFill($data['category']);
            $cat->exists = true;
            $eatery->setRelation('category', $cat);
        }

        if (isset($data['commune'])) {
            $com = new Commune();
            $com->forceFill($data['commune']);
            $com->exists = true;
            $eatery->setRelation('commune', $com);
        }

        if (isset($data['dishes'])) {
            $eatery->setRelation('dishes', \App\Models\Dish::hydrate($data['dishes']));
        }

        return $eatery;
    }

    protected function resolveImagePath($imageFile, ?string $imageUrl): ?string
    {
        if ($imageFile) {
            return R2Helper::upload($imageFile, 'eateries');
        }

        if ($imageUrl) {
            return $this->parseImageUrl($imageUrl);
        }

        return null;
    }

    protected function parseImageUrl(string $url): string
    {
        if (preg_match('/(?:drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=))([a-zA-Z0-9_-]{25,50})/i', $url, $matches)) {
            return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
        }
        return $url;
    }

    /**
     * Prepare full view presentation data for eatery detail page (Schema.org, checkins, posts, doctors, etc.)
     */
    public function getEateryDetailViewData(string $slug, ?int $currentUserId, ?string $currentSessionId): ?array
    {
        $eatery = EateryApiService::getEateryBySlug($slug);
        if (!$eatery) {
            return null;
        }

        // Tự động phân tích danh mục để chọn Schema Type thích hợp của Google
        $schemaType = 'LocalBusiness';
        $categorySlug = $eatery->category->slug ?? '';
        if ($categorySlug === 'dong-anh-food-map') {
            $schemaType = 'Restaurant';
        } elseif ($categorySlug === 'stay-in-dong-anh') {
            $schemaType = 'Hotel';
        } elseif ($categorySlug === 'wellness-care') {
            $schemaType = 'HealthAndBeautyBusiness';
        } elseif (in_array($categorySlug, ['dong-anh-market', 'traditional-market', 'co-so-kinh-doanh'])) {
            $schemaType = 'Store';
        } elseif ($categorySlug === 'smart-education-map') {
            $schemaType = 'School';
        }

        $currentUrl = request()->url();

        // Khởi tạo mảng dữ liệu có cấu trúc Schema.org chuẩn Google
        $schemaData = [
            '@context' => 'https://schema.org',
            '@type' => $schemaType,
            'name' => $eatery->name,
            'image' => $eatery->image_path ?: 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80',
            'telephone' => $eatery->phone ?: 'Chưa cập nhật',
            'priceRange' => $eatery->price_range ?: 'Đang cập nhật',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $eatery->address,
                'addressLocality' => $eatery->commune->name ?? 'Đông Anh',
                'addressRegion' => 'Đông Anh, Hà Nội',
                'addressCountry' => 'VN'
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $eatery->latitude,
                'longitude' => $eatery->longitude
            ],
            'url' => $currentUrl
        ];

        if ($schemaType === 'Restaurant' || $schemaType === 'CafeOrCoffeeShop') {
            $schemaData['servesCuisine'] = $eatery->category->name ?? 'Ẩm thực';
        }

        // Thêm đánh giá trung bình vào Schema nếu đã có bình luận
        if ($eatery->reviews && $eatery->reviews->count() > 0) {
            $schemaData['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $eatery->average_rating,
                'reviewCount' => $eatery->reviews->count()
            ];

            $schemaData['review'] = [];
            foreach ($eatery->reviews->take(3) as $rev) {
                $schemaData['review'][] = [
                    '@type' => 'Review',
                    'author' => [
                        '@type' => 'Person',
                        'name' => $rev->user_name
                    ],
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => $rev->rating
                    ],
                    'reviewBody' => $rev->comment
                ];
            }
        }

        $sameEateryIds = [$eatery->id];

        // Lấy danh sách ảnh check-in thực tế của thực khách tại địa điểm này
        $checkinPhotos = \App\Models\Checkin::with('user')
            ->whereIn('eatery_id', $sameEateryIds)
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->latest()
            ->take(15)
            ->get();

        $checkinReviews = \App\Models\Checkin::with('user')
            ->whereIn('eatery_id', $sameEateryIds)
            ->latest()
            ->get();

        // Lấy thông tin tài khoản cá nhân & bài viết mới nhất từ Hiệu trưởng/Nhà trường liên kết
        $principalUser = null;
        if ($eatery->user_id) {
            $principalUser = \App\Models\User::find($eatery->user_id);
        } else {
            $principalUser = \App\Models\User::where('eatery_id', $eatery->id)->first();
        }

        $principalPosts = collect();
        try {
            $principalPosts = \App\Models\EducationProgram::on('mysql_education')
                ->where('eatery_id', $eatery->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Throwable $e) {}

        if ($principalPosts->isEmpty()) {
            try {
                $principalPosts = \App\Models\EducationProgram::on('mysql')
                    ->where('eatery_id', $eatery->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } catch (\Throwable $e) {}
        }

        $postIds = $principalPosts->pluck('id')->toArray();

        if (!empty($postIds)) {
            $realLikesMap = \App\Models\CheckinReaction::where('reactionable_type', 'post')
                ->whereIn('reactionable_id', $postIds)
                ->selectRaw('reactionable_id, count(*) as total')
                ->groupBy('reactionable_id')
                ->pluck('total', 'reactionable_id')
                ->toArray();

            $uQuery = \App\Models\CheckinReaction::where('reactionable_type', 'post')
                ->whereIn('reactionable_id', $postIds);
            if ($currentUserId) {
                $uQuery->where('user_id', $currentUserId);
            } elseif (!empty($currentSessionId)) {
                $uQuery->whereNull('user_id')->where('session_id', $currentSessionId);
            } else {
                $uQuery->whereRaw('1 = 0');
            }
            $userLikedMap = $uQuery->pluck('reactionable_id')->toArray();

            $realCommentsMap = \App\Models\Comment::where('commentable_type', 'post')
                ->whereIn('commentable_id', $postIds)
                ->selectRaw('commentable_id, count(*) as total')
                ->groupBy('commentable_id')
                ->pluck('total', 'commentable_id')
                ->toArray();

            foreach ($principalPosts as $p) {
                $p->real_likes_count = (int) ($realLikesMap[$p->id] ?? $p->likes_count ?? 0);
                $p->is_liked = in_array($p->id, $userLikedMap);
                $p->real_comments_count = (int) ($realCommentsMap[$p->id] ?? 0);
                $p->real_shares_count = (int) ($p->shares_count ?? 0);
            }
        }

        // Mã hóa Schema dữ liệu có cấu trúc sang định dạng thẻ script JSON-LD
        $jsonLd = '<script type="application/ld+json">' . PHP_EOL . 
                  json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL . 
                  '</script>';

        $viewData = compact('eatery', 'jsonLd', 'checkinPhotos', 'checkinReviews', 'principalPosts', 'principalUser');

        $viewName = match ($categorySlug) {
            'traditional-market'                                               => 'detail-market',
            'dong-anh-market'                                                  => 'detail-ocop',
            'co-so-kinh-doanh'                                                 => 'detail-business',
            'stay-in-dong-anh'                                                 => 'detail-stay',
            'wellness-care'                                                    => 'detail-wellness',
            'smart-education-map'                                              => 'detail-education',
            'discover-dong-anh-community-culture-hub', 'hanh-trinh-di-san'     => 'detail-culture',
            'dong-anh-food-map'                                                => 'detail-food',
            default                                                            => 'detail',
        };

        if ($viewName === 'detail-wellness') {
            $viewData['doctors'] = \Illuminate\Support\Facades\DB::table('wellness_doctors')
                ->where('eatery_id', $eatery->id)
                ->orderBy('id', 'asc')
                ->get();
        }

        return [
            'view' => $viewName,
            'data' => $viewData,
        ];
    }

    /**
     * Handle user review submission for an eatery with media upload.
     */
    public function submitEateryReview(int $eateryId, array $data, array $mediaFiles = []): Review
    {
        $eatery = Eatery::with('category')->find($eateryId);
        if (!$eatery) {
            abort(404, 'Địa điểm không tồn tại.');
        }

        $uploadedMedia = [];
        if (!empty($mediaFiles)) {
            $uploaded = R2Helper::uploadMultiple($mediaFiles, 'reviews');
            foreach ($uploaded as $item) {
                $uploadedMedia[] = [
                    'path' => $item['url'],
                    'type' => $item['file_type'],
                ];
            }
        }

        return $this->storeReview(
            $eatery->category->slug ?? 'dong-anh-food-map',
            $eatery->id,
            [
                'user_name'   => $data['user_name'] ?? 'Khách vãng lai',
                'rating'      => $data['rating'] ?? 5,
                'comment'     => $data['comment'] ?? '',
                'media_files' => $uploadedMedia,
            ]
        );
    }
}

