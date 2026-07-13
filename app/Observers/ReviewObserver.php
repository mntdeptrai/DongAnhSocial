<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\Eatery;
use Illuminate\Support\Facades\Log;

class ReviewObserver
{
    public function created(Review $review): void
    {
        $connection = $review->getConnectionName();
        $eateryId = $review->eatery_id;

        Log::info("Một đánh giá mới vừa được gửi cho địa điểm ID [{$eateryId}] với điểm số [{$review->rating}] trên connection [{$connection}].");

        $eatery = Eatery::on($connection)->find($eateryId);
        if ($eatery) {
            $avgRating = Review::on($connection)->where('eatery_id', $eateryId)->avg('rating');
            if ($avgRating !== null) {
                $eatery->update([
                    'rating' => round($avgRating, 2)
                ]);
                Log::info("Tự động cập nhật điểm đánh giá trung bình của cơ sở '{$eatery->name}' thành [{$eatery->rating}].");
            }
        }
    }

    public function deleted(Review $review): void
    {
        $connection = $review->getConnectionName();
        $eateryId = $review->eatery_id;

        Log::info("Đánh giá ID [{$review->id}] cho địa điểm ID [{$eateryId}] đã bị xóa khỏi connection [{$connection}].");

        $eatery = Eatery::on($connection)->find($eateryId);
        if ($eatery) {
            $avgRating = Review::on($connection)->where('eatery_id', $eateryId)->avg('rating');
            $eatery->update([
                'rating' => $avgRating ? round($avgRating, 2) : 5.00
            ]);
            Log::info("Tự động cập nhật điểm đánh giá trung bình của cơ sở '{$eatery->name}' sau khi xóa review thành [{$eatery->rating}].");
        }
    }
}
