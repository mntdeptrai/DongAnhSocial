<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event phát khi có bình luận mới được gửi thành công.
 * Dùng ShouldBroadcastNow để phát ngay.
 */
class NewCommentPosted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Comment $comment)
    {
        // Eager load quan hệ user để tránh N+1 query
        $this->comment->loadMissing(['user']);
    }

    /**
     * Channel public — bất kỳ ai đang xem trang /checkin đều nhận được
     */
    public function broadcastOn(): Channel
    {
        return new Channel('checkin-feed');
    }

    /**
     * Tên event phía client lắng nghe
     */
    public function broadcastAs(): string
    {
        return 'NewCommentPosted';
    }

    /**
     * Dữ liệu gửi kèm theo event để render bình luận
     */
    public function broadcastWith(): array
    {
        $comment = $this->comment;
        return [
            'id' => $comment->id,
            'commentable_id' => $comment->commentable_id,
            'commentable_type' => $comment->commentable_type,
            'content' => $comment->content,
            'display_name' => $comment->display_name,
            'avatar_char' => $comment->user
                ? mb_substr($comment->user->name, 0, 1, 'UTF-8')
                : '👤',
            'role' => $comment->user?->role ?? 'guest',
            'created_at_human' => $comment->created_at->diffForHumans(),
        ];
    }
}
