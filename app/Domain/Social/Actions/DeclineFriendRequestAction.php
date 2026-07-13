<?php

namespace App\Domain\Social\Actions;

use App\Models\Friendship;

class DeclineFriendRequestAction
{
    public function execute(int $friendshipId, int $authUserId): void
    {
        $friendship = Friendship::where('id', $friendshipId)
            ->where(function($q) use ($authUserId) {
                $q->where('user_id', $authUserId)
                  ->orWhere('friend_id', $authUserId);
            })->firstOrFail();

        $friendship->delete();
    }
}
