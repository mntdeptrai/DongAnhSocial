<?php

namespace App\Domain\ReviewVideo;

use Illuminate\Http\Request;

class ReviewVideoData
{
    public function __construct(
        public int $eatery_id,
        public int $user_id,
        public string $title,
        public ?string $video_url,
        public mixed $video_file = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            eatery_id: (int) $request->input('eatery_id'),
            user_id: (int) session('user_id'),
            title: $request->input('title'),
            video_url: $request->input('video_url'),
            video_file: $request->file('video_file')
        );
    }
}
