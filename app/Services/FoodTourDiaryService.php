<?php

namespace App\Services;

use App\Domain\FoodTour\FoodTourDiaryData;
use App\Models\FoodTourDiary;

class FoodTourDiaryService
{
    public function createDiary(FoodTourDiaryData $data, bool $shareToCommunity): FoodTourDiary
    {
        return app(FoodTourService::class)->createDiary($data, $shareToCommunity);
    }
}
