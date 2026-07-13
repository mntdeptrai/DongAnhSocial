<?php

namespace App\Domain\Dish;

use Illuminate\Http\Request;

class DishData
{
    public function __construct(
        public int $eatery_id,
        public string $name,
        public float $price,
        public ?string $description,
        public bool $is_signature,
        public mixed $image = null,
        public ?string $image_url = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            eatery_id: (int) $request->input('eatery_id'),
            name: $request->input('dish_name'),
            price: (float) $request->input('dish_price'),
            description: $request->input('dish_description'),
            is_signature: $request->has('is_signature'),
            image: $request->file('dish_image'),
            image_url: $request->input('dish_image_url')
        );
    }
}
