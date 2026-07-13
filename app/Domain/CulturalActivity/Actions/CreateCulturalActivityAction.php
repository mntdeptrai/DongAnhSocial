<?php

namespace App\Domain\CulturalActivity\Actions;

use App\Domain\CulturalActivity\CulturalActivityData;
use App\Models\CulturalActivity;

class CreateCulturalActivityAction
{
    public function execute(CulturalActivityData $data, ?string $imagePath): CulturalActivity
    {
        $activity = new CulturalActivity();
        $activity->fill([
            'eatery_id' => $data->eatery_id,
            'name' => $data->name,
            'type' => $data->type,
            'price' => $data->price,
            'unit' => $data->unit,
            'discount_note' => $data->discount_note,
            'description' => $data->description,
            'image_path' => $imagePath,
        ]);
        $activity->save();
        return $activity;
    }
}
