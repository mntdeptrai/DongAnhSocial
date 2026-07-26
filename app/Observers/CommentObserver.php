<?php

namespace App\Observers;

use App\Events\NewCommentPosted;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        Log::info("Một bình luận mới vừa được tạo cho [{$comment->commentable_type}] ID [{$comment->commentable_id}] bởi [{$comment->display_name}].");

        // Broadcast real-time đến tất cả client
        broadcast(new NewCommentPosted($comment));

        // Gửi thông báo tổng hợp kiểu Facebook & FCM push notification tới chủ bài viết
        \App\Services\NotificationService::notifyComment($comment);
    }
}
