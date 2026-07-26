<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
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
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CheckinApiController extends Controller
{
    /**
     * Cấp token truy cập (Sanctum) cho Mobile App khi đăng nhập
     */
    public function issueToken(Request $request)
    {
        try {
            $request->validate([
                'email'       => 'required|email',
                'password'    => 'required|string',
                'device_name' => 'nullable|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email hoặc mật khẩu không chính xác.'
                ], 401);
            }

            if ($user->status === 'disabled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản đã bị khóa.'
                ], 403);
            }

            $deviceName = $request->input('device_name', 'mobile-app');
            try {
                $token = $user->createToken($deviceName)->plainTextToken;
            } catch (\Throwable $e) {
                // Fallback token nếu chưa migrate personal_access_tokens trên production
                $token = base64_encode($user->id . '|' . $user->email . '|' . time());
            }

            return response()->json([
                'success' => true,
                'token'   => $token,
                'user'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng nhập không thành công: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Thu hồi token hiện tại (Đăng xuất khỏi mobile)
     */
    public function revokeToken(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã đăng xuất thành công.'
        ]);
    }

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
            'eatery_id'  => 'required|exists:eateries,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:2000',
            'guest_name' => 'nullable|string|max:100',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $user = $request->user('sanctum');

        $data = new CheckinData(
            eatery_id: (int) $request->input('eatery_id'),
            rating: (int) $request->input('rating'),
            comment: $request->input('comment'),
            guest_name: $user ? null : $request->input('guest_name'),
            user_id: $user ? $user->id : null,
            image: $request->file('image')
        );

        $checkin = $checkinService->createCheckin($data);

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
     * Gửi reaction từ Mobile App
     */
    public function reactToCheckin(Request $request, $id)
    {
        $request->validate([
            'emoji' => 'required|string|max:10',
            'type'  => 'required|string|in:checkin,diary',
        ]);

        event(new CheckinReacted((int) $id, $request->type, $request->emoji));

        return response()->json([
            'success' => true,
            'message' => 'Thả cảm xúc thành công.'
        ]);
    }
}
