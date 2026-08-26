<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveStreamReactionSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $liveStreamId,
        public string $reactionType, // 'heart', 'fire', 'clap', 'wow'
        public int $totalLikes
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('live-stream.' . $this->liveStreamId);
    }

    public function broadcastAs(): string
    {
        return 'LiveStreamReactionSent';
    }

    public function broadcastWith(): array
    {
        return [
            'live_stream_id' => $this->liveStreamId,
            'reaction_type'  => $this->reactionType,
            'total_likes'    => $this->totalLikes,
        ];
    }
}
