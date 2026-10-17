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
        // Áp dụng CheckUserStatus, HandleInertiaRequests & OptimizeResponseMiddleware cho toàn bộ web routes
        $middleware->web(append: [
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\OptimizeResponseMiddleware::class,
        ]);

        // Thêm session & cookie middleware vào API group
        // để middleware 'auth' có thể đọc web session (session-based auth)
        $middleware->api(prepend: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ], append: [
            \App\Http\Middleware\OptimizeResponseMiddleware::class,
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
        // 🛡️ SECURITY & PRIVACY: Intercept Database Query Exceptions & PDO Errors
        // Prevents raw SQL, table names, host IPs & internal schema from leaking to users
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            \Illuminate\Support\Facades\Log::error('Database Query Error: ' . $e->getMessage(), [
                'sql' => $e->getSql(),
                'url' => $request->fullUrl(),
            ]);

            $friendlyMessage = 'Đã có lỗi xảy ra trong quá trình xử lý. Vui lòng thử lại sau.';

            if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $friendlyMessage,
                ], 500);
            }

            // Tránh vòng lặp redirect vô hạn nếu lỗi xảy ra khi truy cập trang web qua GET
            if ($request->isMethod('GET') && (url()->previous() === $request->fullUrl() || empty(url()->previous()))) {
                return response()->view('errors.500', ['message' => $friendlyMessage], 500);
            }

            return back()->withInput()->with('error', $friendlyMessage);
        });

        $exceptions->render(function (\PDOException $e, $request) {
            \Illuminate\Support\Facades\Log::error('PDO Exception: ' . $e->getMessage());

            $friendlyMessage = 'Hệ thống đang bảo trì hoặc gặp sự cố kết nối. Vui lòng thử lại sau ít phút.';

            if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $friendlyMessage,
                ], 500);
            }

            if ($request->isMethod('GET') && (url()->previous() === $request->fullUrl() || empty(url()->previous()))) {
                return response()->view('errors.500', ['message' => $friendlyMessage], 500);
            }

            return back()->withInput()->with('error', $friendlyMessage);
        });

        // Chặn toàn bộ Unhandled Exceptions: Không bao giờ lộ stack trace cho người dùng
        $exceptions->render(function (\Throwable $e, $request) {
            // Không can thiệp vào các response do validation hoặc authentication đã xử lý chuẩn
            if ($e instanceof \Illuminate\Validation\ValidationException ||
                $e instanceof \Illuminate\Auth\AuthenticationException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return null;
            }

            \Illuminate\Support\Facades\Log::error('Unhandled Exception: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url'  => $request->fullUrl(),
            ]);

            $friendlyMessage = 'Đã có sự cố xảy ra. Vui lòng thử lại sau ít phút.';

            // Luôn trả JSON chung chung cho API / AJAX request
            if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $friendlyMessage,
                ], 500);
            }

            // Đối với Web: Tránh vòng lặp redirect nếu truy cập trực tiếp bằng GET
            if ($request->isMethod('GET') && (url()->previous() === $request->fullUrl() || empty(url()->previous()))) {
                return response()->view('errors.500', ['message' => $friendlyMessage], 500);
            }

            return back()->withInput()->with('error', $friendlyMessage);
        });
    })->create();
