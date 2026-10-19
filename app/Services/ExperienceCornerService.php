<?php

namespace App\Services;

use App\Contracts\ExperienceCornerServiceInterface;
use App\Models\ExperienceFeedback;
use App\Models\FoodTour;
use App\Models\User;
use App\Services\CulturalActivityService;
use Illuminate\Support\Collection;

class ExperienceCornerService implements ExperienceCornerServiceInterface
{
    public function __construct(
        protected CulturalActivityService $culturalActivityService
    ) {}

    /**
     * Get cooking & cultural craft tours.
     */
    public function getCookingTours(): Collection
    {
        return FoodTour::public()
            ->where('mood', 'cooking')
            ->with(['stops.eatery', 'diaries.user'])
            ->withCount('diaries')
            ->get();
    }

    /**
     * Get all cultural & heritage activities from database.
     */
    public function getCulturalActivities(): Collection
    {
        return $this->culturalActivityService->getAllCulturalActivities();
    }

    /**
     * Get community experience feedbacks from database.
     */
    public function getCommunityFeedbacks(): Collection
    {
        return ExperienceFeedback::where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function ($fb) {
                return (object) [
                    'id'            => 'fb_' . $fb->id,
                    'author_name'   => $fb->author_name,
                    'activity_name' => $fb->activity_name,
                    'rating'        => (int) $fb->rating,
                    'verified'      => (bool) $fb->is_verified,
                    'helpful_count' => (int) $fb->helpful_count,
                    'comment'       => $fb->comment,
                    'image_url'     => $fb->image_url,
                    'created_at'    => $fb->created_at ? $fb->created_at->diffForHumans() : 'Vừa xong',
                ];
            });
    }

    /**
     * Store new visitor experience feedback.
     */
    public function storeFeedback(array $data, ?User $user): ExperienceFeedback
    {
        return ExperienceFeedback::create([
            'user_id'       => $user ? $user->id : null,
            'author_name'   => strip_tags($data['author_name']),
            'activity_name' => strip_tags($data['activity_name']),
            'rating'        => (int) $data['rating'],
            'comment'       => strip_tags($data['comment']),
            'image_url'     => $data['image_url'] ?? null,
            'is_verified'   => true,
            'helpful_count' => 1,
            'status'        => 'approved',
        ]);
    }
}
