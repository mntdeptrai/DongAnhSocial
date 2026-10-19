<?php

namespace App\Contracts;

use App\Domain\Eatery\EateryData;
use App\Models\Eatery;
use App\Models\Review;

interface EateryServiceInterface
{
    public function getCategories();
    public function getCommunes();
    public function countEateries(?string $categorySlug = null, array $filters = []): int;
    public function getEateries(?string $categorySlug = null, array $filters = []);
    public function getEateryBySlug(string $slug): ?Eatery;
    public function fetchEateryBySlug(?string $categorySlug, string $slug): ?Eatery;
    public function create(EateryData|array $data, string $categorySlug): Eatery;
    public function update($id, EateryData|array $data, string $categorySlug, ?string $currentImagePath = null): ?Eatery;
    public function delete(string $categorySlug, $id): bool;
    public function storeReview(?string $categorySlug, $eateryId, array $data): Review;
    public function deleteReview($id): bool;
    public function replyReview($id, string $reply): ?Review;

    /**
     * Prepare full view presentation data for eatery detail page (Schema.org, checkins, posts, doctors, etc.)
     */
    public function getEateryDetailViewData(string $slug, ?int $currentUserId, ?string $currentSessionId): ?array;

    /**
     * Handle user review submission for an eatery with media upload.
     */
    public function submitEateryReview(int $eateryId, array $data, array $mediaFiles = []): Review;
}
