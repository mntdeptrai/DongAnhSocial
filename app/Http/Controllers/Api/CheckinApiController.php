<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\CheckinReaction;
use App\Models\FoodTourDiary;
use App\Models\Eatery;
use App\Models\User;
use App\Services\CheckinService;
use App\Services\CommentService;
use App\Domain\Checkin\CheckinData;
use App\Domain\Comment\CommentData;
use App\Events\CheckinReacted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * CheckinApiController — Social Check-in & Feed cho Mobile App
 *
 * Chịu trách nhiệm duy nhất: Feed check-in, tạo check-in, bình luận, reaction.
 * Auth đã tách sang AuthApiController, Admin đã tách sang AdminApiController.
 */
class CheckinApiController extends Controller
{
    /**
     * Lấy feed Check-in hỗn hợp (gồm Check-in và Nhật ký hành trình)
     */
    public function getFeed(Request $request)
    {
        $limit = (int) $request->query('limit', 20);

        // 1. Lấy Standalone Checkins
        $checkins = Checkin::with(['user', 'eatery.category', 'eatery.commune', 'comments.user'])
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // 2. Lấy Food Tour Diaries
        $diaries = FoodTourDiary::with(['user', 'foodTour.stops', 'comments.user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // 3. Chuẩn hóa cấu trúc feed trước khi gửi về app
        $feed = collect();

        foreach ($checkins as $c) {
            $feed->push([
                'id'                => $c->id,
                'type'              => 'checkin',
                'display_name'      => $c->display_name,
                'avatar_char'       => $c->user ? mb_substr($c->user->name, 0, 1, 'UTF-8') : '👤',
                'role'              => $c->user?->role ?? 'guest',
                'rating'            => $c->rating,
                'comment'           => $c->comment,
                'image_path'        => $c->image_path,
                'created_at_human'  => $c->created_at->diffForHumans(),
                'created_at_format' => $c->created_at->format('d/m/Y H:i'),
                'created_at_ts'     => $c->created_at->timestamp,
                'eatery'            => $c->eatery ? [
                    'name'     => $c->eatery->name,
                    'slug'     => $c->eatery->slug,
                    'category' => $c->eatery->category?->name,
                    'commune'  => $c->eatery->commune?->name,
                ] : null,
                'comments'          => $c->comments->map(function ($comment) {
                    return [
                        'id'           => $comment->id,
                        'display_name' => $comment->display_name,
                        'content'      => $comment->content,
                        'created_at'   => $comment->created_at->diffForHumans(),
                    ];
                }),
            ]);
        }

        foreach ($diaries as $d) {
            // Chặng đi và địa điểm
            $stops = $d->foodTour?->stops ?? collect();
            $stopsData = $stops->map(function ($stop) {
                return [
                    'stop_index' => $stop->stop_index,
                    'name'       => $stop->eatery?->name ?? 'Điểm dừng',
                    'category'   => $stop->eatery?->category?->name,
                    'commune'    => $stop->eatery?->commune?->name,
                ];
            });

            $feed->push([
                'id'                => $d->id,
                'type'              => 'diary',
                'display_name'      => $d->user?->name ?? 'Thực khách',
                'avatar_char'       => $d->user ? mb_substr($d->user->name, 0, 1, 'UTF-8') : '👤',
                'role'              => $d->user?->role ?? 'guest',
                'rating'            => $d->rating,
                'comment'           => $d->comment,
                'image_path'        => $d->image_path,
                'created_at_human'  => $d->created_at->diffForHumans(),
                'created_at_format' => $d->created_at->format('d/m/Y H:i'),
                'created_at_ts'     => $d->created_at->timestamp,
                'food_tour'         => $d->foodTour ? [
                    'name'  => $d->foodTour->name,
                    'slug'  => $d->foodTour->slug,
                    'stops' => $stopsData,
                ] : null,
                'comments'          => $d->comments->map(function ($comment) {
                    return [
                        'id'           => $comment->id,
                        'display_name' => $comment->display_name,
                        'content'      => $comment->content,
                        'created_at'   => $comment->created_at->diffForHumans(),
                    ];
                }),
            ]);
        }

        // Sắp xếp feed theo thời gian mới nhất
        $sortedFeed = $feed->sortByDesc('created_at_ts')->values();

        return response()->json([
            'success' => true,
            'feed'    => $sortedFeed
        ]);
    }

    /**
     * Lưu check-in từ Mobile App (có token hoặc dạng Khách vãng lai)
     */
    public function storeCheckin(Request $request, CheckinService $checkinService)
    {
        $request->validate([
            'eatery_id'  => 'nullable|exists:eateries,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:2000',
            'guest_name' => 'nullable|string|max:100',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $user = $request->user('sanctum');
        $eateryId = $request->filled('eatery_id') ? (int) $request->input('eatery_id') : null;

        $data = new CheckinData(
            eatery_id: $eateryId,
            rating: (int) $request->input('rating'),
            comment: $request->input('comment'),
            guest_name: $user ? null : $request->input('guest_name'),
            user_id: $user ? $user->id : null,
            image: $request->file('image')
        );

        $checkin = $checkinService->createCheckin($data);

        // Tự động gửi thông báo đẩy cho tất cả Bạn bè & Followers ngay lập tức
        if ($user) {
            try {
                \App\Services\NotificationService::notifyNewPost($user, '', $request->input('comment') ?? 'Check-in mới', $eateryId);
            } catch (\Exception $e) {}
        }

        return response()->json([
            'success' => true,
            'message' => 'Check-in của bạn đã được đăng thành công!',
            'checkin' => [
                'id'         => $checkin->id,
                'image_path' => $checkin->image_path,
                'rating'     => $checkin->rating,
                'comment'    => $checkin->comment
            ]
        ], 201);
    }

    /**
     * Đăng bình luận từ Mobile App
     */
    public function storeComment(Request $request, CommentService $commentService)
    {
        $request->validate([
            'content'          => 'required|string|max:1000',
            'commentable_id'   => 'required|integer',
            'commentable_type' => 'required|string|in:App\Models\Checkin,App\Models\FoodTourDiary',
            'guest_name'       => 'nullable|string|max:100',
        ]);

        $user = $request->user('sanctum');

        $data = new CommentData(
            user_id: $user ? $user->id : null,
            guest_name: $user ? null : ($request->input('guest_name') ?? 'Khách vãng lai'),
            content: $request->input('content'),
            commentable_id: (int) $request->input('commentable_id'),
            commentable_type: $request->input('commentable_type')
        );

        $comment = $commentService->createComment($data);

        return response()->json([
            'success' => true,
            'message' => 'Gửi bình luận thành công.',
            'comment' => [
                'id'           => $comment->id,
                'display_name' => $comment->display_name,
                'content'      => $comment->content,
            ]
        ], 201);
    }

    public function getMyCheckins(Request $request)
    {
        $user = $request->user();
        
        $checkins = Checkin::with(['user', 'eatery.category', 'eatery.commune'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $feed = collect();
        foreach ($checkins as $c) {
            $feed->push([
                'id'                => $c->id,
                'type'              => 'checkin',
                'display_name'      => $c->display_name,
                'avatar_char'       => mb_substr($user->name, 0, 1, 'UTF-8'),
                'role'              => $user->role,
                'rating'            => $c->rating,
                'comment'           => $c->comment,
                'image_path'        => $c->image_path,
                'created_at_human'  => $c->created_at->diffForHumans(),
                'created_at_format' => $c->created_at->format('d/m/Y H:i'),
                'eatery'            => $c->eatery ? [
                    'name'     => $c->eatery->name,
                    'slug'     => $c->eatery->slug,
                    'category' => $c->eatery->category?->name,
                    'commune'  => $c->eatery->commune?->name,
                ] : null,
            ]);
        }
        
        return response()->json([
            'success' => true,
            'checkins' => $feed
        ]);
    }

    /**
     * Gửi reaction từ Mobile App / Web
     */
    public function reactToCheckin(Request $request, $id)
    {
        $request->validate([
            'emoji' => 'required|string|max:10',
            'type'  => 'required|string|in:checkin,diary',
        ]);

        $user = Auth::user() ?: $request->user('sanctum');
        $userId = $user ? $user->id : session('user_id');
        $sessionId = $request->header('X-Session-ID') ?: session()->getId();
        if (empty($sessionId)) {
            $sessionId = 'app_' . md5($request->ip() . ($request->header('User-Agent') ?? ''));
        }

        $query = CheckinReaction::where('reactionable_type', $request->type)
            ->where('reactionable_id', (int) $id);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->whereNull('user_id')->where('session_id', $sessionId);
        }

        $existing = $query->first();

        if ($existing) {
            if ($existing->emoji === $request->emoji) {
                $existing->delete();
            } else {
                $existing->update(['emoji' => $request->emoji]);
            }
        } else {
            CheckinReaction::create([
                'reactionable_type' => $request->type,
                'reactionable_id'   => (int) $id,
                'user_id'           => $userId ?: null,
                'session_id'        => $userId ? null : $sessionId,
                'emoji'             => $request->emoji,
            ]);
        }

        $allReactions = CheckinReaction::where('reactionable_type', $request->type)
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

        event(new CheckinReacted((int) $id, $request->type, $request->emoji, $counts, $total));

        // Gửi thông báo tổng hợp kiểu Facebook & FCM push notification tới chủ bài viết
        \App\Services\NotificationService::notifyReaction((int) $id, $request->type, $request->emoji, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Thả cảm xúc thành công.',
            'counts'  => $counts,
            'total'   => $total,
        ]);
    }
}
