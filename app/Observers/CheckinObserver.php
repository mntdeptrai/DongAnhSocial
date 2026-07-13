<?php

namespace App\Observers;

use App\Events\NewCheckinPosted;
use App\Models\Checkin;
use Illuminate\Support\Facades\Log;

class CheckinObserver
{
    public function created(Checkin $checkin): void
    {
        Log::info("Một check-in mới vừa được tạo cho địa điểm ID [{$checkin->eatery_id}] bởi User ID [{$checkin->user_id}] / Guest [{$checkin->guest_name}].");

        try {
            // Broadcast real-time đến tất cả client đang xem trang /checkin
            broadcast(new NewCheckinPosted($checkin));
        } catch (\Exception $e) {
            Log::error("Không thể broadcast check-in real-time: " . $e->getMessage());
        }
    }
}
