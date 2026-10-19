<?php

namespace App\Contracts;

use App\Models\Cart;
use App\Models\User;

interface CartServiceInterface
{
    /**
     * Resolve existing or new cart for user or guest session, auto-merging guest items upon login.
     */
    public function resolveCart(?User $user, ?string $sessionId): Cart;

    /**
     * Format cart response payload with eager-loaded products and totals.
     */
    public function formatCartResponse(Cart $cart): array;

    /**
     * Add dish or OCOP product to cart.
     *
     * @return array{cart: Cart, added_new: bool}
     */
    public function addItem(Cart $cart, ?int $dishId, ?int $ocopProductId, int $quantity = 1): array;

    /**
     * Update cart item quantity, removing if quantity <= 0.
     */
    public function updateItemQuantity(Cart $cart, int|string $itemId, int $quantity): Cart;

    /**
     * Remove single item from cart.
     */
    public function removeItem(Cart $cart, int|string $itemId): Cart;

    /**
     * Clear all items from cart.
     */
    public function clearCart(Cart $cart): Cart;
}
