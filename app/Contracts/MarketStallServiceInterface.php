<?php

namespace App\Contracts;

use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Collection;

interface MarketStallServiceInterface
{
    /**
     * Resolve stall data and view parameters for stall detail page.
     *
     * @param string $marketSlug
     * @param string $stallSlug
     * @param User|null $authUser
     * @return array
     */
    public function getStallDetailData(string $marketSlug, string $stallSlug, ?User $authUser): array;

    /**
     * Store review for a stall from market and stall slug.
     *
     * @param string $marketSlug
     * @param string $stallSlug
     * @param array $data
     * @param User|null $authUser
     * @param array $mediaFiles
     * @return Review
     */
    public function storeReviewFromSlug(string $marketSlug, string $stallSlug, array $data, ?User $authUser, array $mediaFiles = []): Review;

    /**
     * Get formatted reviews for a stall in an eatery.
     *
     * @param int|string $eateryId
     * @param string $stallName
     * @return Collection
     */
    public function getStallReviews(int|string $eateryId, string $stallName): Collection;

    /**
     * Store a simple stall review by eatery ID and stall name.
     *
     * @param int|string $eateryId
     * @param string $stallName
     * @param array $data
     * @param User|null $authUser
     * @return Review
     */
    public function storeStallReview(int|string $eateryId, string $stallName, array $data, ?User $authUser): Review;
}
