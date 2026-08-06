<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallOffer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $callId,
        public int $callerId,
        public string $callerName,
        public string $callerAvatar,
        public int $receiverId,
        public string $type, // 'audio' | 'video'
        public string $signalData // JSON string from simple-peer
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('call.' . $this->receiverId);
    }

    public function broadcastAs(): string
    {
        return 'CallOffer';
    }

    public function broadcastWith(): array
    {
        return [
            'call_id'      => $this->callId,
            'caller_id'    => $this->callerId,
            'caller_name'  => $this->callerName,
            'caller_avatar'=> $this->callerAvatar,
            'receiver_id'  => $this->receiverId,
            'type'         => $this->type,
            'signal_data'  => $this->signalData,
        ];
    }
}
