<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Luôn lấy dữ liệu mới nhất từ DB thông qua Auth::user()
            // Tránh trường hợp admin thay đổi role/status trong DB
            // nhưng session cũ của user đang online vẫn còn hiệu lực
            $user = Auth::user();

            // Chặn tài khoản bị vô hiệu hóa — buộc logout ngay lập tức
            if ($user->status === 'disabled') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/auth/login')->withErrors([
                    'email' => 'Tài khoản của bạn đã bị ban quản trị vô hiệu hóa.',
                ]);
            }

            // Luôn đồng bộ session với dữ liệu DB mới nhất
            // (bao gồm cả trường hợp role bị thay đổi từ phía Admin)
            session([
                'user_id'   => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
            ]);
        }

        return $next($request);
    }
}
