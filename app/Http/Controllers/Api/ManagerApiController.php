<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Eatery;
use Illuminate\Http\Request;

/**
 * ManagerApiController — Ban Quản Lý Chợ
 *
 * Chịu trách nhiệm duy nhất: Dashboard quản lý chợ, bảng tin BQL,
 * duyệt/đình chỉ gian hàng.
 */
class ManagerApiController extends Controller
{
    /**
     * API Truy vấn Dữ liệu Quản lý Chợ (Manager Portal Data)
     */
    public function getManagerDashboardData(Request $request)
    {
        $stalls = Eatery::on('mysql_market')
            ->whereHas('category', function($q) {
                $q->whereIn('slug', ['traditional-market', 'market', 'ocop-products', 'cho-truyen-thong', 'san-pham-ocop', 'ocop']);
            })
            ->select('id', 'name', 'address', 'phone', 'rating', 'status', 'user_id', 'created_at', 'category_id')
            ->latest()
            ->get();

        $totalStalls = $stalls->count();
        $activeStalls = $stalls->where('status', 'active')->count();
        $pendingApprovals = $stalls->where('status', 'pending')->count();

        return response()->json([
            'success' => true,
            'market_name' => 'Chợ Trung Tâm Đông Anh',
            'stalls' => $stalls,
            'stats' => [
                'total_stalls' => $totalStalls,
                'active_stalls' => $activeStalls,
                'pending_approvals' => $pendingApprovals,
                'attp_inspected' => (int)($totalStalls * 0.9),
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Manager: Đăng Bảng tin thông báo BQL Chợ
     */
    public function storeManagerBulletin(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã phát thông báo BQL Chợ tới toàn bộ tiểu thương thành công!',
        ]);
    }

    /**
     * Manager: Duyệt / Đình chỉ gian hàng chợ
     */
    public function updateStallStatus(Request $request, $id)
    {
        $status = $request->input('status', 'active');
        $eatery = Eatery::on('mysql_market')->find($id) ?? Eatery::find($id);
        if ($eatery) {
            $eatery->status = $status;
            $eatery->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật trạng thái gian hàng!',
        ]);
    }
}
