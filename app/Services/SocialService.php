<?php

namespace App\Services;

use App\Domain\Social\FriendshipData;
use App\Domain\Social\MessageData;
use App\Domain\Social\Actions\SendFriendRequestAction;
use App\Domain\Social\Actions\AcceptFriendRequestAction;
use App\Domain\Social\Actions\DeclineFriendRequestAction;
use App\Domain\Social\Actions\SendMessageAction;
use App\Models\Friendship;
use App\Models\Message;

class SocialService
{
    public function __construct(
        protected SendFriendRequestAction $sendRequestAction,
        protected AcceptFriendRequestAction $acceptRequestAction,
        protected DeclineFriendRequestAction $declineRequestAction,
        protected SendMessageAction $sendMessageAction
    ) {}

    public function sendFriendRequest(FriendshipData $data): Friendship
    {
        return $this->sendRequestAction->execute($data);
    }

    public function acceptFriendRequest(int $friendshipId, int $authUserId): Friendship
    {
        return $this->acceptRequestAction->execute($friendshipId, $authUserId);
    }

    public function declineFriendRequest(int $friendshipId, int $authUserId): void
    {
        $this->declineRequestAction->execute($friendshipId, $authUserId);
    }

    public function sendMessage(MessageData $data): Message
    {
        return $this->sendMessageAction->execute($data);
    }
}
