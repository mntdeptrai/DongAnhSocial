<?php

namespace App\Domain\Social;

use Illuminate\Http\Request;

class FriendshipData
{
    public function __construct(
        public int $user_id,
        public int $friend_id
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            user_id: (int) auth()->id(),
            friend_id: (int) $request->input('friend_id')
        );
    }
}
