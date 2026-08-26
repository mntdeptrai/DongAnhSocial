<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveStreamProductsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $liveStreamId;
    public array $products;
    public ?array $pinnedProduct;

    public function __construct(int $liveStreamId, array $products, ?array $pinnedProduct = null)
    {
        $this->liveStreamId  = $liveStreamId;
        $this->products      = $products;
        $this->pinnedProduct = $pinnedProduct;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('live-stream.' . $this->liveStreamId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'products.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'live_stream_id' => $this->liveStreamId,
            'products'       => $this->products,
            'pinned_product' => $this->pinnedProduct,
        ];
    }
}
