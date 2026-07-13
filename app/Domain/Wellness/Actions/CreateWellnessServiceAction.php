<?php

namespace App\Domain\Wellness\Actions;

use App\Domain\Wellness\WellnessServiceData;
use App\Models\WellnessService;

class CreateWellnessServiceAction
{
    public function execute(WellnessServiceData $data, ?string $imagePath): WellnessService
    {
        $service = new WellnessService();
        $service->fill([
            'eatery_id' => $data->eatery_id,
            'name' => $data->name,
            'price' => $data->price,
            'description' => $data->description,
            'image_path' => $imagePath,
            'duration' => $data->duration,
        ]);
        $service->save();
        return $service;
    }
}
