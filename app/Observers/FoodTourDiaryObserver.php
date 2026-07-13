<?php

namespace App\Observers;

use App\Models\FoodTourDiary;
use App\Models\FoodTour;
use Illuminate\Support\Facades\Log;

class FoodTourDiaryObserver
{
    public function created(FoodTourDiary $diary): void
    {
        Log::info("Nhật ký hành trình mới vừa được hoàn thành cho Food Tour ID [{$diary->food_tour_id}] bởi User ID [{$diary->user_id}].");

        $tour = FoodTour::find($diary->food_tour_id);
        if ($tour) {
            Log::info("Độ phổ biến của Food Tour '{$tour->name}' vừa tăng lên!");
        }

        // Broadcast real-time đến tất cả client đang xem trang /checkin
        broadcast(new \App\Events\NewFoodTourDiaryPosted($diary));
    }
}
