<?php

namespace App\Contracts;

use App\Models\ExperienceFeedback;
use App\Models\User;
use Illuminate\Support\Collection;

interface ExperienceCornerServiceInterface
{
    /**
     * Get cooking & cultural craft tours.
     */
    public function getCookingTours(): Collection;

    /**
     * Get all cultural & heritage activities from database.
     */
    public function getCulturalActivities(): Collection;

    /**
     * Get community experience feedbacks from database.
     */
    public function getCommunityFeedbacks(): Collection;

    /**
     * Store new visitor experience feedback.
     */
    public function storeFeedback(array $data, ?User $user): ExperienceFeedback;
}
