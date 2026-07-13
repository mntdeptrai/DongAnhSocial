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

            // Nếu là admin hoặc seller bị chặn ở route con (ví dụ: seller vào trang quản lý user), redirect về admin dashboard
            if (in_array($request->user()->role, ['admin', 'seller'])) {
                return redirect('/admin/dashboard')->with('error', 'Bạn không có quyền truy cập chức năng này!');
            }

            // Nếu là user thường cố tình vào khu vực quản trị, redirect về trang chủ
            return redirect('/')->with('error', 'Bạn không có quyền truy cập khu vực này!');
        }

        return $next($request);
    }
}
