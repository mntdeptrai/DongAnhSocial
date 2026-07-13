<?php

namespace App\Events;

use App\Models\Friendship;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendRequestAccepted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Friendship $friendship)
    {
        $this->friendship->loadMissing(['sender', 'receiver']);
    }

    public function broadcastOn(): Channel
    {
        // Gửi ngược lại cho người gửi lời mời ban đầu (để họ thêm vào danh sách bạn bè)
        return new PrivateChannel('chat.' . $this->friendship->user_id);
    }

    public function broadcastAs(): string
    {
        return 'FriendRequestAccepted';
    }

    public function broadcastWith(): array
    {
        $avatarUrl = null;
        if ($this->friendship->receiver->avatar && str_starts_with($this->friendship->receiver->avatar, 'avatars/')) {
            $avatarUrl = rtrim(env('R2_PUBLIC_URL', ''), '/') . '/' . $this->friendship->receiver->avatar;
        }

        return [
            'id' => $this->friendship->id,
            'friendship' => [
                'id' => $this->friendship->id,
                'user_id' => $this->friendship->user_id,
                'friend_id' => $this->friendship->friend_id,
                'status' => $this->friendship->status,
            ],
            'friend' => [
                'id' => $this->friendship->receiver->id,
                'name' => $this->friendship->receiver->name,
                'avatar' => $this->friendship->receiver->avatar ?? '👤',
                'avatar_url' => $avatarUrl,
                'is_online' => true,
            ]
        ];
    }
}
