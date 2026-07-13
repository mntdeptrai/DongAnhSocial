<?php

namespace App\Domain\Dish\Actions;

use App\Domain\Dish\DishData;
use App\Services\EateryApiService;

class CreateDishAction
{
    public function execute(DishData $data, ?string $imagePath)
    {
        return EateryApiService::storeDish([
            'eatery_id' => $data->eatery_id,
            'name' => $data->name,
            'price' => $data->price,
            'description' => $data->description,
            'image_path' => $imagePath,
            'is_signature' => $data->is_signature,
        ]);
    }
}
