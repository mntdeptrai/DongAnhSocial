<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\ReviewVideo;
use App\Models\Eatery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * RpcApiController - Triển khai giao thức JSON-RPC 2.0 (Action-oriented / RPC)
 * 
 * Thích hợp cho việc gọi hàm hàng loạt (Batch calls) hoặc các tác vụ phức tạp
 * mang tính hành động cao (hơn là CRUD tài nguyên), giúp tiết kiệm số lượng HTTP request.
 */
class RpcApiController extends Controller
{
    /**
     * Endpoint chính tiếp nhận JSON-RPC request
     * POST /api/v1/rpc
     */
    public function handle(Request $request)
    {
        $payload = $request->json()->all();

        // Kiểm tra xem có phải batch request (mảng chứa nhiều request)
        if (isset($payload[0]) && is_array($payload[0])) {
            $responses = [];
            foreach ($payload as $singleRequest) {
                $responses[] = $this->processRequest($singleRequest);
            }
            return response()->json(array_filter($responses)); // Bỏ null cho notification requests
        }

        // Single request
        $response = $this->processRequest($payload);
        return $response ? response()->json($response) : response('', 204);
    }

    /**
     * Xử lý một request JSON-RPC đơn lẻ
     */
    protected function processRequest($req)
    {
        // Kiểm tra đúng định dạng JSON-RPC
        if (!isset($req['jsonrpc']) || $req['jsonrpc'] !== '2.0' || !isset($req['method'])) {
            return $this->formatErrorResponse($req['id'] ?? null, -32600, 'Invalid Request');
        }

        $method = $req['method'];
        $params = $req['params'] ?? [];
        $id = $req['id'] ?? null; // Nếu không có id tức là client chỉ gửi Notification, không cần trả kết quả

        // Yêu cầu xác thực tài khoản cho tất cả các RPC calls
        $user = auth('sanctum')->user() ?: auth()->user();
        if (!$user && $method !== 'ping') {
            return $this->formatErrorResponse($id, -32001, 'Unauthorized');
        }

        try {
            switch ($method) {
                case 'ping':
                    $result = ['status' => 'pong', 'timestamp' => time()];
                    break;

                case 'batchApproveVideos':
                    $result = $this->batchApproveVideos($params, $user);
                    break;

                case 'batchUpdatePrices':
                    $result = $this->batchUpdatePrices($params, $user);
                    break;

                case 'getEateryDetails':
                    $result = $this->getEateryDetails($params);
                    break;

                case 'batchUpdateOrderStatus':
                    $result = $this->batchUpdateOrderStatus($params, $user);
                    break;

                case 'batchApproveStalls':
                    $result = $this->batchApproveStalls($params, $user);
                    break;

                default:
                    return $this->formatErrorResponse($id, -32601, 'Method not found');
            }

            if ($id === null) {
                return null; // Notification request
            }

            return [
                'jsonrpc' => '2.0',
                'result' => $result,
                'id' => $id
            ];

        } catch (\InvalidArgumentException $e) {
            return $this->formatErrorResponse($id, -32602, $e->getMessage());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('RPC internal error: ' . $e->getMessage());
            return $this->formatErrorResponse($id, -32000, 'Internal error');
        }
    }

    /**
     * RPC Method: Duyệt hàng loạt Video đánh giá
     */
    protected function batchApproveVideos($params, $user = null)
    {
        if (!$user || !in_array($user->role, ['admin', 'manager'])) {
            throw new \InvalidArgumentException('Forbidden: Access denied.');
        }

        $ids = $params['ids'] ?? null;
        if (!is_array($ids)) {
            throw new \InvalidArgumentException('Invalid parameters.');
        }

        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        $updatedCount = 0;

        foreach ($connections as $conn) {
            $updatedCount += ReviewVideo::on($conn)
                ->whereIn('id', $ids)
                ->update(['status' => 'approved']);
        }

        return [
            'success' => true,
            'approved_count' => $updatedCount
        ];
    }

