<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CommentService;
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

        $data = CommentData::fromRequest($request);
        $this->commentService->createComment($data);

        return redirect()->back()->with('success', 'Bình luận của bạn đã được gửi thành công! 💬');
    }
}
