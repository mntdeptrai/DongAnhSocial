<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Http\Request;

interface HealthStationServiceInterface
{
    /**
     * Resolve health station context for authenticated user.
     */
    public function getHealthStationContext(?User $user): array;

    /**
     * Get dashboard view data for the health station.
     */
    public function getDashboardData(array $context): array;

    /**
     * Get profile view data including photos and storytelling.
     */
    public function getProfileData(array $context): array;

    /**
     * Update health station profile, coordinates, photos, and storytelling data.
     */
    public function updateProfile(array $context, array $validated, Request $request): bool;

    /**
     * Parse Google Maps URL / share link / coordinate string into [latitude, longitude].
     */
    public function parseGoogleMapsUrl(?string $url): array;

    /**
     * Get services list for the health station.
     */
    public function getServices(array $context);

    /**
     * Store new health service.
     */
    public function storeService(array $context, array $data): bool;

    /**
     * Update existing health service.
     */
    public function updateService(array $context, int|string $id, array $data): bool;

    /**
     * Delete health service.
     */
    public function deleteService(array $context, int|string $id): bool;

    /**
     * Get doctors list for the health station.
     */
    public function getDoctors(array $context);

    /**
     * Store new doctor / medical staff.
     */
    public function storeDoctor(array $context, array $data): bool;

    /**
     * Update existing doctor / medical staff.
     */
    public function updateDoctor(array $context, int|string $id, array $data): bool;

    /**
     * Delete doctor / medical staff.
     */
    public function deleteDoctor(array $context, int|string $id): bool;
}
