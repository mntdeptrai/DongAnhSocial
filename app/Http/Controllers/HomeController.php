<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\Eatery;
use App\Models\ReviewVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\EateryApiService;
use App\Helpers\R2Helper;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $categories = EateryApiService::getCategories();
        $communes = EateryApiService::getCommunes();
        
        $selectedCatSlug = $request->query('cat');
        $selectedComSlug = $request->query('com');
        
        $selectedComId = null;
        if ($selectedComSlug) {
            $commune = $communes->firstWhere('slug', $selectedComSlug);
            if ($commune) {
                $selectedComId = $commune->id;
            }
        }
        
        $eateries = EateryApiService::getEateries($selectedCatSlug, [
            'commune_id' => $selectedComId
        ]);

        $ocopProducts = collect();
        if ($selectedCatSlug === 'dong-anh-market') {
            $ocopProducts = EateryApiService::getOcopProducts([
                'commune_id' => $selectedComId
            ]);
        }
        
        $featuredEateries = EateryApiService::getEateries(null, [
            'is_featured' => true
        ]);
            
        $specialties = EateryApiService::getEateries('dong-anh-market');

        if ($request->has('ajax')) {
            return response()->json([
                'eateries' => $eateries,
                'ocopProducts' => $ocopProducts,
                'selectedCatSlug' => $selectedCatSlug
            ]);
        }

        return view('home', compact(
            'categories', 
            'communes', 
            'eateries', 
            'ocopProducts',
            'featuredEateries', 
            'specialties', 
            'selectedCatSlug', 
            'selectedComSlug'
        ));
    }

    public function sitemap()
    {
        $eateries = EateryApiService::getEateries();
        return response()->view('sitemap', compact('eateries'))
                         ->header('Content-Type', 'text/xml');
    }

    public function getVideos()
    {
        $videos = EateryApiService::getVideos()->map(function($vid) {
            return [
                'id' => $vid->id,
                'title' => $vid->title,
                'video_url' => $vid->video_url,
                'video_type' => $vid->video_type,
                'thumbnail_path' => $vid->thumbnail_path,
                'likes_count' => $vid->likes_count,
                'eatery' => [
                    'name' => $vid->eatery->name,
                    'slug' => $vid->eatery->slug,
                    'address' => $vid->eatery->address,
                    'rating' => (float)$vid->eatery->rating,
                    'category' => $vid->eatery->category->name,
                    'latitude' => $vid->eatery->latitude,
                    'longitude' => $vid->eatery->longitude,
                    'image_path' => $vid->eatery->image_path,
                ]
            ];
        });

        return response()->json($videos);
    }

    public function likeVideo($id)
    {
        $result = EateryApiService::likeVideo($id);
        if (!$result) {
            return response()->json(['success' => false, 'message' => 'Video không tồn tại'], 404);
        }

        return response()->json([
            'success' => true,
            'likes_count' => $result['likes_count']
        ]);
    }

    /**
     * Trang newsfeed cộng đồng check-in — gồm cả Food Tour Diary và Check-in đơn lẻ
     */
    public function checkinFeed()
    {
        // 1. Lấy diary từ Food Tour
        $diaries = \App\Models\FoodTourDiary::with(['user', 'foodTour.stops', 'comments.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Lấy Check-in đơn lẻ mới nhất
        $standaloneCheckins = Checkin::with(['user', 'eatery.category', 'eatery.commune', 'comments.user'])
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();

        $eateries = EateryApiService::getEateries();
        $eateriesMap = $eateries->keyBy('id');

        // 3. Danh sách địa điểm cho modal tạo check-in từ tất cả 7 DB
        $allEateries = EateryApiService::getEateries()->sortBy('name')->values();

        return view('checkin', compact('diaries', 'eateriesMap', 'standaloneCheckins', 'allEateries'));
    }

    /**
     * Lưu check-in đơn lẻ mới từ form
     */
    public function storeCheckin(Request $request, \App\Services\CheckinService $checkinService)
    {
        $request->validate([
            'eatery_id'    => 'required|integer',
            'eatery_slug'  => 'nullable|string',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:2000',
            'guest_name'   => 'nullable|string|max:100',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'image_base64' => 'nullable|string',
        ]);

        $eaterySlug = $request->input('eatery_slug');
        $eateryId = $request->input('eatery_id');
        $hasEatery = false;

        if (!empty($eaterySlug)) {
            $hasEatery = \App\Models\Eatery::where('slug', $eaterySlug)->exists() || 
                         !is_null(EateryApiService::getEateryBySlug($eaterySlug));
        } else {
            $hasEatery = \App\Models\Eatery::where('id', $eateryId)->exists();
        }

        if (!$hasEatery) {
            return back()->withErrors(['eatery_id' => 'Địa điểm check-in không hợp lệ.'])->withInput();
        }

        $data = \App\Domain\Checkin\CheckinData::fromRequest($request);
        $checkinService->createCheckin($data);

        return redirect()->route('checkin.feed')->with('success', 'Check-in của bạn đã được đăng thành công! 🎉');
    }

    /**
     * API tìm kiếm địa điểm (dùng trong modal AJAX)
     */
    public function searchEateries(Request $request)
    {
        $q = trim($request->query('q', ''));
        $eateries = EateryApiService::getEateries();

        if ($q !== '') {
            $qNormalized = mb_strtolower($this->removeVietnameseSign($q));
            $eateries = $eateries->filter(function($e) use ($qNormalized) {
                $nameNormalized = mb_strtolower($this->removeVietnameseSign($e->name));
                $addressNormalized = mb_strtolower($this->removeVietnameseSign($e->address ?? ''));
                $descNormalized = mb_strtolower($this->removeVietnameseSign($e->description ?? ''));

                return str_contains($nameNormalized, $qNormalized) || 
                       str_contains($addressNormalized, $qNormalized) ||
                       str_contains($descNormalized, $qNormalized);
            });
        }

        $eateries = $eateries->sortBy('name')->take(20);

        return response()->json($eateries->map(function($e) {
            return [
                'id'       => $e->id,
                'name'     => $e->name,
                'address'  => $e->address ?? '',
                'category' => optional($e->category)->name ?? 'Địa điểm',
                'commune'  => optional($e->commune)->name ?? 'Đông Anh',
                'image'    => $e->image_path,
                'rating'   => $e->rating,
                'slug'     => $e->slug,
            ];
        }));
    }

    /**
     * API tìm địa điểm gần vị trí hiện tại nhất (Haversine formula in PHP)
     */
    public function nearbyEateries(Request $request)
    {
        $lat    = (float) $request->query('lat');
        $lng    = (float) $request->query('lng');
        $radius = (float) ($request->query('radius', 3)); // km, default 3km

        if (!$lat || !$lng) {
            return response()->json(['error' => 'Missing coordinates'], 422);
        }

        // Lấy tất cả các địa điểm trên cả 7 cơ sở dữ liệu và tính khoảng cách bằng PHP
        $eateries = EateryApiService::getEateries()
            ->filter(function($e) use ($lat, $lng, $radius) {
                if (is_null($e->latitude) || is_null($e->longitude)) {
                    return false;
                }

                $earthRadius = 6371; // km
                $latFrom = deg2rad($lat);
                $lonFrom = deg2rad($lng);
                $latTo = deg2rad((float) $e->latitude);
                $lonTo = deg2rad((float) $e->longitude);

                $latDelta = $latTo - $latFrom;
                $lonDelta = $lonTo - $lonFrom;

                $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

                $distance = $angle * $earthRadius;
                $e->distance = $distance;

                return $distance <= $radius;
            })
            ->sortBy('distance')
            ->take(10)
            ->values();

        return response()->json($eateries->map(function($e) {
            return [
                'id'       => $e->id,
                'name'     => $e->name,
                'address'  => $e->address ?? '',
                'category' => optional($e->category)->name ?? 'Địa điểm',
                'commune'  => optional($e->commune)->name ?? 'Đông Anh',
                'image'    => $e->image_path,
                'rating'   => $e->rating,
                'slug'     => $e->slug,
                'distance' => round($e->distance * 1000), // metres
            ];
        }));
    }

    /**
     * API lấy danh sách check-in mới hơn một timestamp nhất định
     * Dùng làm fallback khi WebSocket (Reverb) mất kết nối
     */
    public function latestCheckins(Request $request)
    {
        $after = $request->query('after'); // Unix timestamp

        $query = Checkin::with(['user', 'eatery.category', 'eatery.commune'])
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit(20);

        if ($after) {
            $query->where('created_at', '>', \Carbon\Carbon::createFromTimestamp((int) $after));
        }

        $checkins = $query->get()->map(function ($checkin) {
            return [
                'id'                => $checkin->id,
                'display_name'      => $checkin->display_name,
                'avatar_char'       => $checkin->user
                    ? mb_substr($checkin->user->name, 0, 1, 'UTF-8')
                    : '👤',
                'role'              => $checkin->user?->role ?? 'guest',
                'rating'            => (int) $checkin->rating,
                'comment'           => $checkin->comment,
                'image_path'        => $checkin->image_path,
                'created_at_human'  => $checkin->created_at->diffForHumans(),
                'created_at_format' => $checkin->created_at->format('d/m/Y H:i'),
                'created_at_ts'     => $checkin->created_at->timestamp,
                'eatery' => $checkin->eatery ? [
                    'name'     => $checkin->eatery->name,
                    'slug'     => $checkin->eatery->slug,
                    'category' => $checkin->eatery->category?->name,
                    'commune'  => $checkin->eatery->commune?->name,
                ] : null,
            ];
        });

        return response()->json($checkins);
    }

    /**
     * API phát event thả emoji (Reactions) qua WebSocket
     */
    public function reactToCheckin(Request $request, $id)
    {
        $request->validate([
            'emoji' => ['required', 'string', 'max:10'],
            'type'  => ['required', 'string', 'in:checkin,diary'],
        ]);

        event(new \App\Events\CheckinReacted((int) $id, $request->type, $request->emoji));

        return response()->json(['success' => true]);
    }

    private function removeVietnameseSign($str)
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
        $str = preg_replace("/(đ)/", "d", $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "A", $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $str);
        $str = preg_replace("/(Ì|Í|Ị|I|Ĩ)/", "I", $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $str);
        $str = preg_replace("/(Đ)/", "D", $str);
        return $str;
    }
}

