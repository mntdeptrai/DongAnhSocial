<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Models\Story;
use App\Models\EducationProgram;
use App\Models\Checkin;
use App\Models\CheckinReaction;
use App\Models\Comment;
use App\Models\User;
use App\Models\Eatery;
use App\Services\NotificationService;
use App\Services\YouTubeService;
use App\Helpers\R2Helper;

/**
 * PostApiController — Quản lý bài viết, Tin 24h (Story), Newsfeed & Reactions cho Mobile App & Web
 */
class PostApiController extends Controller
{
    /**
     * GET /api/v1/newsfeed — Lấy tất cả bài viết Bản tin đa phân quyền (Post, Education, Checkin)
     */
    public function getNewsfeed(Request $request)
    {
        $feedType = $request->input('feed_type', 'for_you');
        $user = auth('sanctum')->user() ?: Auth::user();
        $currentUserId = $user ? $user->id : session('user_id');
        $sessionId = session()->getId() ?: ($request->header('X-Session-ID') ?? null);

        $friendUserIds = [];
        $userEateryIds = [];
        if ($currentUserId) {
            try {
                $friendUserIds = DB::table('friendships')
                    ->where('status', 'accepted')
                    ->where(function($q) use ($currentUserId) {
                        $q->where('user_id', $currentUserId)->orWhere('friend_id', $currentUserId);
                    })
                    ->get()
                    ->map(fn($f) => $f->user_id == $currentUserId ? $f->friend_id : $f->user_id)
                    ->toArray();
            } catch (\Throwable $e) {}

            try {
                $u = User::find($currentUserId);
                if ($u && $u->eatery_id) {
                    $userEateryIds[] = $u->eatery_id;
                }
            } catch (\Throwable $e) {}
        }

        $items = collect();

        try {
            $userPostsMysqlEdu = collect();
            $userPostsMysql = collect();

            try {
                $userPostsMysqlEdu = Post::on('mysql_education')
                    ->with(['user', 'eatery', 'comments.user'])
                    ->orderBy('created_at', 'desc')
                    ->take(60)
                    ->get();
            } catch (\Throwable $e) {}

            try {
                $userPostsMysql = Post::on('mysql')
                    ->with(['user', 'eatery', 'comments.user'])
                    ->orderBy('created_at', 'desc')
                    ->take(60)
                    ->get();
            } catch (\Throwable $e) {}

            $userPosts = $userPostsMysqlEdu->concat($userPostsMysql)->unique('id');
            $items = $items->concat($userPosts);
        } catch (\Throwable $e) {}

        try {
            $excludedTitles = [
                'Hệ đào tạo THPT chính quy chuẩn quốc gia',
                'Lớp chọn ngoại ngữ (Tiếng Anh - Tiếng Trung tăng cường)',
                'Hệ THCS Chất lượng cao trọng điểm',
                'Câu lạc bộ Kỹ năng sống & STEM',
            ];

            $eduPostsMysqlEdu = collect();
            $eduPostsMysql = collect();

            try {
                $eduPostsMysqlEdu = EducationProgram::on('mysql_education')
                    ->with(['eatery'])
                    ->whereNotIn('name', $excludedTitles)
                    ->orderBy('created_at', 'desc')
                    ->take(30)
                    ->get();
            } catch (\Throwable $e) {}

            try {
                $eduPostsMysql = EducationProgram::on('mysql')
                    ->with(['eatery'])
                    ->whereNotIn('name', $excludedTitles)
                    ->orderBy('created_at', 'desc')
                    ->take(30)
                    ->get();
            } catch (\Throwable $e) {}

            $eduPosts = $eduPostsMysqlEdu->concat($eduPostsMysql)->unique('id')->map(function($e) {
                $e->is_edu = true;
                return $e;
            });
            $items = $items->concat($eduPosts);
        } catch (\Throwable $e) {}

        try {
            $checkins = Checkin::with(['user', 'eatery', 'comments.user'])
                ->where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->take(30)
                ->get()
                ->map(function($c) {
                    $c->is_checkin = true;
                    $c->name = $c->eatery ? "📍 Check-in tại " . $c->eatery->name : "📍 Bài đăng Check-in";
                    $c->description = $c->content ?? $c->description;
                    return $c;
                });
            $items = $items->concat($checkins);
        } catch (\Throwable $e) {}

        if ($feedType === 'following') {
            if (!empty($friendUserIds) || !empty($userEateryIds)) {
                $items = $items->filter(function($post) use ($friendUserIds, $userEateryIds) {
                    $uId = $post->user_id ?? null;
                    $eId = $post->eatery_id ?? null;
                    return ($uId && in_array($uId, $friendUserIds)) || ($eId && in_array($eId, $userEateryIds));
                })->values();
            } else {
                $items = collect();
            }
        } elseif ($feedType === 'nearby') {
            $items = $items->filter(function($post) {
                return !empty($post->is_checkin) || !empty($post->eatery_id);
            })->values();
        }

        $engagementReactions = collect();
        $engagementComments = collect();
        try {
            $allPostIds = $items->pluck('id')->filter()->toArray();
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

        $scoredItems = $items->map(function($post) use ($friendUserIds, $userEateryIds, $currentUserId, $engagementReactions, $engagementComments, $feedType) {
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
                    $post->_personal_tag = '🎯 Gợi ý cho bạn';
                }
            }

            if ($currentUserId) {
                $seed = crc32($currentUserId . '_' . $post->id . '_' . date('Y-m-d'));
                $score += ($seed % 20) - 10;
            }

            $post->_feed_score = $score;
            return $post;
        })->sortByDesc('_feed_score')->values();

