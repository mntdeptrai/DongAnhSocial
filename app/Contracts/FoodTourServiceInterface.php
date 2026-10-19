<?php

namespace App\Contracts;

use App\Models\FoodTour;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface FoodTourServiceInterface
{
    /**
     * Get list of food tours with optional mood filter and community tours.
     */
    public function getFoodTours(?string $mood = null);

    /**
     * Get tour detail by slug with stops, missing eateries hydration, and diaries.
     */
    public function getFoodTourBySlug(string $slug);

    /**
     * Generate AI food tour with Gemini.
     */
    public function generateAITourWithGemini(int $budgetLimit, string $mood, ?User $user): array;

    /**
     * Get active eateries dropdown for tour form.
     */
    public function getEateriesDropdown(): Collection;

    /**
     * Store new custom food tour.
     */
    public function createCustomTour(array $validatedData, ?User $user, ?\Illuminate\Http\UploadedFile $thumbnailFile): FoodTour;

    /**
     * Find tour for editing with permission verification.
     */
    public function getTourForManage(string $slug, ?User $user): FoodTour;

    /**
     * Update custom food tour with stops.
     */
    public function updateCustomTour(FoodTour $tour, array $validatedData, ?\Illuminate\Http\UploadedFile $thumbnailFile): FoodTour;

    /**
     * Delete custom food tour.
     */
    public function deleteCustomTour(FoodTour $tour): bool;

    /**
     * Toggle community sharing for custom food tour.
     */
    public function toggleShare(FoodTour $tour): string;

    /**
     * Calculate Haversine distance between 2 coordinates.
     */
    public function getDistance(float $lat1, float $lon1, float $lat2, float $lon2): float;

    /**
     * Automatically determine mood category based on metadata and stops.
     */
    public function determineMood(string $name, string $description, string $budget, array $stops = []): string;
}
