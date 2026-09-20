<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\FoodTour;
use App\Models\FoodTourStop;
use App\Models\FoodTourDiary;
use App\Models\Eatery;
use App\Models\User;
use App\Services\EateryApiService;

/**
 * FoodTourApiController — Quản lý hành trình Food Tour, AI Generator & Nhật ký du ký ẩm thực
 */
class FoodTourApiController extends Controller
{
    /**
     * GET /api/v1/food-tours — Lấy danh sách Food Tour công khai và cộng đồng
     */
    public function getFoodTours(Request $request)
    {
        $mood = $request->query('mood');
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

        return response()->json([
            'success'         => true,
            'tours'           => $tours,
            'community_tours' => $communityTours
        ]);
    }

    /**
     * GET /api/v1/food-tours/{slug} — Xem chi tiết một Food Tour và nhật ký
     */
    public function getFoodTour($slug)
    {
        $tour = FoodTour::where('slug', $slug)
            ->with(['stops' => function($q) {
                $q->orderBy('stop_order');
            }, 'stops.eatery.category', 'stops.eatery.commune'])
            ->firstOrFail();

        $diaries = FoodTourDiary::where('food_tour_id', $tour->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'tour'    => $tour,
            'diaries' => $diaries
        ]);
    }

    /**
     * POST /api/v1/food-tours/generate-ai — Tạo lộ trình Food Tour thông minh bằng AI Gemini
     */
    public function generateAITour(Request $request)
    {
        $budgetLimit = (int) $request->input('budget', 300000);
        $mood = $request->input('mood', 'chill');
        
        $moodText = $mood;
        $extraConstraint = "ĐẶC BIỆT ƯU TIÊN các sản phẩm/cơ sở OCOP Tinh hoa bản địa";
        if ($mood === 'specialty') {
            $moodText = 'Khám phá đặc sản, Tinh hoa bản địa';
            $extraConstraint = "BẮT BUỘC ÍT NHẤT 2 TRONG 3 ĐỊA ĐIỂM PHẢI CÓ CATEGORY LÀ 'Tinh hoa bản địa' HOẶC LÀ CƠ SỞ OCOP";
        }
        
        $allEateries = EateryApiService::getEateries();
        
        $eateries = $allEateries->map(function($e) {
            return [
                'id'          => $e->id,
                'name'        => $e->name,
                'category'    => $e->category->name ?? 'Khác',
                'price_range' => $e->price_range,
                'description' => $e->description
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
            \"recommendation\": \"Gợi ý cụ thể nên ăn món gì hoặc làm hoạt động gì tại đây (VD: Thử bát phở tái nạm hoặc Đi dạo mua sắm đặc sản nông sản sạch).\"
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
            return response()->json(['success' => false, 'message' => 'Thiếu API Key Gemini.'], 500);
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
                throw new \Exception('Phản hồi rỗng từ Gemini API');
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
                throw new \Exception('Không giải mã được dữ liệu JSON từ AI');
            }
            
            $slug = Str::slug($aiData['tour_name']) . '-' . substr(md5(uniqid()), 0, 5);
            
            $tour = FoodTour::create([
                'user_id'         => auth()->check() ? auth()->id() : (session('user_id') ?: null),
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
                    if (!empty($unused)) {
                        $selectedId = reset($unused);
                    } else {
                        $selectedId = $validEateryIds[0] ?? 1;
                    }
                }

                $usedEateryIds[] = $selectedId;

                FoodTourStop::create([
                    'food_tour_id'   => $tour->id,
                    'eatery_id'      => $selectedId,
                    'stop_order'     => $index + 1,
                    'stop_story'     => $stop['recommendation'] ?? ("Điểm đến thứ " . ($index + 1) . " trong hành trình " . $aiData['tour_name'] . "."),
                    'estimated_time' => '45 phút'
                ]);
            }
            
            return response()->json(['success' => true, 'slug' => $slug]);
            
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi kết nối AI: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/food-tours/{id}/diary — Lưu nhật ký hành trình thực tế của thực khách
     */
    public function storeFoodTourDiary($id, Request $request)
    {
        $userId = $request->input('user_id') ?: (auth()->check() ? auth()->id() : null);
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập hoặc cung cấp user_id để lưu trữ nhật ký!'
            ], 401);
        }

        $imagePath = null;
        if ($request->input('image')) {
            $base64 = $request->input('image');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                $base64 = substr($base64, strpos($base64, ',') + 1);
                $type = strtolower($type[1]);

                if (in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    $base64 = base64_decode($base64);

                    if ($base64 !== false) {
                        $fileName = 'selfie_' . time() . '_' . uniqid() . '.' . $type;
                        $dir = public_path('uploads/diaries');
                        if (!file_exists($dir)) {
                            mkdir($dir, 0755, true);
                        }
                        file_put_contents($dir . '/' . $fileName, $base64);
                        $imagePath = '/uploads/diaries/' . $fileName;
                    }
                }
            }
        }

        $stopReviews = $request->input('stop_reviews', []);
        foreach ($stopReviews as $index => &$review) {
            if (!empty($review['image']) && preg_match('/^data:image\/(\w+);base64,/', $review['image'], $type)) {
                $imgBase64 = substr($review['image'], strpos($review['image'], ',') + 1);
                $type = strtolower($type[1]);

                if (in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    $imgBase64 = base64_decode($imgBase64);
                    if ($imgBase64 !== false) {
                        $fileName = 'stop_' . $index . '_' . time() . '_' . uniqid() . '.' . $type;
                        $dir = public_path('uploads/diaries');
                        if (!file_exists($dir)) {
                            mkdir($dir, 0755, true);
                        }
                        file_put_contents($dir . '/' . $fileName, $imgBase64);
                        $review['image_path'] = '/uploads/diaries/' . $fileName;
                        unset($review['image']);
                    }
                }
            }

            if (!empty($review['eatery_id'])) {
                $user = User::find($userId);
                $userName = $user ? $user->name : 'Thực khách Food Tour';
                
                $eatery = EateryApiService::getEateries()->firstWhere('id', $review['eatery_id']);
                if ($eatery) {
                    $mediaFiles = [];
                    if (!empty($review['image_path'])) {
                        $mediaFiles[] = [
                            'path' => $review['image_path'],
                            'type' => 'image'
                        ];
                    }
                    
                    EateryApiService::storeReview($eatery->category->slug, $eatery->id, [
                        'user_name'   => $userName,
                        'rating'      => $review['rating'] ?? null,
                        'comment'     => $review['comment'] ?? '',
                        'media_files' => $mediaFiles
                    ]);
                }
            }
        }

        $diary = FoodTourDiary::create([
            'food_tour_id'    => $id,
            'user_id'         => $userId,
            'rating'          => $request->input('rating'),
            'comment'         => $request->input('comment'),
            'image_path'      => $imagePath,
            'completed_stops' => $request->input('completed_stops', []),
            'stop_reviews'    => $stopReviews,
        ]);

        $tour = FoodTour::find($id);
        if ($tour && $tour->is_ai_generated && $tour->status === 'draft') {
            $updateData = [
                'status'  => 'saved',
                'user_id' => $tour->user_id ?: $userId
            ];
            if ($request->boolean('share_to_community')) {
                $updateData['shared_at'] = now();
                $updateData['expires_at'] = now()->addHours(72);
            }
            $tour->update($updateData);
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Lưu nhật ký hành trình thành công!',
            'image_url' => $imagePath,
            'diary'     => $diary
        ]);
    }
}
