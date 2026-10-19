<?php

namespace App\Services;

use App\Contracts\YouTubeAuthServiceInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class YouTubeAuthService implements YouTubeAuthServiceInterface
{
    /**
     * Build Google YouTube OAuth authorization URL.
     */
    public function getAuthUrl(string $redirectUri): string
    {
        $clientId = config('services.youtube.client_id');
        if (empty($clientId)) {
            throw new RuntimeException('YOUTUBE_CLIENT_ID chưa được cấu hình trong file .env');
        }

        return "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
            'client_id'              => $clientId,
            'redirect_uri'           => $redirectUri,
            'response_type'          => 'code',
            'scope'                  => 'https://www.googleapis.com/auth/youtube https://www.googleapis.com/auth/youtube.upload https://www.googleapis.com/auth/youtube.readonly https://www.googleapis.com/auth/youtube.force-ssl',
            'access_type'            => 'offline',
            'prompt'                 => 'consent',
            'include_granted_scopes' => 'true',
        ]);
    }

    /**
     * Exchange authorization code for tokens and persist them.
     */
    public function handleCallback(string $code, string $redirectUri): array
    {
        $clientId     = config('services.youtube.client_id');
        $clientSecret = config('services.youtube.client_secret');

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code',
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Google từ chối cấp token: ' . $response->body());
        }

        $data = $response->json();
        $refreshToken = $data['refresh_token'] ?? null;

        // Lưu token vào storage/app/youtube_token.json
        $tokenPath = storage_path('app/youtube_token.json');
        File::ensureDirectoryExists(dirname($tokenPath));
        File::put($tokenPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Cập nhật refresh token vào .env nếu có
        $envPath = base_path('.env');
        if ($refreshToken && file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            if (str_contains($envContent, 'YOUTUBE_REFRESH_TOKEN=')) {
                $envContent = preg_replace('/YOUTUBE_REFRESH_TOKEN=.*/', "YOUTUBE_REFRESH_TOKEN=\"{$refreshToken}\"", $envContent);
            } else {
                $envContent .= "\nYOUTUBE_REFRESH_TOKEN=\"{$refreshToken}\"\n";
            }
            File::put($envPath, $envContent);
        }

        return $data;
    }
}
