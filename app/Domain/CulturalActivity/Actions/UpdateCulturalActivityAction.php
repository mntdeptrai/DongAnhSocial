<?php

namespace App\Domain\CulturalActivity\Actions;

use App\Domain\CulturalActivity\CulturalActivityData;
use App\Models\CulturalActivity;

class UpdateCulturalActivityAction
{
    public function execute(CulturalActivity $activity, CulturalActivityData $data, ?string $imagePath): CulturalActivity
    {
        $activity->update([
            'name' => $data->name,
            'type' => $data->type,
            'price' => $data->price,
            'unit' => $data->unit,
            'discount_note' => $data->discount_note,
            'description' => $data->description,
            'image_path' => $imagePath,
        ]);
        return $activity;
    }
}
