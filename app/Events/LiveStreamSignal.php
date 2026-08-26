<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveStreamSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $liveStreamId,
        public string $senderSessionId,
        public string $targetSessionId,
        public string $signalType, // 'viewer_join', 'host_offer', 'viewer_answer', 'ice_candidate'
        public ?string $signalData = null
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('live-stream.' . $this->liveStreamId);
    }

    public function broadcastAs(): string
    {
        return 'LiveStreamSignal';
    }

    public function broadcastWith(): array
    {
        return [
            'live_stream_id'    => $this->liveStreamId,
            'sender_session_id' => $this->senderSessionId,
            'target_session_id' => $this->targetSessionId,
            'signal_type'       => $this->signalType,
            'signal_data'       => $this->signalData,
        ];
    }
}
