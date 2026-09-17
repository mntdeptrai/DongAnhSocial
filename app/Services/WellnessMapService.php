<?php

namespace App\Services;

use App\Domain\Wellness\WellnessServiceData;
use App\Helpers\R2Helper;
use App\Models\Eatery;
use App\Models\WellnessService;

class WellnessMapService
{
    public function create(WellnessServiceData|array $data): ?WellnessService
    {
        if ($data instanceof WellnessServiceData) {
            $imagePath = $this->resolveImagePath($data->image, $data->image_url);
            $attributes = [
                'eatery_id' => $data->eatery_id,
                'name' => $data->name,
                'type' => $data->type,
                'price' => $data->price,
                'duration' => $data->duration,
                'target_audience' => $data->target_audience,
                'description' => $data->description,
                'image_path' => $imagePath,
            ];
        } else {
            $attributes = $data;
        }

        return $this->storeWellnessService($attributes);
    }

    public function update($id, WellnessServiceData|array $data): ?WellnessService
    {
        $service = WellnessService::find($id);
        if (!$service) return null;

        if ($data instanceof WellnessServiceData) {
            $imagePath = $this->resolveImagePath($data->image, $data->image_url) ?? $service->image_path;
            $attributes = [
                'eatery_id' => $data->eatery_id,
                'name' => $data->name,
                'type' => $data->type,
                'price' => $data->price,
                'duration' => $data->duration,
                'target_audience' => $data->target_audience,
                'description' => $data->description,
                'image_path' => $imagePath,
            ];
        } else {
            $attributes = $data;
        }

        $service->update($attributes);
        return $service;
    }

    public function storeWellnessService(array $data): ?WellnessService
    {
        $eatery = Eatery::find($data['eatery_id'] ?? null);
        if (!$eatery) return null;

        return WellnessService::create($data);
    }

    public function updateWellnessService($id, array $data): ?WellnessService
    {
        $service = WellnessService::find($id);
        if (!$service) return null;

        $service->update($data);
        return $service;
    }

    public function delete($id): bool
    {
        $service = WellnessService::find($id);
        if (!$service) return false;

        return (bool) $service->delete();
    }

    public function deleteWellnessService($id): bool
    {
        return $this->delete($id);
    }

    protected function resolveImagePath($imageFile, ?string $imageUrl): ?string
    {
        if ($imageFile) {
            return R2Helper::upload($imageFile, 'wellness');
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
