<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * WebhookApiController - Triển khai giao thức Webhooks (Event-driven / Callback)
 * 
 * Nhận các tín hiệu phản hồi bất đồng bộ từ các bên thứ ba (Cổng thanh toán MoMo/VNPAY,
 * Dịch vụ SMS OTP, Hệ thống đồng bộ gian hàng chợ từ cấp Huyện).
 */
class WebhookApiController extends Controller
{
    /**
     * Webhook xử lý kết quả thanh toán từ VNPay/MoMo
     * POST /api/v1/webhooks/payment
     */
    public function handlePayment(Request $request)
    {
        Log::info('Payment Webhook Received:', $request->all());

        // Mô phỏng xác minh chữ ký bảo mật (Secure Signature Verification)
        $signature = $request->header('X-Signature');
        if (empty($signature)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized signature'], 401);
        }

        $orderId = $request->input('order_id');
        $status = $request->input('status'); // 'SUCCESS' or 'FAILED'
        $amount = $request->input('amount');

        if (!$orderId || !$status) {
            return response()->json(['success' => false, 'message' => 'Missing parameter'], 400);
        }

        // Cập nhật trạng thái đơn hàng trong DB đồng bộ cho cả Mobile & Web
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        if ($status === 'SUCCESS') {
            DB::table('orders')->where('id', $orderId)->update([
                'status' => 'completed',
                'updated_at' => now()
            ]);

            // Ở đây có thể kích hoạt Event phát tin nhắn realtime (WebSocket) hoặc thông báo (SSE)
            Log::info("Order ID {$orderId} has been paid successfully.");
        } else {
            DB::table('orders')->where('id', $orderId)->update([
                'status' => 'cancelled',
                'updated_at' => now()
            ]);
            Log::warning("Order ID {$orderId} payment failed.");
        }

        return response()->json(['success' => true, 'message' => 'Webhook processed successfully']);
    }

    /**
     * Webhook đồng bộ gian hàng từ hệ thống Quản lý Hợp tác xã/Sở Công Thương Đông Anh
     * POST /api/v1/webhooks/sync-stall
     */
    public function syncStall(Request $request)
    {
        Log::info('Stall Sync Webhook Received:', $request->all());

        $token = $request->header('Authorization');
        // Token giả định
        if ($token !== 'Bearer dong_anh_social_hub_token_xyz') {
            return response()->json(['success' => false, 'message' => 'Invalid auth token'], 403);
        }

        $stallData = $request->input('stall');
        if (!$stallData || empty($stallData['name'])) {
            return response()->json(['success' => false, 'message' => 'Invalid stall data'], 400);
        }

        // Đồng bộ dữ liệu gian hàng (Insert/Update) vào db mysql_market
        try {
            $eatery = \App\Models\Eatery::on('mysql_market')
                ->updateOrCreate(
                    ['phone' => $stallData['phone']],
                    [
                        'name' => $stallData['name'],
                        'address' => $stallData['address'] ?? 'Đông Anh, Hà Nội',
                        'description' => $stallData['description'] ?? 'Gian hàng OCOP được cấp phép',
                        'category_id' => 3, // Traditional market
                        'commune_id' => $stallData['commune_id'] ?? 1,
                        'status' => 'active',
                        'rating' => 5.0
                    ]
                );

            return response()->json(['success' => true, 'stall_id' => $eatery->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Sync error: ' . $e->getMessage()], 500);
        }
    }
}
