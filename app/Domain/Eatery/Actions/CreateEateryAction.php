<?php

namespace App\Domain\Eatery\Actions;

use App\Domain\Eatery\EateryData;
use App\Models\Eatery;
use App\Services\EateryApiService;

class CreateEateryAction
{
    public function execute(EateryData $data, string $categorySlug, ?string $imagePath): Eatery
    {
        return EateryApiService::createEatery($categorySlug, [
            'user_id' => $data->user_id,
            'name' => $data->name,
            'category_id' => $data->category_id,
            'commune_id' => $data->commune_id,
            'address' => $data->address,
            'phone' => $data->phone,
            'opening_hours' => $data->opening_hours,
            'latitude' => $data->latitude,
            'longitude' => $data->longitude,
            'price_range' => $data->price_range ?: (in_array($categorySlug, ['smart-education-map', 'hanh-trinh-di-san', 'discover-dong-anh-community-culture-hub']) ? null : '30.000 - 100.000'),
            'image_path' => $imagePath,
            'is_featured' => $data->is_featured,
            'description' => $data->description,
            'rating' => 5.0,
            'status' => 'active',
            'heritage_year' => $data->heritage_year,
            'story' => $data->story,
            'artisans' => $data->artisans,
            'fun_fact' => $data->fun_fact,
            'audio_narrative' => $data->audio_narrative,
            'ocop_stars' => $data->ocop_stars,
            'ingredients' => $data->ingredients,
            'timeline' => $data->timeline,
        ]);
    }
}
