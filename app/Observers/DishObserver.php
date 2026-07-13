<?php

namespace App\Observers;

use App\Models\Dish;
use Illuminate\Support\Facades\Log;

class DishObserver
{
    public function created(Dish $dish): void
    {
        Log::info("Món ăn mới '{$dish->name}' (ID: {$dish->id}) đã được thêm vào thực đơn của quán ID [{$dish->eatery_id}].");
    }

    public function updated(Dish $dish): void
    {
        Log::info("Món ăn '{$dish->name}' (ID: {$dish->id}) đã được cập nhật thông tin.");
    }

    public function deleted(Dish $dish): void
    {
        Log::info("Món ăn '{$dish->name}' (ID: {$dish->id}) đã được gỡ khỏi thực đơn.");
    }
}
