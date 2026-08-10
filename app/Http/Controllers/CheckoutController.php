<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Dish;
use App\Models\OcopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\R2Helper;

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

            $dishIds = $itemsToCheckout->pluck('dish_id')->filter()->unique()->toArray();
            $ocopIds = $itemsToCheckout->pluck('ocop_product_id')->filter()->unique()->toArray();

            $dishes = !empty($dishIds) ? Dish::on('mysql')->with('eatery')->whereIn('id', $dishIds)->get()->keyBy('id') : collect();
            $ocopProducts = !empty($ocopIds) ? OcopProduct::on('mysql_market')->with('eatery')->whereIn('id', $ocopIds)->get()->keyBy('id') : collect();

            $cartItems = $itemsToCheckout->map(function ($item) use ($dishes, $ocopProducts) {
                $product = null;
                if ($item->dish_id) {
                    $product = $dishes->get($item->dish_id);
                } elseif ($item->ocop_product_id) {
                    $product = $ocopProducts->get($item->ocop_product_id);
                }
                $eatery = $product ? $product->eatery : null;

                return [
                    'id'       => $item->id,
                    'dish_id'  => $item->dish_id,
                    'ocop_product_id' => $item->ocop_product_id,
                    'name'     => $item->product_name,
                    'price'    => (float)$item->product_price,
                    'quantity' => $item->quantity,
                    'image'    => $item->product_image ? asset($item->product_image) : asset('images/no-image.png'),
                    'total'    => (float)$item->thanh_tien,
                    'eatery_id' => $eatery ? $eatery->id : ($product ? $product->eatery_id : null),
                    'eatery_name' => $eatery ? $eatery->name : 'Gian hàng Đông Anh',
                    'stall_name' => ($product && isset($product->stall_name)) ? $product->stall_name : null,
                    'category_slug' => $item->dish_id ? 'dong-anh-food-map' : 'dong-anh-market'
                ];
            })->toArray();

            $subtotal = collect($cartItems)->sum('total');
        }

        if (empty($cartItems)) {
            return redirect()->back()->with('error', 'Giỏ hàng của bạn đang trống');
        }

        $distinctMarkets = collect($cartItems)->pluck('eatery_name')->filter()->unique()->values()->all();
        $distinctStalls = collect($cartItems)->pluck('stall_name')->filter()->unique()->values()->all();

        return view('checkout.index', compact('cartItems', 'subtotal', 'distinctMarkets', 'distinctStalls'));
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

        try {
            DB::beginTransaction();

            // Group cart items by eatery_id, category, and stall_name
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

                // Format specific shipping/pickup address for this specific order
                $rawAddress = $request->input('address');
                $specificAddress = $rawAddress;

                if (str_starts_with($rawAddress, '[Ghé sạp lấy đồ]')) {
                    // Tìm tên chợ riêng biệt của đơn này
                    $eateryObj = \App\Models\Eatery::find($eateryId);
                    $specificMarketName = $eateryObj ? $eateryObj->name : 'Chợ';
                    
                    // Trích xuất khung giờ hẹn và xe nếu có
                    $extraInfo = '';
                    if (preg_match('/(\(Hẹn:.*?\))/u', $rawAddress, $matchTime)) {
                        $extraInfo .= ' ' . $matchTime[1];
                    }
                    if (preg_match('/(Phương tiện:.*?\))/u', $rawAddress, $matchVehicle)) {
                        $extraInfo .= ' (' . $matchVehicle[1];
                    }

                    $specificAddress = "[Ghé sạp lấy đồ] Tại " . $specificMarketName . ($stallName ? " - " . $stallName : "") . ($extraInfo ? " " . trim($extraInfo) : "");
                }

                // 1. Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'eatery_id' => $eateryId,
                    'category_slug' => $categorySlug,
                    'stall_name' => $stallName ?: null,
                    'customer_name' => $request->input('name'),
                    'customer_phone' => $request->input('phone'),
                    'shipping_address' => $specificAddress,
                    'total_amount' => $totalAmount,
                    'payment_method' => $request->input('payment_method'),
                    'status' => 'confirmed', // Tự động chuyển thẳng sang 'Sạp nhận đơn' cho cả COD và Online!
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

            DB::commit();

            // Prepare IDs string for multiple orders success page
            $createdIds = collect($createdOrders)->pluck('id')->toArray();
            $idsString = implode(',', $createdIds);

            // If there's only one order and it's online payment, redirect to online payment simulator
            if (count($createdOrders) === 1 && $request->input('payment_method') === 'Online') {
                return redirect()->route('checkout.payment', $this->getOrderCode($createdOrders[0]->id));
            }

            // Otherwise direct to order success page
            return redirect()->route('checkout.success', [
                'code' => $this->getOrderCode($createdOrders[0]->id),
                'ids' => $idsString
            ])->with('success', 'Đặt hàng thành công!');

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors('Không thể xử lý đơn hàng. Vui lòng thử lại: ' . $e->getMessage())->withInput();
        }
    }

    private function resolveOrderId($codeOrId)
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

    private function getOrderCode($id)
    {
        return 'ORD' . str_pad($id, 6, '0', STR_PAD_LEFT);
    }

    public function payment($code)
    {
        $userId = Auth::id() ?? session('user_id');
        $id = $this->resolveOrderId($code);
        $order = Order::with(['items', 'payment'])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->findOrFail($id);
        return view('checkout.payment', compact('order'));
    }

    public function processPayment(Request $request, $code)
    {
        $userId = Auth::id() ?? session('user_id');
        $id = $this->resolveOrderId($code);
        $order = Order::with('payment')
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->findOrFail($id);
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
                $order->status = 'confirmed'; // Tự động chuyển sang 'Sạp nhận đơn' khi khách chuyển khoản thành công
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

        return redirect()->route('checkout.success', $this->getOrderCode($order->id))->with('success', 'Thanh toán online thành công!');
    }

    public function success(Request $request, $code)
    {
        $userId = Auth::id() ?? session('user_id');
        $orderIds = [];
        if ($code) {
            $orderIds[] = $this->resolveOrderId($code);
        }
        if ($request->filled('ids')) {
            $ids = explode(',', $request->input('ids'));
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
        
        $order = $orders->first();
        
        return view('checkout.success', compact('order', 'orders'));
    }

    public function ordersList()
    {
        $userId = Auth::id() ?? session('user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem đơn hàng');
        }

        // We load the page itself, the javascript will trigger the API call to load, filter and display orders.
        return view('checkout.orders');
    }

    public function show($code)
    {
        $userId = Auth::id() ?? session('user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem đơn hàng');
        }

        $id = $this->resolveOrderId($code);
        $order = Order::where('user_id', $userId)->findOrFail($id);

        return view('checkout.show', compact('order'));
    }

    public function apiOrdersList(Request $request)
    {
        $userId = Auth::id() ?? session('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        // Calculate global statistics for this user (before filters are applied)
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

        $query = Order::with(['items', 'payment'])
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
                } else {
                    $q->whereHas('items', function($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "{$search}%");
                    });
                }
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
                'voucher_code' => null,
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
                'stall_name' => $order->stall_name,
                'eatery_name' => $order->eatery 
                    ? ($order->stall_name ? $order->eatery->name . ' - ' . $order->stall_name : $order->eatery->name) 
                    : ($order->stall_name ?: 'Cửa hàng'),
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

    public function apiOrdersShow(Request $request, $codeOrId)
    {
        $userId = Auth::id() ?? session('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $id = $this->resolveOrderId($codeOrId);

        $order = Order::with(['items', 'payment'])
            ->where('user_id', $userId)
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
        $shipping_fee = $subtotal >= 100000 ? 0 : 15000;

        $formattedOrder = [
            'id' => $order->id,
            'order_code' => 'ORD' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
            'order_code_full' => 'ORD' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'created_at_formatted' => $order->created_at->format('H:i d/m/Y'),
            'status' => $order->status,
            'status_label' => $this->getStatusLabel($order->status),
            'status_class' => $this->getStatusClass($order->status),
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping_fee,
            'voucher_code' => null,
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
            'stall_name' => $order->stall_name,
            'eatery_name' => $order->eatery 
                ? ($order->stall_name ? $order->eatery->name . ' - ' . $order->stall_name : $order->eatery->name) 
                : ($order->stall_name ?: 'Cửa hàng'),
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
            case 'paid': return 'Đã thanh toán';
            case 'processing': return 'Đang chuẩn bị';
            case 'shipping':
            case 'delivering': return 'Đang giao';
            case 'completed': return 'Đã nhận / Hoàn thành';
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
            case 'paid': return 'status-paid';
            case 'processing': return 'status-processing';
            case 'shipping':
            case 'delivering': return 'status-shipping';
            case 'completed': return 'status-completed';
            case 'cancelled': return 'status-cancelled';
            case 'returned':
            case 'return_requested': return 'status-returned';
            default: return 'status-default';
        }
    }

    public function confirmReceived(Request $request, $codeOrId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $id = $this->resolveOrderId($codeOrId);
        $order = Order::where('user_id', Auth::user()->id)->findOrFail($id);

        if ($order->status === 'completed') {
            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng này đã được xác nhận hoàn thành trước đó.'
            ]);
        }

        if (in_array($order->status, ['cancelled', 'returned'])) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng đã bị hủy hoặc hoàn trả, không thể xác nhận nhận hàng.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $order->status = 'completed';
            $order->save();

            // Automatic COD payment success transition upon receipt
            if ($order->payment && $order->payment->status !== 'success') {
                $order->payment->status = 'success';
                $order->payment->paid_at = now();
                $order->payment->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cảm ơn bạn! Đã xác nhận nhận được hàng thành công.',
                'order_id' => $order->id,
                'order_code' => 'ORD' . str_pad($order->id, 3, '0', STR_PAD_LEFT)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    public function returnOrder(Request $request, $codeOrId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $id = $this->resolveOrderId($codeOrId);
        $order = Order::where('user_id', Auth::user()->id)->findOrFail($id);

        if (!in_array($order->status, ['completed', 'shipping', 'delivering'])) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể yêu cầu hoàn hàng cho đơn hàng đã nhận hoặc đang giao.'
            ], 422);
        }

        $reason = trim($request->input('reason', 'Khách hàng yêu cầu trả hàng / hoàn tiền'));

        try {
            DB::beginTransaction();

            $order->status = 'returned';
            $order->notes = trim(($order->notes ? $order->notes . ' | ' : '') . '📌 [Lý do hoàn hàng: ' . $reason . ']');
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã gửi yêu cầu hoàn hàng / trả hàng thành công. Cửa hàng sẽ liên hệ phản hồi quý khách!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Request $request, $codeOrId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $id = $this->resolveOrderId($codeOrId);
        $order = Order::where('user_id', Auth::user()->id)->findOrFail($id);

        if (!in_array($order->status, ['pending', 'paid'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy đơn hàng ở trạng thái này. Chỉ có thể hủy đơn hàng chưa được xử lý.'
            ], 422);
        }

        $reason = trim($request->input('reason', 'Khách hàng hủy đơn'));

        try {
            DB::beginTransaction();

            $order->status = 'cancelled';
            if ($reason) {
                $order->notes = trim(($order->notes ? $order->notes . ' | ' : '') . '🚫 [Lý do hủy: ' . $reason . ']');
            }
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

    public function reorder(Request $request, $codeOrId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $id = $this->resolveOrderId($codeOrId);
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

    public function review(Request $request, $codeOrId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $id = $this->resolveOrderId($codeOrId);
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
            $files = is_array($request->file('media')) ? $request->file('media') : [$request->file('media')];
            $uploaded = R2Helper::uploadMultiple($files, 'reviews');
            foreach ($uploaded as $item) {
                $mediaFiles[] = [
                    'path' => $item['url'],
                    'type' => $item['file_type']
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
