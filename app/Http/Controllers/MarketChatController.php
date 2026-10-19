<?php

namespace App\Http\Controllers;

use App\Contracts\MarketChatServiceInterface;
use App\Contracts\MarketStallServiceInterface;
use App\Http\Requests\SendMarketMessageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketChatController extends Controller
{
    public function __construct(
        protected MarketChatServiceInterface $marketChatService,
        protected MarketStallServiceInterface $marketStallService
    ) {}

    /**
     * Get the latest chat messages for a specific market (public or private)
     * GET /api/market-chat/{eateryId}/messages
     */
    public function getMessages(Request $request, int|string $eateryId): JsonResponse
    {
        $feed = $this->marketChatService->getChatFeed(
            $eateryId,
            $request->only(['room', 'private_stall_name', 'private_user_id']),
            Auth::user()
        );

        return response()->json(array_merge(['success' => true], $feed));
    }

    /**
     * Send a new message to the market chat (public or private)
     * POST /api/market-chat/{eateryId}/send
     */
    public function sendMessage(SendMarketMessageRequest $request, int|string $eateryId): JsonResponse
    {
        $privateStallName = $request->input('private_stall_name');
        $privateUserId = $request->input('private_user_id');

        if (($privateStallName || $privateUserId) && !Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để gửi tin nhắn riêng.',
            ], 403);
        }

        $message = $this->marketChatService->sendMessage(
            $eateryId,
            $request->validated(),
            Auth::user(),
            $request->file('image')
        );

        return response()->json([
            'success' => true,
            'message' => [
                'id'             => $message->id,
                'sender_name'    => $message->sender_name,
                'sender_role'    => $message->sender_role,
                'stall_name'     => $message->stall_name,
                'message_text'   => $message->message_text,
                'image_url'      => $message->image_path ? asset($message->image_path) : null,
                'time_formatted' => $message->created_at->format('H:i'),
                'is_own'         => true,
            ],
        ]);
    }

    /**
     * Get all reviews for a specific market stall
     * GET /api/market-stalls/{eateryId}/reviews
     */
    public function getStallReviews(int|string $eateryId, Request $request): JsonResponse
    {
        $stallName = $request->query('stall_name');
        if (!$stallName) {
            return response()->json(['success' => false, 'message' => 'Thiếu tên sạp.'], 400);
        }

        $reviews = $this->marketStallService->getStallReviews($eateryId, $stallName);

        $formatted = $reviews->map(function ($rev) {
            return [
                'id'             => $rev->id,
                'user_name'      => $rev->user_name,
                'rating'         => $rev->rating,
                'comment'        => $rev->comment,
                'time_formatted' => $rev->created_at ? $rev->created_at->diffForHumans() : 'Vừa xong',
            ];
        });

        return response()->json([
            'success' => true,
            'reviews' => $formatted,
        ]);
    }

    /**
     * Store a new review for a specific market stall
     * POST /api/market-stalls/{eateryId}/reviews
     */
    public function storeStallReview(int|string $eateryId, Request $request): JsonResponse
    {
        $stallName = $request->input('stall_name');
        $comment = $request->input('comment');
        $rating = (int) $request->input('rating', 5);

        if (!$stallName || !$comment) {
            return response()->json(['success' => false, 'message' => 'Thiếu thông tin đánh giá.'], 400);
        }

        $review = $this->marketStallService->storeStallReview(
            $eateryId,
            $stallName,
            [
                'comment'   => $comment,
                'rating'    => $rating,
                'user_name' => $request->input('user_name'),
            ],
            Auth::user()
        );

        return response()->json([
            'success' => true,
            'review'  => [
                'id'             => $review->id,
                'user_name'      => $review->user_name,
                'rating'         => $review->rating,
                'comment'        => $review->comment,
                'time_formatted' => 'Vừa xong',
            ],
        ]);
    }
}
