<?php

namespace App\Domain\Social\Actions;

use App\Models\Friendship;
use App\Events\FriendRequestAccepted;

class AcceptFriendRequestAction
{
    public function execute(int $friendshipId, int $authUserId): Friendship
    {
        $friendship = Friendship::where('id', $friendshipId)
            ->where('friend_id', $authUserId)
            ->firstOrFail();

        $friendship->update(['status' => 'accepted']);

        // Phát sóng sự kiện chấp nhận kết bạn
        broadcast(new FriendRequestAccepted($friendship))->toOthers();

        return $friendship;
    }
}
