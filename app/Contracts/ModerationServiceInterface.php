<?php

namespace App\Contracts;

interface ModerationServiceInterface
{
    /**
     * Create and record a moderation report.
     *
     * @param array $data
     * @return array
     */
    public static function createReport(array $data): array;

    /**
     * Check if a given user is currently banned.
     *
     * @param int $userId
     * @return bool
     */
    public static function isUserBanned(int $userId): bool;

    /**
     * Get human-readable remaining ban duration text.
     *
     * @param int $userId
     * @return string|null
     */
    public static function getBanRemainingText(int $userId): ?string;
}
