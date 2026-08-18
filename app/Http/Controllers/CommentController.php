<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CommentService;
use App\Services\SpamProtectionService;
use App\Domain\Comment\CommentData;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'content'          => 'required|string|max:1000',
            'commentable_id'   => 'required|integer',
            'commentable_type' => 'required|string|in:App\Models\Checkin,App\Models\FoodTourDiary,App\Models\Post,post',
            'guest_name'       => 'nullable|string|max:100',
        ]);

        $content = (string) $request->input('content');

        // Kiểm tra chống bot và lọc spam toàn diện
        $spamCheck = SpamProtectionService::check($request, $content, 'comment');
        if ($spamCheck['is_spam']) {
            return redirect()->back()
                ->withErrors(['content' => $spamCheck['reason']])
                ->with('error', $spamCheck['reason'])
                ->withInput();
        }

        $data = CommentData::fromRequest($request);
        $this->commentService->createComment($data);

        // Ghi nhận bình luận thành công (kích hoạt cooldown 4s và chống gửi trùng lặp)
        SpamProtectionService::recordSuccess($request, $content, 'comment');

        return redirect()->back()->with('success', 'Bình luận của bạn đã được gửi thành công! 💬');
    }
}
