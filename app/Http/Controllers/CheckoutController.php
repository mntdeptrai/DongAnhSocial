<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Voucher;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Dish;
use App\Models\OcopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thanh toán');
        }

        $cart = Cart::with(['items'])
            ->where('user_id', Auth::user()->id)
            ->first();

        $cartItems = [];
        $subtotal = 0;

        if ($cart && $cart->items->count() > 0) {
            $selectedIds = request()->query('items') ? explode(',', request()->query('items')) : null;
            $itemsToCheckout = $selectedIds 
                ? $cart->items->whereIn('id', $selectedIds) 
                : $cart->items;

            $cartItems = $itemsToCheckout->map(function ($item) {
                return [
                    'id'       => $item->id,
                    'dish_id'  => $item->dish_id,
                    'ocop_product_id' => $item->ocop_product_id,
                    'name'     => $item->product_name,
                    'price'    => (float)$item->product_price,
                    'quantity' => $item->quantity,
                    'image'    => $item->product_image ? asset($item->product_image) : asset('images/no-image.png'),
                    'total'    => (float)$item->thanh_tien,
                    'eatery_id' => $item->product ? $item->product->eatery_id : null,
                    'category_slug' => $item->dish_id ? 'dong-anh-food-map' : 'dong-anh-market'
                ];
            })->toArray();

            $subtotal = collect($cartItems)->sum('total');
        }

        if (empty($cartItems)) {
            return redirect()->back()->with('error', 'Giỏ hàng của bạn đang trống');
        }

        $vouchers = Voucher::active()->get();
        $bestVoucherApplied = $this->autoApplyBestVoucher($subtotal);

        return view('checkout.index', compact('cartItems', 'subtotal', 'vouchers', 'bestVoucherApplied'));
    }

    public function applyVoucher(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string',
            'items' => 'nullable|string'
        ]);

        $code = trim($request->input('voucher_code'));

        $cart = Cart::with(['items'])->where('user_id', Auth::user()->id)->first();
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng trống'], 422);
        }

        $selectedIds = $request->input('items') ? explode(',', $request->input('items')) : null;
        $itemsToCheckout = $selectedIds 
            ? $cart->items->whereIn('id', $selectedIds) 
            : $cart->items;

        $subtotal = $itemsToCheckout->sum('thanh_tien');

        $voucher = Voucher::where('code', $code)->active()->first();
        if (!$voucher) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn'], 422);
        }

        if ($voucher->min_order_amount && $subtotal < $voucher->min_order_amount) {
            return response()->json(['success' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($voucher->min_order_amount) . 'đ'], 422);
        }

        $percent = (float)$voucher->percentage;
        $discountValue = (int) floor($subtotal * $percent / 100);
        $totalValue = max(0, (int)$subtotal - $discountValue);

        session([
            'checkout.voucher' => [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'percent' => $percent,
                'discount' => $discountValue,
                'total' => $totalValue,
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => "Áp dụng mã {$voucher->code} (-{$percent}%) thành công",
            'voucher' => [
                'code' => $voucher->code,
                'percent' => $percent,
                'discount' => number_format($discountValue, 0, ',', '.') . 'đ',
                'total' => number_format($totalValue, 0, ',', '.') . 'đ',
                'discount_value' => $discountValue,
                'total_value' => $totalValue,
            ]
        ]);
    }

    public function removeVoucher(Request $request)
    {
        $cart = Cart::with(['items'])->where('user_id', Auth::user()->id)->first();
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng trống'], 422);
        }

        $selectedIds = $request->input('items') ? explode(',', $request->input('items')) : null;
        $itemsToCheckout = $selectedIds 
            ? $cart->items->whereIn('id', $selectedIds) 
            : $cart->items;

        $subtotal = $itemsToCheckout->sum('thanh_tien');
        session()->forget('checkout.voucher');

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy bỏ áp dụng mã giảm giá',
            'voucher' => [
                'discount' => '0đ',
                'total' => number_format($subtotal, 0, ',', '.') . 'đ'
            ]
        ]);
    }

    private function autoApplyBestVoucher($subtotal)
    {
        $vouchers = Voucher::active()
            ->where(function ($query) use ($subtotal) {
                $query->whereNull('min_order_amount')
                    ->orWhere('min_order_amount', '<=', $subtotal);
            })
            ->get();

        if ($vouchers->isEmpty()) {
            return null;
        }

        $bestVoucher = null;
        $maxDiscount = 0;

        foreach ($vouchers as $voucher) {
            $percent = (float)$voucher->percentage;
            $discountValue = (int) floor($subtotal * $percent / 100);

            if ($discountValue > $maxDiscount) {
                $maxDiscount = $discountValue;
                $bestVoucher = $voucher;
            }
        }

        if (!$bestVoucher) {
            return null;
        }

        $percent = (float)$bestVoucher->percentage;
        $discountValue = (int) floor($subtotal * $percent / 100);
        $totalValue = max(0, (int)$subtotal - $discountValue);

        session([
            'checkout.voucher' => [
                'id' => $bestVoucher->id,
                'code' => $bestVoucher->code,
                'percent' => $percent,
                'discount' => $discountValue,
                'total' => $totalValue,
            ]
        ]);

        return [
            'id' => $bestVoucher->id,
            'code' => $bestVoucher->code,
            'percent' => $percent,
            'discount' => number_format($discountValue, 0, ',', '.') . ' đ',
            'total' => number_format($totalValue, 0, ',', '.') . ' đ',
            'discount_value' => $discountValue,
            'total_value' => $totalValue,
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:1000',
            'payment_method' => 'required|in:COD,Online',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập');
        }

        $cart = Cart::with(['items'])->where('user_id', $user->id)->first();
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->back()->with('error', 'Giỏ hàng trống');
        }

        $selectedIds = $request->input('items') ? explode(',', $request->input('items')) : null;
        $itemsToCheckout = $selectedIds 
            ? $cart->items->whereIn('id', $selectedIds) 
            : $cart->items;

        if ($itemsToCheckout->isEmpty()) {
            return redirect()->back()->with('error', 'Giỏ hàng trống');
        }

        // Apply voucher if any from session
        $appliedVoucher = session('checkout.voucher');
        $voucherId = $appliedVoucher['id'] ?? null;
        $voucherPercent = $appliedVoucher['percent'] ?? 0;

        try {
            DB::beginTransaction();

            // Group cart items by eatery_id and category
            $groupedItems = $itemsToCheckout->groupBy(function ($item) {
                $eateryId = $item->product ? $item->product->eatery_id : 0;
                $slug = $item->dish_id ? 'dong-anh-food-map' : 'dong-anh-market';
                return $eateryId . '|' . $slug;
            });

            $createdOrders = [];

            foreach ($groupedItems as $key => $items) {
                list($eateryId, $categorySlug) = explode('|', $key);

                $subtotal = $items->sum('thanh_tien');
                $discount = ($subtotal * $voucherPercent) / 100;
                $totalAmount = max(0, $subtotal - $discount);

                // 1. Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'eatery_id' => $eateryId,
                    'category_slug' => $categorySlug,
                    'voucher_id' => $voucherId,
                    'customer_name' => $request->input('name'),
                    'customer_phone' => $request->input('phone'),
                    'shipping_address' => $request->input('address'),
                    'total_amount' => $totalAmount,
                    'payment_method' => $request->input('payment_method'),
                    'status' => 'pending',
                    'notes' => $request->input('notes'),
                ]);

                // 2. Create order items
                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'dish_id' => $item->dish_id,
                        'ocop_product_id' => $item->ocop_product_id,
                        'name' => $item->product_name,
                        'price' => $item->product_price,
                        'quantity' => $item->quantity,
                    ]);
                }

                // 3. Create payment transaction record
                Payment::create([
                    'order_id' => $order->id,
                    'method' => $request->input('payment_method'),
                    'amount' => $totalAmount,
                    'status' => 'pending',
                ]);

                $createdOrders[] = $order;
            }

            // Clear only the checked out items
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

            // Clear voucher session
            session()->forget('checkout.voucher');

            DB::commit();

            // If there's only one order and it's online payment, redirect to online payment simulator
            if (count($createdOrders) === 1 && $request->input('payment_method') === 'Online') {
                return redirect()->route('checkout.payment', $createdOrders[0]->id);
            }

            // Otherwise direct to order success page
            return redirect()->route('checkout.success', ['id' => $createdOrders[0]->id])
                ->with('success', 'Đặt hàng thành công!');

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors('Không thể xử lý đơn hàng. Vui lòng thử lại: ' . $e->getMessage())->withInput();
        }
    }

    public function payment($id)
    {
        $order = Order::with(['items', 'payment'])->findOrFail($id);
        return view('checkout.payment', compact('order'));
    }

    public function processPayment(Request $request, $id)
    {
        $order = Order::with('payment')->findOrFail($id);
        $success = $request->boolean('simulate_success', true);

        try {
            DB::beginTransaction();

            $payment = $order->payment;
            if ($payment) {
                $payment->status = $success ? 'success' : 'failed';
                $payment->paid_at = $success ? now() : null;
                $payment->save();
            }

            if ($success) {
                $order->status = 'paid';
                $order->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors('Lỗi thanh toán trực tuyến.');
        }

        if (!$success) {
            return back()->with('error', 'Thanh toán thất bại. Vui lòng thử lại.');
        }

        return redirect()->route('checkout.success', $order->id)->with('success', 'Thanh toán online thành công!');
    }

    public function success($id)
    {
        $order = Order::with(['items', 'payment'])->findOrFail($id);
        return view('checkout.success', compact('order'));
    }

    public function ordersList()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem đơn hàng');
        }

        // We load the page itself, the javascript will trigger the API call to load, filter and display orders.
        return view('checkout.orders');
    }

    public function show($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem đơn hàng');
        }

        // Just check if it exists for this user, then return the view. The JS will query apiOrdersShow for details.
        $order = Order::where('user_id', Auth::user()->id)->findOrFail($id);

        return view('checkout.show', compact('order'));
    }

    public function apiOrdersList(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $userId = Auth::user()->id;

        // Calculate global statistics for this user (before filters are applied)
        $totalCount = Order::where('user_id', $userId)->count();
        $processingCount = Order::where('user_id', $userId)
            ->whereIn('status', ['pending', 'paid', 'processing', 'shipping', 'delivering'])
            ->count();
        $completedCount = Order::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();
        $totalSpent = Order::where('user_id', $userId)
            ->where('status', 'completed')
            ->sum('total_amount');

        $query = Order::with(['items', 'payment', 'voucher'])
            ->where('user_id', $userId);

        // 1. Filter by search (mã đơn hàng hoặc tên món ăn)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            
            // Clean search term for ID search
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
                }
                $q->orWhere('id', 'like', "%{$search}%");
                $q->orWhereHas('items', function($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        // 2. Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        // 3. Filter by status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            if ($status === 'processing') {
                $query->whereIn('status', ['paid', 'processing']);
            } elseif ($status === 'shipping') {
                $query->whereIn('status', ['shipping', 'delivering']);
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
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => (float)$item->price,
                    'quantity' => $item->quantity,
                    'image' => $imagePath,
                    'total' => (float)($item->price * $item->quantity),
                ];
            });

            $subtotal = $items->sum('total');
            $discount = 0.00;
            if ($order->voucher) {
                $discount = (float)($subtotal * (float)$order->voucher->percentage / 100);
            }
            $shipping_fee = $subtotal >= 100000 ? 0 : 15000;

            return [
                'id' => $order->id,
                'order_code' => 'ORD' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
                'order_code_full' => '#ORD' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                'created_at_formatted' => $order->created_at->format('H:i d/m/Y'),
                'status' => $order->status,
                'status_label' => $this->getStatusLabel($order->status),
                'status_class' => $this->getStatusClass($order->status),
                'items' => $items,
                'subtotal' => $subtotal,
                'shipping_fee' => $shipping_fee,
                'voucher_code' => $order->voucher ? $order->voucher->code : null,
                'discount' => $discount,
                'total_amount' => (float)$order->total_amount,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'shipping_address' => $order->shipping_address,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment ? $order->payment->status : 'pending',
                'notes' => $order->notes,
                'is_reviewed' => (bool)$order->is_reviewed,
                'eatery_id' => $order->eatery_id,
                'category_slug' => $order->category_slug,
                'eatery_name' => $order->eatery ? $order->eatery->name : 'Cửa hàng',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedOrders,
            'stats' => [
                'total' => $totalCount,
                'processing' => $processingCount,
                'completed' => $completedCount,
                'spent' => (float)$totalSpent
            ]
        ]);
    }

    public function apiOrdersShow(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $order = Order::with(['items', 'payment', 'voucher'])
            ->where('user_id', Auth::user()->id)
            ->findOrFail($id);

        $items = $order->items->map(function ($item) {
            $product = $item->product;
            $imagePath = $product && $product->image_path ? asset($product->image_path) : null;
            if (!$imagePath && $item->ocop_product_id) {
                $imagePath = asset('images/ocop-placeholder.png');
            }
            
            return [
                'id' => $item->id,
                'name' => $item->name,
                'price' => (float)$item->price,
                'quantity' => $item->quantity,
                'image' => $imagePath,
                'total' => (float)($item->price * $item->quantity),
            ];
        });

        $subtotal = $items->sum('total');
        $discount = 0.00;
        if ($order->voucher) {
            $discount = (float)($subtotal * (float)$order->voucher->percentage / 100);
        }
        $shipping_fee = $subtotal >= 100000 ? 0 : 15000;

        $formattedOrder = [
            'id' => $order->id,
            'order_code' => 'ORD' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
            'order_code_full' => '#ORD' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'created_at_formatted' => $order->created_at->format('H:i d/m/Y'),
            'status' => $order->status,
            'status_label' => $this->getStatusLabel($order->status),
            'status_class' => $this->getStatusClass($order->status),
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping_fee,
            'voucher_code' => $order->voucher ? $order->voucher->code : null,
            'discount' => $discount,
            'total_amount' => (float)$order->total_amount,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'shipping_address' => $order->shipping_address,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment ? $order->payment->status : 'pending',
            'notes' => $order->notes,
            'is_reviewed' => (bool)$order->is_reviewed,
            'eatery_id' => $order->eatery_id,
            'category_slug' => $order->category_slug,
            'eatery_name' => $order->eatery ? $order->eatery->name : 'Cửa hàng',
        ];

        return response()->json([
            'success' => true,
            'data' => $formattedOrder
        ]);
    }

    private function getStatusLabel($status)
    {
        switch ($status) {
            case 'pending': return 'Chờ xác nhận';
            case 'paid':
            case 'processing': return 'Đang chuẩn bị';
            case 'shipping':
            case 'delivering': return 'Đang giao';
            case 'completed': return 'Đã hoàn thành';
            case 'cancelled': return 'Đã hủy';
            default: return $status;
        }
    }

    private function getStatusClass($status)
    {
        switch ($status) {
            case 'pending': return 'status-pending';
            case 'paid':
            case 'processing': return 'status-processing';
            case 'shipping':
            case 'delivering': return 'status-shipping';
            case 'completed': return 'status-completed';
            case 'cancelled': return 'status-cancelled';
            default: return 'status-default';
        }
    }

    public function cancel(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $order = Order::where('user_id', Auth::user()->id)->findOrFail($id);

        if (!in_array($order->status, ['pending', 'paid'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy đơn hàng ở trạng thái này. Chỉ có thể hủy đơn hàng chưa được xử lý.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $order->status = 'cancelled';
            $order->save();

            if ($order->payment) {
                $order->payment->status = 'failed';
                $order->payment->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hủy đơn hàng thành công!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reorder(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $order = Order::with('items')->where('user_id', Auth::user()->id)->findOrFail($id);

        if ($order->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng không có sản phẩm nào.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $cart = Cart::firstOrCreate(['user_id' => Auth::user()->id]);

            foreach ($order->items as $orderItem) {
                if ($orderItem->dish_id) {
                    $dish = Dish::find($orderItem->dish_id);
                    if (!$dish) continue;
                } elseif ($orderItem->ocop_product_id) {
                    $ocop = OcopProduct::find($orderItem->ocop_product_id);
                    if (!$ocop) continue;
                }

                $cartItem = CartItem::firstOrNew([
                    'cart_id' => $cart->id,
                    'dish_id' => $orderItem->dish_id,
                    'ocop_product_id' => $orderItem->ocop_product_id,
                ]);

                $cartItem->quantity = ($cartItem->exists ? $cartItem->quantity : 0) + $orderItem->quantity;
                $cartItem->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm lại các món vào giỏ hàng thành công!',
                'triggerCartOpen' => true
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    public function review(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $order = Order::where('user_id', Auth::user()->id)->findOrFail($id);

        if ($order->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể đánh giá đơn hàng đã hoàn thành.'
            ], 422);
        }

        if ($order->is_reviewed) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng này đã được đánh giá trước đó.'
            ], 422);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'media.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,heic,heif,mp4,mov,avi,webm,3gp,quicktime|max:20480'
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Số sao đánh giá phải là số nguyên.',
            'rating.min' => 'Số sao đánh giá tối thiểu là 1.',
            'rating.max' => 'Số sao đánh giá tối đa là 5.',
            'comment.max' => 'Nhận xét không được vượt quá 500 ký tự.',
            'media.*.file' => 'Tệp tải lên không hợp lệ.',
            'media.*.mimes' => 'Định dạng tệp đính kèm không hỗ trợ (chỉ chấp nhận ảnh JPG, PNG, WEBP, HEIC hoặc video MP4, MOV, AVI, WEBM).',
            'media.*.max' => 'Kích thước tệp đính kèm không được vượt quá 20MB.'
        ]);

        $mediaFiles = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('reviews', 'public');
                $type = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
                $mediaFiles[] = [
                    'path' => '/storage/' . $path,
                    'type' => $type
                ];
            }
        }

        try {
            DB::beginTransaction();

            // Save review using EateryApiService
            \App\Services\EateryApiService::storeReview($order->category_slug, $order->eatery_id, [
                'user_name' => Auth::user()->name,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'media_files' => $mediaFiles
            ]);

            // Mark order as reviewed
            $order->is_reviewed = true;
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cảm ơn bạn đã gửi đánh giá!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
}
