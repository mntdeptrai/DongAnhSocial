<?php

namespace App\Services;

use App\Domain\FoodTour\FoodTourDiaryData;
use App\Domain\FoodTour\Actions\CreateFoodTourDiaryAction;
use App\Helpers\R2Helper;
use App\Models\FoodTourDiary;
use App\Models\FoodTour;
use App\Services\EateryApiService;
use Illuminate\Support\Facades\DB;

class FoodTourDiaryService
{
    public function __construct(
        protected CreateFoodTourDiaryAction $createAction
    ) {}

    public function createDiary(FoodTourDiaryData $data, bool $shareToCommunity): FoodTourDiary
    {
        return DB::transaction(function() use ($data, $shareToCommunity) {
            // Upload base64 diary main image if exists
            $imagePath = null;
            if ($data->image) {
                $base64 = $data->image;
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $decoded = base64_decode(substr($base64, strpos($base64, ',') + 1));
                    $ext     = strtolower($type[1]);
                    if ($decoded !== false && in_array($ext, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                        $imagePath = R2Helper::uploadRaw($decoded, $ext, 'diaries');
                    }
                }
            }

            // Upload base64 stop reviews images and save reviews
            $processedStopReviews = $data->stop_reviews;
            foreach ($processedStopReviews as $index => &$review) {
                if (!empty($review['image']) && preg_match('/^data:image\/(\w+);base64,/', $review['image'], $type)) {
                    $decoded = base64_decode(substr($review['image'], strpos($review['image'], ',') + 1));
                    $ext     = strtolower($type[1]);
                    if ($decoded !== false && in_array($ext, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                        $review['image_path'] = R2Helper::uploadRaw($decoded, $ext, 'diaries');
                        unset($review['image']); // don't store heavy base64 inside db JSON!
                    }
                }

                // Save to global eatery Review table
                if (!empty($review['eatery_id'])) {
                    $user = auth()->user();
                    $userName = $user ? $user->name : 'Thực khách Food Tour';
                    
                    $eatery = \App\Models\Eatery::with('category:id,slug')->find($review['eatery_id']);
                    if ($eatery) {
                        $mediaFiles = [];
                        if (!empty($review['image_path'])) {
                            $mediaFiles[] = [
                                'path' => $review['image_path'],
                                'type' => 'image'
                            ];
                        }
                        
                        EateryApiService::storeReview($eatery->category->slug, $eatery->id, [
                            'user_name' => $userName,
                            'rating' => $review['rating'] ?? null,
                            'comment' => $review['comment'] ?? '',
                            'media_files' => $mediaFiles
                        ]);
                    }
                }
            }

            // Create diary entry
            $diary = $this->createAction->execute($data, $imagePath, $processedStopReviews);

            // Update draft AI tour state
            $tour = FoodTour::find($data->food_tour_id);
            if ($tour && $tour->is_ai_generated && $tour->status === 'draft') {
                $updateData = [
                    'status' => 'saved',
                    'user_id' => $tour->user_id ?: (auth()->id() ?: $data->user_id)
                ];
                if ($shareToCommunity) {
                    $updateData['shared_at'] = now();
                    $updateData['expires_at'] = now()->addHours(72);
                }
                $tour->update($updateData);
            }

            return $diary;
        });
    }
}
