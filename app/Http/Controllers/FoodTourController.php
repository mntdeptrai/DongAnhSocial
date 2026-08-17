<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use App\Models\FoodTour;
use App\Models\FoodTourStop;
use Illuminate\Http\Request;
use App\Services\EateryApiService;
use App\Helpers\R2Helper;

class FoodTourController extends Controller
{
    /**
     * Display a listing of the pre-designed food tours.
     */
    public function index(Request $request)
    {
        $mood = $request->query('mood');
        
        // Lấy tất cả lộ trình chính thức, lộ trình cộng đồng đã chia sẻ, và lộ trình cá nhân của người dùng hiện tại
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

        // Lấy các AI Tour đang được chia sẻ công khai bởi cộng đồng (chưa hết 72h)
        $communityTours = FoodTour::community()
            ->with(['stops.eatery', 'diaries.user'])
            ->withCount('diaries')
            ->orderBy('shared_at', 'desc')
            ->get();
        
        return view('food-tours.index', compact('tours', 'mood', 'communityTours'));
    }

    /**
     * Display a listing of cooking and experience tours.
     */
    public function cookingIndex(Request $request)
    {
        $tours = FoodTour::public()
            ->where('mood', 'cooking')
            ->with(['stops.eatery', 'diaries.user'])
            ->withCount('diaries')
            ->get();
            
        $activities = \App\Services\EateryApiService::getAllCulturalActivities();
            
        return view('food-tours.cooking', compact('tours', 'activities'));
    }

