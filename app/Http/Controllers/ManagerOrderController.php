<?php

namespace App\Http\Controllers;

use App\Contracts\ManagerOrderServiceInterface;
use App\Http\Requests\UpdateOrderStatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * ManagerOrderController — Quản lý Đơn Hàng toàn Chợ
 * Dành cho vai trò: Manager (Ban Quản Lý Chợ) và Admin
 */
class ManagerOrderController extends Controller
{
    public function __construct(
        protected ManagerOrderServiceInterface $managerOrderService
    ) {}

    /**
     * Xác minh quyền Manager/Admin
     */
    private function verifyManager(): void
    {
        $role = session('user_role') ?: (Auth::user() ? Auth::user()->role : null);
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền truy cập trang Quản lý Đơn hàng Chợ!');
        }
    }

    /**
     * Helper lấy context Chợ của Manager
     */
    private function getContext(): array
    {
        return $this->managerOrderService->getManagerMarketContext(
            Auth::user(),
            session('user_role'),
            session('tenant_id')
        );
    }

    /**
     * Trang danh sách đơn hàng toàn chợ
     * GET /admin/orders
     */
    public function orders(): View
    {
        $this->verifyManager();
        $ctx = $this->getContext();
        $orders = $this->managerOrderService->getOrdersPaginated($ctx['eateryId']);

        return view('manager.orders', array_merge($ctx, ['orders' => $orders]));
    }

    /**
     * Xem chi tiết đơn hàng cho BQL Chợ
     * GET /admin/orders/{id}
     */
    public function showOrder(int|string $id): View|RedirectResponse
    {
        $this->verifyManager();
        $ctx = $this->getContext();
        $role = session('user_role');

        $order = $this->managerOrderService->getOrderDetails($id, $ctx['eateryId'], $role);
        if (!$order) {
            return redirect()->route('admin.orders.index')->with('error', 'Đơn hàng không tồn tại!');
        }

        return view('manager.order-detail', array_merge($ctx, ['order' => $order]));
    }

    /**
     * API JSON: real-time polling cho trang quản lý đơn hàng BQL Chợ
     * GET /admin/api/orders
     */
    public function ordersJson(): JsonResponse
    {
        $this->verifyManager();
        $ctx = $this->getContext();
        $orders = $this->managerOrderService->getOrdersJson($ctx['eateryId']);

        return response()->json([
            'market'    => $ctx['marketName'],
            'orders'    => $orders->values(),
            'polled_at' => now()->toDateTimeString(),
            'count'     => $orders->count(),
        ]);
    }

    /**
     * Cập nhật trạng thái đơn hàng cho BQL Chợ
     * PUT /admin/orders/{id}/status
     */
    public function updateOrderStatus(UpdateOrderStatusRequest $request, int|string $id): RedirectResponse
    {
        $this->verifyManager();
        $ctx = $this->getContext();
        $role = session('user_role');

        $updated = $this->managerOrderService->updateOrderStatus($id, $request->validated('status'), $ctx['eateryId'], $role);

        if (!$updated) {
            return redirect()->back()->with('error', 'Đơn hàng không tồn tại!');
        }

        $statusLabels = [
            'confirmed'  => '✅ Đã xác nhận đơn hàng',
            'preparing'  => '🍳 Đang chuẩn bị món',
            'ready'      => '📦 Đã chuẩn bị xong, sẵn sàng giao',
            'processing' => '🔄 Đang xử lý đơn hàng',
            'completed'  => '🎉 Đã hoàn thành đơn hàng',
            'cancelled'  => '❌ Đã hủy đơn hàng',
        ];

        $msg = $statusLabels[$request->validated('status')] ?? 'Đã cập nhật trạng thái đơn hàng';
        return redirect()->back()->with('success', $msg);
    }
}
