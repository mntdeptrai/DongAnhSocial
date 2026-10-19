<?php

namespace App\Services;

use App\Helpers\R2Helper;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Dish;
use App\Models\Eatery;
use App\Models\OcopProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Services\Interfaces\CheckoutServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutService implements CheckoutServiceInterface
{
    public function getCheckoutData(User $user, ?string $selectedItemsQuery): array
    {
        $cart = Cart::with(['items'])
            ->where('user_id', $user->id)
            ->first();

        $cartItems = [];
        $subtotal = 0;

        if ($cart && $cart->items->count() > 0) {
            $selectedIds = $selectedItemsQuery ? explode(',', $selectedItemsQuery) : null;
            $itemsToCheckout = $selectedIds 
                ? $cart->items->whereIn('id', $selectedIds) 
                : $cart->items;

            $dishIds = $itemsToCheckout->pluck('dish_id')->filter()->unique()->toArray();
            $ocopIds = $itemsToCheckout->pluck('ocop_product_id')->filter()->unique()->toArray();

            $dishes = !empty($dishIds) ? Dish::on('mysql')->with('eatery')->whereIn('id', $dishIds)->get()->keyBy('id') : collect();
            $ocopProducts = !empty($ocopIds) ? OcopProduct::on('mysql_market')->with('eatery')->whereIn('id', $ocopIds)->get()->keyBy('id') : collect();

            $ocopEateryIds = $ocopProducts->pluck('eatery_id')->filter()->unique()->values()->all();
            $eateryCatSlugs = !empty($ocopEateryIds)
                ? DB::table('eateries')->join('categories', 'eateries.category_id', '=', 'categories.id')
                    ->whereIn('eateries.id', $ocopEateryIds)
                    ->pluck('categories.slug', 'eateries.id')
                : collect();

            $cartItems = $itemsToCheckout->map(function ($item) use ($dishes, $ocopProducts, $eateryCatSlugs) {
                $product = null;
                if ($item->dish_id) {
                    $product = $dishes->get($item->dish_id);
                } elseif ($item->ocop_product_id) {
                    $product = $ocopProducts->get($item->ocop_product_id);
                }
                $eatery = $product ? $product->eatery : null;

                return [
                    'id'              => $item->id,
                    'dish_id'         => $item->dish_id,
                    'ocop_product_id' => $item->ocop_product_id,
                    'name'            => $item->product_name,
                    'price'           => (float)$item->product_price,
                    'quantity'        => $item->quantity,
                    'image'           => $item->product_image ? asset($item->product_image) : asset('images/no-image.png'),
                    'total'           => (float)$item->thanh_tien,
                    'eatery_id'       => $eatery ? $eatery->id : ($product ? $product->eatery_id : null),
                    'eatery_name'     => $eatery ? $eatery->name : 'Gian hàng Đông Anh',
                    'stall_name'      => ($product && isset($product->stall_name)) ? $product->stall_name : null,
                    'category_slug'   => $item->dish_id
                        ? 'dong-anh-food-map'
                        : ($product ? ($eateryCatSlugs->get($product->eatery_id) ?? 'dong-anh-market') : 'dong-anh-market'),
                ];
            })->toArray();

            $subtotal = collect($cartItems)->sum('total');
        }

        $distinctMarkets = collect($cartItems)->pluck('eatery_name')->filter()->unique()->values()->all();
        $distinctStalls = collect($cartItems)->pluck('stall_name')->filter()->unique()->values()->all();

        return [
            'cartItems'       => $cartItems,
            'subtotal'        => $subtotal,
            'distinctMarkets' => $distinctMarkets,
            'distinctStalls'  => $distinctStalls,
        ];
    }

    public function createOrders(User $user, array $validatedData, ?string $selectedItemsParam): array
    {
        $cart = Cart::with(['items'])->where('user_id', $user->id)->first();
        if (!$cart || $cart->items->isEmpty()) {
            throw new \InvalidArgumentException('Giỏ hàng trống');
        }

        $selectedIds = $selectedItemsParam ? explode(',', $selectedItemsParam) : null;
        $itemsToCheckout = $selectedIds 
            ? $cart->items->whereIn('id', $selectedIds) 
            : $cart->items;

        if ($itemsToCheckout->isEmpty()) {
            throw new \InvalidArgumentException('Giỏ hàng trống');
        }

        return DB::transaction(function () use ($user, $validatedData, $itemsToCheckout, $cart, $selectedIds) {
            $groupedItems = $itemsToCheckout->groupBy(function ($item) {
                $eateryId = $item->product ? $item->product->eatery_id : 0;
                $slug = $item->dish_id ? 'dong-anh-food-map' : 'dong-anh-market';
                $stallName = '';
                if (!$item->dish_id && $item->product && isset($item->product->stall_name)) {
                    $stallName = $item->product->stall_name;
                }
                return $eateryId . '|' . $slug . '|' . $stallName;
            });

            $createdOrders = [];

            foreach ($groupedItems as $key => $items) {
                list($eateryId, $categorySlug, $stallName) = explode('|', $key);
                $totalAmount = $items->sum('thanh_tien');

                $rawAddress = $validatedData['address'];
                $specificAddress = $rawAddress;

                if (str_starts_with($rawAddress, '[Ghé sạp lấy đồ]')) {
                    $eateryObj = Eatery::find($eateryId);
                    $specificMarketName = $eateryObj ? $eateryObj->name : 'Chợ';
                    
                    $extraInfo = '';
                    if (preg_match('/(\(Hẹn:.*?\))/u', $rawAddress, $matchTime)) {
                        $extraInfo .= ' ' . $matchTime[1];
                    }
                    if (preg_match('/(Phương tiện:.*?\))/u', $rawAddress, $matchVehicle)) {
                        $extraInfo .= ' (' . $matchVehicle[1];
                    }

                    $specificAddress = "[Ghé sạp lấy đồ] Tại " . $specificMarketName . ($stallName ? " - " . $stallName : "") . ($extraInfo ? " " . trim($extraInfo) : "");
                }

                $order = Order::create([
                    'user_id'          => $user->id,
                    'eatery_id'        => $eateryId,
                    'category_slug'    => $categorySlug,
                    'stall_name'       => $stallName ?: null,
                    'customer_name'    => $validatedData['name'],
                    'customer_phone'   => $validatedData['phone'],
                    'shipping_address' => $specificAddress,
                    'total_amount'     => $totalAmount,
                    'payment_method'   => $validatedData['payment_method'],
                    'status'           => 'pending',
                    'notes'            => $validatedData['notes'] ?? null,
                ]);

                $orderItemsData = collect($items)->map(fn($item) => [
                    'order_id'        => $order->id,
                    'dish_id'         => $item->dish_id,
                    'ocop_product_id' => $item->ocop_product_id,
                    'name'            => $item->product_name,
                    'price'           => $item->product_price,
                    'quantity'        => $item->quantity,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ])->all();

                if (!empty($orderItemsData)) {
                    OrderItem::insert($orderItemsData);
                }

                Payment::create([
                    'order_id' => $order->id,
                    'method'   => $validatedData['payment_method'],
                    'amount'   => $totalAmount,
                    'status'   => 'pending',
                ]);

                $createdOrders[] = $order;
            }

            if ($selectedIds) {
                CartItem::whereIn('id', $selectedIds)->delete();
                $cart->load('items');
                if ($cart->items->count() === 0) {
                    $cart->delete();
                }
            } else {
                $cart->items()->delete();
                $cart->delete();
            }

            return $createdOrders;
        });
    }

    public function resolveOrderId($codeOrId): int
    {
        if (is_numeric($codeOrId)) {
            return (int) $codeOrId;
        }
        $clean = ltrim(trim($codeOrId), '#');
        if (str_starts_with(strtoupper($clean), 'ORD')) {
            $clean = preg_replace('/[^\d]/', '', $clean);
        }
        return (int) $clean;
    }

    public function getOrderCode(int $id): string
    {
        return 'ORD' . str_pad($id, 6, '0', STR_PAD_LEFT);
    }

    public function getOrderForPayment($code, ?int $userId)
    {
        $id = $this->resolveOrderId($code);
        return Order::with(['items', 'payment'])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->findOrFail($id);
    }

    public function processPayment($code, ?int $userId, bool $simulateSuccess)
    {
        $id = $this->resolveOrderId($code);
        $order = Order::with('payment')
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->findOrFail($id);

        return DB::transaction(function () use ($order, $simulateSuccess) {
            $payment = $order->payment;
            if ($payment) {
                $payment->status = $simulateSuccess ? 'success' : 'failed';
                $payment->paid_at = $simulateSuccess ? now() : null;
                $payment->save();
            }

            if ($simulateSuccess) {
                $order->status = 'confirmed';
                $order->save();
            }

            return $order;
        });
    }

    public function getOrdersForSuccess(?string $code, ?string $idsParam, ?int $userId): array
    {
        $orderIds = [];
        if ($code) {
            $orderIds[] = $this->resolveOrderId($code);
        }
        if ($idsParam) {
            $ids = explode(',', $idsParam);
            foreach ($ids as $i) {
                $orderIds[] = $this->resolveOrderId($i);
            }
        }
        
        $orderIds = array_unique(array_filter($orderIds));
        
        $orders = Order::with(['items', 'payment'])
            ->whereIn('id', $orderIds)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->get();
            
        if ($orders->isEmpty()) {
            abort(404, 'Không tìm thấy đơn hàng');
        }
        
        return [
            'order'  => $orders->first(),
            'orders' => $orders
        ];
    }

    public function getApiOrdersList(int $userId, Request $request): array
    {
        $totalCount = Order::where('user_id', $userId)->count();
        $processingCount = Order::where('user_id', $userId)
            ->whereIn('status', ['pending', 'paid', 'processing', 'shipping', 'delivering', 'confirmed', 'ready'])
            ->count();
        $completedCount = Order::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();
        $totalSpent = Order::where('user_id', $userId)
            ->where('status', 'completed')
            ->sum('total_amount');

        $query = Order::with(['items.product', 'payment', 'eatery'])
            ->where('user_id', $userId);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $searchClean = ltrim($search, '#');
            if (str_starts_with(strtoupper($searchClean), 'DA-')) {
                $searchClean = substr($searchClean, 3);
            }
            if (str_starts_with(strtoupper($searchClean), 'ORD')) {
                $searchClean = substr($searchClean, 3);
            }
            $searchId = intval($searchClean);

            $query->where(function($q) use ($search, $searchId) {
                if ($searchId > 0) {
                    $q->where('id', $searchId);
                } else {
                    $q->whereHas('items', function($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "{$search}%");
                    });
                }
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            if ($status === 'paid') {
                $query->where('status', 'paid');
            } elseif ($status === 'processing') {
                $query->whereIn('status', ['paid', 'processing']);
            } elseif ($status === 'shipping') {
                $query->whereIn('status', ['shipping', 'delivering']);
            } elseif ($status === 'returned') {
                $query->whereIn('status', ['returned', 'return_requested']);
            } else {
                $query->where('status', $status);
            }
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $formattedOrders = $orders->map(function ($order) {
            $items = $order->items->map(function ($item) {
                $product = $item->product;
                $imagePath = $product && $product->image_path ? asset($product->image_path) : null;
                if (!$imagePath && $item->ocop_product_id) {
                    $imagePath = asset('images/ocop-placeholder.png');
                }
                
                return [
                    'id'       => $item->id,
                    'name'     => $item->name,
                    'price'    => (float)$item->price,
                    'quantity' => $item->quantity,
                    'image'    => $imagePath,
                    'total'    => (float)($item->price * $item->quantity),
                ];
            });

            $subtotal = $items->sum('total');
            $discount = 0.00;
            $shipping_fee = $subtotal >= 100000 ? 0 : 15000;

            return [
                'id'                   => $order->id,
                'order_code'           => 'ORD' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
                'order_code_full'      => '#ORD' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                'created_at_formatted' => $order->created_at->format('H:i d/m/Y'),
                'status'               => $order->status,
                'status_label'         => $this->getStatusLabel($order->status),
                'status_class'         => $this->getStatusClass($order->status),
                'items'                => $items,
                'subtotal'             => $subtotal,
                'shipping_fee'         => $shipping_fee,
                'voucher_code'         => null,
                'discount'             => $discount,
                'total_amount'         => (float)$order->total_amount,
                'customer_name'        => $order->customer_name,
                'customer_phone'       => $order->customer_phone,
                'shipping_address'     => $order->shipping_address,
                'payment_method'       => $order->payment_method,
                'payment_status'       => $order->payment ? $order->payment->status : 'pending',
                'notes'                => $order->notes,
                'is_reviewed'          => (bool)$order->is_reviewed,
                'eatery_id'            => $order->eatery_id,
                'category_slug'        => $order->category_slug,
                'stall_name'           => $order->stall_name,
                'eatery_name'          => $order->eatery 
                    ? ($order->stall_name ? $order->eatery->name . ' - ' . $order->stall_name : $order->eatery->name) 
                    : ($order->stall_name ?: 'Cửa hàng'),
            ];
        });

        return [
            'data' => $formattedOrders,
            'stats' => [
                'total'      => $totalCount,
                'processing' => $processingCount,
                'completed'  => $completedCount,
                'spent'      => (float)$totalSpent
            ]
        ];
    }

    public function getApiOrderDetails(int $userId, $codeOrId): array
    {
        $id = $this->resolveOrderId($codeOrId);

        $order = Order::with(['items.product', 'payment', 'eatery'])
            ->where('user_id', $userId)
            ->findOrFail($id);

        $items = $order->items->map(function ($item) {
            $product = $item->product;
            $imagePath = $product && $product->image_path ? asset($product->image_path) : null;
            if (!$imagePath && $item->ocop_product_id) {
                $imagePath = asset('images/ocop-placeholder.png');
            }
            
            return [
                'id'       => $item->id,
                'name'     => $item->name,
                'price'    => (float)$item->price,
                'quantity' => $item->quantity,
                'image'    => $imagePath,
                'total'    => (float)($item->price * $item->quantity),
            ];
        });

        $subtotal = $items->sum('total');
        $discount = 0.00;
        $shipping_fee = $subtotal >= 100000 ? 0 : 15000;

        return [
            'id'                     => $order->id,
            'order_code'             => 'ORD' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
            'order_code_full'        => 'ORD' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'created_at_formatted'   => $order->created_at ? $order->created_at->format('H:i d/m/Y') : '',
            'confirmed_at_formatted' => $order->confirmed_at ? Carbon::parse($order->confirmed_at)->format('H:i d/m/Y') : null,
            'preparing_at_formatted' => $order->preparing_at ? Carbon::parse($order->preparing_at)->format('H:i d/m/Y') : null,
            'ready_at_formatted'     => $order->ready_at ? Carbon::parse($order->ready_at)->format('H:i d/m/Y') : null,
            'shipping_at_formatted'  => $order->shipping_at ? Carbon::parse($order->shipping_at)->format('H:i d/m/Y') : null,
            'completed_at_formatted' => $order->completed_at ? Carbon::parse($order->completed_at)->format('H:i d/m/Y') : null,
            'cancelled_at_formatted' => $order->cancelled_at ? Carbon::parse($order->cancelled_at)->format('H:i d/m/Y') : null,
            'status'                 => $order->status,
            'status_label'           => $this->getStatusLabel($order->status),
            'status_class'           => $this->getStatusClass($order->status),
            'cancel_reason'          => $order->cancel_reason,
            'cancelled_by'           => $order->cancelled_by,
            'items'                  => $items,
            'subtotal'               => $subtotal,
            'shipping_fee'           => $shipping_fee,
            'voucher_code'           => null,
            'discount'               => $discount,
            'total_amount'           => (float)$order->total_amount,
            'customer_name'          => $order->customer_name,
            'customer_phone'         => $order->customer_phone,
            'shipping_address'       => $order->shipping_address,
            'payment_method'         => $order->payment_method,
            'payment_status'         => $order->payment ? $order->payment->status : 'pending',
            'notes'                  => $order->notes,
            'is_reviewed'            => (bool)$order->is_reviewed,
            'eatery_id'              => $order->eatery_id,
            'category_slug'          => $order->category_slug,
            'stall_name'             => $order->stall_name,
            'eatery_name'            => $order->eatery 
                ? ($order->stall_name ? $order->eatery->name . ' - ' . $order->stall_name : $order->eatery->name) 
                : ($order->stall_name ?: 'Cửa hàng'),
        ];
    }

    public function confirmReceived(int $userId, $codeOrId): array
    {
        $id = $this->resolveOrderId($codeOrId);
        $order = Order::where('user_id', $userId)->findOrFail($id);

        if ($order->status === 'completed') {
            return [
                'success' => true,
                'message' => 'Đơn hàng này đã được xác nhận hoàn thành trước đó.'
            ];
        }

        if (in_array($order->status, ['cancelled', 'returned'])) {
            return [
                'success' => false,
                'message' => 'Đơn hàng đã bị hủy hoặc hoàn trả, không thể xác nhận nhận hàng.',
                'code'    => 422
            ];
        }

        DB::transaction(function () use ($order) {
            $order->status = 'completed';
            $order->completed_at = now();
            $order->save();

            if ($order->payment && $order->payment->status !== 'success') {
                $order->payment->status = 'success';
                $order->payment->paid_at = now();
                $order->payment->save();
            }
        });

        return [
            'success'    => true,
            'message'    => 'Cảm ơn bạn! Đã xác nhận nhận được hàng thành công.',
            'order_id'   => $order->id,
            'order_code' => 'ORD' . str_pad($order->id, 3, '0', STR_PAD_LEFT)
        ];
    }

    public function returnOrder(int $userId, $codeOrId, string $reason): array
    {
        $id = $this->resolveOrderId($codeOrId);
        $order = Order::where('user_id', $userId)->findOrFail($id);

        if (!in_array($order->status, ['completed', 'shipping', 'delivering'])) {
            return [
                'success' => false,
                'message' => 'Chỉ có thể yêu cầu hoàn hàng cho đơn hàng đã nhận hoặc đang giao.',
                'code'    => 422
            ];
        }

        DB::transaction(function () use ($order, $reason) {
            $order->status = 'returned';
            $order->notes = trim(($order->notes ? $order->notes . ' | ' : '') . '📌 [Lý do hoàn hàng: ' . $reason . ']');
            $order->save();
        });

        return [
            'success' => true,
            'message' => 'Đã gửi yêu cầu hoàn hàng / trả hàng thành công. Cửa hàng sẽ liên hệ phản hồi quý khách!'
        ];
    }

    public function cancelOrder(int $userId, $codeOrId, string $reason): array
    {
        $id = $this->resolveOrderId($codeOrId);
        $order = Order::where('user_id', $userId)->findOrFail($id);

        if (!in_array($order->status, ['pending', 'paid', 'confirmed'])) {
            return [
                'success' => false,
                'message' => 'Không thể hủy đơn hàng khi gian hàng đã bắt đầu chuẩn bị món.',
                'code'    => 422
            ];
        }

        DB::transaction(function () use ($order, $reason) {
            $order->status = 'cancelled';
            $order->cancel_reason = $reason;
            $order->cancelled_by = 'customer';
            $order->cancelled_at = now();
            if ($reason && !str_contains($order->notes ?? '', 'Lý do hủy')) {
                $order->notes = trim(($order->notes ? $order->notes . ' | ' : '') . '🚫 [Lý do hủy: ' . $reason . ']');
            }
            $order->save();

            if ($order->payment) {
                $order->payment->status = 'failed';
                $order->payment->save();
            }
        });

        return [
            'success' => true,
            'message' => 'Hủy đơn hàng thành công!'
        ];
    }

    public function reorder(int $userId, $codeOrId): array
    {
        $id = $this->resolveOrderId($codeOrId);
        $order = Order::with('items')->where('user_id', $userId)->findOrFail($id);

        if ($order->items->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Đơn hàng không có sản phẩm nào.',
                'code'    => 422
            ];
        }

        DB::transaction(function () use ($order, $userId) {
            $cart = Cart::firstOrCreate(['user_id' => $userId]);

            foreach ($order->items as $orderItem) {
                if ($orderItem->dish_id) {
                    $dish = Dish::find($orderItem->dish_id);
                    if (!$dish) continue;
                } elseif ($orderItem->ocop_product_id) {
                    $ocop = OcopProduct::find($orderItem->ocop_product_id);
                    if (!$ocop) continue;
                }

                $cartItem = CartItem::firstOrNew([
                    'cart_id'         => $cart->id,
                    'dish_id'         => $orderItem->dish_id,
                    'ocop_product_id' => $orderItem->ocop_product_id,
                ]);

                $cartItem->quantity = ($cartItem->exists ? $cartItem->quantity : 0) + $orderItem->quantity;
                $cartItem->save();
            }
        });

        return [
            'success'         => true,
            'message'         => 'Đã thêm lại các món vào giỏ hàng thành công!',
            'triggerCartOpen' => true
        ];
    }

    public function submitReview(User $user, $codeOrId, array $data, $mediaFiles): array
    {
        $id = $this->resolveOrderId($codeOrId);
        $order = Order::where('user_id', $user->id)->findOrFail($id);

        if ($order->status !== 'completed') {
            return [
                'success' => false,
                'message' => 'Chỉ có thể đánh giá đơn hàng đã hoàn thành.',
                'code'    => 422
            ];
        }

        if ($order->is_reviewed) {
            return [
                'success' => false,
                'message' => 'Đơn hàng này đã được đánh giá trước đó.',
                'code'    => 422
            ];
        }

        DB::transaction(function () use ($order, $user, $data, $mediaFiles) {
            EateryApiService::storeReview($order->category_slug, $order->eatery_id, [
                'user_name'   => $user->name,
                'rating'      => $data['rating'],
                'comment'     => $data['comment'] ?? null,
                'media_files' => $mediaFiles
            ]);

            $order->is_reviewed = true;
            $order->save();
        });

        return [
            'success' => true,
            'message' => 'Cảm ơn bạn đã gửi đánh giá!'
        ];
    }

    private function getStatusLabel($status)
    {
        switch ($status) {
            case 'pending': return 'Chờ xác nhận';
            case 'confirmed': return 'Sạp đã nhận đơn';
            case 'preparing':
            case 'processing':
            case 'paid': return 'Đang chuẩn bị hàng';
            case 'ready': return 'Sẵn sàng chờ lấy';
            case 'shipping':
            case 'delivering': return 'Đang giao';
            case 'completed': return 'Đã hoàn thành';
            case 'cancelled': return 'Đã hủy';
            case 'returned': return 'Đã hoàn hàng';
            case 'return_requested': return 'Yêu cầu hoàn hàng';
            default: return $status;
        }
    }

    private function getStatusClass($status)
    {
        switch ($status) {
            case 'pending': return 'status-pending';
            case 'confirmed': return 'status-confirmed';
            case 'preparing':
            case 'processing':
            case 'paid': return 'status-processing';
            case 'ready': return 'status-ready';
            case 'shipping':
            case 'delivering': return 'status-shipping';
            case 'completed': return 'status-completed';
            case 'cancelled': return 'status-cancelled';
            case 'returned':
            case 'return_requested': return 'status-returned';
            default: return 'status-default';
        }
    }
}