        $postsList = [];
        foreach ($scoredItems as $item) {
            try {
                if (!empty($item->is_edu)) {
                    $authorName = $item->eatery ? $item->eatery->name : 'Ban Giám Hiệu Trường';
                    $img = $item->image_path ?? ($item->eatery ? $item->eatery->image_path : null);

                    $realLikes = CheckinReaction::where('reactionable_type', 'post')
                        ->where('reactionable_id', $item->id)
                        ->count();

                    $isLiked = false;
                    if ($currentUserId) {
                        $isLiked = CheckinReaction::where('reactionable_type', 'post')
                            ->where('reactionable_id', $item->id)
                            ->where('user_id', $currentUserId)
                            ->exists();
                    }

                    $eduImgs = [];
                    if (!empty($item->images)) {
                        if (is_array($item->images)) $eduImgs = $item->images;
                        else if (is_string($item->images)) {
                            $decoded = json_decode($item->images, true);
                            if (is_array($decoded)) $eduImgs = $decoded;
                        }
                    }
                    if (empty($eduImgs) && !empty($img)) {
                        $eduImgs = [$img];
                    }

                    $eduUserId = $item->user_id ?? ($item->eatery ? $item->eatery->user_id : null);
                    $postsList[] = [
                        'id'               => 'edu_' . $item->id,
                        'numeric_id'       => $item->id,
                        'hashid'           => 'edu_' . $item->id,
                        'type'             => 'post',
                        'user_id'          => $eduUserId,
                        'author_id'        => $eduUserId,
                        'author_name'      => $authorName,
                        'author_avatar'    => $item->eatery ? $item->eatery->image_path : null,
                        'author_role'      => 'principal',
                        'title'            => $item->name ?? '',
                        'description'      => $item->description ?? $item->target_students ?? '',
                        'image_path'       => $img,
                        'images'           => $eduImgs,
                        'likes_count'      => $realLikes,
                        'is_liked'         => $isLiked,
                        'comments_count'   => 0,
                        'created_at_human' => $item->created_at ? $item->created_at->diffForHumans() : '2 ngày trước',
                        'comments'         => [],
                        'personal_tag'     => $item->_personal_tag ?? null,
                    ];
                } else {
                    $authorName = $item->user ? $item->user->name : ($item->eatery ? $item->eatery->name : 'Thành viên Đông Anh');
                    $authorAvatar = $item->user ? ($item->user->avatar_url ?: $item->user->avatar) : ($item->eatery ? ($item->eatery->image_path ?? null) : null);
                    $authorRole = $item->user ? ($item->user->role ?? 'user') : 'user';

                    $imgs = [];
                    if (!empty($item->images)) {
                        if (is_array($item->images)) {
                            $imgs = $item->images;
                        } else if (is_string($item->images)) {
                            $decoded = json_decode($item->images, true);
                            if (is_array($decoded)) $imgs = $decoded;
                        }
                    }
                    if (empty($imgs) && !empty($item->image_paths)) {
                        if (is_array($item->image_paths)) {
                            $imgs = $item->image_paths;
                        } else if (is_string($item->image_paths)) {
                            $decoded = json_decode($item->image_paths, true);
                            if (is_array($decoded)) $imgs = $decoded;
                        }
                    }
                    if (empty($imgs) && !empty($item->image_path)) {
                        $imgs = [$item->image_path];
                    }

                    $vids = [];
                    if (!empty($item->videos)) {
                        if (is_array($item->videos)) {
                            $vids = $item->videos;
                        } else if (is_string($item->videos)) {
                            $decoded = json_decode($item->videos, true);
                            if (is_array($decoded)) $vids = $decoded;
                        }
                    }
                    if (empty($imgs) && !empty($vids)) {
                        $imgs = $vids;
                    }
                    $img = !empty($imgs) ? $imgs[0] : (!empty($vids) ? $vids[0] : $item->image_path);

                    $commentsArr = [];
                    if ($item->relationLoaded('comments') && $item->comments) {
                        foreach ($item->comments as $c) {
                            $commentsArr[] = [
                                'author' => $c->user ? $c->user->name : ($c->guest_name ?? 'Thành viên'),
                                'text'   => $c->content ?? '',
                                'time'   => $c->created_at ? $c->created_at->diffForHumans() : 'Vừa xong',
                            ];
                        }
                    }

                    $realLikes = CheckinReaction::where('reactionable_type', 'post')
                        ->where('reactionable_id', $item->id)
                        ->count();

                    $isLiked = false;
                    if ($currentUserId) {
                        $isLiked = CheckinReaction::where('reactionable_type', 'post')
                            ->where('reactionable_id', $item->id)
                            ->where('user_id', $currentUserId)
                            ->exists();
                    } else if (!empty($sessionId)) {
                        $isLiked = CheckinReaction::where('reactionable_type', 'post')
                            ->where('reactionable_id', $item->id)
                            ->whereNull('user_id')
                            ->where('session_id', $sessionId)
                            ->exists();
                    }

                    $postUserId = $item->user_id ?? ($item->user ? $item->user->id : null);
                    $postsList[] = [
                        'id'               => $item->id,
                        'numeric_id'       => $item->id,
                        'hashid'           => $item->hashid ?? ('post_' . $item->id),
                        'type'             => 'post',
                        'user_id'          => $postUserId,
                        'author_id'        => $postUserId,
                        'author_name'      => $authorName,
                        'author_avatar'    => $authorAvatar,
                        'author_role'      => $authorRole,
                        'title'            => $item->name ?? $item->title ?? '',
                        'description'      => $item->description ?? '',
                        'image_path'       => $img,
                        'images'           => $imgs,
                        'videos'           => $vids,
                        'likes_count'      => $realLikes,
                        'is_liked'         => $isLiked,
                        'comments_count'   => count($commentsArr) ?: (int) ($item->comments_count ?? 0),
                        'created_at_human' => $item->created_at ? $item->created_at->diffForHumans() : 'Vừa xong',
                        'comments'         => $commentsArr,
                        'personal_tag'     => $item->_personal_tag ?? null,
                    ];
                }
            } catch (\Throwable $e) {}
        }

