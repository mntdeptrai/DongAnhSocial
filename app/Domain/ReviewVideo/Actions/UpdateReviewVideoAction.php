<?php

namespace App\Domain\ReviewVideo\Actions;

use App\Domain\ReviewVideo\ReviewVideoData;
use App\Services\EateryApiService;

class UpdateReviewVideoAction
{
    public function execute($id, ReviewVideoData $data, string $videoUrl, string $videoType, string $status)
    {
        return EateryApiService::updateVideo($id, [
            'eatery_id' => $data->eatery_id,
            'title' => $data->title,
            'video_url' => $videoUrl,
            'video_type' => $videoType,
            'status' => $status
        ]);
    }
}
