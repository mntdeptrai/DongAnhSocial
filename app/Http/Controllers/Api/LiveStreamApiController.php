<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use App\Models\LiveStreamComment;
use App\Events\LiveStreamCommentSent;
use App\Events\LiveStreamReactionSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LiveStreamApiController extends Controller
{
    /**
     * Danh sách phòng Livestream đang phát (Cho Flutter & Mobile Apps)
     * GET /api/v1/livestreams
     */
    public function index(Request $request)
    {
        $category = $request->get('category');
        $query = LiveStream::with(['user:id,name,avatar', 'pinnedProduct:id,name,price,image_path', 'products:id,name,price,image_path,star_rating'])
            ->where('status', 'live');

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        $streams = $query->orderByDesc('viewer_count')->latest()->get()->map(function ($s) {
            return [
                'id'             => $s->id,
                'title'          => $s->title,
                'description'    => $s->description,
                'category'       => $s->category,
                'cover_image'    => $s->cover_image,
                'viewer_count'   => $s->viewer_count,
                'likes_count'    => $s->likes_count,
                'started_at'     => $s->started_at?->toISOString(),
                'products_count' => $s->products->count(),
                'user'           => [
                    'id'     => $s->user->id,
                    'name'   => $s->user->name,
                    'avatar' => $s->user->avatar ? asset($s->user->avatar) : null,
                ],
                'pinned_product' => $s->pinnedProduct ? [
                    'id'        => $s->pinnedProduct->id,
                    'name'      => $s->pinnedProduct->name,
                    'price'     => $s->pinnedProduct->price ? number_format($s->pinnedProduct->price) . 'đ' : null,
                    'image_url' => $s->pinnedProduct->image_path ? asset($s->pinnedProduct->image_path) : null,
                ] : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $streams,
        ]);
    }

    /**
     * Chi tiết phòng Livestream & bình luận gần nhất
     * GET /api/v1/livestreams/{id}
     */
    public function show($id)
    {
        $stream = LiveStream::with([
            'user:id,name,avatar',
            'pinnedProduct:id,name,price,image_path,star_rating',
            'products:id,name,price,image_path,star_rating,unit',
            'comments' => function ($q) {
                $q->with('user:id,name,avatar')->latest()->limit(30);
            }
        ])->find($id);

        if (!$stream) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy livestream.'], 404);
        }

        $pinnedId = $stream->pinned_product_id;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'             => $stream->id,
                'title'          => $stream->title,
                'description'    => $stream->description,
                'status'         => $stream->status,
                'category'       => $stream->category,
                'viewer_count'   => $stream->viewer_count,
                'likes_count'    => $stream->likes_count,
                'user'           => [
                    'id'     => $stream->user->id,
                    'name'   => $stream->user->name,
                    'avatar' => $stream->user->avatar ? asset($stream->user->avatar) : null,
                ],
                'pinned_product' => $stream->pinnedProduct ? [
                    'id'          => $stream->pinnedProduct->id,
                    'name'        => $stream->pinnedProduct->name,
                    'price'       => $stream->pinnedProduct->price ? number_format($stream->pinnedProduct->price) . 'đ' : null,
                    'image_url'   => $stream->pinnedProduct->image_path ? asset($stream->pinnedProduct->image_path) : null,
                    'star_rating' => $stream->pinnedProduct->star_rating,
                    'detail_url'  => route('ocop.product.show', $stream->pinnedProduct->slug ?: $stream->pinnedProduct->id),
                ] : null,
                'products'       => $stream->products->map(function ($p) use ($pinnedId) {
                    return [
                        'id'          => $p->id,
                        'name'        => $p->name,
                        'price'       => $p->price ? number_format($p->price) . 'đ' : null,
                        'image_url'   => $p->image_path ? asset($p->image_path) : null,
                        'star_rating' => $p->star_rating,
                        'unit'        => $p->unit,
                        'detail_url'  => route('ocop.product.show', $p->slug ?: $p->id),
                        'is_pinned'   => (int)$p->id === (int)$pinnedId,
                    ];
                }),
                'comments'       => $stream->comments->map(function ($c) {
                    return [
                        'id'          => $c->id,
                        'user_name'   => $c->user->name ?? 'Khách',
                        'user_avatar' => $c->user->avatar ? asset($c->user->avatar) : null,
                        'message'     => $c->message,
                        'created_at'  => $c->created_at->format('H:i'),
                    ];
                }),
            ]
        ]);
    }


    /**
     * Gửi bình luận từ Mobile App
     * POST /api/v1/livestreams/{id}/comment
     */
    public function comment(Request $request, $id)
    {
        $user = Auth::guard('sanctum')->user() ?? Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $request->validate(['message' => 'required|string|max:500']);
        $stream = LiveStream::findOrFail($id);

        $comment = LiveStreamComment::create([
            'live_stream_id' => $stream->id,
            'user_id'        => $user->id,
            'message'        => trim($request->message),
        ]);

        $userAvatar = $user->avatar ? asset($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name);

        try {
            broadcast(new LiveStreamCommentSent(
                liveStreamId: $stream->id,
                commentId: $comment->id,
                userId: $user->id,
                userName: $user->name,
                userAvatar: $userAvatar,
                message: $comment->message,
                createdAt: $comment->created_at->format('H:i')
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('LiveStreamCommentSent broadcast warning: ' . $e->getMessage());
        }

        return response()->json([
            'status'  => 'success',
            'comment' => [
                'id'          => $comment->id,
                'user_name'   => $user->name,
                'user_avatar' => $userAvatar,
                'message'     => $comment->message,
                'created_at'  => $comment->created_at->format('H:i'),
            ]
        ]);
    }

    /**
     * Gửi reaction từ Mobile App
     * POST /api/v1/livestreams/{id}/reaction
     */
    public function reaction(Request $request, $id)
    {
        $stream = LiveStream::findOrFail($id);
        $type = $request->input('type', 'heart');

        $stream->increment('likes_count');
        $stream->refresh();

        try {
            broadcast(new LiveStreamReactionSent(
                liveStreamId: $stream->id,
                reactionType: $type,
                totalLikes: (int)$stream->likes_count
            ))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('LiveStreamReactionSent broadcast warning: ' . $e->getMessage());
        }

        return response()->json([
            'status'      => 'success',
            'total_likes' => $stream->likes_count,
        ]);
    }
}
