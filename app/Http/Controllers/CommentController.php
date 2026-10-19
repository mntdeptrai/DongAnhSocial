<?php

namespace App\Http\Controllers;

use App\Contracts\CommentServiceInterface;
use App\Domain\Comment\CommentData;
use App\Http\Requests\StoreCommentRequest;
use App\Services\SpamProtectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function __construct(
        protected CommentServiceInterface $commentService
    ) {}

    public function store(StoreCommentRequest $request): JsonResponse|RedirectResponse
    {
        $content = (string) $request->input('content');

        // Kiểm tra chống bot và lọc spam toàn diện
        $spamCheck = SpamProtectionService::check($request, $content, 'comment');
        if ($spamCheck['is_spam']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $spamCheck['reason']
                ], 422);
            }
            return redirect()->back()
                ->withErrors(['content' => $spamCheck['reason']])
                ->with('error', $spamCheck['reason'])
                ->withInput();
        }

        $data = CommentData::fromRequest($request);
        $comment = $this->commentService->createComment($data);

        // Ghi nhận bình luận thành công (kích hoạt cooldown 4s và chống gửi trùng lặp)
        SpamProtectionService::recordSuccess($request, $content, 'comment');

        if ($request->ajax() || $request->wantsJson()) {
            $user = $comment->user;
            $displayName = $comment->display_name ?? ($user ? $user->name : ($comment->guest_name ?: 'Khách vãng lai'));
            return response()->json([
                'success' => true,
                'message' => 'Bình luận của bạn đã được gửi thành công! 💬',
                'comment' => [
                    'id'            => $comment->id,
                    'content'       => $comment->content,
                    'display_name'  => $displayName,
                    'is_admin'      => $user ? ($user->role === 'admin') : false,
                    'avatar_letter' => mb_substr($displayName, 0, 1, 'UTF-8'),
                    'created_at'    => 'Vừa xong',
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Bình luận của bạn đã được gửi thành công! 💬');
    }
}