    /**
     * Display the specified food tour details.
     */
    public function show(Request $request, $slug)
    {
        $tour = FoodTour::where('slug', $slug)
            ->with(['stops' => function($q) {
                $q->orderBy('stop_order');
            }, 'stops.eatery.category', 'stops.eatery.commune'])
            ->firstOrFail();

        // Hydrate stops eateries if multi-db connection returns null
        $allEateries = EateryApiService::getEateries();
        foreach ($tour->stops as $stop) {
            if (!$stop->eatery) {
                $eatery = $allEateries->firstWhere('id', $stop->eatery_id);
                if ($eatery) {
                    $stop->setRelation('eatery', $eatery);
                }
            }
        }

        $diaries = \App\Models\FoodTourDiary::where('food_tour_id', $tour->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('food-tours.show', compact('tour', 'diaries'));
    }

    /**
     * Advanced Simulated AI Tour Generator.
     * Takes budget, mood, and optional starting region, filters eateries,
     * arranges them geographically using a Nearest Neighbor traveling salesman heuristic,
     * and returns a custom generated cinematic food tour.
     */
    public function generateAI(Request $request)
    {
        $budgetLimit = (int) $request->input('budget', 300000);
        $mood = $request->input('mood', 'chill');
        
        $moodText = $mood;
        $extraConstraint = "ĐẶC BIỆT ƯU TIÊN các sản phẩm/cơ sở OCOP Tinh hoa bản địa";
        if ($mood === 'specialty') {
            $moodText = 'Khám phá đặc sản, Tinh hoa bản địa';
            $extraConstraint = "BẮT BUỘC ÍT NHẤT 2 TRONG 3 ĐỊA ĐIỂM PHẢI CÓ CATEGORY LÀ 'Tinh hoa bản địa' HOẶC LÀ CƠ SỞ OCOP";
        }
        
        $eateries = EateryApiService::getEateries()->map(function($e) {
            return [
                'id' => $e->id,
                'name' => $e->name,
                'category' => $e->category->name ?? 'Khác',
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
            return response()->json(['success' => false, 'message' => 'Thiếu API Key Gemini.']);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);
            
            $result = $response->json();
            $textResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
            
            if ($textResponse === null) {
                \Illuminate\Support\Facades\Log::error('Gemini API Full Response: ' . json_encode($result));
                $errMsg = $result['error']['message'] ?? 'Phản hồi rỗng từ Gemini';
                throw new \Exception('API Error: ' . $errMsg);
            }
            
            \Illuminate\Support\Facades\Log::info('Gemini Raw Response: ' . $textResponse);
            
            // Tách phần JSON ra khỏi phản hồi (nếu AI có chèn thêm chữ ở đầu hoặc cuối)
            if (preg_match('/\{.*\}/s', $textResponse, $matches)) {
                $jsonString = $matches[0];
            } else {
                $jsonString = $textResponse;
            }
            
            $aiData = json_decode(trim($jsonString), true);
            
            // Fallback for older prompt format just in case AI hallucinates
            if (isset($aiData['eatery_ids']) && !isset($aiData['stops'])) {
                $aiData['stops'] = array_map(function($id) {
                    return ['eatery_id' => $id, 'recommendation' => 'Trải nghiệm ẩm thực địa phương hấp dẫn tại đây.'];
                }, $aiData['eatery_ids']);
            }

            if (!$aiData || !isset($aiData['stops']) || count($aiData['stops']) < 1) {
                \Illuminate\Support\Facades\Log::error('JSON Decode Error: ' . json_last_error_msg());
                throw new \Exception('Invalid JSON format returned from AI');
            }
            
            $slug = \Illuminate\Support\Str::slug($aiData['tour_name']) . '-' . substr(md5(uniqid()), 0, 5);
            
            $tour = FoodTour::create([
                'user_id' => auth()->id(),
                'name' => $aiData['tour_name'],
                'slug' => $slug,
                'description' => $aiData['description'],
                'duration' => '2.5 giờ',
                'distance' => '5.0 km',
                'budget' => number_format($budgetLimit, 0, ',', '.') . 'đ',
                'difficulty' => $aiData['difficulty'],
                'best_time' => '17:00 - 21:00',
                'popularity' => 'Mới tạo',
                'mood' => $mood,
                'thumbnail' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80',
                'story' => $aiData['story'],
                'status' => 'draft',           // Trạng thái ban đầu: bản nháp
                'is_ai_generated' => true,      // Đánh dấu đây là AI Tour
            ]);
            
            $validEateryIds = \App\Models\Eatery::pluck('id')->toArray();
            $usedEateryIds = [];

            foreach ($aiData['stops'] as $index => $stop) {
                $rawId = $stop['eatery_id'] ?? null;
                $selectedId = null;

                // 1. Kiểm tra ID có hợp lệ và chưa được dùng trong tour này
                if ($rawId && is_numeric($rawId) && in_array((int)$rawId, $validEateryIds) && !in_array((int)$rawId, $usedEateryIds)) {
                    $selectedId = (int)$rawId;
                }

                // 2. Kiểm tra nếu AI trả về chuỗi tên địa điểm
                if (!$selectedId && is_string($rawId)) {
                    $found = $allEateries->first(function($e) use ($rawId) {
                        return \Illuminate\Support\Str::contains(mb_strtolower($e->name), mb_strtolower($rawId));
                    });
                    if ($found && !in_array($found->id, $usedEateryIds)) {
                        $selectedId = $found->id;
                    }
                }

                // 3. Fallback: Tự động chọn địa điểm chưa sử dụng trong DB
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
            \Illuminate\Support\Facades\Log::error('Gemini API Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Hệ thống AI đang bận hoặc tạo lộ trình chưa hoàn tất. Vui lòng bấm nút "Tạo Lộ Trình" lại lần nữa!']);
        }
    }

    /**
     * Store completed food tour diary.
     */
    public function storeDiary(Request $request, $id, \App\Services\FoodTourDiaryService $diaryService)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để lưu trữ nhật ký hành trình và chia sẻ ảnh kỷ niệm!'
            ], 401);
        }

        $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'image' => 'nullable|string', // base64 string
            'completed_stops' => 'nullable|array',
            'stop_reviews' => 'nullable|array',
        ]);

        $dto = \App\Domain\FoodTour\FoodTourDiaryData::fromRequest($request, (int)$id);
        $diary = $diaryService->createDiary($dto, $request->boolean('share_to_community'));

        return response()->json([
            'success' => true,
            'message' => 'Lưu nhật ký hành trình thành công!',
            'image_url' => $diary->image_path
        ]);
    }

    /**
     * Haversine Distance Formula.
     */
    private function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = 
            sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
            sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }

    /**
     * Show form to manually create a custom food tour.
     */
    public function create()
    {
        $eateries = EateryApiService::getEateries();
        return view('food-tours.form', compact('eateries'));
    }

    /**
     * Store the manually created custom food tour.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|string|max:100',
            'distance' => 'required|string|max:100',
            'budget' => 'required|string|max:100',
            'difficulty' => 'nullable|string|max:100',
            'best_time' => 'required|string|max:100',
            'mood' => 'nullable|string|max:100',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'thumbnail' => 'nullable|string',
            'story' => 'nullable|string',
            'stops' => 'required|array|min:1',
            'stops.*.eatery_id' => 'required|integer',
            'stops.*.stop_story' => 'nullable|string',
            'stops.*.estimated_time' => 'nullable|string|max:100',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail_file')) {
            $file = $request->file('thumbnail_file');
            $uploadDir = public_path('uploads/tours');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $thumbnailPath = '/uploads/tours/' . $fileName;
        } elseif ($request->filled('thumbnail')) {
            $thumbnailPath = $request->input('thumbnail');
        } else {
            $thumbnailPath = 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80';
        }

        $slug = \Illuminate\Support\Str::slug($request->input('name')) . '-' . substr(md5(uniqid()), 0, 5);

        $tour = FoodTour::create([
            'user_id' => auth()->id(),
            'name' => $request->input('name'),
            'slug' => $slug,
            'description' => $request->input('description'),
            'duration' => $request->input('duration'),
            'distance' => $request->input('distance'),
            'budget' => $request->input('budget'),
            'difficulty' => $request->input('difficulty') ?: '☕ Nhẹ nhàng',
            'best_time' => $request->input('best_time'),
            'popularity' => 'Mới tạo',
            'mood' => $this->determineMood($request->input('name'), $request->input('description'), $request->input('budget'), $request->input('stops')),
            'thumbnail' => $thumbnailPath,
            'story' => $request->input('story'),
            'status' => 'saved',
            'is_ai_generated' => false,
        ]);

        foreach ($request->input('stops') as $index => $stop) {
            FoodTourStop::create([
                'food_tour_id' => $tour->id,
                'eatery_id' => $stop['eatery_id'],
                'stop_order' => $index + 1,
                'stop_story' => $stop['stop_story'] ?? '',
                'estimated_time' => $stop['estimated_time'] ?? '45 phút',
            ]);
        }

        return redirect()->route('food-tours.show', $slug)->with('success', 'Tạo lộ trình thành công!');
    }

    /**
     * Show form to edit a custom food tour.
     */
    public function edit($slug)
    {
        $tour = FoodTour::where('slug', $slug)->with('stops.eatery')->firstOrFail();

        // Strict Ownership & Permission Check:
        // A user can ONLY edit their own created tour ($tour->user_id === auth()->id()).
        // Admins can ONLY edit official system tours ($tour->user_id === null).
        $canManage = auth()->check() && (
            ($tour->user_id !== null && $tour->user_id === auth()->id()) ||
            ($tour->user_id === null && (optional(auth()->user())->role === 'admin' || session('user_role') === 'admin'))
        );

        if (!$canManage) {
            abort(403, 'Bạn không có quyền chỉnh sửa lộ trình ẩm thực này.');
        }

        $eateries = EateryApiService::getEateries();
        return view('food-tours.form', compact('tour', 'eateries'));
    }

    /**
     * Update the custom food tour.
     */
    public function update(Request $request, $slug)
    {
        $tour = FoodTour::where('slug', $slug)->firstOrFail();

        $canManage = auth()->check() && (
            ($tour->user_id !== null && $tour->user_id === auth()->id()) ||
            ($tour->user_id === null && (optional(auth()->user())->role === 'admin' || session('user_role') === 'admin'))
        );

        if (!$canManage) {
            abort(403, 'Bạn không có quyền cập nhật lộ trình ẩm thực này.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|string|max:100',
            'distance' => 'required|string|max:100',
            'budget' => 'required|string|max:100',
            'difficulty' => 'nullable|string|max:100',
            'best_time' => 'required|string|max:100',
            'mood' => 'nullable|string|max:100',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'thumbnail' => 'nullable|string',
            'story' => 'nullable|string',
            'stops' => 'required|array|min:1',
            'stops.*.eatery_id' => 'required|integer',
            'stops.*.stop_story' => 'nullable|string',
            'stops.*.estimated_time' => 'nullable|string|max:100',
        ]);

        $thumbnailPath = $tour->thumbnail;
        if ($request->hasFile('thumbnail_file')) {
            $file = $request->file('thumbnail_file');
            $uploadDir = public_path('uploads/tours');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $thumbnailPath = '/uploads/tours/' . $fileName;
        } elseif ($request->filled('thumbnail')) {
            $thumbnailPath = $request->input('thumbnail');
        }

        $tour->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'duration' => $request->input('duration'),
            'distance' => $request->input('distance'),
            'budget' => $request->input('budget'),
            'difficulty' => $request->input('difficulty') ?: '☕ Nhẹ nhàng',
            'best_time' => $request->input('best_time'),
            'mood' => $this->determineMood($request->input('name'), $request->input('description'), $request->input('budget'), $request->input('stops')),
            'thumbnail' => $thumbnailPath ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80',
            'story' => $request->input('story'),
        ]);

        // Recreate stops
        $tour->stops()->delete();

        foreach ($request->input('stops') as $index => $stop) {
            FoodTourStop::create([
                'food_tour_id' => $tour->id,
                'eatery_id' => $stop['eatery_id'],
                'stop_order' => $index + 1,
                'stop_story' => $stop['stop_story'] ?? '',
                'estimated_time' => $stop['estimated_time'] ?? '45 phút',
            ]);
        }

        return redirect()->route('food-tours.show', $tour->slug)->with('success', 'Cập nhật lộ trình thành công!');
    }

    /**
     * Delete the custom food tour.
     */
    public function destroy($slug)
    {
        $tour = FoodTour::where('slug', $slug)->firstOrFail();

        $canManage = auth()->check() && (
            ($tour->user_id !== null && $tour->user_id === auth()->id()) ||
            ($tour->user_id === null && (optional(auth()->user())->role === 'admin' || session('user_role') === 'admin'))
        );

        if (!$canManage) {
            abort(403, 'Bạn không có quyền xóa lộ trình ẩm thực này.');
        }

        $tour->delete();

        return redirect()->route('food-tours.index')->with('success', 'Xóa lộ trình thành công!');
    }

    /**
     * Toggle public sharing of custom food tour.
     */
    public function share($slug)
    {
        $tour = FoodTour::where('slug', $slug)->firstOrFail();

        $canManage = auth()->check() && (
            ($tour->user_id !== null && $tour->user_id === auth()->id()) ||
            ($tour->user_id === null && (optional(auth()->user())->role === 'admin' || session('user_role') === 'admin'))
        );

        if (!$canManage) {
            abort(403, 'Bạn không có quyền chia sẻ lộ trình ẩm thực này.');
        }

        if ($tour->shared_at) {
            $tour->update([
                'shared_at' => null,
                'expires_at' => null,
            ]);
            $message = 'Đã hủy chia sẻ lộ trình lên cộng đồng!';
        } else {
            $tour->update([
                'shared_at' => now(),
                'expires_at' => null, // User tours do not expire
            ]);
            $message = 'Đã chia sẻ lộ trình lên cộng đồng thành công!';
        }

        return back()->with('success', $message);
    }

    /**
     * Tự động phân loại tâm trạng (mood) dựa trên nội dung lộ trình và thông tin các chặng dừng chân.
     */
    private function determineMood($name, $description, $budget, $stops = [])
    {
        $text = mb_strtolower($name . ' ' . $description . ' ' . $budget);
        
        // Thu thập danh sách ID địa điểm trong các chặng dừng
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
            $eateries = \App\Models\Eatery::with('category')->whereIn('id', $eateryIds)->get();
        }
        
        // 1. Phân loại theo Đặc sản Đông Anh (specialty)
        // Nếu chặng dừng có thuộc danh mục di sản, chợ truyền thống/OCOP, hoặc tên/mô tả địa điểm có từ khóa liên quan
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
        
        // 2. Phân loại theo Ăn đêm Cao Lỗ (night)
        // Nếu chặng dừng ở Cao Lỗ, hoặc mở cửa đêm/muộn, hoặc tên/mô tả có chữ đêm, tối
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
        
        // 3. Phân loại theo Sinh viên giá rẻ (cheap)
        // Nếu có địa điểm thuộc quán ăn vặt, trà sữa hoặc mức giá rẻ/bình dân
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
        
        // Check budget của cả tour
        preg_match_all('/\d+/', str_replace('.', '', $budget), $matches);
        if (!empty($matches[0])) {
            $maxBudget = max(array_map('intval', $matches[0]));
            if ($maxBudget > 0 && $maxBudget <= 150000) {
                return 'cheap';
            }
        }
        
        // 4. Mặc định là chill
        return 'chill';
    }
}
