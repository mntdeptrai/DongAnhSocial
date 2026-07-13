<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use App\Models\Dish;
use App\Models\Review;
use App\Models\ReviewVideo;
use App\Models\FoodSafetyCertificate;
use App\Models\DailyFoodLog;
use App\Models\FoodSupplyContract;
use App\Models\PurchaseInvoice;
use App\Models\ReviewMedia;
use App\Models\Room;
use App\Models\WellnessService;
use App\Models\OcopProduct;
use App\Models\EducationProgram;
use App\Models\FoodTour;
use App\Models\FoodTourDiary;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EateryApiService
{
    protected static function getMode()
    {
        return env('API_SERVICE_MODE', 'database');
    }

    protected static function getBaseUrl()
    {
        return rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/api/v1';
    }

    private static function getConnection($categorySlug)
    {
        $default = config('database.default');
        if (config("database.connections.{$default}.driver") === 'sqlite') {
            return $default;
        }

        switch ($categorySlug) {
            case 'stay-in-dong-anh':
                return 'mysql_stay';
            case 'wellness-care':
                return 'mysql_wellness';
            case 'dong-anh-market':
                return 'mysql_market';
            case 'smart-education-map':
                return 'mysql_education';
            case 'discover-dong-anh-community-culture-hub':
                return 'mysql_culture';
            default:
                return 'mysql';
        }
    }

    private static function getSearchConnections()
    {
        $default = config('database.default');
        if (config("database.connections.{$default}.driver") === 'sqlite') {
            return [$default];
        }
        return ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
    }

    private static function findEateryAndConnection($eateryId)
    {
        foreach (self::getSearchConnections() as $conn) {
            $eatery = Eatery::on($conn)->find($eateryId);
            if ($eatery) {
                return [$eatery, $conn];
            }
        }
        return [null, null];
    }

    private static function findModelAndConnection($modelClass, $id)
    {
        $connections = self::getSearchConnections();
        
        $default = config('database.default');
        $isSqlite = config("database.connections.{$default}.driver") === 'sqlite';

        if (!$isSqlite) {
            if ($modelClass === \App\Models\OcopProduct::class) {
                $connections = array_intersect($connections, ['mysql_market']);
            } elseif ($modelClass === \App\Models\Room::class) {
                $connections = array_intersect($connections, ['mysql_stay']);
            } elseif ($modelClass === \App\Models\WellnessService::class) {
                $connections = array_intersect($connections, ['mysql_wellness']);
            } elseif ($modelClass === \App\Models\EducationProgram::class) {
                $connections = array_intersect($connections, ['mysql_education']);
            } elseif ($modelClass === \App\Models\CulturalActivity::class) {
                $connections = array_intersect($connections, ['mysql_culture']);
            }
        }

        foreach ($connections as $conn) {
            try {
                $model = $modelClass::on($conn)->find($id);
                if ($model) {
                    return [$model, $conn];
                }
            } catch (\Illuminate\Database\QueryException $e) {
                continue;
            }
        }
        return [null, null];
    }

    /**
     * Hydrate an Eatery model instance along with all its relationships.
     */
    public static function hydrateEatery($data)
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
            $eatery->setRelation('dishes', Dish::hydrate($data['dishes']));
        }

        if (isset($data['rooms'])) {
            $rooms = collect($data['rooms'])->map(function($r) {
                $room = new \App\Models\Room();
                $room->forceFill($r);
                $room->exists = true;
                return $room;
            });
            $eatery->setRelation('rooms', $rooms);
        }

        if (isset($data['wellness_services'])) {
            $services = collect($data['wellness_services'])->map(function($s) {
                $service = new \App\Models\WellnessService();
                $service->forceFill($s);
                $service->exists = true;
                return $service;
            });
            $eatery->setRelation('wellnessServices', $services);
        }

        if (isset($data['ocop_products'])) {
            $products = collect($data['ocop_products'])->map(function($p) {
                $prod = new \App\Models\OcopProduct();
                $prod->forceFill($p);
                $prod->exists = true;
                return $prod;
            });
            $eatery->setRelation('ocopProducts', $products);
        }

        if (isset($data['education_programs'])) {
            $programs = collect($data['education_programs'])->map(function($ep) {
                $prog = new \App\Models\EducationProgram();
                $prog->forceFill($ep);
                $prog->exists = true;
                return $prog;
            });
            $eatery->setRelation('educationPrograms', $programs);
        }

        if (isset($data['reviews'])) {
            $reviews = collect($data['reviews'])->map(function($r) {
                $rev = new Review();
                $rev->forceFill(Arr::except($r, ['media']));
                $rev->exists = true;
                if (isset($r['media'])) {
                    $media = collect($r['media'])->map(function($m) {
                        $med = new ReviewMedia();
                        $med->forceFill($m);
                        $med->exists = true;
                        return $med;
                    });
                    $rev->setRelation('media', $media);
                }
                return $rev;
            });
            $eatery->setRelation('reviews', $reviews);
        }

        if (isset($data['food_safety_certificate']) && $data['food_safety_certificate']) {
            $cert = new FoodSafetyCertificate();
            $cert->forceFill($data['food_safety_certificate']);
            $cert->exists = true;
            $eatery->setRelation('foodSafetyCertificate', $cert);
        }

        if (isset($data['food_supply_contracts'])) {
            $eatery->setRelation('foodSupplyContracts', FoodSupplyContract::hydrate($data['food_supply_contracts']));
        }

        if (isset($data['purchase_invoices'])) {
            $eatery->setRelation('purchaseInvoices', PurchaseInvoice::hydrate($data['purchase_invoices']));
        }

        if (isset($data['daily_food_logs'])) {
            $eatery->setRelation('dailyFoodLogs', DailyFoodLog::hydrate($data['daily_food_logs']));
        }

        if (isset($data['photos'])) {
            $photos = collect($data['photos'])->map(function($p) {
                $photo = new \App\Models\EateryPhoto();
                $photo->forceFill($p);
                $photo->exists = true;
                return $photo;
            });
            $eatery->setRelation('photos', $photos);
        }

        return $eatery;
    }

    /**
     * Get all categories.
     */
    public static function getCategories()
    {
        if (self::getMode() === 'http') {
            $response = Http::get(self::getBaseUrl() . '/categories');
            return Category::hydrate($response->json());
        }

        return Category::all();
    }

    /**
     * Get all communes.
     */
    public static function getCommunes()
    {
        if (self::getMode() === 'http') {
            $response = Http::get(self::getBaseUrl() . '/communes');
            return Commune::hydrate($response->json());
        }

        return Commune::all();
    }

    /**
     * Get eateries.
     */
    public static function getEateries($categorySlug = null, array $filters = [])
    {
        if ($categorySlug) {
            return self::fetchEateriesFromCategory($categorySlug, $filters);
        }

        // Aggregate eateries across all 7 databases (including heritage journey)
        $categories = ['dong-anh-food-map', 'hanh-trinh-di-san', 'stay-in-dong-anh', 'wellness-care', 'dong-anh-market', 'smart-education-map', 'discover-dong-anh-community-culture-hub'];
        $allEateries = collect();
        foreach ($categories as $cat) {
            try {
                $eateries = self::fetchEateriesFromCategory($cat, $filters);
                $allEateries = $allEateries->concat($eateries);
            } catch (\Exception $e) {
                Log::error("Lỗi khi fetch eateries cho category [{$cat}]: " . $e->getMessage());
            }
        }

        return $allEateries;
    }

    private static function fetchEateriesFromCategory($categorySlug, array $filters = [])
    {
        if (self::getMode() === 'http') {
            $response = Http::get(self::getBaseUrl() . "/{$categorySlug}/eateries", $filters);
            if ($response->successful()) {
                return collect($response->json())->map(function($data) {
                    return self::hydrateEatery($data);
                });
            }
            return collect();
        }

        $conn = self::getConnection($categorySlug);
        $query = Eatery::on($conn)->with(['category', 'commune', 'reviewVideos' => function($q) {
            $q->where('status', 'approved');
        }])->withCount('reviews')->active();

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
            $keyword = $filters['q'];
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhere('address', 'like', "%{$keyword}%");
            });
        }

        return $query->get();
    }

    /**
     * Get all cultural activities across mysql and mysql_culture connections.
     */
    public static function getAllCulturalActivities()
    {
        $activities = collect();
        $default = config('database.default');
        if (config("database.connections.{$default}.driver") === 'sqlite') {
            $conns = [$default];
        } else {
            $conns = ['mysql', 'mysql_culture'];
        }
        foreach ($conns as $conn) {
            try {
                $connActivities = \App\Models\CulturalActivity::on($conn)->with('eatery')->get();
                foreach ($connActivities as $activity) {
                    if ($activity->eatery) {
                        $activity->eatery->category_slug = ($conn === 'mysql_culture') 
                            ? 'discover-dong-anh-community-culture-hub' 
                            : 'hanh-trinh-di-san';
                        $activities->push($activity);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Lỗi khi lấy danh sách cultural activities trên [{$conn}]: " . $e->getMessage());
            }
        }
        return $activities;
    }

    /**
     * Get eatery detail by slug across all databases.
     */
    public static function getEateryBySlug($slug)
    {
        $categories = ['dong-anh-food-map', 'hanh-trinh-di-san', 'stay-in-dong-anh', 'wellness-care', 'dong-anh-market', 'smart-education-map', 'discover-dong-anh-community-culture-hub'];
        foreach ($categories as $cat) {
            try {
                $eatery = self::fetchEateryBySlug($cat, $slug);
                if ($eatery) {
                    return $eatery;
                }
            } catch (\Exception $e) {
                Log::warning("Lỗi khi tìm địa điểm theo slug [{$slug}] ở danh mục [{$cat}]: " . $e->getMessage());
            }
        }
        return null;
    }

    private static function fetchEateryBySlug($categorySlug, $slug)
    {
        if (self::getMode() === 'http') {
            $response = Http::get(self::getBaseUrl() . "/{$categorySlug}/eateries/{$slug}");
            if ($response->successful()) {
                return self::hydrateEatery($response->json());
            }
            return null;
        }

        $conn = self::getConnection($categorySlug);

        // Các relations cơ bản luôn cần load
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

        // Relations phụ thuộc vào category
        $categoryRelations = [];
        if ($categorySlug === 'stay-in-dong-anh') {
            $categoryRelations[] = 'rooms';
        } elseif ($categorySlug === 'wellness-care') {
            $categoryRelations[] = 'wellnessServices';
        } elseif (in_array($categorySlug, ['dong-anh-market', 'dong-anh-food-map', 'hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub'])) {
            $categoryRelations[] = 'ocopProducts';
        } elseif ($categorySlug === 'smart-education-map') {
            $categoryRelations[] = 'educationPrograms';
        }

        $eatery = Eatery::on($conn)->with($baseRelations)->where('slug', $slug)->first();

        if (!$eatery) {
            return null;
        }

        // Load category-specific relations với try-catch để tránh crash khi bảng chưa tồn tại
        foreach ($categoryRelations as $rel) {
            try {
                $eatery->load($rel);
            } catch (\Exception $e) {
                Log::warning("Không thể tải relation [{$rel}] cho [{$slug}] trên [{$conn}]: " . $e->getMessage());
                $eatery->setRelation($rel, collect());
            }
        }

        // Load reviewVideos riêng để tránh crash nếu bảng thiếu trên một số connection
        try {
            $eatery->load('reviewVideos');
        } catch (\Exception $e) {
            Log::warning("Không thể tải reviewVideos cho [{$slug}] trên [{$conn}]: " . $e->getMessage());
            $eatery->setRelation('reviewVideos', collect());
        }

        // Với di sản và văn hóa: thử load culturalActivities riêng để tránh crash
        if (in_array($categorySlug, ['hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub'])) {
            try {
                $eatery->load('culturalActivities');
            } catch (\Exception $e) {
                Log::warning("Không thể tải culturalActivities cho [{$slug}] trên [{$conn}]: " . $e->getMessage());
                $eatery->setRelation('culturalActivities', collect());
            }
        }

        return $eatery;
    }

    /**
     * Create eatery.
     */
    public static function createEatery($categorySlug, array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/{$categorySlug}/eateries", $data);
            return self::hydrateEatery($response->json());
        }

        $conn = self::getConnection($categorySlug);
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
        }

        $eatery = new Eatery();
        $eatery->setConnection($conn);
        $eatery->fill($data);
        $eatery->save();
        return $eatery;
    }

    /**
     * Update eatery.
     */
    public static function updateEatery($categorySlug, $id, array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::put(self::getBaseUrl() . "/{$categorySlug}/eateries/{$id}", $data);
            return self::hydrateEatery($response->json());
        }

        $conn = self::getConnection($categorySlug);
        $eatery = Eatery::on($conn)->findOrFail($id);
        $eatery->update($data);
        return $eatery;
    }

    /**
     * Delete eatery.
     */
    public static function deleteEatery($categorySlug, $id)
    {
        if (self::getMode() === 'http') {
            $response = Http::delete(self::getBaseUrl() . "/{$categorySlug}/eateries/{$id}");
            return $response->successful();
        }

        $conn = self::getConnection($categorySlug);
        $eatery = Eatery::on($conn)->findOrFail($id);
        return $eatery->delete();
    }

    /**
     * Store review.
     */
    public static function storeReview($categorySlug, $eateryId, array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/{$categorySlug}/eateries/{$eateryId}/reviews", $data);
            return $response->json();
        }

        $conn = self::getConnection($categorySlug);
        $eatery = Eatery::on($conn)->findOrFail($eateryId);

        $review = new Review();
        $review->setConnection($conn);
        $review->fill([
            'eatery_id' => $eatery->id,
            'user_name' => $data['user_name'],
            'rating' => $data['rating'],
            'comment' => $data['comment']
        ]);
        $review->save();

        if (isset($data['media_files'])) {
            foreach ($data['media_files'] as $file) {
                $review->media()->create([
                    'file_path' => $file['path'],
                    'file_type' => $file['type']
                ]);
            }
        }

        $avgRating = Review::on($conn)->where('eatery_id', $eatery->id)->avg('rating');
        if ($avgRating !== null) {
            $eatery->update([
                'rating' => round($avgRating, 2)
            ]);
        }

        return $review;
    }

    /**
     * Delete review.
     */
    public static function deleteReview($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::delete(self::getBaseUrl() . "/reviews/{$id}");
            return $response->successful();
        }

        list($review, $conn) = self::findModelAndConnection(Review::class, $id);
        if (!$review) return false;

        $eateryId = $review->eatery_id;
        $review->delete();

        $eatery = Eatery::on($conn)->find($eateryId);
        if ($eatery) {
            $avgRating = Review::on($conn)->where('eatery_id', $eateryId)->avg('rating');
            $eatery->update([
                'rating' => $avgRating ? round($avgRating, 2) : 5.00
            ]);
        }

        return true;
    }

    /**
     * Reply to review.
     */
    public static function replyReview($id, $reply)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/reviews/{$id}/reply", ['seller_reply' => $reply]);
            return $response->json();
        }

        list($review, $conn) = self::findModelAndConnection(Review::class, $id);
        if (!$review) return null;

        $review->update(['seller_reply' => $reply]);
        return $review;
    }

    // CRUD Dishes
    public static function storeDish(array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/dishes", $data);
            return $response->json();
        }

        list($eatery, $conn) = self::findEateryAndConnection($data['eatery_id']);
        if (!$eatery) return null;

        $dish = new Dish();
        $dish->setConnection($conn);
        $dish->fill($data);
        $dish->save();
        return $dish;
    }

    public static function updateDish($id, array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::put(self::getBaseUrl() . "/dishes/{$id}", $data);
            return $response->json();
        }

        list($dish, $conn) = self::findModelAndConnection(Dish::class, $id);
        if (!$dish) return null;

        $dish->update($data);
        return $dish;
    }

    public static function toggleSignatureDish($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/dishes/{$id}/toggle-signature");
            return $response->json();
        }

        list($dish, $conn) = self::findModelAndConnection(Dish::class, $id);
        if (!$dish) return null;

        $dish->update(['is_signature' => !$dish->is_signature]);
        return $dish;
    }

    public static function deleteDish($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::delete(self::getBaseUrl() . "/dishes/{$id}");
            return $response->successful();
        }

        list($dish, $conn) = self::findModelAndConnection(Dish::class, $id);
        if (!$dish) return false;

        return $dish->delete();
    }

    // Videos
    public static function getVideos()
    {
        if (self::getMode() === 'http') {
            $response = Http::get(self::getBaseUrl() . "/videos");
            return collect($response->json())->map(function($v) {
                $vid = new ReviewVideo();
                $vid->forceFill(Arr::except($v, ['eatery']));
                $vid->exists = true;
                if (isset($v['eatery'])) {
                    $vid->setRelation('eatery', self::hydrateEatery($v['eatery']));
                }
                return $vid;
            });
        }

        $connections = self::getSearchConnections();
        $allVideos = collect();
        foreach ($connections as $conn) {
            $vids = ReviewVideo::on($conn)
                ->with(['eatery.category', 'user'])
                ->where('status', 'approved')
                ->orderBy('id', 'desc')
                ->get();
            $allVideos = $allVideos->concat($vids);
        }
        return $allVideos->sortByDesc('id')->values();
    }

    public static function likeVideo($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/videos/{$id}/like");
            return $response->json();
        }

        list($video, $conn) = self::findModelAndConnection(ReviewVideo::class, $id);
        if (!$video) return null;

        $video->increment('likes_count');
        return ['success' => true, 'likes_count' => $video->likes_count];
    }

    public static function storeVideo(array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/videos", $data);
            return $response->json();
        }

        list($eatery, $conn) = self::findEateryAndConnection($data['eatery_id']);
        if (!$eatery) return null;

        $video = new ReviewVideo();
        $video->setConnection($conn);
        $video->fill($data);
        $video->save();
        return $video;
    }

    public static function updateVideo($id, array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::put(self::getBaseUrl() . "/videos/{$id}", $data);
            return $response->json();
        }

        list($video, $conn) = self::findModelAndConnection(ReviewVideo::class, $id);
        if (!$video) return null;

        $video->update($data);
        return $video;
    }

    public static function deleteVideo($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::delete(self::getBaseUrl() . "/videos/{$id}");
            return $response->successful();
        }

        list($video, $conn) = self::findModelAndConnection(ReviewVideo::class, $id);
        if (!$video) return false;

        return $video->delete();
    }

    public static function approveVideo($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/videos/{$id}/approve");
            return $response->json();
        }

        list($video, $conn) = self::findModelAndConnection(ReviewVideo::class, $id);
        if (!$video) return null;

        $video->update(['status' => 'approved']);
        return $video;
    }

    public static function rejectVideo($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/videos/{$id}/reject");
            return $response->json();
        }

        list($video, $conn) = self::findModelAndConnection(ReviewVideo::class, $id);
        if (!$video) return null;

        $video->update(['status' => 'rejected']);
        return $video;
    }

    // Trust Hub
    public static function storeFoodSafetyCertificate(array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/trust/certificate", $data);
            return $response->json();
        }

        list($eatery, $conn) = self::findEateryAndConnection($data['eatery_id']);
        if (!$eatery) return null;

        $cert = new FoodSafetyCertificate();
        $cert->setConnection($conn);
        $cert->fill($data);
        $cert->save();
        return $cert;
    }

    public static function storeDailyFoodLog(array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/trust/logs", $data);
            return $response->json();
        }

        list($eatery, $conn) = self::findEateryAndConnection($data['eatery_id']);
        if (!$eatery) return null;

        $log = new DailyFoodLog();
        $log->setConnection($conn);
        $log->fill($data);
        $log->save();
        return $log;
    }

    public static function deleteDailyFoodLog($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::delete(self::getBaseUrl() . "/trust/logs/{$id}");
            return $response->successful();
        }

        list($log, $conn) = self::findModelAndConnection(DailyFoodLog::class, $id);
        if (!$log) return false;

        return $log->delete();
    }

    public static function storeFoodSupplyContract(array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/trust/contracts", $data);
            return $response->json();
        }

        list($eatery, $conn) = self::findEateryAndConnection($data['eatery_id']);
        if (!$eatery) return null;

        $contract = new FoodSupplyContract();
        $contract->setConnection($conn);
        $contract->fill($data);
        $contract->save();
        return $contract;
    }

    public static function deleteFoodSupplyContract($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::delete(self::getBaseUrl() . "/trust/contracts/{$id}");
            return $response->successful();
        }

        list($contract, $conn) = self::findModelAndConnection(FoodSupplyContract::class, $id);
        if (!$contract) return false;

        return $contract->delete();
    }

    public static function storePurchaseInvoice(array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/trust/invoices", $data);
            return $response->json();
        }

        list($eatery, $conn) = self::findEateryAndConnection($data['eatery_id']);
        if (!$eatery) return null;

        $invoice = new PurchaseInvoice();
        $invoice->setConnection($conn);
        $invoice->fill($data);
        $invoice->save();
        return $invoice;
    }

    public static function deletePurchaseInvoice($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::delete(self::getBaseUrl() . "/trust/invoices/{$id}");
            return $response->successful();
        }

        list($invoice, $conn) = self::findModelAndConnection(PurchaseInvoice::class, $id);
        if (!$invoice) return false;

        return $invoice->delete();
    }

    // CRUD Rooms
    public static function storeRoom(array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/rooms", $data);
            return $response->json();
        }

        list($eatery, $conn) = self::findEateryAndConnection($data['eatery_id']);
        if (!$eatery) return null;

        $room = new Room();
        $room->setConnection($conn);
        $room->fill($data);
        $room->save();
        return $room;
    }

    public static function updateRoom($id, array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::put(self::getBaseUrl() . "/rooms/{$id}", $data);
            return $response->json();
        }

        list($room, $conn) = self::findModelAndConnection(Room::class, $id);
        if (!$room) return null;

        $room->update($data);
        return $room;
    }

    public static function deleteRoom($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::delete(self::getBaseUrl() . "/rooms/{$id}");
            return $response->successful();
        }

        list($room, $conn) = self::findModelAndConnection(Room::class, $id);
        if (!$room) return false;

        return $room->delete();
    }

    // CRUD Wellness Services
    public static function storeWellnessService(array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/wellness-services", $data);
            return $response->json();
        }

        list($eatery, $conn) = self::findEateryAndConnection($data['eatery_id']);
        if (!$eatery) return null;

        $service = new WellnessService();
        $service->setConnection($conn);
        $service->fill($data);
        $service->save();
        return $service;
    }

    public static function updateWellnessService($id, array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::put(self::getBaseUrl() . "/wellness-services/{$id}", $data);
            return $response->json();
        }

        list($service, $conn) = self::findModelAndConnection(WellnessService::class, $id);
        if (!$service) return null;

        $service->update($data);
        return $service;
    }

    public static function deleteWellnessService($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::delete(self::getBaseUrl() . "/wellness-services/{$id}");
            return $response->successful();
        }

        list($service, $conn) = self::findModelAndConnection(WellnessService::class, $id);
        if (!$service) return false;

        return $service->delete();
    }

    // CRUD OCOP Products
    public static function storeOcopProduct(array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/ocop-products", $data);
            return $response->json();
        }

        list($eatery, $conn) = self::findEateryAndConnection($data['eatery_id']);
        if (!$eatery) return null;

        $product = new OcopProduct();
        $product->setConnection($conn);
        $product->fill($data);
        $product->save();
        return $product;
    }

    public static function updateOcopProduct($id, array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::put(self::getBaseUrl() . "/ocop-products/{$id}", $data);
            return $response->json();
        }

        list($product, $conn) = self::findModelAndConnection(OcopProduct::class, $id);
        if (!$product) return null;

        $product->update($data);
        return $product;
    }

    public static function deleteOcopProduct($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::delete(self::getBaseUrl() . "/ocop-products/{$id}");
            return $response->successful();
        }

        list($product, $conn) = self::findModelAndConnection(OcopProduct::class, $id);
        if (!$product) return false;

        return $product->delete();
    }

    // CRUD Education Programs
    public static function storeEducationProgram(array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/education-programs", $data);
            return $response->json();
        }

        list($eatery, $conn) = self::findEateryAndConnection($data['eatery_id']);
        if (!$eatery) return null;

        $program = new EducationProgram();
        $program->setConnection($conn);
        $program->fill($data);
        $program->save();
        return $program;
    }

    public static function updateEducationProgram($id, array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::put(self::getBaseUrl() . "/education-programs/{$id}", $data);
            return $response->json();
        }

        list($program, $conn) = self::findModelAndConnection(EducationProgram::class, $id);
        if (!$program) return null;

        $program->update($data);
        return $program;
    }

    public static function deleteEducationProgram($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::delete(self::getBaseUrl() . "/education-programs/{$id}");
            return $response->successful();
        }

        list($program, $conn) = self::findModelAndConnection(EducationProgram::class, $id);
        if (!$program) return false;

        return $program->delete();
    }

    // Food Tours & Journeys
    public static function getFoodTours($mood = null)
    {
        if (self::getMode() === 'http') {
            $response = Http::get(self::getBaseUrl() . "/food-tours", ['mood' => $mood]);
            return $response->json();
        }

        $query = FoodTour::public()->with(['stops.eatery', 'diaries.user'])->withCount('diaries');
        if ($mood) {
            $query->where('mood', $mood);
        }
        $tours = $query->get();

        $communityTours = FoodTour::community()
            ->with(['stops.eatery', 'diaries.user'])
            ->withCount('diaries')
            ->orderBy('shared_at', 'desc')
            ->get();

        return [
            'tours' => $tours,
            'community_tours' => $communityTours
        ];
    }

    public static function getFoodTourBySlug($slug)
    {
        if (self::getMode() === 'http') {
            $response = Http::get(self::getBaseUrl() . "/food-tours/{$slug}");
            if ($response->successful()) {
                return $response->json();
            }
            return null;
        }

        $tour = FoodTour::where('slug', $slug)
            ->with(['stops' => function($q) {
                $q->orderBy('stop_order');
            }, 'stops.eatery.category', 'stops.eatery.commune'])
            ->first();

        if (!$tour) return null;

        $diaries = FoodTourDiary::where('food_tour_id', $tour->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'tour' => $tour,
            'diaries' => $diaries
        ];
    }

    public static function generateAITour($budget, $mood)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/food-tours/generate-ai", [
                'budget' => $budget,
                'mood' => $mood
            ]);
            return $response->json();
        }

        // Gọi HTTP API nội bộ để chạy qua Gemini AI tích hợp
        $response = Http::post(self::getBaseUrl() . "/food-tours/generate-ai", [
            'budget' => $budget,
            'mood' => $mood
        ]);
        return $response->json();
    }

    public static function storeFoodTourDiary($id, array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/food-tours/{$id}/diary", $data);
            return $response->json();
        }

        $userId = $data['user_id'] ?? (auth()->check() ? auth()->id() : null);
        if (!$userId) return null;

        $imagePath = $data['image_path'] ?? null;
        $stopReviews = $data['stop_reviews'] ?? [];

        foreach ($stopReviews as $index => &$review) {
            if (!empty($review['eatery_id'])) {
                $user = User::find($userId);
                $userName = $user ? $user->name : 'Thực khách Food Tour';
                $eatery = self::getEateries()->firstWhere('id', $review['eatery_id']);
                if ($eatery) {
                    $mediaFiles = [];
                    if (!empty($review['image_path'])) {
                        $mediaFiles[] = [
                            'path' => $review['image_path'],
                            'type' => 'image'
                        ];
                    }
                    self::storeReview($eatery->category->slug, $eatery->id, [
                        'user_name' => $userName,
                        'rating' => $review['rating'] ?? null,
                        'comment' => $review['comment'] ?? '',
                        'media_files' => $mediaFiles
                    ]);
                }
            }
        }

        $diary = FoodTourDiary::create([
            'food_tour_id' => $id,
            'user_id' => $userId,
            'rating' => $data['rating'] ?? null,
            'comment' => $data['comment'] ?? null,
            'image_path' => $imagePath,
            'completed_stops' => $data['completed_stops'] ?? [],
            'stop_reviews' => $stopReviews,
        ]);

        $tour = FoodTour::find($id);
        if ($tour && $tour->is_ai_generated && $tour->status === 'draft') {
            $updateData = ['status' => 'saved'];
            if (!empty($data['share_to_community'])) {
                $updateData['shared_at'] = now();
                $updateData['expires_at'] = now()->addHours(72);
            }
            $tour->update($updateData);
        }

        return $diary;
    }

    // User Auth & Accounts Management
    public static function apiLogin($email, $password)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/auth/login", [
                'email' => $email,
                'password' => $password
            ]);
            return $response->json();
        }

        if (\Illuminate\Support\Facades\Auth::attempt(['email' => $email, 'password' => $password])) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user->status === 'disabled') {
                \Illuminate\Support\Facades\Auth::logout();
                return ['success' => false, 'message' => 'Tài khoản đã bị vô hiệu hóa.'];
            }
            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
            ]);
            return ['success' => true, 'user' => $user];
        }
        return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng.'];
    }

    public static function apiRegister(array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/auth/register", $data);
            return $response->json();
        }

        $role = $data['role'] ?? 'user';
        if ($role === 'admin') {
            $role = 'user';
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'role' => $role,
            'phone' => $data['phone'] ?? '',
            'status' => 'active',
            'avatar' => '🧑',
        ]);

        \Illuminate\Support\Facades\Auth::login($user);
        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
        ]);

        return ['success' => true, 'user' => $user];
    }

    public static function apiLogout()
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/auth/logout");
            return $response->successful();
        }

        \Illuminate\Support\Facades\Auth::logout();
        session()->forget(['user_id', 'user_name', 'user_role']);
        return true;
    }

    public static function getUsers()
    {
        if (self::getMode() === 'http') {
            $response = Http::get(self::getBaseUrl() . "/users");
            return User::hydrate($response->json());
        }

        return User::orderBy('created_at', 'desc')->get();
    }

    public static function storeUser(array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/users", $data);
            if ($response->successful()) {
                return User::hydrate([$response->json()])->first();
            }
            return null;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'role' => $data['role'],
            'avatar' => $data['avatar'] ?? '🧑',
            'phone' => $data['phone'] ?? '',
            'status' => 'active',
        ]);
        return $user;
    }

    public static function updateUser($id, array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::put(self::getBaseUrl() . "/users/{$id}", $data);
            if ($response->successful()) {
                return User::hydrate([$response->json()])->first();
            }
            return null;
        }

        $user = User::findOrFail($id);
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'avatar' => $data['avatar'] ?? '🧑',
            'phone' => $data['phone'] ?? '',
            'status' => $data['status'] ?? 'active',
        ];
        if (!empty($data['password'])) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        }
        $user->update($updateData);
        return $user;
    }

    public static function deleteUser($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::delete(self::getBaseUrl() . "/users/{$id}");
            return $response->successful();
        }

        $user = User::findOrFail($id);
        if ($user->id === session('user_id')) {
            return false;
        }
        return $user->delete();
    }

    public static function toggleUserStatus($id)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/users/{$id}/toggle-status");
            return $response->json();
        }

        $user = User::findOrFail($id);
        if ($user->id === session('user_id')) {
            return ['success' => false, 'message' => 'Không tự ban chính mình.'];
        }
        $user->status = $user->status === 'active' ? 'disabled' : 'active';
        $user->save();
        return ['success' => true, 'status' => $user->status];
    }

    // CRUD Cultural Activities
    public static function storeCulturalActivity(array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::post(self::getBaseUrl() . "/cultural-activities", $data);
            return $response->json();
        }

        // Nếu category_slug được cung cấp, xác định connection trực tiếp — tránh ID collision giữa các database
        if (!empty($data['category_slug'])) {
            $conn = self::getConnection($data['category_slug']);
            unset($data['category_slug']); // không lưu vào DB
        } else {
            list($eatery, $conn) = self::findEateryAndConnection($data['eatery_id']);
            if (!$eatery) return null;
        }

        $activity = new \App\Models\CulturalActivity();
        $activity->setConnection($conn);
        $activity->fill($data);
        $activity->save();
        return $activity;
    }

    public static function updateCulturalActivity($id, array $data)
    {
        if (self::getMode() === 'http') {
            $response = Http::put(self::getBaseUrl() . "/cultural-activities/{$id}", $data);
            return $response->json();
        }

        list($activity, $conn) = self::findModelAndConnection(\App\Models\CulturalActivity::class, $id);
        if (!$activity) return null;

        $activity->update($data);
        return $activity;
    }

    public static function deleteCulturalActivity($id): bool
    {
        if (self::getMode() === 'http') {
            $response = Http::delete(self::getBaseUrl() . "/cultural-activities/{$id}");
            return $response->successful();
        }

        list($activity, $conn) = self::findModelAndConnection(\App\Models\CulturalActivity::class, $id);
        if (!$activity) return false;

        return $activity->delete();
    }

    // =====================================================================
    // Eatery Photos Gallery
    // =====================================================================

    public static function storeEateryPhoto(array $data): ?\App\Models\EateryPhoto
    {
        list($eatery, $conn) = self::findEateryAndConnection($data['eatery_id']);
        if (!$eatery) return null;

        $photo = new \App\Models\EateryPhoto();
        $photo->setConnection($conn);
        $photo->fill($data);
        $photo->save();
        return $photo;
    }

    public static function deleteEateryPhoto(int $id): bool
    {
        list($photo, $conn) = self::findModelAndConnection(\App\Models\EateryPhoto::class, $id);
        if (!$photo) return false;

        return (bool) $photo->delete();
    }
}
