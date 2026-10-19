<?php

namespace App\Services;

use App\Contracts\ManagerOrderServiceInterface;
use App\Models\Eatery;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ManagerOrderService implements ManagerOrderServiceInterface
{
    /**
     * Get market context for the current manager/admin user.
     */
    public function getManagerMarketContext(?User $user, ?string $sessionRole, ?int $tenantId): array
    {
        $role = $sessionRole ?: ($user ? $user->role : null);
        $eatery = null;

        if ($role === 'manager' && $user) {
            $eatery = Eatery::where('user_id', $user->id)->first();
            if (!$eatery) {
                try {
                    $eatery = DB::connection('mysql_market')->table('eateries')->where('user_id', $user->id)->first();
                } catch (\Throwable $e) {}
            }
        } elseif ($role === 'admin' && $tenantId) {
            $eatery = DB::table('eateries')->where('id', $tenantId)->first();
        }

        return [
            'eatery' => $eatery,
            'eateryId' => $eatery ? $eatery->id : null,
            'marketName' => $eatery ? $eatery->name : 'Tất cả Chợ',
        ];
    }

    /**
     * Paginate orders for the specified market or all markets.
     */
    public function getOrdersPaginated(?int $eateryId, int $perPage = 25): LengthAwarePaginator
    {
        if (!Schema::hasTable('orders')) {
            return new ConcretePaginator([], 0, $perPage);
        }

        $query = DB::table('orders');

        if ($eateryId) {
            $query->where('eatery_id', $eateryId);
        }

        $orders = $query->latest()->paginate($perPage);

        $orderIds = $orders->pluck('id');
        if ($orderIds->isNotEmpty() && Schema::hasTable('order_items')) {
            $allItems = DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->groupBy('order_id');

            $orders->each(function ($ord) use ($allItems) {
                $ord->items = $allItems->get($ord->id, collect());
            });
        }

        return $orders;
    }

    /**
     * Get single order details with items, verifying manager market ownership.
     */
    public function getOrderDetails(int|string $orderId, ?int $managerEateryId, ?string $userRole): ?object
    {
        if (!Schema::hasTable('orders')) {
            return null;
        }

        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) {
            return null;
        }

        if ($userRole === 'manager' && $managerEateryId && $order->eatery_id != $managerEateryId) {
            abort(403, 'Đơn hàng không thuộc chợ bạn đang quản lý!');
        }

        if (Schema::hasTable('order_items')) {
            $order->items = DB::table('order_items')->where('order_id', $order->id)->get();
        } else {
            $order->items = collect();
        }

        return $order;
    }

    /**
     * Get recent orders as JSON collection for polling.
     */
    public function getOrdersJson(?int $eateryId, int $limit = 50): Collection
    {
        if (!Schema::hasTable('orders')) {
            return collect();
        }

        $query = DB::table('orders');

        if ($eateryId) {
            $query->where('eatery_id', $eateryId);
        }

        $rawOrders = $query->latest()->limit($limit)->get();

        $orderIds = $rawOrders->pluck('id');
        if ($orderIds->isNotEmpty() && Schema::hasTable('order_items')) {
            $allItems = DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->groupBy('order_id');

            $rawOrders = $rawOrders->map(function ($ord) use ($allItems) {
                $ord->items = $allItems->get($ord->id, collect())->values();
                return $ord;
            });
        }

        return $rawOrders;
    }

    /**
     * Update order status with security verification.
     */
    public function updateOrderStatus(int|string $orderId, string $status, ?int $managerEateryId, ?string $userRole): bool
    {
        if (!Schema::hasTable('orders')) {
            return false;
        }

        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) {
            return false;
        }

        if ($userRole === 'manager' && $managerEateryId && $order->eatery_id != $managerEateryId) {
            abort(403, 'Đơn hàng không thuộc chợ bạn đang quản lý!');
        }

        DB::table('orders')->where('id', $orderId)->update([
            'status' => $status,
            'updated_at' => now(),
        ]);

        return true;
    }
}
