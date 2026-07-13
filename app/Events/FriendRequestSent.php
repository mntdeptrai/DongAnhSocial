<?php

namespace App\Events;

use App\Models\Friendship;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendRequestSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Friendship $friendship)
    {
        $this->friendship->loadMissing(['sender', 'receiver']);
    }

    public function broadcastOn(): Channel
    {
        // Gửi tới kênh private của người nhận lời mời
        return new PrivateChannel('chat.' . $this->friendship->friend_id);
    }

    public function broadcastAs(): string
    {
        return 'FriendRequestSent';
    }

    public function broadcastWith(): array
    {
        $avatarUrl = null;
        if ($this->friendship->sender->avatar && str_starts_with($this->friendship->sender->avatar, 'avatars/')) {
            $avatarUrl = rtrim(env('R2_PUBLIC_URL', ''), '/') . '/' . $this->friendship->sender->avatar;
        }

        return [
            'id' => $this->friendship->id,
            'user_id' => $this->friendship->user_id,
            'friend_id' => $this->friendship->friend_id,
            'status' => $this->friendship->status,
            'created_at' => $this->friendship->created_at,
            'sender' => [
                'id' => $this->friendship->sender->id,
                'name' => $this->friendship->sender->name,
                'avatar' => $this->friendship->sender->avatar ?? '👤',
                'avatar_url' => $avatarUrl,
            ]
        ];
    }
}
