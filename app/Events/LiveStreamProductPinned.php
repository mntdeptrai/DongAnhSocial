<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveStreamProductPinned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $liveStreamId,
        public ?array $productData = null
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('live-stream.' . $this->liveStreamId);
    }

    public function broadcastAs(): string
    {
        return 'LiveStreamProductPinned';
    }

    public function broadcastWith(): array
    {
        return [
            'live_stream_id' => $this->liveStreamId,
            'product'        => $this->productData,
        ];
    }
}
