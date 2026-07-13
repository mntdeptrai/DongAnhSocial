<?php

namespace App\Domain\FoodTour\Actions;

use App\Domain\FoodTour\FoodTourDiaryData;
use App\Models\FoodTourDiary;

class CreateFoodTourDiaryAction
{
    public function execute(FoodTourDiaryData $data, ?string $imagePath, array $processedStopReviews): FoodTourDiary
    {
        return FoodTourDiary::create([
            'food_tour_id' => $data->food_tour_id,
            'user_id' => $data->user_id,
            'rating' => $data->rating,
            'comment' => $data->comment,
            'image_path' => $imagePath,
            'completed_stops' => $data->completed_stops,
            'stop_reviews' => $processedStopReviews,
        ]);
    }
}
