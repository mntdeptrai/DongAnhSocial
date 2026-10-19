<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ManagerOrderServiceInterface
{
    /**
     * Get market context for the current manager/admin user.
     *
     * @param User|null $user
     * @param string|null $sessionRole
     * @param int|null $tenantId
     * @return array{eatery: mixed, eateryId: int|null, marketName: string}
     */
    public function getManagerMarketContext(?User $user, ?string $sessionRole, ?int $tenantId): array;

    /**
     * Paginate orders for the specified market or all markets.
     */
    public function getOrdersPaginated(?int $eateryId, int $perPage = 25): LengthAwarePaginator;

    /**
     * Get single order details with items, verifying manager market ownership.
     */
    public function getOrderDetails(int|string $orderId, ?int $managerEateryId, ?string $userRole): ?object;

    /**
     * Get recent orders as JSON collection for polling.
     */
    public function getOrdersJson(?int $eateryId, int $limit = 50): Collection;

    /**
     * Update order status with security verification.
     */
    public function updateOrderStatus(int|string $orderId, string $status, ?int $managerEateryId, ?string $userRole): bool;
}
