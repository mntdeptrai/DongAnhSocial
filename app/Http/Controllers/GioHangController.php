<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Dish;
use App\Models\OcopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GioHangController extends Controller
{
    private function resolveCart(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::user()->id]);
        }
        $sessionId = session()->getId();
        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    private function formatCartResponse(Cart $cart)
    {
        $cart->load('items');

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
                // Generate a deterministic colored placeholder based on item id
                $colorIndex = $item->id % count($foodColors);
                $emojiIndex = $item->id % count($foodEmojis);
                $color = ltrim($foodColors[$colorIndex], '#');
                $emoji = $foodEmojis[$emojiIndex];
                $name = urlencode(mb_substr($item->product_name, 0, 10));
                $imageUrl = "https://placehold.co/80x80/{$color}/ffffff?text={$emoji}";
            }

            return [
                'id'          => $item->id,
                'dish_id'     => $item->dish_id,
                'ocop_product_id' => $item->ocop_product_id,
                'name'        => $item->product_name,
                'price'       => (int)$item->product_price,
                'image'       => $imageUrl,
                'quantity'    => (int)$item->quantity,
                'total'       => (int)$item->thanh_tien,
                'eatery_id'   => $eatery ? $eatery->id : 0,
                'eatery_name' => $eatery ? $eatery->name : 'Khác',
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data'    => $items,
            'count'   => (int)$cart->tong_so_luong,
            'total'   => (int)$cart->tong_tien,
        ]);
    }

    public function index()
    {
        $cart = $this->resolveCart();
        return $this->formatCartResponse($cart);
    }

    public function store(Request $request)
    {
        $request->validate([
            'dish_id' => 'nullable|integer',
            'ocop_product_id' => 'nullable|integer',
            'quantity' => 'nullable|integer|min:1',
        ]);

        if (!$request->dish_id && !$request->ocop_product_id) {
            return response()->json(['success' => false, 'message' => 'Yêu cầu mã món ăn hoặc sản phẩm OCOP'], 422);
        }

        // Validate item existence
        if ($request->dish_id) {
            $dish = Dish::on('mysql')->find($request->dish_id);
            if (!$dish) {
                return response()->json(['success' => false, 'message' => 'Món ăn không tồn tại'], 404);
            }
        } else {
            $ocop = OcopProduct::on('mysql_market')->find($request->ocop_product_id);
            if (!$ocop) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm OCOP không tồn tại'], 404);
            }
        }

        $cart = $this->resolveCart();

        $item = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'dish_id' => $request->dish_id,
            'ocop_product_id' => $request->ocop_product_id,
        ]);

        $added_new = !$item->exists;
        $item->quantity = ($item->exists ? $item->quantity : 0) + ($request->quantity ?? 1);
        $item->save();

        $res = $this->formatCartResponse($cart);
        $data = $res->getData(true);
        $data['added_new'] = $added_new;

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:0']);

        $cart = $this->resolveCart();

        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $id)
            ->first();

        if (!$item) {
            return $this->formatCartResponse($cart);
        }

        if ($request->quantity == 0) {
            $item->delete();
        } else {
            $item->quantity = $request->quantity;
            $item->save();
        }

        return $this->formatCartResponse($cart);
    }

    public function destroy($id)
    {
        $cart = $this->resolveCart();

        CartItem::where('cart_id', $cart->id)
            ->where('id', $id)
            ->delete();

        return $this->formatCartResponse($cart);
    }

    public function clear()
    {
        $cart = $this->resolveCart();
        CartItem::where('cart_id', $cart->id)->delete();

        return $this->formatCartResponse($cart);
    }
}
