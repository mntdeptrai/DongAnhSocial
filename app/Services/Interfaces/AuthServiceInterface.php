<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Resolve destination URL after login based on user role and permissions.
     */
    public function getLoginRedirectUrl(?User $user): ?string;

    /**
     * Authenticate user credentials.
     */
    public function attemptLogin(string $login, string $password): array;

    /**
     * Register new user account with OTP verification.
     */
    public function register(array $data): User;

    /**
     * Generate and send OTP for new registration.
     */
    public function sendRegisterOtp(string $email): array;

    /**
     * Logout and invalidate user session.
     */
    public function logout(): void;

    /**
     * Get profile data for profile view.
     */
    public function getProfileData(?string $identifier, ?int $currentUserId): ?array;

    /**
     * Update user profile and synchronize linked businesses/schools.
     */
    public function updateProfile(int $userId, array $data): User;

    /**
     * Permanently delete user account.
     */
    public function deleteAccount(int $userId, string $keyword): bool;

    /**
     * Update avatar and sync with photos gallery.
     */
    public function updateAvatar(User $user, $avatarFile): string;

    /**
     * Update cover photo and sync with photos gallery.
     */
    public function updateCover(User $user, $coverFile): string;

    /**
     * Change user password with OTP verification.
     */
    public function changePassword(User $user, string $otp, string $newPassword): array;

    /**
     * Send password change OTP to logged in user.
     */
    public function sendOtp(User $user): array;

    /**
     * Send OTP for forgot password request.
     */
    public function sendForgotPasswordOtp(string $email): array;

    /**
     * Reset password using OTP.
     */
    public function resetPassword(string $email, string $otp, string $newPassword): array;

    /**
     * Heartbeat user last active timestamp.
     */
    public function heartbeat(?int $userId): void;
}
