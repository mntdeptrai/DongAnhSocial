<?php

namespace App\Domain\Wellness;

use Illuminate\Http\Request;

class WellnessServiceData
{
    public function __construct(
        public int $eatery_id,
        public string $name,
        public ?float $price,
        public ?string $description,
        public ?string $duration,
        public mixed $image = null,
        public ?string $image_url = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            eatery_id: (int) $request->input('eatery_id'),
            name: $request->input('name'),
            price: $request->filled('price') ? (float) $request->input('price') : null,
            description: $request->input('description'),
            duration: $request->input('duration'),
            image: $request->file('image'),
            image_url: $request->input('image_url')
        );
    }
}