        // Inject Stories 24h vào đầu danh sách (hiển thị trên Story Cards)
        $storyItems = [];
        try {
            $stories24h = Story::with('user')
                ->where('created_at', '>=', now()->subHours(24))
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->get();

            foreach ($stories24h as $story) {
                $sAuthor = $story->user ? $story->user->name : ($story->author_name ?? 'Thành viên');
                $sAvatar = $story->user ? ($story->user->avatar_url ?: $story->user->avatar) : $story->author_avatar;
                $sImages = $story->media_url ? [$story->media_url] : [];
                $sUserId = $story->user_id ?? ($story->user ? $story->user->id : null);

                $storyItems[] = [
                    'id'               => 'story_' . $story->id,
                    'numeric_id'       => $story->id,
                    'hashid'           => 'story_' . $story->id,
                    'type'             => 'story',
                    'is_story'         => true,
                    'user_id'          => $sUserId,
                    'author_id'        => $sUserId,
                    'author_name'      => $sAuthor,
                    'author_avatar'    => $sAvatar,
                    'author_role'      => $story->user ? ($story->user->role ?? 'user') : 'user',
                    'title'            => $story->caption ?? '',
                    'description'      => $story->caption ?? '',
                    'image_path'       => $story->media_url,
                    'media_url'        => $story->media_url,
                    'images'           => $sImages,
                    'videos'           => ($story->type === 'video' && $story->media_url) ? [$story->media_url] : [],
                    'bg_gradient'      => $story->bg_gradient,
                    'story_type'       => $story->type ?? 'image',
                    'likes_count'      => 0,
                    'is_liked'         => false,
                    'comments_count'   => 0,
                    'created_at_human' => $story->created_at ? $story->created_at->diffForHumans() : 'Vừa xong',
                    'comments'         => [],
                    'personal_tag'     => '📸 Tin 24h',
                ];
            }
        } catch (\Throwable $e) {}

