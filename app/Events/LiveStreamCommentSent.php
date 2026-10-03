<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveStreamCommentSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $liveStreamId,
        public int $commentId,
        public int $userId,
        public string $userName,
        public string $userAvatar,
        public string $message,
        public string $createdAt
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('live-stream.' . $this->liveStreamId);
    }

    public function broadcastAs(): string
    {
        return 'LiveStreamCommentSent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'             => $this->commentId,
            'live_stream_id' => $this->liveStreamId,
            'user_id'        => $this->userId,
            'user_name'      => $this->userName,
            'user_avatar'    => $this->userAvatar,
            'message'        => $this->message,
            'created_at'     => $this->createdAt,
        ];
    }
}
