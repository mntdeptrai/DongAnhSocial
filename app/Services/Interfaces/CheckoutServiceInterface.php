<?php

namespace App\Services\Interfaces;

use App\Models\User;
use Illuminate\Http\Request;

interface CheckoutServiceInterface
{
    /**
     * Get checkout data including cart items, subtotal, and distinct markets/stalls.
     */
    public function getCheckoutData(User $user, ?string $selectedItemsQuery): array;

    /**
     * Create orders from checked-out cart items.
     */
    public function createOrders(User $user, array $validatedData, ?string $selectedItemsParam): array;

    /**
     * Resolve order ID from code or numeric ID.
     */
    public function resolveOrderId($codeOrId): int;

    /**
     * Get standardized order code string.
     */
    public function getOrderCode(int $id): string;

    /**
     * Get single order for payment page.
     */
    public function getOrderForPayment($code, ?int $userId);

    /**
     * Process simulated or gateway payment.
     */
    public function processPayment($code, ?int $userId, bool $simulateSuccess);

    /**
     * Get order(s) for success page.
     */
    public function getOrdersForSuccess(?string $code, ?string $idsParam, ?int $userId): array;

    /**
     * Get formatted orders list and stats for API.
     */
    public function getApiOrdersList(int $userId, Request $request): array;

    /**
     * Get formatted order details for API.
     */
    public function getApiOrderDetails(int $userId, $codeOrId): array;

    /**
     * Confirm receipt of order by customer.
     */
    public function confirmReceived(int $userId, $codeOrId): array;

    /**
     * Request return/refund for an order.
     */
    public function returnOrder(int $userId, $codeOrId, string $reason): array;

    /**
     * Cancel order by customer.
     */
    public function cancelOrder(int $userId, $codeOrId, string $reason): array;

    /**
     * Reorder items from a past order into active cart.
     */
    public function reorder(int $userId, $codeOrId): array;

    /**
     * Submit review for an order.
     */
    public function submitReview(User $user, $codeOrId, array $data, $mediaFiles): array;
}
