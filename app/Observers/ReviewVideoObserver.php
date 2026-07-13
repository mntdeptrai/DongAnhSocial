<?php

namespace App\Observers;

use App\Models\ReviewVideo;
use Illuminate\Support\Facades\Log;

class ReviewVideoObserver
{
    public function created(ReviewVideo $video): void
    {
        Log::info("Review video mới '{$video->title}' (ID: {$video->id}) đã được gửi cho địa điểm ID [{$video->eatery_id}], trạng thái: [{$video->status}].");
    }

    public function updated(ReviewVideo $video): void
    {
        if ($video->isDirty('status')) {
            Log::info("Trạng thái của review video ID [{$video->id}] thay đổi từ [{$video->getOriginal('status')}] sang [{$video->status}].");
        }
    }

    public function deleted(ReviewVideo $video): void
    {
        Log::info("Review video '{$video->title}' (ID: {$video->id}) đã bị gỡ.");
    }
}
