<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface SchoolManagementServiceInterface
{
    /**
     * Check access rights for principal or admin.
     */
    public function verifyPrincipalOrAdmin(): void;

    /**
     * Find school by ID or slug across databases.
     */
    public function findSchool($idOrSlug);

    /**
     * Resolve redirect school for principal.
     */
    public function getDashboardRedirectSchool();

    /**
     * Get schools list managed by user/admin.
     */
    public function getSchoolsList(User $user);

    /**
     * Get school and merged components data for edit view.
     */
    public function getSchoolForEdit($id, User $user): ?array;

    /**
     * Update school details and merged component campuses.
     */
    public function updateSchool($id, User $user, array $data, $imageFile, ?string $imageUrl);

    /**
     * Get full dashboard overview data for a school.
     */
    public function getDashboardData($id, User $user): array;

    /**
     * Store new education post / program.
     */
    public function storePost(User $user, array $data, $imageFile, $imagesFiles, $videosFiles, ?string $imageUrls): array;

    /**
     * Update education post / program.
     */
    public function updatePost($id, User $user, array $data, $imageFile, $imagesFiles): array;

    /**
     * Delete education post / program.
     */
    public function destroyPost($id, User $user): bool;

    /**
     * Store photo to school gallery.
     */
    public function storePhoto($eateryId, User $user, $imageFile, ?string $caption): void;

    /**
     * Delete photo from school gallery.
     */
    public function destroyPhoto($id, User $user): void;

    /**
     * Store school review / activity video.
     */
    public function storeVideo($eateryId, User $user, array $data, $thumbnailFile): void;

    /**
     * Delete school video.
     */
    public function destroyVideo($id, User $user): void;
}
