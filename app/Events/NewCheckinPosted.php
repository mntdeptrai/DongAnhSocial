<?php

namespace App\Events;

use App\Models\Checkin;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event phát khi có check-in mới được đăng.
 * Dùng ShouldBroadcastNow để broadcast ngay, không qua Queue.
 */
class NewCheckinPosted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Checkin $checkin)
    {
        // Eager load các quan hệ cần thiết để broadcastWith() không gây N+1
        $this->checkin->loadMissing(['user', 'eatery.category', 'eatery.commune']);
    }

    /**
     * Channel public — bất kỳ ai đang xem trang /checkin đều nhận được
     */
    public function broadcastOn(): Channel
    {
        return new Channel('checkin-feed');
    }

    /**
     * Tên event phía client lắng nghe
     */
    public function broadcastAs(): string
    {
        return 'NewCheckinPosted';
    }

    /**
     * Dữ liệu gửi kèm theo event — đủ để render card HTML phía frontend
     */
    public function broadcastWith(): array
    {
        $checkin = $this->checkin;
        $eatery  = $checkin->eatery;

        return [
            'id'           => $checkin->id,
            'user_id'      => $checkin->user_id,
            'display_name' => $checkin->display_name,
            'avatar_char'  => $checkin->user
                ? mb_substr($checkin->user->name, 0, 1, 'UTF-8')
                : '👤',
            'role'         => $checkin->user?->role ?? 'guest',
            'rating'       => (int) $checkin->rating,
            'comment'      => $checkin->comment,
            'image_path'   => $checkin->image_path,
            'created_at_human'  => $checkin->created_at->diffForHumans(),
            'created_at_format' => $checkin->created_at->format('d/m/Y H:i'),
            'eatery' => $eatery ? [
                'name'     => $eatery->name,
                'slug'     => $eatery->slug,
                'category' => $eatery->category?->name,
                'commune'  => $eatery->commune?->name,
            ] : null,
        ];
    }
}