        // Stories go first, then normal posts
        $finalFeed = array_merge($storyItems, $postsList);

        return response()->json($finalFeed, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * POST /api/v1/reactions/toggle — Thả tim / Bỏ tim bài viết (Đồng bộ với DB web)
     */
    public function toggleReaction(Request $request)
    {
        try {
            $id = (int) ($request->input('id') ?? $request->input('post_id'));
            $type = $request->input('type', 'post');
            $emoji = $request->input('emoji', '👍');

            if (!$id) {
                return response()->json(['success' => false, 'message' => 'Bài viết không hợp lệ'], 400);
            }

            $user = auth('sanctum')->user() ?: Auth::user();
            $userId = $user ? $user->id : session('user_id');
            $sessionId = session()->getId() ?: ($request->header('X-Session-ID') ?? null);
            if (empty($sessionId)) {
                $sessionId = 'app_' . md5($request->ip() . ($request->header('User-Agent') ?? ''));
            }

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
                    'emoji'             => $emoji ?: '👍',
                ]);
                $isLiked = true;

                try {
                    NotificationService::notifyReaction($id, $type, $emoji ?: '👍', $userId);
                } catch (\Throwable $notifErr) {}
            }

            $realLikesCount = CheckinReaction::where('reactionable_type', $type)
                ->where('reactionable_id', $id)
                ->count();

            if ($type === 'post') {
                try {
                    Post::where('id', $id)->update(['likes_count' => $realLikesCount]);
                } catch (\Throwable $e) {}
                try {
                    Post::on('mysql_education')->where('id', $id)->update(['likes_count' => $realLikesCount]);
                } catch (\Throwable $e) {}
                try {
                    EducationProgram::on('mysql_education')->where('id', $id)->update(['likes_count' => $realLikesCount]);
                } catch (\Throwable $e) {}
                try {
                    EducationProgram::on('mysql')->where('id', $id)->update(['likes_count' => $realLikesCount]);
                } catch (\Throwable $e) {}
            } else if ($type === 'checkin') {
                try {
                    Checkin::where('id', $id)->update(['likes_count' => $realLikesCount]);
                } catch (\Throwable $e) {}
            }

