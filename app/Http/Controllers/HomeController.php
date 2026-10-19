<?php

namespace App\Http\Controllers;

use App\Domain\Checkin\CheckinData;
use App\Services\EateryApiService;
use App\Services\Interfaces\HomeServiceInterface;
use App\Services\SpamProtectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct(
        protected HomeServiceInterface $homeService
    ) {}

    public function index(Request $request)
    {
        $selectedCatSlug = $request->query('cat');
        $selectedComSlug = $request->query('com');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 24;

        $data = $this->homeService->getHomeData($selectedCatSlug, $selectedComSlug, $page, $perPage);

        if ($request->has('ajax')) {
            return response()->json([
                'eateries'        => $data['eateries'],
                'ocopProducts'    => $data['ocopProducts'],
                'selectedCatSlug' => $data['selectedCatSlug'],
                'page'            => $data['page'],
                'per_page'        => $data['perPage'],
                'has_more'        => $data['hasMore'],
                'total'           => $data['totalCount'],
            ]);
        }

        return view('home', $data);
    }

    public function sitemap()
    {
        $data = $this->homeService->getSitemapData();
        return response()->view('sitemap', $data)
                         ->header('Content-Type', 'text/xml');
    }

    /**
     * Trang Bản đồ Tuyến đường số 4.0 - Xã Đông Anh
     */
    public function tuyenDuong40()
    {
        $data = $this->homeService->getTuyenDuongData();
        return view('tuyen-duong', $data);
    }

    public function getVideos()
    {
        $videos = $this->homeService->getVideoList();
        return response()->json($videos);
    }

    public function likeVideo($id)
    {
        $result = $this->homeService->likeVideo((int) $id);
        if (!$result) {
            return response()->json(['success' => false, 'message' => 'Video không tồn tại'], 404);
        }

        return response()->json([
            'success'     => true,
            'likes_count' => $result['likes_count'],
        ]);
    }

    /**
     * Trang Bản tin (Newsfeed chuyên biệt cho các bài đăng Profile, Trường học, Gian hàng & Cập nhật cộng đồng)
     */
    public function newsfeed()
    {
        $feedType = request()->query('feed_type', 'for_you');
        $currentUserId = Auth::id() ?? session('user_id');

        $data = $this->homeService->getNewsfeedData($currentUserId, $feedType);

        return view('newsfeed', $data);
    }

    /**
     * Trang newsfeed cộng đồng check-in — gồm cả Food Tour Diary và Check-in đơn lẻ
     */
    public function checkinFeed()
    {
        $user = auth('sanctum')->user() ?: Auth::user();
        $userId = $user ? $user->id : session('user_id');
        $sessionId = session()->getId();

        $data = $this->homeService->getCheckinFeedData($userId, $sessionId);

        return view('checkin', $data);
    }

    /**
     * Lưu check-in đơn lẻ mới từ form
     */
    public function storeCheckin(Request $request)
    {
        $request->validate([
            'eatery_id'    => 'nullable|integer',
            'eatery_slug'  => 'nullable|string',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:2000',
            'guest_name'   => 'nullable|string|max:100',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'image_base64' => 'nullable|string',
        ]);

        $eaterySlug = $request->input('eatery_slug');
        $eateryId = $request->input('eatery_id');

        if (!empty($eaterySlug)) {
            $hasEatery = \App\Models\Eatery::where('slug', $eaterySlug)->exists() || 
                         !is_null(EateryApiService::getEateryBySlug($eaterySlug));
            if (!$hasEatery) {
                return back()->withErrors(['eatery_id' => 'Địa điểm check-in không hợp lệ.'])->withInput();
            }
        } elseif (!empty($eateryId)) {
            $hasEatery = \App\Models\Eatery::where('id', $eateryId)->exists();
            if (!$hasEatery) {
                return back()->withErrors(['eatery_id' => 'Địa điểm check-in không hợp lệ.'])->withInput();
            }
        }

        $data = CheckinData::fromRequest($request);
        $this->homeService->storeCheckin(
            $data,
            Auth::id(),
            $eateryId ? (int) $eateryId : null,
            $request->input('comment')
        );

        return redirect()->route('checkin.feed')->with('success', 'Check-in của bạn đã được đăng thành công! 🎉');
    }

    /**
     * API tìm kiếm địa điểm (dùng trong modal AJAX)
     */
    public function searchEateries(Request $request)
    {
        $q = trim($request->query('q', ''));
        $eateries = $this->homeService->searchEateries($q);

        return response()->json($eateries);
    }

    /**
     * API tìm địa điểm gần vị trí hiện tại nhất
     */
    public function nearbyEateries(Request $request)
    {
        $lat = (float) $request->query('lat');
        $lng = (float) $request->query('lng');
        $radius = (float) ($request->query('radius', 3));

        if (!$lat || !$lng) {
            return response()->json(['error' => 'Missing coordinates'], 422);
        }

        $eateries = $this->homeService->getNearbyEateries($lat, $lng, $radius);
        return response()->json($eateries);
    }

    /**
     * API lấy danh sách check-in mới hơn một timestamp
     */
    public function latestCheckins(Request $request)
    {
        $after = $request->query('after');
        $checkins = $this->homeService->getLatestCheckins($after ? (int) $after : null);

        return response()->json($checkins);
    }

    /**
     * Lấy danh sách thông báo cho Web Header
     */
    public function getWebNotifications(Request $request)
    {
        $userId = auth()->id() ?? session('user_id');
        if (!$userId) {
            return response()->json([]);
        }

        $notifications = $this->homeService->getWebNotifications($userId);
        return response()->json($notifications);
    }

    /**
     * Đánh dấu đã đọc tất cả thông báo
     */
    public function markWebNotificationsRead(Request $request)
    {
        $userId = auth()->id() ?? session('user_id');
        if ($userId) {
            $this->homeService->markWebNotificationsRead($userId);
        }
        return response()->json(['success' => true]);
    }

    /**
     * API phát event thả emoji (Reactions) qua WebSocket & lưu vào DB
     */
    public function reactToCheckin(Request $request, $id)
    {
        $request->validate([
            'emoji' => ['required', 'string', 'max:10'],
            'type'  => ['required', 'string', 'in:checkin,diary,post'],
        ]);

        $type = $request->input('type', 'checkin');
        $emoji = $request->input('emoji', '👍');

        $user = auth('sanctum')->user() ?: Auth::user();
        $userId = $user ? $user->id : session('user_id');
        $sessionId = session()->getId() ?: ('guest_' . md5($request->ip() . ($request->header('User-Agent') ?? '')));

        $result = $this->homeService->reactToCheckin((int) $id, $type, $emoji, $userId, $sessionId);

        return response()->json(array_merge(['success' => true], $result));
    }

    /**
     * Tương tác Like / Thả tim chuẩn DB Real
     */
    public function toggleReaction(Request $request)
    {
        try {
            $request->validate([
                'id'    => 'required|integer',
                'type'  => 'required|string|in:post,eatery,checkin,diary',
                'emoji' => 'nullable|string|max:10',
            ]);

            $id = (int) $request->input('id');
            $type = $request->input('type');
            $emoji = $request->input('emoji', '👍');

            $user = auth('sanctum')->user() ?: Auth::user();
            $userId = $user ? $user->id : session('user_id');
            $sessionId = session()->getId() ?: ('guest_' . md5($request->ip() . ($request->header('User-Agent') ?? '')));

            $result = $this->homeService->toggleReaction($id, $type, $emoji, $userId, $sessionId);

            return response()->json(array_merge(['success' => true], $result));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('toggleLike error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra. Vui lòng thử lại sau.',
            ], 400);
        }
    }

    /**
     * Lấy danh sách người đã thích bài viết
     */
    public function getPostLikers(Request $request)
    {
        $request->validate([
            'id'   => 'required|integer',
            'type' => 'required|string|in:post,eatery,checkin,diary',
        ]);

        $likers = $this->homeService->getPostLikers((int) $request->input('id'), $request->input('type'));

        return response()->json([
            'success' => true,
            'likers'  => $likers,
            'total'   => count($likers),
        ]);
    }

    /**
     * Lấy danh sách bình luận thực tế từ DB
     */
    public function getComments(Request $request)
    {
        $request->validate([
            'id'   => 'required|integer',
            'type' => 'required|string|in:post,eatery,checkin,diary',
        ]);

        $comments = $this->homeService->getComments((int) $request->id, $request->type);

        return response()->json([
            'success'  => true,
            'count'    => count($comments),
            'comments' => $comments,
        ]);
    }

    /**
     * Lưu bình luận mới vào DB
     */
    public function storeComment(Request $request)
    {
        $request->validate([
            'id'      => 'required|integer',
            'type'    => 'required|string|in:post,eatery,checkin,diary',
            'content' => 'required|string|max:1000',
        ]);

        $content = (string) $request->input('content');

        // Kiểm tra chống bot và lọc spam
        $spamCheck = SpamProtectionService::check($request, $content, 'comment');
        if ($spamCheck['is_spam']) {
            return response()->json([
                'success' => false,
                'message' => $spamCheck['reason'],
            ], $spamCheck['code']);
        }

        $userId = Auth::id() ?? session('user_id');
        $result = $this->homeService->storeComment(
            (int) $request->input('id'),
            $request->input('type'),
            $content,
            $userId,
            $request->input('guest_name')
        );

        SpamProtectionService::recordSuccess($request, $content, 'comment');

        return response()->json(array_merge(['success' => true], $result));
    }

    /**
     * Tăng số lượt chia sẻ cho bài viết
     */
    public function incrementShare(Request $request)
    {
        $request->validate([
            'id'   => 'required',
            'type' => 'nullable|string|in:post,education,checkin',
        ]);

        $userId = Auth::id() ?? session('user_id');
        $newShareCount = $this->homeService->incrementShare($request->input('id'), $request->input('type'), $userId);

        return response()->json([
            'success'      => true,
            'shares_count' => $newShareCount,
            'message'      => 'Tăng số lượt chia sẻ thành công',
        ]);
    }

    public function privacyPolicy()
    {
        return view('privacy_policy');
    }
}
