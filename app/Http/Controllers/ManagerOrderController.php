<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\EateryApiService;

/**
 * ManagerOrderController — Quản lý Đơn Hàng toàn Chợ
 * Dành cho vai trò: Manager (Ban Quản Lý Chợ) và Admin
 * Query: Tất cả đơn hàng thuộc eatery_id của chợ mà Manager đang quản lý
 */
class ManagerOrderController extends Controller
{
    /**
     * Xác minh quyền Manager/Admin
     */
    private function verifyManager()
    {
        $role = session('user_role') ?: (Auth::user() ? Auth::user()->role : null);
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền truy cập trang Quản lý Đơn hàng Chợ!');
        }
    }

    /**
     * Lấy eatery_id của chợ mà Manager đang quản lý
     */
    private function getManagerMarketContext()
    {
        $user = Auth::user();
        $role = session('user_role') ?: ($user ? $user->role : null);

        $eatery = null;

        if ($role === 'manager') {
            // Manager: lấy chợ mà họ quản lý
            $eatery = Eatery::where('user_id', $user->id)->first();
            if (!$eatery) {
                try {
                    $eatery = DB::connection('mysql_market')->table('eateries')->where('user_id', $user->id)->first();
                } catch (\Throwable $e) {}
            }
        } elseif ($role === 'admin') {
            // Admin: lấy tất cả (tenant_id từ session nếu có)
            $tenantId = session('tenant_id');
            if ($tenantId) {
                $eatery = DB::table('eateries')->where('id', $tenantId)->first();
            }
        }

        return [
            'eatery' => $eatery,
            'eateryId' => $eatery ? $eatery->id : null,
            'marketName' => $eatery ? $eatery->name : 'Tất cả Chợ',
        ];
    }

    /**
     * Trang danh sách đơn hàng toàn chợ
     * GET /admin/orders
     */
    public function orders()
    {
        $this->verifyManager();
        $ctx = $this->getManagerMarketContext();

        $orders = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            $query = DB::table('orders');

            if ($ctx['eateryId']) {
                $query->where('eatery_id', $ctx['eateryId']);
            }

            $orders = $query->latest()->paginate(25);

            $orderIds = $orders->pluck('id');
            if ($orderIds->isNotEmpty()) {
                $allItems = DB::table('order_items')
                    ->whereIn('order_id', $orderIds)
                    ->get()
                    ->groupBy('order_id');

                $orders->each(function ($ord) use ($allItems) {
                    $ord->items = $allItems->get($ord->id, collect());
                });
            }
        }

        return view('manager.orders', array_merge($ctx, ['orders' => $orders]));
    }

    /**
     * Xem chi tiết đơn hàng cho BQL Chợ
     * GET /admin/orders/{id}
     */
    public function showOrder($id)
    {
        $this->verifyManager();
        $ctx = $this->getManagerMarketContext();

        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) {
            return redirect()->route('admin.orders.index')->with('error', 'Đơn hàng không tồn tại!');
        }

        // Manager chỉ được cập nhật đơn hàng thuộc chợ của mình
        $role = session('user_role');
        if ($role === 'manager' && $ctx['eateryId'] && $order->eatery_id != $ctx['eateryId']) {
            abort(403, 'Đơn hàng không thuộc chợ bạn đang quản lý!');
        }

        $items = DB::table('order_items')->where('order_id', $order->id)->get();
        $order->items = $items;

        return view('manager.order-detail', array_merge($ctx, ['order' => $order]));
    }

    /**
     * API JSON: real-time polling cho trang quản lý đơn hàng BQL Chợ
     * GET /admin/api/orders
     */
    public function ordersJson()
    {
        $this->verifyManager();
        $ctx = $this->getManagerMarketContext();

        $rawOrders = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            $query = DB::table('orders');

            if ($ctx['eateryId']) {
                $query->where('eatery_id', $ctx['eateryId']);
            }

            $rawOrders = $query->latest()->limit(50)->get();

            $orderIds = $rawOrders->pluck('id');
            if ($orderIds->isNotEmpty()) {
                $allItems = DB::table('order_items')
                    ->whereIn('order_id', $orderIds)
                    ->get()
                    ->groupBy('order_id');

                $rawOrders = $rawOrders->map(function ($ord) use ($allItems) {
                    $ord->items = $allItems->get($ord->id, collect())->values();
                    return $ord;
                });
            }
        }

        return response()->json([
            'market'    => $ctx['marketName'],
            'orders'    => $rawOrders->values(),
            'polled_at' => now()->toDateTimeString(),
            'count'     => $rawOrders->count(),
        ]);
    }

    /**
     * Cập nhật trạng thái đơn hàng cho BQL Chợ
     * PUT /admin/orders/{id}/status
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $this->verifyManager();
        $request->validate([
            'status' => 'required|string|in:confirmed,preparing,ready,completed,cancelled'
        ], [
            'status.in' => 'Trạng thái không hợp lệ! Chỉ chấp nhận: confirmed, preparing, ready, completed, cancelled.'
        ]);

        if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            // Kiểm tra đơn hàng thuộc chợ đang quản lý
            $ctx = $this->getManagerMarketContext();
            $order = DB::table('orders')->where('id', $id)->first();

            if (!$order) {
                return redirect()->back()->with('error', 'Đơn hàng không tồn tại!');
            }

            // Manager chỉ được cập nhật đơn hàng thuộc chợ của mình
            $role = session('user_role');
            if ($role === 'manager' && $ctx['eateryId'] && $order->eatery_id != $ctx['eateryId']) {
                abort(403, 'Đơn hàng không thuộc chợ bạn đang quản lý!');
            }

            DB::table('orders')->where('id', $id)->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);
        }

        $statusLabels = [
            'confirmed' => '✅ Đã xác nhận đơn hàng',
            'processing' => '🔄 Đang xử lý đơn hàng',
            'completed' => '🎉 Đã hoàn thành đơn hàng',
            'cancelled' => '❌ Đã hủy đơn hàng',
        ];

        $msg = $statusLabels[$request->status] ?? 'Đã cập nhật trạng thái đơn hàng';
        return redirect()->back()->with('success', $msg);
    }
}
