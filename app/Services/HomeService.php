<?php

namespace App\Services;

use App\Domain\Checkin\CheckinData;
use App\Events\CheckinReacted;
use App\Models\Checkin;
use App\Models\CheckinReaction;
use App\Models\Comment;
use App\Models\DigitalRoute;
use App\Models\Dish;
use App\Models\Eatery;
use App\Models\EducationProgram;
use App\Models\FoodTourDiary;
use App\Models\OcopProduct;
use App\Models\Post;
use App\Models\RouteBusiness;
use App\Models\User;
use App\Services\Interfaces\HomeServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomeService implements HomeServiceInterface
{
    public function __construct(
        protected CheckinService $checkinService
    ) {}

    public function getHomeData(?string $selectedCatSlug, ?string $selectedComSlug, int $page = 1, int $perPage = 24): array
    {
        $categories = Cache::remember('home_categories', 1800, fn() => EateryApiService::getCategories());
        $communes = Cache::remember('home_communes', 1800, fn() => EateryApiService::getCommunes());

        $selectedComId = null;
        if ($selectedComSlug) {
            $commune = $communes->firstWhere('slug', $selectedComSlug);
            if ($commune) {
                $selectedComId = $commune->id;
            }
        }

        $totalCount = EateryApiService::countEateries($selectedCatSlug, [
            'commune_id' => $selectedComId,
        ]);

        $eateries = EateryApiService::getEateries($selectedCatSlug, [
            'commune_id' => $selectedComId,
            'page'       => $page,
            'per_page'   => $perPage,
        ]);

        $ocopProducts = collect();
        if ($selectedCatSlug === 'dong-anh-market') {
            $ocopProducts = EateryApiService::getOcopProducts([
                'commune_id' => $selectedComId,
            ]);
        }

        return [
            'categories'      => $categories,
            'communes'        => $communes,
            'eateries'        => $eateries,
            'ocopProducts'    => $ocopProducts,
            'selectedCatSlug' => $selectedCatSlug,
            'selectedComSlug' => $selectedComSlug,
            'totalCount'      => $totalCount,
            'page'            => $page,
            'perPage'         => $perPage,
            'hasMore'         => ($page * $perPage) < $totalCount,
        ];
    }

    public function getSitemapData(): array
    {
        $eateries = Eatery::select('id', 'name', 'slug', 'category_id', 'updated_at')->active()->get();
        $ocopProducts = OcopProduct::select('id', 'name', 'slug', 'star_rating', 'eatery_id', 'stall_name', 'updated_at')
            ->whereNotNull('slug')
            ->get();
        $dishes = Dish::select('id', 'name', 'eatery_id', 'updated_at')->get();

        return compact('eateries', 'ocopProducts', 'dishes');
    }

    public function getTuyenDuongData(): array
    {
        $dbRoutes = DigitalRoute::select('route_key', 'name', 'length', 'color', 'village_key', 'anim_class', 'path_coords')->get()->map(fn($r) => [
            'id'         => $r->route_key,
            'name'       => $r->name,
            'length'     => $r->length,
            'color'      => $r->color,
            'villages'   => [$r->village_key],
            'animClass'  => $r->anim_class,
            'pathCoords' => $r->path_coords,
        ]);

        $dbLocations = RouteBusiness::select('id', 'route_key', 'name', 'owner', 'village_key', 'village_name', 'type', 'rating', 'address', 'phone', 'bank_account', 'bank_name', 'is_open', 'menu', 'image_url', 'lat', 'lng')->get()->map(function ($b) {
            $vName = $b->village_name;
            $vMap = [
                'phu-loc'        => 'Đường Phúc Lộc',
                'dong-anh-cum-3' => 'Quốc Lộ 3',
                'duc-noi'        => 'Đường Cổ Vân',
                'viet-hung'      => 'Đường Việt Hùng',
                'cao-lo'         => 'Đường Cao Lỗ',
                'xuan-canh'      => 'Đường Xuân Canh',
                'dan-di'         => 'Đường Đản Dị',
                'mai-lam'        => 'Đường Dốc Vân',
                'duc-tu'         => 'Đường Phía Nam Dục Tú',
                'dan-mo'         => 'Đường Đản Mỗ',
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

            $img = $b->image_url;
            if (!$img) {
                $typeImages = [
                    'quan-an'   => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=600&q=80',
                    'nha-hang'  => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=600&q=80',
                    'tap-hoa'   => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80',
                    'thuc-pham' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80',
                    'thoi-trang'=> 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=600&q=80',
                    'y-te'      => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=600&q=80',
                    'dich-vu'   => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=600&q=80',
                ];
                $img = $typeImages[$b->type] ?? 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80';
            }

            $menuItems = $b->menu;
            if (is_string($menuItems)) {
                $menuItems = json_decode($menuItems, true);
            }
            if (empty($menuItems)) {
                $menuItems = ['Hộ kinh doanh thực tế'];
            }

            return [
                'id'          => $b->id,
                'name'        => $b->name,
                'owner'       => $b->owner,
                'village'     => $b->village_key,
                'villageName' => $vName,
                'type'        => $b->type,
                'rating'      => (float) $b->rating,
                'address'     => $addr,
                'phone'       => $b->phone,
                'bankAccount' => $b->bank_account,
                'bank'        => $b->bank_name,
                'open'        => (bool) $b->is_open,
                'menu'        => $menuItems,
                'image'       => $img,
                'lat'         => (float) $b->lat,
                'lng'         => (float) $b->lng,
            ];
        });

        return compact('dbRoutes', 'dbLocations');
    }

    public function getVideoList(): Collection
    {
        return EateryApiService::getVideos()->map(fn($vid) => [
            'id'             => $vid->id,
            'title'          => $vid->title,
            'video_url'      => $vid->video_url,
            'video_type'     => $vid->video_type,
            'thumbnail_path' => $vid->thumbnail_path,
            'likes_count'    => $vid->likes_count,
            'eatery'         => [
                'name'       => $vid->eatery->name,
                'slug'       => $vid->eatery->slug,
                'address'    => $vid->eatery->address,
                'rating'     => (float) $vid->eatery->rating,
                'category'   => $vid->eatery->category->name,
                'latitude'   => $vid->eatery->latitude,
                'longitude'  => $vid->eatery->longitude,
                'image_path' => $vid->eatery->image_path,
            ],
        ]);
    }

    public function likeVideo(int $id): ?array
    {
        return EateryApiService::likeVideo($id);
    }

    public function getNewsfeedData(?int $currentUserId, string $feedType = 'for_you'): array
    {
        // 1. Lọc EducationProgram
        $excludedTitles = [
            'Hệ đào tạo THPT chính quy chuẩn quốc gia',
            'Lớp chọn ngoại ngữ (Tiếng Anh - Tiếng Trung tăng cường)',
            'Hệ THCS Chất lượng cao trọng điểm',
            'Câu lạc bộ Kỹ năng sống & STEM',
        ];

        $eduPosts = collect();
        foreach (['mysql_education', 'mysql'] as $conn) {
            try {
                $progs = EducationProgram::on($conn)
                    ->with(['eatery'])
                    ->whereNotIn('name', $excludedTitles)
                    ->orderBy('created_at', 'desc')
                    ->get();
                $eduPosts = $eduPosts->concat($progs);
            } catch (\Throwable $e) {}
        }
        $eduPosts = $eduPosts->unique('id');

        // 2. Lấy Post từ cả 2 connection
        $userPosts = collect();
        foreach (['mysql_education', 'mysql'] as $conn) {
            try {
                $posts = Post::on($conn)
                    ->with(['user', 'eatery', 'comments.user'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                $userPosts = $userPosts->concat($posts);
            } catch (\Throwable $e) {}
        }
        $userPosts = $userPosts->unique('id');

        // 3. Lấy Checkin
        $checkinPosts = collect();
        try {
            $checkinPosts = Checkin::with(['user', 'eatery', 'comments.user'])
                ->where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($c) {
                    $c->is_checkin = true;
                    $c->name = $c->eatery ? "📍 Check-in tại " . $c->eatery->name : "📍 Bài đăng Check-in";
                    $c->description = $c->content ?? $c->description;
                    return $c;
                });
        } catch (\Throwable $e) {}

        // 4. Lấy FoodTourDiary
        $foodTourDiaries = collect();
        try {
            $foodTourDiaries = FoodTourDiary::with(['user', 'foodTour', 'comments.user'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($d) {
                    $d->is_food_tour = true;
                    $tourTitle = $d->foodTour ? $d->foodTour->title : 'Hành trình Ẩm thực Đông Anh';
                    $d->name = "🍲 Nhật ký Food Tour: " . $tourTitle;
                    $d->description = $d->comment;
                    if ($d->rating) {
                        $d->description = "⭐ Đánh giá: " . $d->rating . "/5 sao\n\n" . $d->description;
                    }
                    return $d;
                });
        } catch (\Throwable $e) {}

        $allPostsCombined = $eduPosts->concat($userPosts)->concat($checkinPosts)->concat($foodTourDiaries)->values();

        // Cá nhân hóa Newsfeed
        $friendUserIds = [];
        $userEateryIds = [];
        if ($currentUserId) {
            try {
                $friendUserIds = DB::table('friendships')
                    ->where('status', 'accepted')
                    ->where(function ($q) use ($currentUserId) {
                        $q->where('user_id', $currentUserId)->orWhere('friend_id', $currentUserId);
                    })
                    ->get()
                    ->map(fn($f) => $f->user_id == $currentUserId ? $f->friend_id : $f->user_id)
                    ->toArray();
            } catch (\Throwable $e) {}

            try {
                $user = User::find($currentUserId);
                if ($user && $user->eatery_id) {
                    $userEateryIds[] = $user->eatery_id;
                }
            } catch (\Throwable $e) {}
        }

        if ($feedType === 'following') {
            if (!empty($friendUserIds) || !empty($userEateryIds)) {
                $allPostsCombined = $allPostsCombined->filter(function ($post) use ($friendUserIds, $userEateryIds) {
                    $uId = $post->user_id ?? null;
                    $eId = $post->eatery_id ?? null;
                    return ($uId && in_array($uId, $friendUserIds)) || ($eId && in_array($eId, $userEateryIds));
                })->values();
            }
        } elseif ($feedType === 'nearby') {
            $allPostsCombined = $allPostsCombined->filter(function ($post) {
                return !empty($post->is_checkin) || !empty($post->is_food_tour) || !empty($post->eatery_id);
            })->values();
        }

        $engagementReactions = collect();
        $engagementComments = collect();
        try {
            $allPostIds = $allPostsCombined->pluck('id')->filter()->toArray();
            if (!empty($allPostIds)) {
                $engagementReactions = CheckinReaction::selectRaw('reactionable_id, count(*) as cnt')
                    ->whereIn('reactionable_id', $allPostIds)
                    ->groupBy('reactionable_id')
                    ->pluck('cnt', 'reactionable_id');
                $engagementComments = Comment::selectRaw('commentable_id, count(*) as cnt')
                    ->whereIn('commentable_id', $allPostIds)
                    ->groupBy('commentable_id')
                    ->pluck('cnt', 'commentable_id');
            }
        } catch (\Throwable $e) {}

        $allPostsCombined = $allPostsCombined->map(function ($post) use ($friendUserIds, $userEateryIds, $engagementReactions, $engagementComments, $feedType) {
            $score = 0;
            $createdTs = $post->created_at ? $post->created_at->timestamp : 0;
            $ageHours = max(1, (time() - $createdTs) / 3600);
            $score += max(0, 100 - ($ageHours * 0.5));

            $postUserId = $post->user_id ?? null;
            $isFriend = $postUserId && in_array($postUserId, $friendUserIds);
            if ($isFriend) $score += 45;

            $postEateryId = $post->eatery_id ?? null;
            $isFollowedEatery = $postEateryId && in_array($postEateryId, $userEateryIds);
            if ($isFollowedEatery) $score += 30;

            $reactionCount = $engagementReactions->get($post->id, 0);
            $commentCount = $engagementComments->get($post->id, 0);
            $score += min(30, ($reactionCount * 3) + ($commentCount * 5));

            if ($feedType === 'following') {
                $post->_personal_tag = '👥 Từ người bạn theo dõi';
            } elseif ($feedType === 'nearby') {
                $post->_personal_tag = '📍 Khám phá gần bạn';
            } else {
                if ($isFriend) {
                    $post->_personal_tag = '📌 Từ bạn bè của bạn';
                } elseif ($isFollowedEatery) {
                    $post->_personal_tag = '🏛️ Cơ sở bạn quan tâm';
                } elseif (($reactionCount + $commentCount) >= 4) {
                    $post->_personal_tag = '🔥 Đang thịnh hành';
                } else {
                    $post->_personal_tag = '✨ Đề xuất cho bạn';
                }
            }

            $post->_feed_score = $score;
            return $post;
        })->sortByDesc('_feed_score')->values();

        $featuredUsers = User::whereNotNull('name')->limit(5)->get();
        $allEateries = Eatery::active()->limit(10)->get();
        $stories = collect();

        return [
            'posts'         => $allPostsCombined,
            'featuredUsers' => $featuredUsers,
            'allEateries'   => $allEateries,
            'stories'       => $stories,
            'feedType'      => $feedType,
        ];
    }

    public function getCheckinFeedData(?int $userId, ?string $sessionId): array
    {
        $diaries = FoodTourDiary::with(['user', 'foodTour.stops', 'comments.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $standaloneCheckins = Checkin::with(['user', 'eatery.category', 'eatery.commune', 'comments.user'])
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();

        $allCheckinReactions = CheckinReaction::selectRaw('reactionable_type, reactionable_id, emoji, count(*) as count')
            ->groupBy('reactionable_type', 'reactionable_id', 'emoji')
            ->get()
            ->groupBy(fn($item) => $item->reactionable_type . '_' . $item->reactionable_id);

        $userReactions = CheckinReaction::where(function ($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } elseif (!empty($sessionId)) {
                    $q->whereNull('user_id')->where('session_id', $sessionId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->get()
            ->keyBy(fn($item) => $item->reactionable_type . '_' . $item->reactionable_id);

        $emojis = ['❤️', '🔥', '👍', '😂', '😍', '🤤'];

        $standaloneCheckins->transform(function ($chk) use ($allCheckinReactions, $userReactions, $emojis) {
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

        $diaries->transform(function ($dry) use ($allCheckinReactions, $userReactions, $emojis) {
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

        $allEateries = Eatery::select('id', 'name', 'slug', 'address', 'image_path', 'rating', 'category_id', 'commune_id')
            ->with('category:id,name,slug,icon', 'commune:id,name,slug')
            ->active()->orderBy('name')->get();
        $eateriesMap = $allEateries->keyBy('id');

        return [
            'diaries'            => $diaries,
            'eateriesMap'        => $eateriesMap,
            'standaloneCheckins' => $standaloneCheckins,
            'profilePosts'       => collect(),
            'allEateries'        => $allEateries,
        ];
    }

    public function storeCheckin(CheckinData $data, ?int $authUserId, ?int $eateryId, ?string $comment): void
    {
        $this->checkinService->createCheckin($data);

        if ($authUserId) {
            try {
                $user = User::find($authUserId);
                if ($user) {
                    NotificationService::notifyNewPost($user, '', $comment ?? 'Check-in mới', $eateryId);
                }
            } catch (\Throwable $e) {
                Log::warning('Checkin notification warning: ' . $e->getMessage());
            }
        }
    }

    public function searchEateries(string $query)
    {
        $q = trim($query);
        $qb = Eatery::select('id', 'name', 'slug', 'address', 'image_path', 'rating', 'category_id', 'commune_id')
            ->with('category:id,name', 'commune:id,name')
            ->active();

        if ($q !== '') {
            $qNormalized = mb_strtolower($this->removeVietnameseSign($q));
            $qb->where(function ($sub) use ($q, $qNormalized) {
                if (mb_strlen($q) >= 2) {
                    try {
                        $sub->whereFullText(['name', 'address'], $q);
                    } catch (\Throwable $e) {}
                }
                $sub->orWhere('name', 'like', "{$q}%")
                    ->orWhere('slug', 'like', "{$qNormalized}%")
                    ->orWhere('address', 'like', "{$q}%");
            });
        }

        return $qb->orderBy('name')->limit(20)->get()->map(fn($e) => [
            'id'       => $e->id,
            'name'     => $e->name,
            'address'  => $e->address ?? '',
            'category' => optional($e->category)->name ?? 'Địa điểm',
            'commune'  => optional($e->commune)->name ?? 'Đông Anh',
            'image'    => $e->image_path,
            'rating'   => $e->rating,
            'slug'     => $e->slug,
        ]);
    }

    public function getNearbyEateries(float $lat, float $lng, float $radius = 3)
    {
        $eateries = Eatery::select('id', 'name', 'slug', 'address', 'image_path', 'rating',
                'latitude', 'longitude', 'category_id', 'commune_id')
            ->selectRaw(
                "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance",
                [$lat, $lng, $lat]
            )
            ->with('category:id,name', 'commune:id,name')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->active()
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->limit(10)
            ->get();

        return $eateries->map(fn($e) => [
            'id'       => $e->id,
            'name'     => $e->name,
            'address'  => $e->address ?? '',
            'category' => optional($e->category)->name ?? 'Địa điểm',
            'commune'  => optional($e->commune)->name ?? 'Đông Anh',
            'image'    => $e->image_path,
            'rating'   => $e->rating,
            'slug'     => $e->slug,
            'distance' => round($e->distance * 1000),
        ]);
    }

    public function getLatestCheckins(?int $afterTimestamp = null)
    {
        $query = Checkin::with(['user', 'eatery.category', 'eatery.commune'])
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit(20);

        if ($afterTimestamp) {
            $query->where('created_at', '>', Carbon::createFromTimestamp($afterTimestamp));
        }

        return $query->get()->map(fn($checkin) => [
            'id'                => $checkin->id,
            'display_name'      => $checkin->display_name,
            'avatar_char'       => $checkin->user ? mb_substr($checkin->user->name, 0, 1, 'UTF-8') : '👤',
            'role'              => $checkin->user?->role ?? 'guest',
            'rating'            => (int) $checkin->rating,
            'comment'           => $checkin->comment,
            'image_path'        => $checkin->image_path,
            'created_at_human'  => $checkin->created_at->diffForHumans(),
            'created_at_format' => $checkin->created_at->format('d/m/Y H:i'),
            'created_at_ts'     => $checkin->created_at->timestamp,
            'eatery'            => $checkin->eatery ? [
                'name'     => $checkin->eatery->name,
                'slug'     => $checkin->eatery->slug,
                'category' => $checkin->eatery->category?->name,
                'commune'  => $checkin->eatery->commune?->name,
            ] : null,
        ]);
    }

    public function getWebNotifications(int $userId): array
    {
        return NotificationService::getNotificationsForUser($userId);
    }

    public function markWebNotificationsRead(int $userId): void
    {
        NotificationService::markAsRead($userId);
    }

    public function reactToCheckin(int $id, string $type, string $emoji, ?int $userId, string $sessionId): array
    {
        $query = CheckinReaction::where('reactionable_type', $type)
            ->where('reactionable_id', $id);

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
            CheckinReaction::create([
                'reactionable_type' => $type,
                'reactionable_id'   => $id,
                'user_id'           => $userId ?: null,
                'session_id'        => $userId ? null : $sessionId,
                'emoji'             => $emoji,
            ]);

            try {
                NotificationService::notifyReaction($id, $type, $emoji, $userId);
            } catch (\Throwable $notifErr) {}
        }

        $allReactions = CheckinReaction::where('reactionable_type', $type)
            ->where('reactionable_id', $id)
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

        $targetModel = match ($type) {
            'checkin' => Checkin::class,
            'post'    => Post::class,
            default   => null,
        };
        if ($targetModel) {
            rescue(fn() => $targetModel::where('id', $id)->update(['likes_count' => $total]), report: false);
        }

        event(new CheckinReacted($id, $type, $emoji, $counts, $total));

        return [
            'counts' => $counts,
            'total'  => $total,
        ];
    }

    public function toggleReaction(int $id, string $type, string $emoji, ?int $userId, string $sessionId): array
    {
        $query = CheckinReaction::where('reactionable_type', $type)
            ->where('reactionable_id', $id);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->whereNull('user_id')->where('session_id', $sessionId);
        }

        $existing = $query->first();
        $isLiked = false;

        if ($existing) {
            $existing->delete();
            $isLiked = false;
        } else {
            CheckinReaction::create([
                'reactionable_type' => $type,
                'reactionable_id'   => $id,
                'user_id'           => $userId ?: null,
                'session_id'        => $userId ? null : $sessionId,
                'emoji'             => $emoji,
            ]);
            $isLiked = true;

            try {
                NotificationService::notifyReaction($id, $type, $emoji, $userId);
            } catch (\Throwable $notifErr) {}
        }

        $realLikesCount = CheckinReaction::where('reactionable_type', $type)
            ->where('reactionable_id', $id)
            ->count();

        if ($type === 'post') {
            foreach (['mysql', 'mysql_education'] as $conn) {
                rescue(fn() => Post::on($conn)->where('id', $id)->update(['likes_count' => $realLikesCount]), report: false);
                rescue(fn() => EducationProgram::on($conn)->where('id', $id)->update(['likes_count' => $realLikesCount]), report: false);
            }
        } elseif ($type === 'checkin') {
            rescue(fn() => Checkin::where('id', $id)->update(['likes_count' => $realLikesCount]), report: false);
        }

        return [
            'liked'       => $isLiked,
            'likes_count' => $realLikesCount,
            'message'     => $isLiked ? 'Đã thích' : 'Đã bỏ thích',
        ];
    }

    public function getPostLikers(int $id, string $type): array
    {
        $reactions = CheckinReaction::where('reactionable_type', $type)
            ->where('reactionable_id', $id)
            ->with('user:id,name,avatar')
            ->latest()
            ->get();

        return $reactions->map(fn($r) => [
            'name'   => $r->user ? $r->user->name : 'Khách',
            'avatar' => $r->user ? $r->user->avatar : null,
            'emoji'  => $r->emoji ?? '👍',
        ])->toArray();
    }

    public function getComments(int $id, string $type): array
    {
        return Comment::with('user')
            ->where('commentable_type', $type)
            ->where('commentable_id', $id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($c) {
                $avatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80';
                if ($c->user) {
                    if ($c->user->avatar && str_starts_with($c->user->avatar, 'avatars/')) {
                        $avatar = rtrim(env('R2_PUBLIC_URL', ''), '/') . '/' . $c->user->avatar;
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
                    'created_at_human' => $c->created_at ? $c->created_at->diffForHumans() : 'Vừa xong',
                ];
            })->toArray();
    }

    public function storeComment(int $id, string $type, string $content, ?int $userId, ?string $guestName): array
    {
        $user = $userId ? User::find($userId) : null;
        $name = $user ? null : ($guestName ?: 'Khách vãng lai');

        $comment = Comment::create([
            'user_id'          => $userId,
            'guest_name'       => $name,
            'commentable_id'   => $id,
            'commentable_type' => $type,
            'content'          => $content,
        ]);

        $totalCommentsCount = Comment::where('commentable_type', $type)
            ->where('commentable_id', $id)
            ->count();

        $avatar = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=100&q=80';
        if ($user) {
            if ($user->avatar && str_starts_with($user->avatar, 'avatars/')) {
                $avatar = rtrim(env('R2_PUBLIC_URL', ''), '/') . '/' . $user->avatar;
            } elseif ($user->avatar) {
                $avatar = $user->avatar;
            }
        }

        return [
            'total_comments' => $totalCommentsCount,
            'comment'        => [
                'id'               => $comment->id,
                'author_name'      => $comment->display_name,
                'author_avatar'    => $avatar,
                'content'          => $comment->content,
                'created_at_human' => 'Vừa xong',
            ],
        ];
    }

    public function incrementShare(string|int $id, ?string $type, ?int $userId): int
    {
        $newShareCount = 0;
        $connections = ['mysql', 'mysql_education'];

        foreach ($connections as $conn) {
            try {
                if ($type === 'education' || $type === 'App\\Models\\EducationProgram') {
                    $item = EducationProgram::on($conn)->where('id', $id)->orWhere('hashid', $id)->first();
                    if ($item) {
                        $item->increment('shares_count');
                        $newShareCount = (int) $item->fresh()->shares_count;
                        break;
                    }
                } elseif ($type === 'checkin' || $type === 'App\\Models\\Checkin') {
                    $item = Checkin::on($conn)->where('id', $id)->orWhere('hashid', $id)->first();
                    if ($item) {
                        $item->increment('shares_count');
                        $newShareCount = (int) $item->fresh()->shares_count;
                        break;
                    }
                } else {
                    $item = Post::on($conn)->where('id', $id)->orWhere('hashid', $id)->first()
                         ?: EducationProgram::on($conn)->where('id', $id)->orWhere('hashid', $id)->first()
                         ?: Checkin::on($conn)->where('id', $id)->orWhere('hashid', $id)->first();

                    if ($item) {
                        $item->increment('shares_count');
                        $newShareCount = (int) $item->fresh()->shares_count;
                        if ($userId) {
                            NotificationService::notifyShare((int)$id, $type ?? 'post', $userId);
                        }
                        break;
                    }
                }
            } catch (\Throwable $e) {}
        }

        return $newShareCount;
    }

    private function removeVietnameseSign(string $str): string
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
