<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Áp dụng CheckUserStatus & HandleInertiaRequests cho toàn bộ web routes
        $middleware->web(append: [
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Thêm session & cookie middleware vào API group
        // để middleware 'auth' có thể đọc web session (session-based auth)
        $middleware->api(prepend: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'tenant.auth' => \App\Http\Middleware\TenantAuthMiddleware::class,
        ]);

        // 🛡️ Ngoại lệ CSRF cho đăng xuất để tránh lỗi 419 Page Expired khi Session hết hạn
        $middleware->validateCsrfTokens(except: [
            'auth/logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 🛡️ SECURITY AUDIT FIX: Intercept Database Query Exceptions & PDO Errors
        // Prevents raw SQL, table names, host IPs & internal schema from leaking to users
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            \Illuminate\Support\Facades\Log::error('Database Query Error: ' . $e->getMessage(), [
                'sql' => $e->getSql(),
                'url' => $request->fullUrl(),
            ]);

            $friendlyMessage = 'Đã xảy ra lỗi khi thao tác dữ liệu. Vui lòng kiểm tra lại thông tin nhập hoặc thử lại sau.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $friendlyMessage,
                ], 500);
            }

            return back()->with('error', $friendlyMessage);
        });

        $exceptions->render(function (\PDOException $e, $request) {
            \Illuminate\Support\Facades\Log::error('PDO Exception: ' . $e->getMessage());

            $friendlyMessage = 'Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $friendlyMessage,
                ], 500);
            }

            return back()->with('error', $friendlyMessage);
        });

        // Intercept uncaught exceptions when debug mode is disabled
        $exceptions->render(function (\Throwable $e, $request) {
            if (!config('app.debug')) {
                \Illuminate\Support\Facades\Log::error('Unhandled Exception: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                $friendlyMessage = 'Hệ thống đã ghi nhận sự cố. Vui lòng thử lại sau.';

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $friendlyMessage,
                    ], 500);
                }
            }
        });
    })->create();
