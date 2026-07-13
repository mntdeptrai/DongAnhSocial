<?php

namespace App\Domain\Checkin;

use Illuminate\Http\Request;

class CheckinData
{
    public function __construct(
        public int $eatery_id,
        public int $rating,
        public ?string $comment,
        public ?string $guest_name,
        public ?int $user_id,
        public mixed $image = null,
        public ?string $eatery_slug = null,
        public ?string $image_base64 = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            eatery_id: (int) $request->input('eatery_id'),
            rating: (int) $request->input('rating'),
            comment: $request->input('comment'),
            guest_name: $request->input('guest_name'),
            user_id: session('user_id') ? (int) session('user_id') : null,
            image: $request->file('image'),
            eatery_slug: $request->input('eatery_slug'),
            image_base64: $request->input('image_base64')
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            eatery_id: (int) ($data['eatery_id'] ?? 0),
            rating: (int) ($data['rating'] ?? 0),
            comment: $data['comment'] ?? null,
            guest_name: $data['guest_name'] ?? null,
            user_id: isset($data['user_id']) ? (int) $data['user_id'] : null,
            image: $data['image'] ?? null,
            eatery_slug: $data['eatery_slug'] ?? null,
            image_base64: $data['image_base64'] ?? null
        );
    }
}
