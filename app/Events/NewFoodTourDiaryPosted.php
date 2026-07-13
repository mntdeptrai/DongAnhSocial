<?php

namespace App\Events;

use App\Models\FoodTourDiary;
use App\Models\FoodTour;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event phát khi có nhật ký Food Tour mới được hoàn thành.
 * Dùng ShouldBroadcastNow để phát ngay.
 */
class NewFoodTourDiaryPosted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly FoodTourDiary $diary)
    {
        // Eager load các quan hệ cần thiết
        $this->diary->loadMissing(['user', 'foodTour.stops', 'comments.user']);
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
        return 'NewFoodTourDiaryPosted';
    }

    /**
     * Dữ liệu gửi kèm theo event để render card Nhật ký
     */
    public function broadcastWith(): array
    {
        $diary = $this->diary;
        
        // Lấy danh sách địa điểm để lấy tên/slug chặng dừng
        $eateries = \App\Services\EateryApiService::getEateries();
        $eateriesMap = $eateries->keyBy('id');

        $stopReviews = [];
        if (!empty($diary->stop_reviews) && $diary->foodTour) {
            foreach ($diary->stop_reviews as $stopIdx => $stopRev) {
                $eateryId = $stopRev['eatery_id'] ?? null;
                if (!$eateryId) {
                    $tourStop = $diary->foodTour->stops->firstWhere('stop_order', $stopIdx + 1);
                    $eateryId = $tourStop?->eatery_id;
                }
                $eatery = $eateryId ? $eateriesMap->get($eateryId) : null;
                $stopReviews[] = [
                    'stop_index' => $stopIdx + 1,
                    'eatery' => $eatery ? [
                        'name' => $eatery->name,
                        'slug' => $eatery->slug,
                        'category' => $eatery->category?->name,
                        'commune' => $eatery->commune?->name,
                    ] : null,
                    'rating' => isset($stopRev['rating']) ? (int) $stopRev['rating'] : null,
                    'comment' => $stopRev['comment'] ?? '',
                    'image_path' => $stopRev['image_path'] ?? null,
                ];
            }
        }

        return [
            'id' => $diary->id,
            'user_id' => $diary->user_id,
            'display_name' => $diary->user->name ?? 'Thực khách Đông Anh',
            'avatar_char' => $diary->user ? mb_substr($diary->user->name, 0, 1, 'UTF-8') : '👤',
            'role' => $diary->user?->role ?? 'guest',
            'rating' => $diary->rating ? (int) $diary->rating : null,
            'comment' => $diary->comment,
            'image_path' => $diary->image_path,
            'created_at_human' => $diary->created_at->diffForHumans(),
            'created_at_format' => $diary->created_at->format('d/m/Y H:i'),
            'created_at_ts' => $diary->created_at->timestamp,
            'foodTour' => $diary->foodTour ? [
                'name' => $diary->foodTour->name,
                'slug' => $diary->foodTour->slug,
            ] : null,
            'stop_reviews' => $stopReviews,
        ];
    }
}