    /**
     * RPC Method: Cập nhật giá hàng loạt cho món ăn/sản phẩm (tiết kiệm connection)
     */
    protected function batchUpdatePrices($params, $user = null)
    {
        if (!$user || !in_array($user->role, ['admin', 'seller'])) {
            throw new \InvalidArgumentException('Forbidden: Access denied.');
        }

        $updates = $params['updates'] ?? null; // [{id: 1, price: 35000}, ...]
        if (!is_array($updates)) {
            throw new \InvalidArgumentException('Invalid parameters.');
        }

        $myEateryIds = ($user->role === 'admin') ? [] : Eatery::where('user_id', $user->id)->pluck('id')->toArray();
        if ($user->role !== 'admin' && empty($myEateryIds)) {
            throw new \InvalidArgumentException('Forbidden: Access denied.');
        }

        $rows = [];
        foreach ($updates as $update) {
            if (isset($update['id']) && isset($update['price'])) {
                $dishId = (int) $update['id'];
                if ($user->role !== 'admin') {
                    $dish = Dish::find($dishId);
                    if (!$dish || !in_array($dish->eatery_id, $myEateryIds)) {
                        continue; // Bỏ qua món ăn không thuộc quán của seller
                    }
                }
                $rows[] = [
                    'id'    => $dishId,
                    'price' => $update['price'],
                ];
            }
        }

        if (!empty($rows)) {
            Dish::upsert($rows, ['id'], ['price']);
        }

        return ['success' => true, 'updated_count' => count($rows)];
    }

    /**
     * RPC Method: Lấy thông tin chi tiết nhanh của cơ sở
     */
    protected function getEateryDetails($params)
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            throw new \InvalidArgumentException('Invalid parameters.');
        }

        $eatery = Eatery::find($id);
        if (!$eatery) {
            throw new \InvalidArgumentException('Record not found.');
        }

        return [
            'name' => $eatery->name,
            'rating' => $eatery->rating,
            'address' => $eatery->address
        ];
    }

    /**
     * RPC Method: Cập nhật hàng loạt trạng thái đơn hàng (Seller/Manager/Admin)
     */
    protected function batchUpdateOrderStatus($params, $user = null)
    {
        if (!$user || !in_array($user->role, ['admin', 'manager', 'seller'])) {
            throw new \InvalidArgumentException('Forbidden: Access denied.');
        }

        $orderIds = $params['order_ids'] ?? null;
        $status = $params['status'] ?? 'confirmed';

        if (!is_array($orderIds)) {
            throw new \InvalidArgumentException('Invalid parameters.');
        }

        $query = DB::table('orders')->whereIn('id', $orderIds);
        if ($user->role !== 'admin' && $user->role !== 'manager') {
            $myEateryIds = Eatery::where('user_id', $user->id)->pluck('id')->toArray();
            if (empty($myEateryIds)) {
                return ['success' => true, 'updated_count' => 0, 'status' => $status];
            }
            $query->whereIn('eatery_id', $myEateryIds);
        }

        $count = $query->update([
            'status' => $status,
            'updated_at' => now(),
        ]);

        return ['success' => true, 'updated_count' => $count, 'status' => $status];
    }

    /**
     * RPC Method: Duyệt hàng loạt gian hàng (Manager/Admin)
     */
    protected function batchApproveStalls($params, $user = null)
    {
        if (!$user || !in_array($user->role, ['admin', 'manager'])) {
            throw new \InvalidArgumentException('Forbidden: Access denied.');
        }

        $ids = $params['ids'] ?? null;
        $status = $params['status'] ?? 'active';

        if (!is_array($ids)) {
            throw new \InvalidArgumentException('Invalid parameters.');
        }

        $count = Eatery::on('mysql_market')->whereIn('id', $ids)->update(['status' => $status]);

        return ['success' => true, 'approved_count' => $count];
    }

    protected function formatErrorResponse($id, $code, $message)
    {
        if ($id === null) return null;
        return [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $code,
                'message' => $message
            ],
            'id' => $id
        ];
    }
}
