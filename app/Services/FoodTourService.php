<?php

namespace App\Services;

use App\Domain\FoodTour\FoodTourDiaryData;
use App\Helpers\R2Helper;
use App\Models\Eatery;
use App\Models\FoodTour;
use App\Models\FoodTourDiary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FoodTourService
{
    /**
     * Lấy danh sách Food Tour theo tâm trạng kèm các tour do cộng đồng chia sẻ.
     */
    public function getFoodTours(?string $mood = null)
    {
        $query = FoodTour::public()->with(['stops.eatery', 'diaries.user'])->withCount('diaries');
        if ($mood) {
            $query->where('mood', $mood);
        }
        $tours = $query->get();

        $communityTours = FoodTour::community()
            ->with(['stops.eatery', 'diaries.user'])
            ->withCount('diaries')
            ->orderBy('shared_at', 'desc')
            ->get();

        return [
            'tours' => $tours,
            'community_tours' => $communityTours
        ];
    }

    /**
     * Lấy chi tiết Food Tour theo slug kèm các trạm dừng và nhật ký.
     */
    public function getFoodTourBySlug(string $slug)
    {
        $tour = FoodTour::where('slug', $slug)
            ->with(['stops' => function($q) {
                $q->orderBy('stop_order');
            }, 'stops.eatery.category', 'stops.eatery.commune'])
            ->first();

        if (!$tour) return null;

        $diaries = FoodTourDiary::where('food_tour_id', $tour->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'tour' => $tour,
            'diaries' => $diaries
        ];
    }

    /**
     * Gọi API tạo lộ trình Food Tour bằng Gemini AI.
     */
    public function generateAITour($budget, $mood)
    {
        $baseUrl = rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/api/v1';
        $response = Http::post($baseUrl . "/food-tours/generate-ai", [
            'budget' => $budget,
            'mood' => $mood
        ]);
        return $response->json();
    }

    /**
     * Lưu nhật ký Food Tour từ form/API.
     */
    public function storeFoodTourDiary($id, array $data): ?FoodTourDiary
    {
        $tour = FoodTour::find($id);
        if (!$tour) return null;

        $diary = new FoodTourDiary();
        $diary->food_tour_id = $id;
        $diary->user_id = $data['user_id'] ?? (auth()->id() ?? session('user_id'));
        $diary->caption = $data['caption'] ?? '';
        $diary->rating = $data['rating'] ?? 5;
        $diary->image_path = $data['image_path'] ?? null;
        $diary->stop_reviews = $data['stop_reviews'] ?? [];
        $diary->save();

        return $diary;
    }

    /**
     * Tạo nhật ký Food Tour nâng cao (xử lý base64 ảnh, lưu review quán, chia sẻ cộng đồng).
     */
    public function createDiary(FoodTourDiaryData $data, bool $shareToCommunity = false): FoodTourDiary
    {
        return DB::transaction(function() use ($data, $shareToCommunity) {
            $imagePath = null;
            if ($data->image) {
                $base64 = $data->image;
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $decoded = base64_decode(substr($base64, strpos($base64, ',') + 1));
                    $ext = strtolower($type[1]);
                    if ($decoded !== false && in_array($ext, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                        $imagePath = R2Helper::uploadRaw($decoded, $ext, 'diaries');
                    }
                }
            }

            $processedStopReviews = $data->stop_reviews;
            foreach ($processedStopReviews as &$review) {
                if (!empty($review['image']) && preg_match('/^data:image\/(\w+);base64,/', $review['image'], $type)) {
                    $decoded = base64_decode(substr($review['image'], strpos($review['image'], ',') + 1));
                    $ext = strtolower($type[1]);
                    if ($decoded !== false && in_array($ext, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                        $review['image_path'] = R2Helper::uploadRaw($decoded, $ext, 'diaries');
                        unset($review['image']);
                    }
                }

                if (!empty($review['eatery_id'])) {
                    $user = auth()->user();
                    $userName = $user ? $user->name : 'Thực khách Food Tour';
                    
                    $eatery = Eatery::with('category:id,slug')->find($review['eatery_id']);
                    if ($eatery) {
                        $mediaFiles = [];
                        if (!empty($review['image_path'])) {
                            $mediaFiles[] = [
                                'path' => $review['image_path'],
                                'type' => 'image'
                            ];
                        }
                        
                        app(EateryService::class)->storeReview($eatery->category->slug ?? 'dong-anh-food-map', $eatery->id, [
                            'user_name' => $userName,
                            'rating' => $review['rating'] ?? null,
                            'comment' => $review['comment'] ?? '',
                            'media_files' => $mediaFiles
                        ]);
                    }
                }
            }

            $diary = FoodTourDiary::create([
                'food_tour_id' => $data->food_tour_id,
                'user_id' => $data->user_id,
                'caption' => $data->caption,
                'rating' => $data->rating,
                'image_path' => $imagePath,
                'stop_reviews' => $processedStopReviews,
            ]);

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
