<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Services\EateryApiService;

class TenantAuthMiddleware
{
    /**
     * Handle an incoming request for Multi-Tenant Isolation & Authorization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect('/auth/login')->with('error', 'Vui lòng đăng nhập để xác thực Tenant!');
        }

        $role = $user->role ?? session('user_role');
        $userId = $user->id ?? session('user_id');

        // 1. Super Admin: Toàn quyền truy cập mọi Tenant
        if ($role === 'admin') {
            session([
                'tenant_id' => null,
                'tenant_type' => 'master',
                'stall_name' => null,
            ]);
            return $next($request);
        }

        // 2. Ban Quản lý Chợ (Market Tenant Manager): Scoped to single Market
        if ($role === 'manager') {
            $market = Eatery::where('user_id', $userId)->first();
            if (!$market) {
                // Fallback attempt: find market where slug or id matches
                try {
                    $market = DB::connection('mysql_market')->table('eateries')->where('user_id', $userId)->first();
                } catch (\Throwable $e) {}
            }

            $tenantId = $market ? $market->id : null;

            session([
                'tenant_id' => $tenantId,
                'tenant_type' => 'market',
                'tenant_name' => $market ? $market->name : 'Ban Quản lý Chợ',
                'stall_name' => null,
            ]);

            $request->attributes->set('tenant_id', $tenantId);
            $request->attributes->set('tenant_type', 'market');

            return $next($request);
        }

        // 3. Chủ Gian hàng (Stall Tenant Vendor): Scoped to single Stall within Market
        if ($role === 'seller') {
            $stallProduct = null;

            // 3a. Ưu tiên stall_id từ bảng users
            if ($user->stall_id) {
                $stallProduct = DB::connection('mysql_market')->table('ocop_products')
                    ->where('id', $user->stall_id)
                    ->first();
            }

            // 3b. Tìm theo eatery_id được gắn cho user
            if (!$stallProduct && $user->eatery_id) {
                $stallProduct = DB::connection('mysql_market')->table('ocop_products')
                    ->where('eatery_id', $user->eatery_id)
                    ->first();
            }

            // 3c. Tìm theo seller_phone
            if (!$stallProduct && !empty($user->phone)) {
                $stallProduct = DB::connection('mysql_market')->table('ocop_products')
                    ->where('seller_phone', $user->phone)
                    ->first();
            }

            $tenantId = $stallProduct ? $stallProduct->eatery_id : ($user->eatery_id ?: null);
            $stallName = $stallProduct ? $stallProduct->stall_name : ($user->stall_name ?? 'Gian hàng số');

            session([
                'tenant_id' => $tenantId,
                'tenant_type' => 'stall',
                'stall_name' => $stallName,
                'seller_name' => $user->name,
                'seller_phone' => $user->phone,
            ]);

            $request->attributes->set('tenant_id', $tenantId);
            $request->attributes->set('stall_name', $stallName);
            $request->attributes->set('tenant_type', 'stall');

            return $next($request);
        }

        return $next($request);
    }
}