            return response()->json([
                'success'     => true,
                'liked'       => $isLiked,
                'likes_count' => $realLikesCount,
                'message'     => $isLiked ? 'Đã thích' : 'Đã bỏ thích'
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 200);
        }
    }

    /**
     * POST /api/v1/posts — Đăng bài viết mới lên Bản tin (Dành cho tất cả các Role)
     */
    public function storePost(Request $request)
    {
        $user = auth('sanctum')->user() ?: Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để đăng bài lên Bản tin'], 401);
        }

        $request->validate([
            'description'  => 'required|string',
            'name'         => 'nullable|string',
            'image_path'   => 'nullable|string',
            'image_base64' => 'nullable|string',
            'images'       => 'nullable|array',
            'videos'       => 'nullable|array',
            'image_urls'   => 'nullable|array',
            'video_urls'   => 'nullable|array',
        ]);

        $savedImagePath = $request->image_path;
        $images = $request->input('images', $request->input('image_urls', []));
        $videos = $request->input('videos', $request->input('video_urls', []));

        if ($request->filled('image_base64')) {
            try {
                $base64Data = $request->input('image_base64');
                $type = 'jpg';
                if (preg_match('/^data:(image|video)\/(\w+);base64,/', $base64Data, $matches)) {
                    $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                    $type = strtolower($matches[2]);
                }
                $decodedBytes = base64_decode($base64Data);

                if ($decodedBytes !== false) {
                    $filename = 'posts/post_' . time() . '_' . uniqid() . '.' . $type;
                    $r2PublicUrl = rtrim(env('R2_PUBLIC_URL', 'https://media.xadonganh.com'), '/');
                    if (env('R2_ACCESS_KEY_ID') && env('R2_BUCKET')) {
                        try {
                            Storage::disk('r2')->put($filename, $decodedBytes, 'public');
                            $savedImagePath = $r2PublicUrl . '/' . $filename;
                        } catch (\Throwable $r2Err) {
                            Log::warning("R2 Upload Error: " . $r2Err->getMessage());
                            $destinationPath = public_path('storage/posts');
                            if (!file_exists($destinationPath)) {
                                mkdir($destinationPath, 0755, true);
                            }
                            file_put_contents(public_path('storage/' . $filename), $decodedBytes);
                            $savedImagePath = 'storage/' . $filename;
                        }
                    } else {
                        $destinationPath = public_path('storage/posts');
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0755, true);
                        }
                        file_put_contents(public_path('storage/' . $filename), $decodedBytes);
                        $savedImagePath = 'storage/' . $filename;
                    }
                }
            } catch (\Throwable $e) {}
        } else if ($request->hasFile('image_file')) {
            try {
                $file = $request->file('image_file');
                $filename = 'posts/post_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $r2PublicUrl = rtrim(env('R2_PUBLIC_URL', 'https://media.xadonganh.com'), '/');
                if (env('R2_ACCESS_KEY_ID') && env('R2_BUCKET')) {
                    try {
                        Storage::disk('r2')->putFileAs('', $file, $filename, 'public');
                        $savedImagePath = $r2PublicUrl . '/' . $filename;
                    } catch (\Throwable $r2Err) {
                        $file->move(public_path('storage/posts'), basename($filename));
                        $savedImagePath = 'storage/posts/' . basename($filename);
                    }
                } else {
                    $file->move(public_path('storage/posts'), basename($filename));
                    $savedImagePath = 'storage/posts/' . basename($filename);
                }
            } catch (\Throwable $e) {}
        }

        if (empty($savedImagePath) && !empty($images) && is_array($images)) {
            $savedImagePath = $images[0];
        }

        $post = Post::create([
            'user_id'     => $user->id,
            'name'        => $request->input('name') ?: (mb_substr($request->description, 0, 50) . '...'),
            'description' => $request->description,
            'image_path'  => $savedImagePath,
            'images'      => !empty($images) ? array_values($images) : ($savedImagePath ? [$savedImagePath] : null),
            'videos'      => !empty($videos) ? array_values($videos) : null,
            'status'      => 'published',
        ]);

        try {
            NotificationService::notifyNewPost($user, $post->name, $post->description ?? '');
        } catch (\Throwable $notifErr) {}

        return response()->json([
            'success' => true,
            'message' => 'Đã đăng bài viết thành công lên Bản tin!',
            'post'    => $post
        ], 201, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * POST /api/v1/stories hoặc POST /stories — Đăng Story tin mới 24h (Facebook / Instagram Style)
     */
    public function storeStory(Request $request)
    {
        $user = auth('sanctum')->user() ?: (Auth::user() ?: User::find(session('user_id')));

        $mediaUrl = null;
        $type = $request->input('type', 'image');

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            if ($file->isValid()) {
                $mime = $file->getMimeType() ?: '';
                $isVid = str_contains($mime, 'video') || in_array(strtolower($file->getClientOriginalExtension()), ['mp4', 'mov', 'avi', 'mkv', 'webm', '3gp', 'm4v']) || $request->input('type') === 'video';
                if ($isVid) {
                    $type = 'video';
                    if (YouTubeService::isConfigured()) {
                        try {
                            $captionTitle = $request->input('caption') ?: ('Story Đông Anh - ' . ($user ? $user->name : 'Thành viên'));
                            $ytResult = YouTubeService::uploadVideo(
                                video: $file,
                                title: Str::limit($captionTitle, 95),
                                description: "Story tin ngắn đăng tải tại Đông Anh Discovery bởi " . ($user ? $user->name : 'Thành viên'),
                                privacy: 'unlisted',
                                tags: ['Shorts', 'DongAnh', 'Story']
                            );
                            if ($ytResult && !empty($ytResult['url'])) {
                                $mediaUrl = $ytResult['url'];
                            }
                        } catch (\Throwable $e) {
                            Log::warning('YouTube story upload warning: ' . $e->getMessage());
                        }
                    }
                }

                if (empty($mediaUrl)) {
                    $uploaded = R2Helper::upload($file, 'stories');
                    if ($uploaded) {
                        $mediaUrl = $uploaded;
                    }
                }
            }
        }

        $story = Story::create([
            'user_id'       => $user ? $user->id : null,
            'author_name'   => $user ? $user->name : ($request->input('author_name') ?: 'Thành viên Đông Anh'),
            'author_avatar' => $user ? ($user->avatar_url ?? $user->avatar) : null,
            'media_url'     => $mediaUrl,
            'caption'       => $request->input('caption'),
            'bg_gradient'   => $request->input('bg_gradient', 'linear-gradient(135deg, #0ea5e9, #0284c7)'),
            'type'          => $type
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã chia sẻ Story tin mới thành công! ✨',
            'story'   => $story
        ]);
    }

    /**
     * DELETE /posts/{id} — Xóa bài viết (chỉ chủ sở hữu hoặc admin)
     */
    public function destroyPost($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
        }

        $cleanId = str_replace(['post_', 'edu_', 'checkin_'], '', $id);

        $post = Post::on('mysql_education')->find($cleanId)
             ?: Post::on('mysql')->find($cleanId)
             ?: Checkin::find($cleanId)
             ?: EducationProgram::on('mysql_education')->find($cleanId)
             ?: EducationProgram::on('mysql')->find($cleanId);

        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bài viết!'], 404);
        }

        $userRole = strtolower($user->role ?? '');
        $isAdmin = ($user->isAdmin() || $userRole === 'admin' || $userRole === 'superadmin');
        $isOwner = $isAdmin || (isset($post->user_id) && $post->user_id == $user->id);

        if (!$isOwner && !empty($post->eatery_id)) {
            $school = Eatery::on('mysql_education')->find($post->eatery_id)
                   ?: Eatery::on('mysql')->find($post->eatery_id);
            if ($school && $school->user_id == $user->id) {
                $isOwner = true;
            }
        }

        if (!$isOwner) {
            return response()->json(['success' => false, 'message' => 'Quyền truy cập bị từ chối!'], 403);
        }

        $post->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa bài viết thành công!']);
    }

    /**
     * DELETE /stories/{id} — Xóa Story 24h (chỉ chủ sở hữu hoặc admin)
     */
    public function destroyStory($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
        }

        $cleanId = str_replace('story_', '', $id);
        $story = Story::find($cleanId);
        if (!$story) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy story!'], 404);
        }

        $userRole = strtolower($user->role ?? '');
        $isAdmin = ($user->isAdmin() || $userRole === 'admin' || $userRole === 'superadmin');
        $isOwner = $isAdmin || ($story->user_id && $story->user_id == $user->id);

        if (!$isOwner) {
            return response()->json(['success' => false, 'message' => 'Quyền truy cập bị từ chối!'], 403);
        }

        $story->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa story thành công!']);
    }
}
