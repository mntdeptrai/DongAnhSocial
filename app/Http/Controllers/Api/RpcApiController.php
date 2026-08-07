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

        try {
            switch ($method) {
                case 'ping':
                    $result = ['status' => 'pong', 'timestamp' => time()];
                    break;

                case 'batchApproveVideos':
                    $result = $this->batchApproveVideos($params);
                    break;

                case 'batchUpdatePrices':
                    $result = $this->batchUpdatePrices($params);
                    break;

                case 'getEateryDetails':
                    $result = $this->getEateryDetails($params);
                    break;

                case 'batchUpdateOrderStatus':
                    $result = $this->batchUpdateOrderStatus($params);
                    break;

                case 'batchApproveStalls':
                    $result = $this->batchApproveStalls($params);
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
            return $this->formatErrorResponse($id, -32000, 'Internal error: ' . $e->getMessage());
        }
    }

    /**
     * RPC Method: Duyệt hàng loạt Video đánh giá
     */
    protected function batchApproveVideos($params)
    {
        $ids = $params['ids'] ?? null;
        if (!is_array($ids)) {
            throw new \InvalidArgumentException('Invalid params: "ids" must be an array.');
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
    protected function batchUpdatePrices($params)
    {
        $updates = $params['updates'] ?? null; // [{id: 1, price: 35000}, ...]
        if (!is_array($updates)) {
            throw new \InvalidArgumentException('Invalid params: "updates" must be an array.');
        }

        $count = 0;
        DB::beginTransaction();
        try {
            foreach ($updates as $update) {
                if (isset($update['id']) && isset($update['price'])) {
                    $dish = Dish::find($update['id']);
                    if ($dish) {
                        $dish->update(['price' => $update['price']]);
                        $count++;
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return ['success' => true, 'updated_count' => $count];
    }

    /**
     * RPC Method: Lấy thông tin chi tiết nhanh của cơ sở
     */
    protected function getEateryDetails($params)
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            throw new \InvalidArgumentException('Invalid params: "id" is required.');
        }

        $eatery = Eatery::find($id);
        if (!$eatery) {
            throw new \InvalidArgumentException('Eatery not found.');
        }

        return [
            'name' => $eatery->name,
            'rating' => $eatery->rating,
            'address' => $eatery->address
        ];
    }

    /**
     * RPC Method: Cập nhật hàng loạt trạng thái đơn hàng (Seller)
     */
    protected function batchUpdateOrderStatus($params)
    {
        $orderIds = $params['order_ids'] ?? null;
        $status = $params['status'] ?? 'confirmed';

        if (!is_array($orderIds)) {
            throw new \InvalidArgumentException('Invalid params: "order_ids" must be an array.');
        }

        $count = DB::table('orders')->whereIn('id', $orderIds)->update([
            'status' => $status,
            'updated_at' => now(),
        ]);

        return ['success' => true, 'updated_count' => $count, 'status' => $status];
    }

    /**
     * RPC Method: Duyệt hàng loạt gian hàng (Manager)
     */
    protected function batchApproveStalls($params)
    {
        $ids = $params['ids'] ?? null;
        $status = $params['status'] ?? 'active';

        if (!is_array($ids)) {
            throw new \InvalidArgumentException('Invalid params: "ids" must be an array.');
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
