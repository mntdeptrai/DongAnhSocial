<?php

namespace App\Http\Controllers;

use App\Contracts\YouTubeAuthServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class YouTubeAuthController extends Controller
{
    public function __construct(
        protected YouTubeAuthServiceInterface $authService
    ) {}

    /**
     * Chuyển hướng người dùng sang trang cấp quyền Google YouTube
     */
    public function redirect(Request $request): RedirectResponse|JsonResponse
    {
        $currentHost = preg_replace('/^http:\/\//', 'https://', $request->getSchemeAndHttpHost());
        $redirectUri = config('services.youtube.redirect_uri') ?: ($currentHost . '/youtube/callback');

        try {
            $authUrl = $this->authService->getAuthUrl($redirectUri);
            return redirect()->away($authUrl);
        } catch (Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Nhận callback từ Google và tự động lưu refresh_token
     */
    public function callback(Request $request): JsonResponse
    {
        $code  = $request->get('code');
        $error = $request->get('error');

        if ($error) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Người dùng từ chối hoặc Google trả về lỗi: ' . $error,
            ], 400);
        }

        if (empty($code)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Không nhận được mã authorization code từ Google',
            ], 400);
        }

        $currentHost = preg_replace('/^http:\/\//', 'https://', $request->getSchemeAndHttpHost());
        $redirectUri = config('services.youtube.redirect_uri') ?: ($currentHost . '/youtube/callback');

        try {
            $data = $this->authService->handleCallback($code, $redirectUri);
            $refreshToken = $data['refresh_token'] ?? null;

            return response()->json([
                'status'        => 'success',
                'message'       => 'Xác thực Google YouTube thành công!',
                'refresh_token' => $refreshToken ? (substr($refreshToken, 0, 10) . '...') : 'Đã có token từ trước',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
