<?php

namespace App\Services;

use App\Domain\Dish\DishData;
use App\Domain\Dish\Actions\CreateDishAction;
use App\Domain\Dish\Actions\UpdateDishAction;
use App\Helpers\R2Helper;
use App\Services\EateryApiService;

class DishService
{
    public function __construct(
        protected CreateDishAction $createAction,
        protected UpdateDishAction $updateAction
    ) {}

    public function create(DishData $data)
    {
        $imagePath = $this->resolveImagePath($data->image, $data->image_url);
        return $this->createAction->execute($data, $imagePath);
    }

    public function update($id, DishData $data)
    {
        $imagePath = $this->resolveImagePath($data->image, $data->image_url);
        return $this->updateAction->execute($id, $data, $imagePath);
    }

    public function toggleSignature($id)
    {
        return EateryApiService::toggleSignatureDish($id);
    }

    public function delete($id): bool
    {
        return EateryApiService::deleteDish($id);
    }

    protected function resolveImagePath($imageFile, ?string $imageUrl): ?string
    {
        if ($imageFile) {
            return R2Helper::upload($imageFile, 'dishes');
        }

        if ($imageUrl) {
            if (preg_match('/(?:drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=))([a-zA-Z0-9_-]{25,50})/i', $imageUrl, $matches)) {
                return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            }
            return $imageUrl;
        }

        return null;
    }
}
