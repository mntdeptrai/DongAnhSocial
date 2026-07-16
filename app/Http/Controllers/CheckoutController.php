<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Voucher;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
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
            $cartItems = $cart->items->map(function ($item) {
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
        $request->validate(['voucher_code' => 'required|string']);

        $code = trim($request->input('voucher_code'));

        $cart = Cart::with(['items'])->where('user_id', Auth::user()->id)->first();
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng trống'], 422);
        }

        $subtotal = $cart->items->sum('thanh_tien');

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
                'discount' => number_format($discountValue, 0, ',', '.') . ' đ',
                'total' => number_format($totalValue, 0, ',', '.') . ' đ',
                'discount_value' => $discountValue,
                'total_value' => $totalValue,
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

        // Apply voucher if any from session
        $appliedVoucher = session('checkout.voucher');
        $voucherId = $appliedVoucher['id'] ?? null;
        $voucherPercent = $appliedVoucher['percent'] ?? 0;

        try {
            DB::beginTransaction();

            // Group cart items by eatery_id and category
            $groupedItems = $cart->items->groupBy(function ($item) {
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

            // Clear the cart
            $cart->items()->delete();
            $cart->delete();

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

        $orders = Order::with(['items', 'payment'])
            ->where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('checkout.orders', compact('orders'));
    }
}
