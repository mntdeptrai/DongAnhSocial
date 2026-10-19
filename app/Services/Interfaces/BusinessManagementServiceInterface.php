<?php

namespace App\Services\Interfaces;

interface BusinessManagementServiceInterface
{
    /**
     * Verify access right for Hộ kinh doanh & Doanh nghiệp
     */
    public function verifyHkdAccess(): void;

    /**
     * Get business context for logged in user
     */
    public function getBusinessContext(): array;

    /**
     * Switch active business
     */
    public function switchBusiness($hkdId): void;

    /**
     * Get dashboard metrics and analytics data
     */
    public function getDashboardData(): array;

    /**
     * Get business profile data
     */
    public function getProfileData(): array;

    /**
     * Update business profile and story info
     */
    public function updateProfile(array $data, $imageFile): void;

    /**
     * Get paginated products for the current business
     */
    public function getProductsPaginated(int $perPage = 15): array;

    /**
     * Get product for edit form
     */
    public function getProductForEdit($id): ?array;

    /**
     * Get product for detail show view
     */
    public function getProductDetails($id): ?array;

    /**
     * Store new business product
     */
    public function storeProduct(array $data, $imageFile): void;

    /**
     * Update business product
     */
    public function updateProduct($id, array $data, $imageFile): void;

    /**
     * Delete business product
     */
    public function deleteProduct($id): void;

    /**
     * Get paginated orders list
     */
    public function getOrdersPaginated(?string $status, int $perPage = 15): array;

    /**
     * Get order details
     */
    public function getOrderDetails($id): ?array;

    /**
     * Update order status
     */
    public function updateOrderStatus($id, string $status): bool;

    /**
     * Get reports and financial statistics data
     */
    public function getReportsData(): array;

    /**
     * Get QR and VietQR generation data
     */
    public function getQrData(): array;

    /**
     * Get chat context
     */
    public function getChatContext(): array;
}
