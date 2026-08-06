<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallHangup implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $callId,
        public int $targetUserId,
        public string $reason // 'ended' | 'rejected' | 'missed' | 'busy'
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('call.' . $this->targetUserId);
    }

    public function broadcastAs(): string
    {
        return 'CallHangup';
    }

    public function broadcastWith(): array
    {
        return [
            'call_id' => $this->callId,
            'reason' => $this->reason,
        ];
    }
}
