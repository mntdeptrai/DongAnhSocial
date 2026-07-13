<?php

namespace App\Domain\OcopProduct;

use Illuminate\Http\Request;

class OcopProductData
{
    public function __construct(
        public int $eatery_id,
        public string $name,
        public ?float $price,
        public ?string $description,
        public ?string $star_rating,
        public ?string $heritage_year,
        public ?string $story,
        public ?string $artisans,
        public ?string $fun_fact,
        public ?string $audio_narrative,
        public ?array $ingredients,
        public ?array $timeline,
        public mixed $image = null,
        public ?string $image_url = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        $ingredients = null;
        if ($request->filled('ingredients_raw')) {
            $ingredients = array_values(array_filter(array_map('trim', explode("\n", $request->input('ingredients_raw')))));
        }

        $timeline = null;
        if ($request->filled('timeline_raw')) {
            $lines = explode("\n", $request->input('timeline_raw'));
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $parts = explode('|', $line, 2);
                if (count($parts) === 2) {
                    $timeline[] = [
                        'year' => trim($parts[0]),
                        'event' => trim($parts[1])
                    ];
                } else {
                    $timeline[] = [
                        'year' => 'Mốc thời gian',
                        'event' => trim($line)
                    ];
                }
            }
        }

        return new self(
            eatery_id: (int) $request->input('eatery_id'),
            name: $request->input('name'),
            price: $request->filled('price') ? (float) $request->input('price') : null,
            description: $request->input('description'),
            star_rating: $request->input('star_rating'),
            heritage_year: $request->input('heritage_year'),
            story: $request->input('story'),
            artisans: $request->input('artisans'),
            fun_fact: $request->input('fun_fact'),
            audio_narrative: $request->input('audio_narrative'),
            ingredients: $ingredients,
            timeline: $timeline,
            image: $request->file('image'),
            image_url: $request->input('image_url')
        );
    }
}
