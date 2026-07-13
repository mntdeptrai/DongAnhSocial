<?php

namespace App\Domain\Room;

use Illuminate\Http\Request;

class RoomData
{
    public function __construct(
        public int $eatery_id,
        public string $name,
        public ?float $price,
        public ?string $description,
        public ?string $bed_type,
        public ?int $capacity,
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
            bed_type: $request->input('bed_type'),
            capacity: $request->filled('capacity') ? (int) $request->input('capacity') : null,
            image: $request->file('image'),
            image_url: $request->input('image_url')
        );
    }
}
