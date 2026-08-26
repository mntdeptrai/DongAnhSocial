<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class YouTubeAuthCommand extends Command
{
    /**
     * Tên và chữ ký của command
     *
     * @var string
     */
    protected $signature = 'youtube:auth {--code= : Mã Authorization code từ Google}';

    /**
     * Mô tả command
     *
     * @var string
     */
    protected $description = 'Hướng dẫn và tạo Refresh Token cho YouTube Data API v3 để tự động upload video';

    /**
     * Thực thi command
     */
    public function handle()
    {
        $this->info('=====================================================');
        $this->info('   GOOGLE YOUTUBE DATA API v3 - OAUTH2 SETUP HELPER  ');
        $this->info('=====================================================');

        $clientId = config('services.youtube.client_id') ?: $this->ask('Nhập Google Client ID của bạn');
        $clientSecret = config('services.youtube.client_secret') ?: $this->secret('Nhập Google Client Secret của bạn');
        $redirectUri = config('services.youtube.redirect_uri', 'https://developers.google.com/oauthplayground');

        if (empty($clientId) || empty($clientSecret)) {
            $this->error('Client ID và Client Secret không được để trống!');
            return 1;
        }

        $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
            'client_id'             => $clientId,
            'redirect_uri'          => $redirectUri,
            'response_type'         => 'code',
            'scope'                 => 'https://www.googleapis.com/auth/youtube.upload https://www.googleapis.com/auth/youtube.readonly',
            'access_type'           => 'offline',
            'prompt'                => 'consent',
            'include_granted_scopes'=> 'true',
        ]);

        $this->newLine();
        $this->line('👉 <comment>BƯỚC 1:</comment> Hãy mở đường dẫn sau trên trình duyệt để cấp quyền cho kênh YouTube:');
        $this->line("<info>{$authUrl}</info>");
        $this->newLine();

        $code = $this->option('code');
        if (empty($code)) {
            $this->line('👉 <comment>BƯỚC 2:</comment> Sau khi đăng nhập và cấp quyền Google, hãy sao chép mã Authorization Code được cấp (hoặc mã trong thanh địa chỉ URL).');
            $code = $this->ask('Dán mã Authorization Code vào đây');
        }

        if (empty($code)) {
            $this->error('Mã Authorization Code không hợp lệ!');
            return 1;
        }

        // Loại bỏ khoảng trắng hoặc format
        $code = trim($code);
        if (str_contains($code, 'code=')) {
            parse_str(parse_url($code, PHP_URL_QUERY) ?: $code, $parsed);
            $code = $parsed['code'] ?? $code;
        }

        $this->info('Đang gửi yêu cầu đổi mã sang Refresh Token...');

        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code'          => $code,
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri'  => $redirectUri,
                'grant_type'    => 'authorization_code',
            ]);

            if (!$response->successful()) {
                $this->error('Không thể lấy Token từ Google: ' . $response->body());
                return 1;
            }

            $data = $response->json();
            $refreshToken = $data['refresh_token'] ?? null;
            $accessToken = $data['access_token'] ?? null;

            if (!$refreshToken) {
                $this->warn('Google không trả về refresh_token mới (có thể do tài khoản đã được cấp quyền trước đó).');
                $this->line('Hãy thử thu hồi quyền ứng dụng trên Google Account Security rồi chạy lại lệnh với prompt=consent.');
                if ($accessToken) {
                    $this->info("Access Token tạm thời: {$accessToken}");
                }
                return 0;
            }

            $this->newLine();
            $this->info('✅ LẤY REFRESH TOKEN THÀNH CÔNG!');
            $this->line("YOUTUBE_REFRESH_TOKEN=<comment>{$refreshToken}</comment>");
            $this->newLine();

            if ($this->confirm('Bạn có muốn tự động cập nhật cấu hình vào file .env không?', true)) {
                $this->updateEnvFile([
                    'YOUTUBE_CLIENT_ID'     => $clientId,
                    'YOUTUBE_CLIENT_SECRET' => $clientSecret,
                    'YOUTUBE_REFRESH_TOKEN' => $refreshToken,
                ]);
                $this->info('Đã ghi thành công các khóa YouTube API vào file .env!');
            }

            return 0;

        } catch (\Throwable $e) {
            $this->error('Có lỗi xảy ra: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Cập nhật các biến trong file .env
     */
    protected function updateEnvFile(array $values): void
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            return;
        }

        $content = File::get($envPath);

        foreach ($values as $key => $value) {
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $content);
    }
}
