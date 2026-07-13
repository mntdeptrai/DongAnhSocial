<?php

namespace App\Domain\OcopProduct\Actions;

use App\Domain\OcopProduct\OcopProductData;
use App\Models\OcopProduct;

class CreateOcopProductAction
{
    public function execute(OcopProductData $data, ?string $imagePath): OcopProduct
    {
        $product = new OcopProduct();
        $product->fill([
            'eatery_id' => $data->eatery_id,
            'name' => $data->name,
            'price' => $data->price,
            'description' => $data->description,
            'image_path' => $imagePath,
            'star_rating' => $data->star_rating,
            'heritage_year' => $data->heritage_year,
            'story' => $data->story,
            'artisans' => $data->artisans,
            'fun_fact' => $data->fun_fact,
            'audio_narrative' => $data->audio_narrative,
            'ingredients' => $data->ingredients,
            'timeline' => $data->timeline,
        ]);
        $product->save();
        return $product;
    }
}
