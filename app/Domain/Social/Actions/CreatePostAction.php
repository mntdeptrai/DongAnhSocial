<?php

namespace App\Domain\Social\Actions;

use App\Models\Checkin;
use App\Events\NewCheckinPosted;

class CreatePostAction
{
    public function execute(array $data, ?string $imagePath = null): Checkin
    {
        $checkin = Checkin::create([
            'user_id'      => $data['user_id'] ?? null,
            'display_name' => $data['name'] ?? 'Thành viên Đông Anh',
            'comment'      => $data['content'] ?? $data['comment'] ?? '',
            'rating'       => $data['rating'] ?? 5,
            'eatery_id'    => $data['eatery_id'] ?? null,
            'image_path'   => $imagePath ?? $data['image_path'] ?? null,
            'status'       => 'approved',
        ]);

        // Broadcast Realtime Event qua Laravel Reverb WebSocket
        NewCheckinPosted::dispatch($checkin);

        return $checkin;
    }
}
