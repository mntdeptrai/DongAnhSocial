<?php

namespace App\Domain\Eatery;

use Illuminate\Http\Request;

class EateryData
{
    public function __construct(
        public string $name,
        public int $category_id,
        public int $commune_id,
        public string $address,
        public ?string $phone,
        public ?string $opening_hours,
        public float $latitude,
        public float $longitude,
        public ?string $price_range,
        public ?string $description,
        public bool $is_featured,
        public ?int $user_id,
        public mixed $image = null,
        public ?string $image_url = null,
        public ?string $heritage_year = null,
        public ?string $story = null,
        public ?string $artisans = null,
        public ?string $fun_fact = null,
        public ?string $audio_narrative = null,
        public ?int $ocop_stars = null,
        public ?array $ingredients = null,
        public ?array $timeline = null
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
            name: $request->input('name'),
            category_id: (int) $request->input('category_id'),
            commune_id: (int) $request->input('commune_id'),
            address: $request->input('address'),
            phone: $request->input('phone'),
            opening_hours: $request->input('opening_hours'),
            latitude: (float) $request->input('latitude'),
            longitude: (float) $request->input('longitude'),
            price_range: $request->input('price_range'),
            description: $request->input('description'),
            is_featured: session('user_role') === 'admin' ? $request->has('is_featured') : false,
            user_id: session('user_role') === 'seller' ? (int) session('user_id') : null,
            image: $request->file('image'),
            image_url: $request->input('image_url'),
            heritage_year: $request->input('heritage_year'),
            story: $request->input('story'),
            artisans: $request->input('artisans'),
            fun_fact: $request->input('fun_fact'),
            audio_narrative: $request->input('audio_narrative'),
            ocop_stars: $request->filled('ocop_stars') ? (int) $request->input('ocop_stars') : null,
            ingredients: $ingredients,
            timeline: $timeline
        );
    }
}
