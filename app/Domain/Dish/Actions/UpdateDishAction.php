<?php

namespace App\Domain\Dish\Actions;

use App\Domain\Dish\DishData;
use App\Services\EateryApiService;

class UpdateDishAction
{
    public function execute($id, DishData $data, ?string $imagePath)
    {
        $payload = [
            'name' => $data->name,
            'price' => $data->price,
            'description' => $data->description,
            'is_signature' => $data->is_signature,
        ];
        
        if ($imagePath !== null) {
            $payload['image_path'] = $imagePath;
        }

        return EateryApiService::updateDish($id, $payload);
    }
}
