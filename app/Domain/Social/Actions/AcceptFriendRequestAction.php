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

        try {
            $sender = \App\Models\User::find($friendship->user_id);
            if ($sender && !empty($sender->fcm_token)) {
                $acceptor = \App\Models\User::find($authUserId);
                $acceptorName = $acceptor ? $acceptor->name : 'Một người bạn';
                \App\Services\FcmService::sendNotification(
                    $sender->fcm_token,
                    '🎉 Đã chấp nhận lời mời kết bạn',
                    "{$acceptorName} đã chấp nhận lời mời kết bạn của bạn. Hãy gửi lời chào ngay!",
                    [
                        'type' => 'friend_accepted',
                        'friend_id' => (string)$authUserId,
                    ]
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('FCM Notification error on AcceptFriendRequestAction: ' . $e->getMessage());
        }

        return $friendship;
    }
}
