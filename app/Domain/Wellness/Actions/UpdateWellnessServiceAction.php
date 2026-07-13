<?php

namespace App\Domain\Wellness\Actions;

use App\Domain\Wellness\WellnessServiceData;
use App\Models\WellnessService;

class UpdateWellnessServiceAction
{
    public function execute(WellnessService $service, WellnessServiceData $data, ?string $imagePath): WellnessService
    {
        $service->update([
            'name' => $data->name,
            'price' => $data->price,
            'description' => $data->description,
            'image_path' => $imagePath,
            'duration' => $data->duration,
        ]);
        return $service;
    }
}
