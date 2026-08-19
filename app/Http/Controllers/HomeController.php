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
        $selectedCatSlug = $request->query('cat');
        $selectedComSlug = $request->query('com');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 24;

        $categories = \Illuminate\Support\Facades\Cache::remember('home_categories', 1800, function() {
            return EateryApiService::getCategories();
        });

        $communes = \Illuminate\Support\Facades\Cache::remember('home_communes', 1800, function() {
            return EateryApiService::getCommunes();
        });

        $selectedComId = null;
        if ($selectedComSlug) {
            $commune = $communes->firstWhere('slug', $selectedComSlug);
            if ($commune) {
                $selectedComId = $commune->id;
            }
        }

        $totalCount = EateryApiService::countEateries($selectedCatSlug, [
            'commune_id' => $selectedComId
        ]);

        $eateries = EateryApiService::getEateries($selectedCatSlug, [
            'commune_id' => $selectedComId,
            'page' => $page,
            'per_page' => $perPage,
        ]);

        $ocopProducts = collect();
        if ($selectedCatSlug === 'dong-anh-market') {
            $ocopProducts = EateryApiService::getOcopProducts([
                'commune_id' => $selectedComId
            ]);
        }

        if ($request->has('ajax')) {
            return response()->json([
                'eateries' => $eateries,
                'ocopProducts' => $ocopProducts,
                'selectedCatSlug' => $selectedCatSlug,
                'page' => $page,
                'per_page' => $perPage,
                'has_more' => ($page * $perPage) < $totalCount,
                'total' => $totalCount,
            ]);
        }

        return view('home', compact(
            'categories', 
            'communes', 
            'eateries',
            'ocopProducts',
            'selectedCatSlug', 
            'selectedComSlug',
            'totalCount',
            'page',
            'perPage'
        ));
    }

    public function sitemap()
    {
        $eateries = EateryApiService::getEateries();
        return response()->view('sitemap', compact('eateries'))
                         ->header('Content-Type', 'text/xml');
    }

    /**
     * Trang Bản đồ Tuyến đường số 4.0 - Xã Đông Anh
     */
    public function tuyenDuong40()
    {
        $dbRoutes = \App\Models\DigitalRoute::select('route_key', 'name', 'length', 'color', 'village_key', 'anim_class', 'path_coords')->get()->map(function($r) {
            return [
                'id' => $r->route_key,
                'name' => $r->name,
                'length' => $r->length,
                'color' => $r->color,
                'villages' => [$r->village_key],
                'animClass' => $r->anim_class,
                'pathCoords' => $r->path_coords,
            ];
        });

        $dbLocations = \App\Models\RouteBusiness::select('id', 'route_key', 'name', 'owner', 'village_key', 'village_name', 'type', 'rating', 'address', 'phone', 'bank_account', 'bank_name', 'is_open', 'menu', 'image_url', 'lat', 'lng')->get()->map(function($b) {
            $vName = $b->village_name;
            $vMap = [
                'phu-loc' => 'Đường Phúc Lộc',
                'dong-anh-cum-3' => 'Quốc Lộ 3',
                'duc-noi' => 'Đường Cổ Vân',
                'viet-hung' => 'Đường Việt Hùng',
                'cao-lo' => 'Đường Cao Lỗ',
                'xuan-canh' => 'Đường Xuân Canh',
                'dan-di' => 'Đường Đản Dị',
                'mai-lam' => 'Đường Dốc Vân',
                'duc-tu' => 'Đường Phía Nam Dục Tú',
            ];
            if (isset($vMap[$b->village_key])) {
                $vName = $vMap[$b->village_key];
            } else {
                $vName = preg_replace('/^(Thôn|Thon)\s+/iu', 'Đường ', $vName);
            }

            $addr = $b->address;
            if ($addr) {
                $addr = preg_replace('/Thon Phuc Loc/i', '', $addr);
                $addr = preg_replace('/Thon Dong Anh \(Cum 3\)/i', '', $addr);
                $addr = preg_replace('/Thon Duc Noi/i', '', $addr);
                $addr = preg_replace('/Thon Viet Hung/i', '', $addr);
                $addr = preg_replace('/Thôn Xuân Canh/u', '', $addr);
                $addr = preg_replace('/Thị trấn Đông Anh \(Đản Dị\)/u', '', $addr);
                $addr = preg_replace('/Thôn Mai Lâm \(Dốc Vân\)/u', '', $addr);
                $addr = preg_replace('/Thôn Mai Lâm/u', '', $addr);
                $addr = preg_replace('/,?\s*(Thôn|Thon)\s+[^,]+/iu', '', $addr);
                $addr = preg_replace('/\s*,\s*,+/', ',', $addr);
                $addr = trim($addr, " \t\n\r\0\x0B,");
            }

            return [
                'id' => $b->id,
                'name' => $b->name,
                'owner' => $b->owner,
                'village' => $b->village_key,
                'villageName' => $vName,
                'type' => $b->type,
                'rating' => (float)$b->rating,
                'address' => $addr,
                'phone' => $b->phone,
                'bankAccount' => $b->bank_account,
                'bank' => $b->bank_name,
                'open' => (bool)$b->is_open,
                'menu' => $b->menu ?? [],
                'image' => $b->image_url,
                'lat' => (float)$b->lat,
                'lng' => (float)$b->lng,
            ];
        });

        return view('tuyen-duong', compact('dbRoutes', 'dbLocations'));
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
     * Trang Bản tin (Newsfeed chuyên biệt cho các bài đăng Profile, Trường học, Gian hàng & Cập nhật cộng đồng)
     */
    public function newsfeed()
    {
        // 1. Lấy tất cả bài viết từ EducationProgram (Trường học / Hiệu trưởng), loại bỏ các tiêu đề mẫu mặc định
        $excludedTitles = [
            'Hệ đào tạo THPT chính quy chuẩn quốc gia',
            'Lớp chọn ngoại ngữ (Tiếng Anh - Tiếng Trung tăng cường)',
            'Hệ THCS Chất lượng cao trọng điểm',
            'Câu lạc bộ Kỹ năng sống & STEM',
        ];

        $eduPostsMysqlEdu = collect();
        $eduPostsMysql = collect();

        try {
            $eduPostsMysqlEdu = \App\Models\EducationProgram::on('mysql_education')
                ->with(['eatery'])
                ->whereNotIn('name', $excludedTitles)
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Throwable $e) {}

        try {
            $eduPostsMysql = \App\Models\EducationProgram::on('mysql')
                ->with(['eatery'])
                ->whereNotIn('name', $excludedTitles)
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Throwable $e) {}

        $eduPosts = $eduPostsMysqlEdu->concat($eduPostsMysql)->unique('id');

        // 2. Lấy tất cả bài viết từ table Post (Người dùng / Trường học / Gian hàng) từ cả mysql_education & mysql
        $userPostsMysqlEdu = collect();
        $userPostsMysql = collect();

        try {
            $userPostsMysqlEdu = \App\Models\Post::on('mysql_education')
                ->with(['user', 'eatery', 'comments.user'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Throwable $e) {}

        try {
            $userPostsMysql = \App\Models\Post::on('mysql')
                ->with(['user', 'eatery', 'comments.user'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Throwable $e) {}

        $userPosts = $userPostsMysqlEdu->concat($userPostsMysql)->unique('id');

        // 3. Lấy bài viết Checkin công khai
        $checkinPosts = collect();
        try {
            $checkinPosts = \App\Models\Checkin::with(['user', 'eatery', 'comments.user'])
                ->where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Throwable $e) {}

        // Gộp tất cả bài viết (không bao gồm bài checkin)
        $allPostsCombined = $eduPosts->concat($userPosts)->values();

        // ========================================================
        // THUẬT TOÁN CÁ NHÂN HÓA BẢNG TIN (Personalized Feed)
        // Mỗi user sẽ thấy thứ tự bài viết khác nhau
        // ========================================================
        $currentUserId = \Illuminate\Support\Facades\Auth::id() ?? session('user_id');

        // Lấy danh sách bạn bè & eatery đã theo dõi của user hiện tại
        $friendUserIds = [];
        $userEateryIds = [];
        if ($currentUserId) {
            try {
                $friendUserIds = \DB::table('friendships')
                    ->where('status', 'accepted')
                    ->where(function($q) use ($currentUserId) {
                        $q->where('user_id', $currentUserId)->orWhere('friend_id', $currentUserId);
                    })
                    ->get()
                    ->map(fn($f) => $f->user_id == $currentUserId ? $f->friend_id : $f->user_id)
                    ->toArray();
            } catch (\Throwable $e) {}

            // Lấy eatery_id của user (trường/gian hàng user quản lý)
            try {
                $user = \App\Models\User::find($currentUserId);
                if ($user && $user->eatery_id) {
                    $userEateryIds[] = $user->eatery_id;
                }
            } catch (\Throwable $e) {}
        }

        // Pre-load engagement counts cho tất cả bài viết (tránh N+1 query)
        $allPostIds = $allPostsCombined->pluck('id')->toArray();
        $engagementReactions = \App\Models\CheckinReaction::selectRaw('reactionable_id, count(*) as cnt')
            ->whereIn('reactionable_id', $allPostIds)
            ->groupBy('reactionable_id')
            ->pluck('cnt', 'reactionable_id');
        $engagementComments = \App\Models\Comment::selectRaw('commentable_id, count(*) as cnt')
            ->whereIn('commentable_id', $allPostIds)
            ->groupBy('commentable_id')
            ->pluck('cnt', 'commentable_id');

        // Tính điểm cá nhân hóa cho từng bài viết
        $allPostsCombined = $allPostsCombined->map(function($post) use ($friendUserIds, $userEateryIds, $currentUserId, $engagementReactions, $engagementComments) {
            $score = 0;
            $createdTs = $post->created_at ? $post->created_at->timestamp : 0;
            $ageHours = max(1, (time() - $createdTs) / 3600);

            // Điểm thời gian: bài mới được ưu tiên (giảm dần theo giờ)
            $score += max(0, 100 - ($ageHours * 0.5));

            // Điểm bạn bè: bài từ bạn bè +40 điểm
            $postUserId = $post->user_id ?? null;
            if ($postUserId && in_array($postUserId, $friendUserIds)) {
                $score += 40;
            }

            // Điểm trường/gian hàng theo dõi: +30 điểm
            $postEateryId = $post->eatery_id ?? null;
            if ($postEateryId && in_array($postEateryId, $userEateryIds)) {
                $score += 30;
            }

            // Điểm tương tác: bài có nhiều reaction/comment được boost
            $reactionCount = $engagementReactions->get($post->id, 0);
            $commentCount = $engagementComments->get($post->id, 0);
            $score += min(25, ($reactionCount * 3) + ($commentCount * 5));

            // Biến thể theo user: thêm nhiễu nhẹ dựa trên user_id để mỗi người thấy khác nhau
            if ($currentUserId) {
                $seed = crc32($currentUserId . '_' . $post->id . '_' . date('Y-m-d'));
                $noise = ($seed % 20) - 10; // -10 đến +10 điểm nhiễu
                $score += $noise;
            }

            $post->_feed_score = $score;
            return $post;
        });

        // Sắp xếp theo điểm cá nhân hóa (cao → thấp)
        $allPostsCombined = $allPostsCombined->sortByDesc('_feed_score')->values();

        // Deep link: nếu có ?post=HASHID (hoặc ID), đẩy bài viết đó lên đầu tiên
        $highlightPostParam = request()->query('post');
        if ($highlightPostParam) {
            $pinnedPost = $allPostsCombined->first(fn($p) => (isset($p->hashid) && $p->hashid === $highlightPostParam) || $p->id == $highlightPostParam);
            if ($pinnedPost) {
                $allPostsCombined = $allPostsCombined->reject(fn($p) => ((isset($p->hashid) && $p->hashid === $highlightPostParam) || $p->id == $highlightPostParam) && get_class($p) === get_class($pinnedPost));
                $allPostsCombined = collect([$pinnedPost])->concat($allPostsCombined)->values();
            }
        }

        // Attach comments and reactions
        $postIds = $allPostsCombined->pluck('id')->toArray();
        $commentsGroup = \App\Models\Comment::with('user')
            ->whereIn('commentable_type', ['post', 'App\Models\Post', 'App\Models\EducationProgram', 'App\Models\Checkin', 'checkin'])
            ->whereIn('commentable_id', $postIds)
            ->get()
            ->groupBy('commentable_id');

        $allReactions = \App\Models\CheckinReaction::selectRaw('reactionable_type, reactionable_id, emoji, count(*) as count')
            ->groupBy('reactionable_type', 'reactionable_id', 'emoji')
            ->get()
            ->groupBy(function($item) {
                return $item->reactionable_type . '_' . $item->reactionable_id;
            });

        $userId = \Illuminate\Support\Facades\Auth::id() ?? session('user_id');
        $sessionId = session()->getId();

        $userReactions = \App\Models\CheckinReaction::where(function($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else if (!empty($sessionId)) {
                    $q->whereNull('user_id')->where('session_id', $sessionId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->get()
            ->keyBy(function($item) {
                return $item->reactionable_type . '_' . $item->reactionable_id;
            });

        $emojis = ['❤️', '🔥', '👍', '😂', '😍', '🤤'];

        $posts = $allPostsCombined->transform(function($post) use ($allReactions, $userReactions, $emojis, $commentsGroup) {
            $typeKey = ($post instanceof \App\Models\EducationProgram) ? 'post_' : (($post instanceof \App\Models\Checkin) ? 'checkin_' : 'post_');
            $key = $typeKey . $post->id;
            $reactionsGroup = $allReactions->get($key, collect());
            $counts = [];
            $total = 0;
            foreach ($emojis as $e) {
                $cnt = (int) ($reactionsGroup->firstWhere('emoji', $e)?->count ?? 0);
                $counts[$e] = $cnt;
                $total += $cnt;
            }
            $post->reaction_counts = $counts;
            $post->reaction_total = $total;
            $post->is_liked = $userReactions->has($key);
            $post->comments = $commentsGroup->get($post->id, collect());
            return $post;
        });

        // 4. Gợi ý Profile mới nhất
        $featuredUsers = \App\Models\User::whereNotNull('name')
            ->inRandomOrder()
            ->take(10)
            ->get();

        $allEateries = \Illuminate\Support\Facades\Cache::remember('all_eateries_dropdown', 3600, function() {
            return \App\Models\Eatery::active()
                ->with('category:id,name')
                ->select('id', 'name', 'slug', 'address', 'category_id')
                ->orderBy('name')
                ->get();
        });

        return view('newsfeed', compact('posts', 'featuredUsers', 'allEateries'));
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

        // 3. Bài viết từ Profile không hiển thị ở trang Check-in nữa (đã chuyển sang Bảng tin)
        $profilePosts = collect();

        // 4. Tải tất cả bộ đếm cảm xúc (Reactions) từ DB
        $allCheckinReactions = \App\Models\CheckinReaction::selectRaw('reactionable_type, reactionable_id, emoji, count(*) as count')
            ->groupBy('reactionable_type', 'reactionable_id', 'emoji')
            ->get()
            ->groupBy(function($item) {
                return $item->reactionable_type . '_' . $item->reactionable_id;
            });

        $user = auth('sanctum')->user() ?: \Illuminate\Support\Facades\Auth::user();
        $userId = $user ? $user->id : session('user_id');
        $sessionId = session()->getId();

        $userReactions = \App\Models\CheckinReaction::where(function($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else if (!empty($sessionId)) {
                    $q->whereNull('user_id')->where('session_id', $sessionId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->get()
            ->keyBy(function($item) {
                return $item->reactionable_type . '_' . $item->reactionable_id;
            });

        $emojis = ['❤️', '🔥', '👍', '😂', '😍', '🤤'];

        $standaloneCheckins->transform(function($chk) use ($allCheckinReactions, $userReactions, $emojis) {
            $key = 'checkin_' . $chk->id;
            $reactionsGroup = $allCheckinReactions->get($key, collect());
            $counts = [];
            $total = 0;
            foreach ($emojis as $e) {
                $cnt = (int) ($reactionsGroup->firstWhere('emoji', $e)?->count ?? 0);
                $counts[$e] = $cnt;
                $total += $cnt;
            }
            $chk->reaction_counts = $counts;
            $chk->reaction_total = $total;
            $chk->is_liked = $userReactions->has($key);
            return $chk;
        });

        $diaries->transform(function($dry) use ($allCheckinReactions, $userReactions, $emojis) {
            $key = 'diary_' . $dry->id;
            $reactionsGroup = $allCheckinReactions->get($key, collect());
            $counts = [];
            $total = 0;
            foreach ($emojis as $e) {
                $cnt = (int) ($reactionsGroup->firstWhere('emoji', $e)?->count ?? 0);
                $counts[$e] = $cnt;
                $total += $cnt;
            }
            $dry->reaction_counts = $counts;
            $dry->reaction_total = $total;
            $dry->is_liked = $userReactions->has($key);
            return $dry;
        });

        $allEateries = \Illuminate\Support\Facades\Cache::remember('all_eateries_dropdown', 3600, function() {
            return \App\Models\Eatery::active()
                ->with('category:id,name')
                ->select('id', 'name', 'slug', 'address', 'category_id')
                ->orderBy('name')
                ->get();
        });
        $eateriesMap = $allEateries->keyBy('id');

        return view('checkin', compact('diaries', 'eateriesMap', 'standaloneCheckins', 'profilePosts', 'allEateries'));
    }

    /**
     * Lưu check-in đơn lẻ mới từ form
     */
    public function storeCheckin(Request $request, \App\Services\CheckinService $checkinService)
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

        $data = \App\Domain\Checkin\CheckinData::fromRequest($request);
        $checkinService->createCheckin($data);

        $authUser = Auth::user();
        if ($authUser) {
            try {
                \App\Services\NotificationService::notifyNewPost($authUser, '', $request->input('comment') ?? 'Check-in mới', $eateryId ? (int)$eateryId : null);
            } catch (\Exception $e) {}
        }

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
     * Lấy danh sách thông báo tổng hợp chuẩn Facebook cho Web Header
     */
    public function getWebNotifications(Request $request)
    {
        $userId = auth()->id() ?? session('user_id');
        if (!$userId) {
            return response()->json([]);
        }

        $notifications = \App\Services\NotificationService::getNotificationsForUser($userId);
        return response()->json($notifications);
    }

    /**
     * Đánh dấu đã đọc tất cả thông báo
     */
    public function markWebNotificationsRead(Request $request)
    {
        $userId = auth()->id() ?? session('user_id');
        if ($userId) {
            \App\Services\NotificationService::markAsRead($userId);
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

        $user = auth('sanctum')->user() ?: \Illuminate\Support\Facades\Auth::user();
        $userId = $user ? $user->id : session('user_id');
        $sessionId = session()->getId() ?: ('guest_' . md5($request->ip() . ($request->header('User-Agent') ?? '')));

        $query = \App\Models\CheckinReaction::where('reactionable_type', $type)
            ->where('reactionable_id', (int) $id);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->whereNull('user_id')->where('session_id', $sessionId);
        }

        $existing = $query->first();

        if ($existing) {
            if ($existing->emoji === $emoji) {
                $existing->delete();
            } else {
                $existing->update(['emoji' => $emoji]);
            }
        } else {
            \App\Models\CheckinReaction::create([
                'reactionable_type' => $type,
                'reactionable_id'   => (int) $id,
                'user_id'           => $userId ?: null,
                'session_id'        => $userId ? null : $sessionId,
                'emoji'             => $emoji,
            ]);

            try {
                \App\Services\NotificationService::notifyReaction((int) $id, $type, $emoji, $userId);
            } catch (\Throwable $notifErr) {}
        }

        $allReactions = \App\Models\CheckinReaction::where('reactionable_type', $type)
            ->where('reactionable_id', (int) $id)
            ->selectRaw('emoji, count(*) as count')
            ->groupBy('emoji')
            ->pluck('count', 'emoji')
            ->toArray();

        $emojis = ['❤️', '🔥', '👍', '😂', '😍', '🤤'];
        $counts = [];
        $total = 0;
        foreach ($emojis as $e) {
            $cnt = (int) ($allReactions[$e] ?? 0);
            $counts[$e] = $cnt;
            $total += $cnt;
        }

        if ($type === 'checkin') {
            try { \App\Models\Checkin::where('id', $id)->update(['likes_count' => $total]); } catch (\Throwable $e) {}
        } elseif ($type === 'post') {
            try { \App\Models\Post::where('id', $id)->update(['likes_count' => $total]); } catch (\Throwable $e) {}
        }

        event(new \App\Events\CheckinReacted((int) $id, $type, $emoji, $counts, $total));

        return response()->json([
            'success' => true,
            'counts'  => $counts,
            'total'   => $total,
        ]);
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

    /**
     * Tương tác Like / Thả tim chuẩn DB Real (Bài viết & Địa điểm checkin)
     * Mỗi tài khoản / session chỉ được Like / Thả tim 1 lần per item
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

            $user = auth('sanctum')->user() ?: \Illuminate\Support\Facades\Auth::user();
            $userId = $user ? $user->id : session('user_id');
            $sessionId = session()->getId() ?: ('guest_' . md5($request->ip() . ($request->header('User-Agent') ?? '')));

            $query = \App\Models\CheckinReaction::where('reactionable_type', $type)
                ->where('reactionable_id', $id);

            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->whereNull('user_id')->where('session_id', $sessionId);
            }

            $existing = $query->first();
            $isLiked = false;

            if ($existing) {
                // Đã like -> Bỏ thích (Unlike)
                $existing->delete();
                $isLiked = false;
            } else {
                // Chưa like -> Tạo 1 lượt thích duy nhất cho tài khoản/session
                \App\Models\CheckinReaction::create([
                    'reactionable_type' => $type,
                    'reactionable_id'   => $id,
                    'user_id'           => $userId ?: null,
                    'session_id'        => $userId ? null : $sessionId,
                    'emoji'             => $emoji,
                ]);
                $isLiked = true;

                // Gửi thông báo đến người tạo nội dung
                try {
                    \App\Services\NotificationService::notifyReaction($id, $type, $emoji, $userId);
                } catch (\Throwable $notifErr) {}
            }

            // Đếm chính xác lượt thích từ bảng DB checkin_reactions
            $realLikesCount = \App\Models\CheckinReaction::where('reactionable_type', $type)
                ->where('reactionable_id', $id)
                ->count();

            // Cập nhật lại số lượng cho Post & EducationProgram nếu type là post
            if ($type === 'post') {
                try {
                    \App\Models\Post::where('id', $id)->update(['likes_count' => $realLikesCount]);
                } catch (\Throwable $e) {}
                try {
                    \App\Models\Post::on('mysql_education')->where('id', $id)->update(['likes_count' => $realLikesCount]);
                } catch (\Throwable $e) {}
                try {
                    \App\Models\EducationProgram::on('mysql_education')->where('id', $id)->update(['likes_count' => $realLikesCount]);
                } catch (\Throwable $e) {}
                try {
                    \App\Models\EducationProgram::on('mysql')->where('id', $id)->update(['likes_count' => $realLikesCount]);
                } catch (\Throwable $e) {}
            } else if ($type === 'checkin') {
                try {
                    \App\Models\Checkin::where('id', $id)->update(['likes_count' => $realLikesCount]);
                } catch (\Throwable $e) {}
            }

            return response()->json([
                'success'     => true,
                'liked'       => $isLiked,
                'likes_count' => $realLikesCount,
                'message'     => $isLiked ? 'Đã thích' : 'Đã bỏ thích'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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

        $id = (int) $request->input('id');
        $type = $request->input('type');

        $reactions = \App\Models\CheckinReaction::where('reactionable_type', $type)
            ->where('reactionable_id', $id)
            ->with('user:id,name,avatar')
            ->latest()
            ->get();

        $likers = $reactions->map(function ($reaction) {
            if ($reaction->user) {
                return [
                    'name'   => $reaction->user->name,
                    'avatar' => $reaction->user->avatar,
                    'emoji'  => $reaction->emoji ?? '👍',
                ];
            }
            return [
                'name'   => 'Khách',
                'avatar' => null,
                'emoji'  => $reaction->emoji ?? '👍',
            ];
        });

        return response()->json([
            'success' => true,
            'likers'  => $likers,
            'total'   => $likers->count(),
        ]);
    }

    /**
     * Lấy danh sách bình luận thực tế từ DB
     */
    public function getComments(Request $request)
    {
        $request->validate([
            'id'   => 'required|integer',
            'type' => 'required|string|in:post,eatery,checkin,diary'
        ]);

        $comments = \App\Models\Comment::with('user')
            ->where('commentable_type', $request->type)
            ->where('commentable_id', $request->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($c) {
                $avatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80';
                if ($c->user) {
                    if ($c->user->avatar && str_starts_with($c->user->avatar, 'avatars/')) {
                        $avatar = rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $c->user->avatar;
                    } elseif ($c->user->avatar) {
                        $avatar = $c->user->avatar;
                    }
                }
                return [
                    'id'               => $c->id,
                    'user_id'          => $c->user_id,
                    'author_name'      => $c->display_name,
                    'author_avatar'    => $avatar,
                    'content'          => $c->content,
                    'created_at_human' => $c->created_at ? $c->created_at->diffForHumans() : 'Vừa xong'
                ];
            });

        return response()->json([
            'success'  => true,
            'count'    => $comments->count(),
            'comments' => $comments
        ]);
    }

    /**
     * Lưu bình luận thực tế mới vào DB
     */
    public function storeComment(Request $request)
    {
        $request->validate([
            'id'      => 'required|integer',
            'type'    => 'required|string|in:post,eatery,checkin,diary',
            'content' => 'required|string|max:1000'
        ]);

        $userId = \Illuminate\Support\Facades\Auth::id() ?? session('user_id');
        $user = $userId ? \App\Models\User::find($userId) : null;
        $guestName = $user ? null : ($request->input('guest_name') ?? 'Khách vãng lai');

        $comment = \App\Models\Comment::create([
            'user_id'          => $userId,
            'guest_name'       => $guestName,
            'commentable_id'   => (int) $request->input('id'),
            'commentable_type' => $request->input('type'),
            'content'          => $request->input('content')
        ]);

        $totalCommentsCount = \App\Models\Comment::where('commentable_type', $request->input('type'))
            ->where('commentable_id', $request->input('id'))
            ->count();

        $avatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80';
        if ($user) {
            if ($user->avatar && str_starts_with($user->avatar, 'avatars/')) {
                $avatar = rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $user->avatar;
            } elseif ($user->avatar) {
                $avatar = $user->avatar;
            }
        }

        return response()->json([
            'success'        => true,
            'total_comments' => $totalCommentsCount,
            'comment'        => [
                'id'               => $comment->id,
                'author_name'      => $comment->display_name,
                'author_avatar'    => $avatar,
                'content'          => $comment->content,
                'created_at_human' => 'Vừa xong'
            ]
        ]);
    }

    /**
     * Tăng số lượt chia sẻ cho bài viết trong DB (Post, EducationProgram, Checkin)
     */
    public function incrementShare(Request $request)
    {
        $request->validate([
            'id'   => 'required',
            'type' => 'nullable|string|in:post,education,checkin'
        ]);

        $idParam = $request->input('id');
        $type = $request->input('type');

        $newShareCount = 0;
        $connections = ['mysql', 'mysql_education'];

        foreach ($connections as $conn) {
            try {
                if ($type === 'education' || $type === 'App\\Models\\EducationProgram') {
                    $item = \App\Models\EducationProgram::on($conn)
                        ->where('id', $idParam)
                        ->orWhere('hashid', $idParam)
                        ->first();
                    if ($item) {
                        $item->increment('shares_count');
                        $newShareCount = (int) $item->fresh()->shares_count;
                        break;
                    }
                } elseif ($type === 'checkin' || $type === 'App\\Models\\Checkin') {
                    $item = \App\Models\Checkin::on($conn)
                        ->where('id', $idParam)
                        ->orWhere('hashid', $idParam)
                        ->first();
                    if ($item) {
                        $item->increment('shares_count');
                        $newShareCount = (int) $item->fresh()->shares_count;
                        break;
                    }
                } else {
                    $item = \App\Models\Post::on($conn)
                        ->where('id', $idParam)
                        ->orWhere('hashid', $idParam)
                        ->first();
                    if (!$item) {
                        $item = \App\Models\EducationProgram::on($conn)
                            ->where('id', $idParam)
                            ->orWhere('hashid', $idParam)
                            ->first();
                    }
                    if (!$item) {
                        $item = \App\Models\Checkin::on($conn)
                            ->where('id', $idParam)
                            ->orWhere('hashid', $idParam)
                            ->first();
                    }
                    if ($item) {
                        $item->increment('shares_count');
                        $newShareCount = (int) $item->fresh()->shares_count;
                        $user = auth('sanctum')->user() ?: \Illuminate\Support\Facades\Auth::user();
                        \App\Services\NotificationService::notifyShare((int)$idParam, $type ?? 'post', $user ? $user->id : null);
                        break;
                    }
                }
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success'      => true,
            'shares_count' => $newShareCount,
            'message'      => 'Tăng số lượt chia sẻ thành công'
        ]);
    }
}

