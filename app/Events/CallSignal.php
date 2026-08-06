<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $callId,
        public int $targetUserId,
        public string $signalData  // JSON string (SDP answer hoặc ICE candidate)
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('call.' . $this->targetUserId);
    }

    public function broadcastAs(): string
    {
        return 'CallSignal';
    }

    public function broadcastWith(): array
    {
        return [
            'call_id'     => $this->callId,
            'signal_data' => $this->signalData,
        ];
    }
}
