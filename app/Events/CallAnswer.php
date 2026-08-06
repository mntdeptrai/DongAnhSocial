<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallAnswer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $callId,
        public int $callerId,
        public int $receiverId,
        public array $sdpAnswer
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('call.' . $this->callerId);
    }

    public function broadcastAs(): string
    {
        return 'CallAnswer';
    }

    public function broadcastWith(): array
    {
        return [
            'call_id' => $this->callId,
            'caller_id' => $this->callerId,
            'receiver_id' => $this->receiverId,
            'sdp_answer' => $this->sdpAnswer,
        ];
    }
}
