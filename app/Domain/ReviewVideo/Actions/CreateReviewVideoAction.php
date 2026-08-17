<?php

namespace App\Domain\ReviewVideo\Actions;

use App\Domain\ReviewVideo\ReviewVideoData;
use App\Services\EateryApiService;

class CreateReviewVideoAction
{
    public function execute(ReviewVideoData $data, string $videoUrl, string $videoType, string $status)
    {
        return EateryApiService::storeVideo([
            'eatery_id' => $data->eatery_id,
            'user_id' => $data->user_id,
            'title' => $data->title,
            'video_url' => $videoUrl,
            'video_type' => $videoType,
            'thumbnail_path' => '/images/ocop-placeholder.png',
            'likes_count' => 0,
            'status' => $status
        ]);
    }
}
