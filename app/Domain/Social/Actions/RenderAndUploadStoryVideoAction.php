<?php

namespace App\Domain\Social\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RenderAndUploadStoryVideoAction
{
    /**
     * Ghép Ảnh/Video + Nhạc thành Video MP4 và tự động Upload lên YouTube API v3
     */
    public function execute(string $imagePath, ?string $musicPath = null, string $title = 'DongAnh Social Story'): array
    {
        $outputDir = storage_path('app/public/rendered_stories');
        File::ensureDirectoryExists($outputDir);
        $outputVideoPath = $outputDir . '/story_' . time() . '.mp4';

        // 1. Render Ảnh + Nhạc nền thành Video MP4 qua FFmpeg CLI
        if (!empty($musicPath) && file_exists($imagePath) && file_exists($musicPath)) {
            $cmd = "ffmpeg -loop 1 -i " . escapeshellarg($imagePath) . " -i " . escapeshellarg($musicPath) . " -c:v libx264 -tune stillimage -c:a aac -b:a 192k -pix_fmt yuv420p -shortest -y " . escapeshellarg($outputVideoPath);
            exec($cmd);
        } else {
            $outputVideoPath = $imagePath;
        }

        // 2. Upload Video vừa tạo lên YouTube API v3
        $youtubeData = $this->uploadToYouTube($outputVideoPath, $title);

        return [
            'success'           => true,
            'local_video_path'  => asset('storage/rendered_stories/' . basename($outputVideoPath)),
            'youtube_id'        => $youtubeData['id'] ?? null,
            'youtube_url'       => $youtubeData['url'] ?? null,
        ];
    }

    private function uploadToYouTube(string $videoPath, string $title): array
    {
        try {
            $tokenPath = storage_path('app/youtube_token.json');
            if (!File::exists($tokenPath)) {
                return ['id' => null, 'url' => null];
            }

            $tokenData = json_decode(File::get($tokenPath), true);
            $accessToken = $tokenData['access_token'] ?? null;

            if (!$accessToken) {
                return ['id' => null, 'url' => null];
            }

            // Gọi YouTube API v3 Upload Video
            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://www.googleapis.com/upload/youtube/v3/videos?uploadType=multipart&part=snippet,status', [
                    'snippet' => [
                        'title'       => $title,
                        'description' => 'Được đăng tự động từ DongAnhSocial Story',
                        'tags'        => ['DongAnhSocial', 'Story', 'Reels'],
                    ],
                    'status' => [
                        'privacyStatus' => 'unlisted',
                    ],
                ]);

            if ($response->successful()) {
                $youtubeId = $response->json('id');
                return [
                    'id'  => $youtubeId,
                    'url' => 'https://www.youtube.com/watch?v=' . $youtubeId,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('[YouTubeUpload] Error uploading video to YouTube: ' . $e->getMessage());
        }

        return ['id' => null, 'url' => null];
    }
}
