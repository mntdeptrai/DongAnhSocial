<?php

namespace App\Domain\Social;

use Illuminate\Http\Request;

class MessageData
{
    public function __construct(
        public int $sender_id,
        public int $receiver_id,
        public ?string $message,
        public ?int $food_tour_id = null,
        public ?string $media_path = null,
        public ?string $media_type = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            sender_id: (int) auth()->id(),
            receiver_id: (int) $request->input('receiver_id'),
            message: $request->input('message'),
            food_tour_id: $request->filled('food_tour_id') ? (int) $request->input('food_tour_id') : null,
            media_path: $request->input('media_path'),
            media_type: $request->input('media_type')
        );
    }
}
