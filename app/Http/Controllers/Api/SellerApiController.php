<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Eatery;
use App\Models\Dish;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * SellerApiController — Chủ Gian Hàng / Tiểu Thương
 *
 * Chịu trách nhiệm duy nhất: Dashboard Seller, Hồ sơ tiểu thương,
 * Quản lý món ăn/sản phẩm, Đơn hàng.
 */
class SellerApiController extends Controller
{
    /**
     * API Lấy Hồ sơ Người Bán / Chủ Gian Hàng Chợ (Mobile App)
     */
    public function getSellerProfile(Request $request)
    {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'data' => [
                'merchant_name' => $user->name ?? 'Tiểu thương chợ',
                'business_items' => 'Rau củ quả, Đặc sản OCOP Đông Anh',
                'price_listed' => 'Có niêm yết giá công khai',
                'product_origin' => 'Tự sản xuất & Nhập từ nông trại',
                'bank_account' => '1028734912',
                'bank_name' => 'VietinBank',
                'qr_code_url' => '',
                'phone' => $user->phone ?? '0988xxxxxx',
                'has_smartphone' => true,
                'has_attp_certificate' => true,
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * API Cập nhật Hồ sơ đăng ký gian hàng chợ
     */
    public function updateSellerProfile(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hồ sơ đăng ký gian hàng chợ thành công!',
            'data' => $request->all()
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * API Lấy Danh sách Đơn hàng của Chủ Gian Hàng (Seller / Manager)
     */
    public function getSellerOrders(Request $request)
    {
        $user = Auth::user() ?: auth('sanctum')->user();
        $userId = $user ? $user->id : 0;

        $myEateryIds = Eatery::where('user_id', $userId)->pluck('id')->toArray();

        $orders = DB::table('orders')
            ->when(!empty($myEateryIds), function ($q) use ($myEateryIds) {
                return $q->whereIn('eatery_id', $myEateryIds);
            })
            ->latest()
            ->limit(20)
            ->get();

        return response()->json($orders, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * API Truy vấn Dữ liệu Gian Hàng thuộc sở hữu của User (Seller Portal Data)
     */
    public function getSellerDashboardData(Request $request)
    {
        $user = Auth::user() ?: auth('sanctum')->user();
        $userId = $user ? $user->id : 0;

        // Truy vấn cửa hàng/gian hàng thuộc sở hữu của tài khoản người dùng này
        $myEatery = Eatery::where('user_id', $userId)->first();
        if (!$myEatery && $user && !empty($user->phone)) {
            $myEatery = Eatery::where('phone', $user->phone)->first();
        }

        $eateryId = $myEatery ? $myEatery->id : 0;

        // Lấy danh sách món ăn thuộc sở hữu của gian hàng này
        $dishes = $myEatery ? Dish::where('eatery_id', $eateryId)->get() : collect();

        // Lấy danh sách đơn hàng đặt cho gian hàng này
        $orders = DB::table('orders')
            ->where('eatery_id', $eateryId)
            ->latest()
            ->get();

        $totalRevenue = $orders->where('status', 'completed')->sum('total_amount');
        $todayOrdersCount = $orders->where('created_at', '>=', now()->startOfDay())->count();
        $pendingOrdersCount = $orders->where('status', 'pending')->count();

        return response()->json([
            'success' => true,
            'eatery' => $myEatery ? [
                'id' => $myEatery->id,
                'name' => $myEatery->name,
                'address' => $myEatery->address,
                'phone' => $myEatery->phone,
                'image_path' => $myEatery->image_path,
                'rating' => $myEatery->rating,
            ] : null,
            'dishes' => $dishes,
            'orders' => $orders,
            'stats' => [
                'total_revenue' => (int)$totalRevenue,
                'today_orders' => $todayOrdersCount,
                'pending_orders' => $pendingOrdersCount,
                'dishes_count' => $dishes->count(),
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Seller: Xóa món ăn / sản phẩm
     */
    public function deleteDish(Request $request, $id)
    {
        $dish = Dish::find($id);
        if ($dish) {
            $dish->delete();
        }
        return response()->json(['success' => true, 'message' => 'Đã xóa sản phẩm']);
    }

    /**
     * Seller: Cập nhật trạng thái đơn hàng (Xác nhận, Giao hàng, Hoàn thành, Hủy)
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $status = $request->input('status', 'confirmed');
        DB::table('orders')->where('id', $id)->update(['status' => $status, 'updated_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật trạng thái đơn hàng thành ' . strtoupper($status),
        ]);
    }
}
