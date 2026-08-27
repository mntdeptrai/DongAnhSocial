<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeService
{
    const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    const UPLOAD_URL = 'https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status';

    /**
     * Lấy Refresh Token từ cấu hình .env hoặc tệp lưu trữ oauth token
     */
    public static function getRefreshToken(): ?string
    {
        $token = config('services.youtube.refresh_token') ?: env('YOUTUBE_REFRESH_TOKEN');
        if (!empty($token)) {
            return $token;
        }

        $tokenPath = storage_path('app/youtube_token.json');
        if (file_exists($tokenPath)) {
            $data = json_decode(file_get_contents($tokenPath), true);
            if (!empty($data['refresh_token'])) {
                return $data['refresh_token'];
            }
        }

        return null;
    }

    /**
     * Kiểm tra xem cấu hình YouTube API đã sẵn sàng chưa
     */
    public static function isConfigured(): bool
    {
        $clientId = config('services.youtube.client_id') ?: env('YOUTUBE_CLIENT_ID');
        $clientSecret = config('services.youtube.client_secret') ?: env('YOUTUBE_CLIENT_SECRET');
        $refreshToken = self::getRefreshToken();

        return !empty($clientId) && !empty($clientSecret) && !empty($refreshToken);
    }

    /**
     * Lấy Access Token từ Refresh Token (tự động cache để tối ưu)
     */
    public static function getAccessToken(): ?string
    {
        if (!self::isConfigured()) {
            Log::warning('YouTubeService: Chưa cấu hình đầy đủ client_id, client_secret hoặc refresh_token trong .env / storage');
            return null;
        }

        $clientId = config('services.youtube.client_id') ?: env('YOUTUBE_CLIENT_ID');
        $clientSecret = config('services.youtube.client_secret') ?: env('YOUTUBE_CLIENT_SECRET');
        $refreshToken = self::getRefreshToken();

        return Cache::remember('youtube_api_access_token', 3300, function () use ($clientId, $clientSecret, $refreshToken) {
            try {
                $response = Http::asForm()->post(self::TOKEN_URL, [
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'refresh_token' => $refreshToken,
                    'grant_type'    => 'refresh_token',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['access_token'] ?? null;
                }

                Log::error('YouTubeService: Lỗi làm mới Access Token: ' . $response->body());
                return null;
            } catch (\Throwable $e) {
                Log::error('YouTubeService: Exception khi lấy Access Token: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Upload video trực tiếp lên Kênh YouTube thông qua Resumable Upload
     *
     * @param string|UploadedFile $video File tải lên hoặc đường dẫn file cục bộ
     * @param string $title Tiêu đề video
     * @param string $description Mô tả video
     * @param string|null $privacy Quyền riêng tư: 'public', 'unlisted', 'private'
     * @param array $tags Thẻ từ khóa
     * @return array|null Trả về mảng ['id' => ..., 'url' => ..., 'embed_url' => ...] hoặc null nếu thất bại
     */
    public static function uploadVideo($video, string $title, string $description = '', ?string $privacy = null, array $tags = []): ?array
    {
        $accessToken = self::getAccessToken();
        if (!$accessToken) {
            Log::error('YouTubeService: Không thể lấy Access Token để upload video.');
            return null;
        }

        // 1. Xác định đường dẫn file, kích thước và mime type
        $filePath = '';
        $mimeType = 'video/mp4';
        $fileSize = 0;

        if ($video instanceof UploadedFile) {
            $filePath = $video->getRealPath();
            $mimeType = $video->getMimeType() ?: 'video/mp4';
            $fileSize = $video->getSize();
        } elseif (is_string($video) && file_exists($video)) {
            $filePath = $video;
            $mimeType = mime_content_type($video) ?: 'video/mp4';
            $fileSize = filesize($video);
        } else {
            Log::error('YouTubeService: File video không hợp lệ hoặc không tồn tại.');
            return null;
        }

        if ($fileSize <= 0) {
            Log::error('YouTubeService: Kích thước file video rỗng (0 bytes).');
            return null;
        }

        $privacyStatus = $privacy ?: config('services.youtube.default_privacy', 'unlisted');

        try {
            // 2. Khởi tạo phiên Resumable Upload Session
            $metadata = [
                'snippet' => [
                    'title'       => mb_substr($title, 0, 100),
                    'description' => $description,
                    'tags'        => $tags ?: ['DongAnh', 'Social', 'Discovery'],
                    'categoryId'  => '22', // People & Blogs
                ],
                'status' => [
                    'privacyStatus'           => $privacyStatus,
                    'selfDeclaredMadeForKids' => false,
                ]
            ];

            $initResponse = Http::withHeaders([
                'Authorization'          => 'Bearer ' . $accessToken,
                'Content-Type'           => 'application/json; charset=UTF-8',
                'X-Upload-Content-Type'   => $mimeType,
                'X-Upload-Content-Length' => (string)$fileSize,
            ])->withBody(json_encode($metadata), 'application/json')
              ->post(self::UPLOAD_URL);

            if ($initResponse->status() !== 200 && $initResponse->status() !== 201) {
                Log::error('YouTubeService: Không thể khởi tạo phiên upload video: ' . $initResponse->body());
                return null;
            }

            $uploadLocationUrl = $initResponse->header('Location');
            if (empty($uploadLocationUrl)) {
                Log::error('YouTubeService: Google không trả về header Location cho Resumable Upload.');
                return null;
            }

            // 3. Đẩy luồng nhị phân video lên upload endpoint (Stream PUT)
            $fileHandle = fopen($filePath, 'r');
            if (!$fileHandle) {
                Log::error('YouTubeService: Không thể mở file stream ' . $filePath);
                return null;
            }

            $uploadResponse = Http::withHeaders([
                'Content-Type'   => $mimeType,
                'Content-Length' => (string)$fileSize,
            ])->timeout(300) // 5 phút cho video lớn
              ->withBody($fileHandle, $mimeType)
              ->put($uploadLocationUrl);

            if (is_resource($fileHandle)) {
                fclose($fileHandle);
            }

            if ($uploadResponse->successful()) {
                $responseData = $uploadResponse->json();
                $videoId = $responseData['id'] ?? null;

                if ($videoId) {
                    $watchUrl = "https://www.youtube.com/watch?v={$videoId}";
                    $embedUrl = "https://www.youtube.com/embed/{$videoId}";

                    Log::info("YouTubeService: Upload video thành công lên YouTube. Video ID: {$videoId}");

                    return [
                        'id'         => $videoId,
                        'url'        => $watchUrl,
                        'embed_url'  => $embedUrl,
                        'title'      => $title,
                        'privacy'    => $privacyStatus,
                    ];
                }
            }

            Log::error('YouTubeService: Lỗi trong quá trình upload video content: ' . $uploadResponse->body());
            return null;

        } catch (\Throwable $e) {
            Log::error('YouTubeService: Exception trong quá trình upload video: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Trích xuất Video ID từ mọi dạng link YouTube (watch, shorts, youtu.be, embed, v.v.)
     */
    public static function extractVideoId(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);

        // Trường hợp người dùng nhập luôn 11 ký tự Video ID
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        // Pattern hỗ trợ: youtube.com/watch?v=..., youtu.be/..., youtube.com/shorts/..., youtube.com/embed/..., youtube.com/v/..., youtube.com/live/...
        if (preg_match('/(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Kiểm tra xem đường dẫn có phải là link YouTube hay không
     */
    public static function isYouTubeUrl(?string $url): bool
    {
        return !empty(self::extractVideoId($url));
    }

    /**
     * Chuyển đổi bất kỳ link YouTube nào sang dạng URL nhúng (Embed Player URL)
     */
    public static function getEmbedUrl(?string $url): ?string
    {
        $id = self::extractVideoId($url);
        return $id ? "https://www.youtube.com/embed/{$id}" : null;
    }

    /**
     * Chuyển đổi bất kỳ link YouTube nào sang dạng link xem chuẩn (Watch URL)
     */
    public static function getWatchUrl(?string $url): ?string
    {
        $id = self::extractVideoId($url);
        return $id ? "https://www.youtube.com/watch?v={$id}" : null;
    }

    /**
     * Lấy ảnh Thumbnail của video YouTube
     *
     * @param string|null $url
     * @param string $quality 'maxresdefault', 'hqdefault', 'mqdefault', 'default'
     * @return string|null
     */
    public static function getThumbnailUrl(?string $url, string $quality = 'hqdefault'): ?string
    {
        $id = self::extractVideoId($url);
        return $id ? "https://img.youtube.com/vi/{$id}/{$quality}.jpg" : null;
    }
}
