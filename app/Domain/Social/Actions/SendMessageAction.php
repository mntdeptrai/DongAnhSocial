<?php

namespace App\Domain\Social\Actions;

use App\Domain\Social\MessageData;
use App\Models\Message;
use App\Models\Friendship;
use App\Events\MessageSent;
use Exception;

class SendMessageAction
{
    public function execute(MessageData $data): Message
    {
        // Xác minh họ thực sự là bạn bè
        $isFriend = Friendship::where('status', 'accepted')
            ->where(function($q) use ($data) {
                $q->where(function($sub) use ($data) {
                    $sub->where('user_id', $data->sender_id)->where('friend_id', $data->receiver_id);
                })->orWhere(function($sub) use ($data) {
                    $sub->where('user_id', $data->receiver_id)->where('friend_id', $data->sender_id);
                });
            })->exists();

        if (!$isFriend) {
            throw new Exception('Bạn chỉ có thể nhắn tin với người đã kết bạn.');
        }

        $message = Message::create([
            'sender_id' => $data->sender_id,
            'receiver_id' => $data->receiver_id,
            'message' => $data->message ?? '',
            'food_tour_id' => $data->food_tour_id,
            'media_path' => $data->media_path,
            'media_type' => $data->media_type,
            'is_read' => false,
        ]);

        // Phát sự kiện broadcast qua Laravel Reverb
        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }
}
