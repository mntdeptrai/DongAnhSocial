<?php

namespace App\Domain\CulturalActivity;

use Illuminate\Http\Request;

class CulturalActivityData
{
    public function __construct(
        public int $eatery_id,
        public string $name,
        public ?string $type,
        public ?float $price,
        public ?string $unit,
        public ?string $discount_note,
        public ?string $description,
        public mixed $image = null,
        public ?string $image_url = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            eatery_id: (int) $request->input('eatery_id'),
            name: $request->input('name'),
            type: $request->input('type'),
            price: $request->filled('price') ? (float) $request->input('price') : null,
            unit: $request->input('unit'),
            discount_note: $request->input('discount_note'),
            description: $request->input('description'),
            image: $request->file('image'),
            image_url: $request->input('image_url')
        );
    }
}
