<?php

namespace App\Domain\FoodTour;

use Illuminate\Http\Request;

class FoodTourDiaryData
{
    public function __construct(
        public int $food_tour_id,
        public int $user_id,
        public ?int $rating,
        public ?string $comment,
        public ?string $image, // base64 string
        public array $completed_stops,
        public array $stop_reviews
    ) {}

    public static function fromRequest(Request $request, int $foodTourId): self
    {
        return new self(
            food_tour_id: $foodTourId,
            user_id: (int) auth()->id(),
            rating: $request->input('rating') ? (int) $request->input('rating') : null,
            comment: $request->input('comment'),
            image: $request->input('image'),
            completed_stops: $request->input('completed_stops', []),
            stop_reviews: $request->input('stop_reviews', [])
        );
    }
}
