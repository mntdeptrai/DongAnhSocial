<?php

namespace App\Services;

use App\Contracts\CartServiceInterface;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Dish;
use App\Models\OcopProduct;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService implements CartServiceInterface
{
    /**
     * Resolve existing or new cart for user or guest session, auto-merging guest items upon login.
     */
    public function resolveCart(?User $user, ?string $sessionId): Cart
    {
        if ($user) {
            $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

            // Tự động gộp giỏ hàng tạm (Guest Cart) khi người dùng đăng nhập tài khoản
            if ($sessionId) {
                $guestCart = Cart::where('session_id', $sessionId)->first();
                if ($guestCart && $guestCart->id !== $userCart->id) {
                    foreach ($guestCart->items as $gItem) {
                        $uItem = CartItem::firstOrNew([
                            'cart_id'         => $userCart->id,
                            'dish_id'         => $gItem->dish_id,
                            'ocop_product_id' => $gItem->ocop_product_id,
                        ]);
                        $uItem->quantity = ($uItem->exists ? $uItem->quantity : 0) + $gItem->quantity;
                        $uItem->save();
                    }
                    $guestCart->items()->delete();
                    $guestCart->delete();
                }
            }

            return $userCart;
        }

        $sid = $sessionId ?: session()->getId();
        return Cart::firstOrCreate(['session_id' => $sid]);
    }

    /**
     * Format cart response payload with eager-loaded products and totals.
     */
    public function formatCartResponse(Cart $cart): array
    {
        $cart->load('items');

        $dishIds = $cart->items->pluck('dish_id')->filter()->unique()->toArray();
        $ocopIds = $cart->items->pluck('ocop_product_id')->filter()->unique()->toArray();

        $dishes = !empty($dishIds) ? Dish::on('mysql')->with('eatery')->whereIn('id', $dishIds)->get()->keyBy('id') : collect();
        $ocopProducts = !empty($ocopIds) ? OcopProduct::on('mysql_market')->with('eatery')->whereIn('id', $ocopIds)->get()->keyBy('id') : collect();

        foreach ($cart->items as $item) {
            $product = null;
            if ($item->dish_id) {
                $product = $dishes->get($item->dish_id);
            } elseif ($item->ocop_product_id) {
                $product = $ocopProducts->get($item->ocop_product_id);
            }
            $item->setRelation('product', $product);
        }

        $foodColors = ['#FF6B6B','#FF8E53','#FFA726','#66BB6A','#26C6DA','#42A5F5','#AB47BC','#EF5350'];
        $foodEmojis = ['🍜','🍲','🥘','🍛','🥗','🍤','🥩','🍱','🍝','🍣'];

        $items = $cart->items->map(function ($item) use ($foodColors, $foodEmojis) {
            $product = $item->product;
            $eatery = $product ? $product->eatery : null;

            // Resolve image URL
            $imgRaw = $item->product_image;
            if ($imgRaw) {
                $imageUrl = asset($imgRaw);
            } else {
                $colorIndex = $item->id % count($foodColors);
                $emojiIndex = $item->id % count($foodEmojis);
                $color = ltrim($foodColors[$colorIndex], '#');
                $emoji = $foodEmojis[$emojiIndex];
                $imageUrl = "https://placehold.co/80x80/{$color}/ffffff?text={$emoji}";
            }

            return [
                'id'              => $item->id,
                'dish_id'         => $item->dish_id,
                'ocop_product_id' => $item->ocop_product_id,
                'name'            => $item->product_name,
                'price'           => (int)$item->product_price,
                'image'           => $imageUrl,
                'quantity'        => (int)$item->quantity,
                'total'           => (int)$item->thanh_tien,
                'eatery_id'       => $eatery ? $eatery->id : 0,
                'eatery_name'     => $eatery ? $eatery->name : 'Khác',
                'stall_name'      => ($product && isset($product->stall_name)) ? $product->stall_name : null,
            ];
        })->values();

        return [
            'success' => true,
            'data'    => $items,
            'count'   => (int)$cart->tong_so_luong,
            'total'   => (int)$cart->tong_tien,
        ];
    }

    /**
     * Add dish or OCOP product to cart.
     */
    public function addItem(Cart $cart, ?int $dishId, ?int $ocopProductId, int $quantity = 1): array
    {
        $item = CartItem::firstOrNew([
            'cart_id'         => $cart->id,
            'dish_id'         => $dishId,
            'ocop_product_id' => $ocopProductId,
        ]);

        $addedNew = !$item->exists;
        $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $item->save();

        return [
            'cart'      => $cart,
            'added_new' => $addedNew,
        ];
    }

    /**
     * Update cart item quantity, removing if quantity <= 0.
     */
    public function updateItemQuantity(Cart $cart, int|string $itemId, int $quantity): Cart
    {
        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->first();

        if ($item) {
            if ($quantity <= 0) {
                $item->delete();
            } else {
                $item->quantity = $quantity;
                $item->save();
            }
        }

        return $cart;
    }

    /**
     * Remove single item from cart.
     */
    public function removeItem(Cart $cart, int|string $itemId): Cart
    {
        CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->delete();

        return $cart;
    }

    /**
     * Clear all items from cart.
     */
    public function clearCart(Cart $cart): Cart
    {
        CartItem::where('cart_id', $cart->id)->delete();
        return $cart;
    }
}
