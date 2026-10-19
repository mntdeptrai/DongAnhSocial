<?php

namespace App\Http\Controllers;

use App\Helpers\R2Helper;
use App\Models\Order;
use App\Services\Interfaces\CheckoutServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutServiceInterface $checkoutService
    ) {}

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thanh toán');
        }

        $data = $this->checkoutService->getCheckoutData(Auth::user(), request()->query('items'));

        if (empty($data['cartItems'])) {
            return redirect()->back()->with('error', 'Giỏ hàng của bạn đang trống');
        }

        return view('checkout.index', [
            'cartItems'       => $data['cartItems'],
            'subtotal'        => $data['subtotal'],
            'distinctMarkets' => $data['distinctMarkets'],
            'distinctStalls'  => $data['distinctStalls'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string|max:1000',
            'payment_method' => 'required|in:COD,Online',
            'notes'          => 'nullable|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập');
        }

        try {
            $createdOrders = $this->checkoutService->createOrders($user, $validated, $request->input('items'));

            if (empty($createdOrders)) {
                return redirect()->back()->with('error', 'Giỏ hàng trống');
            }

            $createdIds = collect($createdOrders)->pluck('id')->toArray();
            $idsString = implode(',', $createdIds);

            if (count($createdOrders) === 1 && $request->input('payment_method') === 'Online') {
                return redirect()->route('checkout.payment', $this->checkoutService->getOrderCode($createdOrders[0]->id));
            }

            return redirect()->route('checkout.success', [
                'code' => $this->checkoutService->getOrderCode($createdOrders[0]->id),
                'ids'  => $idsString
            ])->with('success', 'Đặt hàng thành công!');

        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors('Không thể xử lý đơn hàng lúc này. Vui lòng thử lại sau hoặc liên hệ hỗ trợ.')->withInput();
        }
    }

    public function payment($code)
    {
        $userId = Auth::id() ?? session('user_id');
        $order = $this->checkoutService->getOrderForPayment($code, $userId);
        return view('checkout.payment', compact('order'));
    }

    public function processPayment(Request $request, $code)
    {
        $userId = Auth::id() ?? session('user_id');
        $success = $request->boolean('simulate_success', true);

        try {
            $order = $this->checkoutService->processPayment($code, $userId, $success);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors('Lỗi thanh toán trực tuyến.');
        }

        if (!$success) {
            return back()->with('error', 'Thanh toán thất bại. Vui lòng thử lại.');
        }

        return redirect()->route('checkout.success', $this->checkoutService->getOrderCode($order->id))
            ->with('success', 'Thanh toán online thành công!');
    }

    public function success(Request $request, $code)
    {
        $userId = Auth::id() ?? session('user_id');
        $result = $this->checkoutService->getOrdersForSuccess($code, $request->input('ids'), $userId);
        
        return view('checkout.success', [
            'order'  => $result['order'],
            'orders' => $result['orders']
        ]);
    }

    public function ordersList()
    {
        $userId = Auth::id() ?? session('user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem đơn hàng');
        }

        return view('checkout.orders');
    }

    public function show($code)
    {
        $userId = Auth::id() ?? session('user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem đơn hàng');
        }

        $id = $this->checkoutService->resolveOrderId($code);
        $order = Order::where('user_id', $userId)->findOrFail($id);

        return view('checkout.show', compact('order'));
    }

    public function apiOrdersList(Request $request)
    {
        $userId = Auth::id() ?? session('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $result = $this->checkoutService->getApiOrdersList($userId, $request);

        return response()->json([
            'success' => true,
            'data'    => $result['data'],
            'stats'   => $result['stats']
        ]);
    }

    public function apiOrdersShow(Request $request, $codeOrId)
    {
        $userId = Auth::id() ?? session('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $formattedOrder = $this->checkoutService->getApiOrderDetails($userId, $codeOrId);

        return response()->json([
            'success' => true,
            'data'    => $formattedOrder
        ]);
    }

    public function confirmReceived(Request $request, $codeOrId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $result = $this->checkoutService->confirmReceived(Auth::id(), $codeOrId);
        $statusCode = $result['code'] ?? 200;
        unset($result['code']);

        return response()->json($result, $statusCode);
    }

    public function returnOrder(Request $request, $codeOrId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $reason = trim($request->input('reason', 'Khách hàng yêu cầu trả hàng / hoàn tiền'));
        $result = $this->checkoutService->returnOrder(Auth::id(), $codeOrId, $reason);
        $statusCode = $result['code'] ?? 200;
        unset($result['code']);

        return response()->json($result, $statusCode);
    }

    public function cancel(Request $request, $codeOrId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $reason = trim($request->input('reason', 'Khách hàng hủy đơn'));
        $result = $this->checkoutService->cancelOrder(Auth::id(), $codeOrId, $reason);
        $statusCode = $result['code'] ?? 200;
        unset($result['code']);

        return response()->json($result, $statusCode);
    }

    public function reorder(Request $request, $codeOrId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $result = $this->checkoutService->reorder(Auth::id(), $codeOrId);
        $statusCode = $result['code'] ?? 200;
        unset($result['code']);

        return response()->json($result, $statusCode);
    }

    public function review(Request $request, $codeOrId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'media.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,heic,heif,mp4,mov,avi,webm,3gp,quicktime|max:20480'
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer'  => 'Số sao đánh giá phải là số nguyên.',
            'rating.min'      => 'Số sao đánh giá tối thiểu là 1.',
            'rating.max'      => 'Số sao đánh giá tối đa là 5.',
            'comment.max'     => 'Nhận xét không được vượt quá 500 ký tự.',
            'media.*.file'    => 'Tệp tải lên không hợp lệ.',
            'media.*.mimes'   => 'Định dạng tệp đính kèm không hỗ trợ.',
            'media.*.max'     => 'Kích thước tệp đính kèm không được vượt quá 20MB.'
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

        $result = $this->checkoutService->submitReview(Auth::user(), $codeOrId, $request->only(['rating', 'comment']), $mediaFiles);
        $statusCode = $result['code'] ?? 200;
        unset($result['code']);

        return response()->json($result, $statusCode);
    }
}
