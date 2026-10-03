<?php

namespace App\Services;

use App\Domain\Dish\DishData;
use App\Helpers\R2Helper;
use App\Models\Dish;
use App\Models\Eatery;

class DishService
{
    public function create(DishData|array $data): ?Dish
    {
        if ($data instanceof DishData) {
            $imagePath = $this->resolveImagePath($data->image, $data->image_url);
            $attributes = [
                'eatery_id' => $data->eatery_id,
                'name' => $data->name,
                'price' => $data->price,
                'description' => $data->description,
                'image_path' => $imagePath,
                'is_signature' => (bool) $data->is_signature,
            ];
        } else {
            $attributes = $data;
        }

        return $this->storeDish($attributes);
    }

    public function update($id, DishData|array $data): ?Dish
    {
        $dish = Dish::find($id);
        if (!$dish) return null;

        if ($data instanceof DishData) {
            $imagePath = $this->resolveImagePath($data->image, $data->image_url) ?? $dish->image_path;
            $attributes = [
                'name' => $data->name,
                'price' => $data->price,
                'description' => $data->description,
                'image_path' => $imagePath,
                'is_signature' => (bool) $data->is_signature,
            ];
        } else {
            $attributes = $data;
        }

        $dish->update($attributes);
        return $dish;
    }

    public function storeDish(array $data): ?Dish
    {
        $eatery = Eatery::find($data['eatery_id'] ?? null);
        if (!$eatery) return null;

        return Dish::create($data);
    }

    public function toggleSignature($id): ?Dish
    {
        $dish = Dish::find($id);
        if (!$dish) return null;

        $dish->update(['is_signature' => !$dish->is_signature]);
        return $dish;
    }

    public function toggleSignatureDish($id): ?Dish
    {
        return $this->toggleSignature($id);
    }

    public function delete($id): bool
    {
        $dish = Dish::find($id);
        if (!$dish) return false;

        return (bool) $dish->delete();
    }

    public function deleteDish($id): bool
    {
        return $this->delete($id);
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
