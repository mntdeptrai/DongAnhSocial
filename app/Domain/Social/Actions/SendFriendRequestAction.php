<?php

namespace App\Domain\Social\Actions;

use App\Domain\Social\FriendshipData;
use App\Models\Friendship;
use App\Events\FriendRequestSent;
use Exception;

class SendFriendRequestAction
{
    public function execute(FriendshipData $data): Friendship
    {
        if ($data->user_id === $data->friend_id) {
            throw new Exception('Bạn không thể tự kết bạn với chính mình.');
        }

        // Kiểm tra xem đã có lời mời hay quan hệ kết bạn chưa
        $exists = Friendship::where(function($q) use ($data) {
            $q->where('user_id', $data->user_id)->where('friend_id', $data->friend_id);
        })->orWhere(function($q) use ($data) {
            $q->where('user_id', $data->friend_id)->where('friend_id', $data->user_id);
        })->exists();

        if ($exists) {
            throw new Exception('Đã tồn tại lời mời kết bạn hoặc hai người đã là bạn bè.');
        }

        $friendship = Friendship::create([
            'user_id' => $data->user_id,
            'friend_id' => $data->friend_id,
            'status' => 'pending',
        ]);

        // Phát sóng sự kiện gửi lời mời kết bạn
        broadcast(new FriendRequestSent($friendship))->toOthers();

        return $friendship;
    }
}
