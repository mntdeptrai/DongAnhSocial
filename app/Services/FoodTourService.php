<?php

namespace App\Services;

use App\Contracts\EateryServiceInterface;
use App\Contracts\FoodTourServiceInterface;
use App\Domain\FoodTour\FoodTourDiaryData;
use App\Helpers\R2Helper;
use App\Models\Eatery;
use App\Models\FoodTour;
use App\Models\FoodTourDiary;
use App\Models\FoodTourStop;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FoodTourService implements FoodTourServiceInterface
{
    public function __construct(
        protected EateryServiceInterface $eateryService
    ) {}

    /**
     * Lấy danh sách Food Tour theo tâm trạng kèm các tour do cộng đồng chia sẻ.
     */
    public function getFoodTours(?string $mood = null)
    {
        $query = FoodTour::where('status', 'saved')
            ->where('mood', '!=', 'cooking')
            ->with(['stops.eatery', 'diaries.user'])
            ->withCount('diaries');

        $query->where(function ($q) {
            $q->whereNull('user_id')
              ->orWhereNotNull('shared_at')
              ->orWhereHas('user', function ($u) {
                  $u->where('role', 'admin');
              });

            if (auth()->check()) {
                $q->orWhere('user_id', auth()->id());
            }
        });

        if ($mood) {
            $query->where('mood', $mood);
        }

        $tours = $query->orderBy('created_at', 'desc')->get();

        $communityTours = FoodTour::community()
            ->with(['stops.eatery', 'diaries.user'])
            ->withCount('diaries')
            ->orderBy('shared_at', 'desc')
            ->get();

        return [
            'tours'           => $tours,
            'community_tours' => $communityTours,
        ];
    }

    /**
     * Lấy chi tiết Food Tour theo slug kèm các trạm dừng và nhật ký.
     */
    public function getFoodTourBySlug(string $slug)
    {
        $tour = FoodTour::where('slug', $slug)
            ->with(['stops' => function($q) {
                $q->orderBy('stop_order');
            }, 'stops.eatery.category', 'stops.eatery.commune'])
            ->firstOrFail();

        // Hydrate stops eateries if needed
        $missingIds = $tour->stops->filter(fn($s) => !$s->eatery)->pluck('eatery_id')->filter()->unique();
        if ($missingIds->isNotEmpty()) {
            $foundEateries = Eatery::with(['category', 'commune'])->whereIn('id', $missingIds)->get()->keyBy('id');
            foreach ($tour->stops as $stop) {
                if (!$stop->eatery && isset($foundEateries[$stop->eatery_id])) {
                    $stop->setRelation('eatery', $foundEateries[$stop->eatery_id]);
                }
            }
        }

        $diaries = FoodTourDiary::where('food_tour_id', $tour->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'tour'    => $tour,
            'diaries' => $diaries,
        ];
    }

    /**
     * Generate AI food tour with Gemini.
     */
    public function generateAITourWithGemini(int $budgetLimit, string $mood, ?User $user): array
    {
        $moodText = $mood;
        $extraConstraint = "ĐẶC BIỆT ƯU TIÊN các sản phẩm/cơ sở OCOP Tinh hoa bản địa";
        if ($mood === 'specialty') {
            $moodText = 'Khám phá đặc sản, Tinh hoa bản địa';
            $extraConstraint = "BẮT BUỘC ÍT NHẤT 2 TRONG 3 ĐỊA ĐIỂM PHẢI CÓ CATEGORY LÀ 'Tinh hoa bản địa' HOẶC LÀ CƠ SỞ OCOP";
        }

        $allEateries = $this->eateryService->getEateries();
        $eateries = $allEateries->map(function($e) {
            return [
                'id'          => $e->id,
                'name'        => $e->name,
                'category'    => $e->category->name ?? 'Khác',
                'price_range' => $e->price_range,
                'description' => $e->description,
            ];
        });

        $prompt = "Tôi đang ở Đông Anh, Hà Nội. Tôi có ngân sách khoảng {$budgetLimit} VND. Tâm trạng của tôi là '{$moodText}'. 
Hãy đóng vai một chuyên gia bản địa. Chọn chính xác 3 địa điểm phù hợp nhất từ danh sách sau để tạo thành 1 lộ trình Food Tour / Trải nghiệm liên hoàn mang đậm bản sắc văn hóa. YÊU CẦU QUAN TRỌNG: {$extraConstraint}.
Danh sách địa điểm:
" . json_encode($eateries, JSON_UNESCAPED_UNICODE) . "
YÊU CẦU TRẢ VỀ CHỈ LÀ CHUỖI JSON ĐÚNG ĐỊNH DẠNG SAU, KHÔNG CHỨA BẤT KỲ TEXT NÀO KHÁC BÊN NGOÀI (KHÔNG CÓ DẤU ```json):
{
    \"tour_name\": \"Tên tour sáng tạo (VD: Hành trình Khám phá Chợ & Ẩm thực Đông Anh)\",
    \"description\": \"Mô tả ngắn gọn 2 câu về tour.\",
    \"story\": \"Câu chuyện 3 câu dẫn dắt vì sao lại chọn 3 địa điểm này.\",
    \"difficulty\": \"✨ Lộ trình AI\",
    \"stops\": [
        {
            \"eatery_id\": id_1,
            \"recommendation\": \"Gợi ý cụ thể nên ăn món gì hoặc làm hoạt động gì tại đây.\"
        },
        {
            \"eatery_id\": id_2,
            \"recommendation\": \"...\"
        },
        {
            \"eatery_id\": id_3,
            \"recommendation\": \"...\"
        }
    ]
}";

        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            return ['success' => false, 'message' => 'Thiếu API Key Gemini.'];
        }

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            $result = $response->json();
            $textResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if ($textResponse === null) {
                Log::error('Gemini API Full Response: ' . json_encode($result));
                $errMsg = $result['error']['message'] ?? 'Phản hồi rỗng từ Gemini';
                throw new \Exception('API Error: ' . $errMsg);
            }

            if (preg_match('/\{.*\}/s', $textResponse, $matches)) {
                $jsonString = $matches[0];
            } else {
                $jsonString = $textResponse;
            }

            $aiData = json_decode(trim($jsonString), true);

            if (isset($aiData['eatery_ids']) && !isset($aiData['stops'])) {
                $aiData['stops'] = array_map(function($id) {
                    return ['eatery_id' => $id, 'recommendation' => 'Trải nghiệm ẩm thực địa phương hấp dẫn tại đây.'];
                }, $aiData['eatery_ids']);
            }

            if (!$aiData || !isset($aiData['stops']) || count($aiData['stops']) < 1) {
                throw new \Exception('Invalid JSON format returned from AI');
            }

            $slug = Str::slug($aiData['tour_name']) . '-' . substr(md5(uniqid()), 0, 5);

            $tour = FoodTour::create([
                'user_id'         => $user ? $user->id : null,
                'name'            => $aiData['tour_name'],
                'slug'            => $slug,
                'description'     => $aiData['description'],
                'duration'        => '2.5 giờ',
                'distance'        => '5.0 km',
                'budget'          => number_format($budgetLimit, 0, ',', '.') . 'đ',
                'difficulty'      => $aiData['difficulty'],
                'best_time'       => '17:00 - 21:00',
                'popularity'      => 'Mới tạo',
                'mood'            => $mood,
                'thumbnail'       => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80',
                'story'           => $aiData['story'],
                'status'          => 'draft',
                'is_ai_generated' => true,
            ]);

            $validEateryIds = Eatery::pluck('id')->toArray();
            $usedEateryIds = [];
            $stopsData = [];

            foreach ($aiData['stops'] as $index => $stop) {
                $rawId = $stop['eatery_id'] ?? null;
                $selectedId = null;

                if ($rawId && is_numeric($rawId) && in_array((int)$rawId, $validEateryIds) && !in_array((int)$rawId, $usedEateryIds)) {
                    $selectedId = (int)$rawId;
                }

                if (!$selectedId && is_string($rawId)) {
                    $found = $allEateries->first(function($e) use ($rawId) {
                        return Str::contains(mb_strtolower($e->name), mb_strtolower($rawId));
                    });
                    if ($found && !in_array($found->id, $usedEateryIds)) {
                        $selectedId = $found->id;
                    }
                }

                if (!$selectedId) {
                    $unused = array_diff($validEateryIds, $usedEateryIds);
                    $selectedId = !empty($unused) ? reset($unused) : ($validEateryIds[0] ?? 1);
                }

                $usedEateryIds[] = $selectedId;

                $stopsData[] = [
                    'food_tour_id'   => $tour->id,
                    'eatery_id'      => $selectedId,
                    'stop_order'     => $index + 1,
                    'stop_story'     => $stop['recommendation'] ?? ("Điểm đến thứ " . ($index + 1) . " trong hành trình " . $aiData['tour_name'] . "."),
                    'estimated_time' => '45 phút',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }

            if (!empty($stopsData)) {
                FoodTourStop::insert($stopsData);
            }

            return ['success' => true, 'slug' => $slug];
        } catch (\Throwable $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Hệ thống AI đang bận hoặc tạo lộ trình chưa hoàn tất. Vui lòng bấm nút "Tạo Lộ Trình" lại lần nữa!'];
        }
    }

    /**
     * Get active eateries dropdown for tour form.
     */
    public function getEateriesDropdown(): Collection
    {
        return Cache::remember('all_eateries_dropdown', 3600, function() {
            return Eatery::active()
                ->with('category:id,name')
                ->select('id', 'name', 'slug', 'address', 'category_id')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Store new custom food tour.
     */
    public function createCustomTour(array $validatedData, ?User $user, ?UploadedFile $thumbnailFile): FoodTour
    {
        $thumbnailPath = null;
        if ($thumbnailFile) {
            $uploadDir = public_path('uploads/tours');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . Str::random(8) . '.' . $thumbnailFile->getClientOriginalExtension();
            $thumbnailFile->move($uploadDir, $fileName);
            $thumbnailPath = '/uploads/tours/' . $fileName;
        } elseif (!empty($validatedData['thumbnail'])) {
            $thumbnailPath = $validatedData['thumbnail'];
        } else {
            $thumbnailPath = 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80';
        }

        $slug = Str::slug($validatedData['name']) . '-' . substr(md5(uniqid()), 0, 5);

        $tour = FoodTour::create([
            'user_id'         => $user ? $user->id : null,
            'name'            => $validatedData['name'],
            'slug'            => $slug,
            'description'     => $validatedData['description'],
            'duration'        => $validatedData['duration'],
            'distance'        => $validatedData['distance'],
            'budget'          => $validatedData['budget'],
            'difficulty'      => $validatedData['difficulty'] ?: '☕ Nhẹ nhàng',
            'best_time'       => $validatedData['best_time'],
            'popularity'      => 'Mới tạo',
            'mood'            => $this->determineMood($validatedData['name'], $validatedData['description'], $validatedData['budget'], $validatedData['stops'] ?? []),
            'thumbnail'       => $thumbnailPath,
            'story'           => $validatedData['story'] ?? null,
            'status'          => 'saved',
            'is_ai_generated' => false,
        ]);

        $stopsData = [];
        foreach ($validatedData['stops'] as $index => $stop) {
            $stopsData[] = [
                'food_tour_id'   => $tour->id,
                'eatery_id'      => $stop['eatery_id'],
                'stop_order'     => $index + 1,
                'stop_story'     => $stop['stop_story'] ?? '',
                'estimated_time' => $stop['estimated_time'] ?? '45 phút',
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }
        if (!empty($stopsData)) {
            FoodTourStop::insert($stopsData);
        }

        return $tour;
    }

    /**
     * Find tour for editing with permission verification.
     */
    public function getTourForManage(string $slug, ?User $user): FoodTour
    {
        $tour = FoodTour::where('slug', $slug)->with('stops.eatery')->firstOrFail();

        $sessionRole = session('user_role');
        $canManage = $user && (
            ($tour->user_id !== null && $tour->user_id === $user->id) ||
            ($tour->user_id === null && ($user->role === 'admin' || $sessionRole === 'admin'))
        );

        if (!$canManage) {
            abort(403, 'Bạn không có quyền quản lý lộ trình ẩm thực này.');
        }

        return $tour;
    }

    /**
     * Update custom food tour with stops.
     */
    public function updateCustomTour(FoodTour $tour, array $validatedData, ?UploadedFile $thumbnailFile): FoodTour
    {
        $thumbnailPath = $tour->thumbnail;
        if ($thumbnailFile) {
            $uploadDir = public_path('uploads/tours');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . Str::random(8) . '.' . $thumbnailFile->getClientOriginalExtension();
            $thumbnailFile->move($uploadDir, $fileName);
            $thumbnailPath = '/uploads/tours/' . $fileName;
        } elseif (!empty($validatedData['thumbnail'])) {
            $thumbnailPath = $validatedData['thumbnail'];
        }

        $tour->update([
            'name'        => $validatedData['name'],
            'description' => $validatedData['description'],
            'duration'    => $validatedData['duration'],
            'distance'    => $validatedData['distance'],
            'budget'      => $validatedData['budget'],
            'difficulty'  => $validatedData['difficulty'] ?: '☕ Nhẹ nhàng',
            'best_time'   => $validatedData['best_time'],
            'mood'        => $this->determineMood($validatedData['name'], $validatedData['description'], $validatedData['budget'], $validatedData['stops'] ?? []),
            'thumbnail'   => $thumbnailPath ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80',
            'story'       => $validatedData['story'] ?? null,
        ]);

        $tour->stops()->delete();

        $stopsData = [];
        foreach ($validatedData['stops'] as $index => $stop) {
            $stopsData[] = [
                'food_tour_id'   => $tour->id,
                'eatery_id'      => $stop['eatery_id'],
                'stop_order'     => $index + 1,
                'stop_story'     => $stop['stop_story'] ?? '',
                'estimated_time' => $stop['estimated_time'] ?? '45 phút',
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
        }
        if (!empty($stopsData)) {
            FoodTourStop::insert($stopsData);
        }

        return $tour;
    }

    /**
     * Delete custom food tour.
     */
    public function deleteCustomTour(FoodTour $tour): bool
    {
        return (bool) $tour->delete();
    }

    /**
     * Toggle community sharing for custom food tour.
     */
    public function toggleShare(FoodTour $tour): string
    {
        if ($tour->shared_at) {
            $tour->update([
                'shared_at'  => null,
                'expires_at' => null,
            ]);
            return 'Đã hủy chia sẻ lộ trình lên cộng đồng!';
        }

        $tour->update([
            'shared_at'  => now(),
            'expires_at' => null,
        ]);
        return 'Đã chia sẻ lộ trình lên cộng đồng thành công!';
    }

    /**
     * Calculate Haversine distance between 2 coordinates.
     */
    public function getDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    /**
     * Automatically determine mood category based on metadata and stops.
     */
    public function determineMood(string $name, string $description, string $budget, array $stops = []): string
    {
        $text = mb_strtolower($name . ' ' . $description . ' ' . $budget);

        $eateryIds = [];
        if (is_array($stops)) {
            foreach ($stops as $stop) {
                if (isset($stop['eatery_id'])) {
                    $eateryIds[] = $stop['eatery_id'];
                }
            }
        }

        $eateries = [];
        if (!empty($eateryIds)) {
            $eateries = Eatery::with('category')->whereIn('id', $eateryIds)->get();
        }

        $hasSpecialtyEatery = false;
        foreach ($eateries as $eat) {
            $eatText = mb_strtolower($eat->name . ' ' . $eat->description);
            $catSlug = $eat->category ? $eat->category->slug : '';

            if (
                $catSlug === 'hanh-trinh-di-san' ||
                $catSlug === 'dong-anh-market' ||
                str_contains($eatText, 'đặc sản') ||
                str_contains($eatText, 'di sản') ||
                str_contains($eatText, 'truyền thống') ||
                str_contains($eatText, 'mạch tràng') ||
                str_contains($eatText, 'cháo se') ||
                str_contains($eatText, 'ocop') ||
                str_contains($eatText, 'cổ loa')
            ) {
                $hasSpecialtyEatery = true;
                break;
            }
        }

        if ($hasSpecialtyEatery || str_contains($text, 'đặc sản') || str_contains($text, 'bản địa') || str_contains($text, 'truyền thống') || str_contains($text, 'làng nghề') || str_contains($text, 'cổ') || str_contains($text, 'di sản')) {
            return 'specialty';
        }

        $hasNightEatery = false;
        foreach ($eateries as $eat) {
            $eatText = mb_strtolower($eat->name . ' ' . $eat->description . ' ' . $eat->address . ' ' . $eat->opening_hours);
            if (
                str_contains($eatText, 'cao lỗ') ||
                str_contains($eatText, 'đêm') ||
                str_contains($eatText, 'khuya') ||
                str_contains($eatText, '22h') ||
                str_contains($eatText, '23h') ||
                str_contains($eatText, '00h') ||
                str_contains($eatText, '24h') ||
                str_contains($eatText, '24/24')
            ) {
                $hasNightEatery = true;
                break;
            }
        }

        if ($hasNightEatery || str_contains($text, 'đêm') || str_contains($text, 'tối') || str_contains($text, 'khuya') || str_contains($text, 'night') || str_contains($text, 'cao lỗ')) {
            return 'night';
        }

        $hasCheapEatery = false;
        foreach ($eateries as $eat) {
            $eatText = mb_strtolower($eat->name . ' ' . $eat->description . ' ' . $eat->price_range);
            if (
                str_contains($eatText, 'sinh viên') ||
                str_contains($eatText, 'học sinh') ||
                str_contains($eatText, 'vỉa hè') ||
                str_contains($eatText, 'ăn vặt') ||
                str_contains($eatText, 'trà sữa') ||
                str_contains($eatText, 'giá rẻ') ||
                str_contains($eatText, 'bình dân')
            ) {
                $hasCheapEatery = true;
                break;
            }
        }

        if ($hasCheapEatery || str_contains($text, 'sinh viên') || str_contains($text, 'giá rẻ') || str_contains($text, 'học sinh') || str_contains($text, 'vỉa hè') || str_contains($text, 'rẻ')) {
            return 'cheap';
        }

        preg_match_all('/\d+/', str_replace('.', '', $budget), $matches);
        if (!empty($matches[0])) {
            $maxBudget = max(array_map('intval', $matches[0]));
            if ($maxBudget > 0 && $maxBudget <= 150000) {
                return 'cheap';
            }
        }

        return 'chill';
    }

    /**
     * Tạo nhật ký Food Tour nâng cao.
     */
    public function createDiary(FoodTourDiaryData $data, bool $shareToCommunity = false): FoodTourDiary
    {
        return DB::transaction(function() use ($data, $shareToCommunity) {
            $imagePath = null;
            if ($data->image) {
                $base64 = $data->image;
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $decoded = base64_decode(substr($base64, strpos($base64, ',') + 1));
                    $ext = strtolower($type[1]);
                    if ($decoded !== false && in_array($ext, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                        $imagePath = R2Helper::uploadRaw($decoded, $ext, 'diaries');
                    }
                }
            }

            $processedStopReviews = $data->stop_reviews;
            foreach ($processedStopReviews as &$review) {
                if (!empty($review['image']) && preg_match('/^data:image\/(\w+);base64,/', $review['image'], $type)) {
                    $decoded = base64_decode(substr($review['image'], strpos($review['image'], ',') + 1));
                    $ext = strtolower($type[1]);
                    if ($decoded !== false && in_array($ext, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                        $review['image_path'] = R2Helper::uploadRaw($decoded, $ext, 'diaries');
                        unset($review['image']);
                    }
                }

                if (!empty($review['eatery_id'])) {
                    $user = auth()->user();
                    $userName = $user ? $user->name : 'Thực khách Food Tour';

                    $eatery = Eatery::with('category:id,slug')->find($review['eatery_id']);
                    if ($eatery) {
                        $mediaFiles = [];
                        if (!empty($review['image_path'])) {
                            $mediaFiles[] = [
                                'path' => $review['image_path'],
                                'type' => 'image',
                            ];
                        }

                        $this->eateryService->storeReview($eatery->category->slug ?? 'dong-anh-food-map', $eatery->id, [
                            'user_name'   => $userName,
                            'rating'      => $review['rating'] ?? null,
                            'comment'     => $review['comment'] ?? '',
                            'media_files' => $mediaFiles,
                        ]);
                    }
                }
            }

            $diary = FoodTourDiary::create([
                'food_tour_id' => $data->food_tour_id,
                'user_id'      => $data->user_id,
                'caption'      => $data->caption,
                'rating'       => $data->rating,
                'image_path'   => $imagePath,
                'stop_reviews' => $processedStopReviews,
            ]);

            $tour = FoodTour::find($data->food_tour_id);
            if ($tour && $tour->is_ai_generated && $tour->status === 'draft') {
                $updateData = [
                    'status'  => 'saved',
                    'user_id' => $tour->user_id ?: (auth()->id() ?: $data->user_id),
                ];
                if ($shareToCommunity) {
                    $updateData['shared_at'] = now();
                    $updateData['expires_at'] = now()->addHours(72);
                }
                $tour->update($updateData);
            }

            return $diary;
        });
    }
}
