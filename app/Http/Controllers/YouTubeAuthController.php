<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class YouTubeAuthController extends Controller
{
    /**
     * Chuyển hướng người dùng sang trang cấp quyền Google YouTube
     */
    public function redirect(Request $request)
    {
        $clientId = config('services.youtube.client_id') ?: env('YOUTUBE_CLIENT_ID');
        $configuredRedirect = config('services.youtube.redirect_uri') ?: env('YOUTUBE_REDIRECT_URI');
        
        $currentHost = preg_replace('/^http:\/\//', 'https://', $request->getSchemeAndHttpHost());
        $redirectUri = !empty($configuredRedirect) ? $configuredRedirect : ($currentHost . '/youtube/callback');

        if (empty($clientId)) {
            return response()->json([
                'error' => 'YOUTUBE_CLIENT_ID chưa được cấu hình trong file .env'
            ], 500);
        }

        $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
            'client_id'             => $clientId,
            'redirect_uri'          => $redirectUri,
            'response_type'         => 'code',
            'scope'                 => 'https://www.googleapis.com/auth/youtube https://www.googleapis.com/auth/youtube.upload https://www.googleapis.com/auth/youtube.readonly https://www.googleapis.com/auth/youtube.force-ssl',
            'access_type'           => 'offline',
            'prompt'                => 'consent',
            'include_granted_scopes'=> 'true',
        ]);

        return redirect()->away($authUrl);
    }

    /**
     * Nhận callback từ Google và tự động lưu refresh_token
     */
    public function callback(Request $request)
    {
        $code = $request->get('code');
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

        $clientId     = config('services.youtube.client_id') ?: env('YOUTUBE_CLIENT_ID');
        $clientSecret = config('services.youtube.client_secret') ?: env('YOUTUBE_CLIENT_SECRET');
        $configuredRedirect = config('services.youtube.redirect_uri') ?: env('YOUTUBE_REDIRECT_URI');
        $currentHost  = preg_replace('/^http:\/\//', 'https://', $request->getSchemeAndHttpHost());
        $redirectUri  = !empty($configuredRedirect) ? $configuredRedirect : ($currentHost . '/youtube/callback');

        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code'          => $code,
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri'  => $redirectUri,
                'grant_type'    => 'authorization_code',
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Google từ chối cấp token: ' . $response->body(),
                ], 400);
            }

            $data = $response->json();
            $refreshToken = $data['refresh_token'] ?? null;

            // Lưu token vào storage/app/youtube_token.json
            $tokenPath = storage_path('app/youtube_token.json');
            File::ensureDirectoryExists(dirname($tokenPath));
            File::put($tokenPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            // Đồng thời cập nhật hoặc thêm YOUTUBE_REFRESH_TOKEN vào .env
            $envPath = base_path('.env');
            if ($refreshToken && file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                if (str_contains($envContent, 'YOUTUBE_REFRESH_TOKEN=')) {
                    $envContent = preg_replace('/YOUTUBE_REFRESH_TOKEN=.*/', "YOUTUBE_REFRESH_TOKEN=\"{$refreshToken}\"", $envContent);
                } else {
                    $envContent .= PHP_EOL . "YOUTUBE_REFRESH_TOKEN=\"{$refreshToken}\"" . PHP_EOL;
                }
                File::put($envPath, $envContent);
            }

            // Xóa cache access token cũ để nhận token mới ngay lập tức
            \Illuminate\Support\Facades\Cache::forget('youtube_api_access_token');

            return response()->view('youtube_auth_success', [
                'hasRefresh' => !empty($refreshToken),
                'data'       => $data
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Lỗi kết nối máy chủ Google: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kiểm tra trạng thái kết nối YouTube API
     */
    public function status(Request $request)
    {
        $test = \App\Services\YouTubeService::testConnection();
        return response()->json([
            'status'     => $test['ok'] ? 'connected' : 'error',
            'details'    => $test,
            'reauth_url' => url('/youtube/auth'),
        ], $test['ok'] ? 200 : 400);
    }
}
