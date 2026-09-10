<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            if ($request->expectsJson()) {
                abort(401, 'Chưa xác thực tài khoản!');
            }
            return redirect('/auth/login')->with('error', 'Vui lòng đăng nhập để tiếp tục!');
        }

        if (! in_array($request->user()->role, $roles)) {
            if ($request->expectsJson()) {
                abort(403, 'Bạn không có quyền truy cập trang quản lý này!');
            }

            $userRole = $request->user()->role;
            if (($userRole === 'admin' || $userRole === 'manager') && !$request->is('admin/*')) {
                return redirect('/admin/dashboard')->with('error', 'Bạn không có quyền truy cập chức năng này!');
            }
            if ($userRole === 'health_station' && !$request->is('health-station/*')) {
                return redirect('/health-station/dashboard')->with('error', 'Bạn không có quyền truy cập chức năng này!');
            }
            if ($userRole === 'seller' && !$request->is('seller/*')) {
                return redirect('/seller/dashboard')->with('error', 'Bạn không có quyền truy cập chức năng này!');
            }
            if (in_array($userRole, ['hkd', 'dn', 'business']) && !$request->is('hkd/*')) {
                return redirect('/hkd/dashboard')->with('error', 'Bạn không có quyền truy cập chức năng này!');
            }

            abort(403, 'Bạn không có quyền truy cập khu vực này!');
        }

        return $next($request);
    }
}
