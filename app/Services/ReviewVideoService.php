<?php

namespace App\Services;

use App\Domain\ReviewVideo\ReviewVideoData;
use App\Domain\ReviewVideo\Actions\CreateReviewVideoAction;
use App\Domain\ReviewVideo\Actions\UpdateReviewVideoAction;
use App\Helpers\R2Helper;
use App\Services\EateryApiService;

class ReviewVideoService
{
    public function __construct(
        protected CreateReviewVideoAction $createAction,
        protected UpdateReviewVideoAction $updateAction
    ) {}

    public function create(ReviewVideoData $data, string $status)
    {
        list($videoUrl, $videoType) = $this->resolveVideoDetails($data->video_file, $data->video_url);
        return $this->createAction->execute($data, $videoUrl, $videoType, $status);
    }

    public function update($id, ReviewVideoData $data, string $currentUrl, string $currentType, string $status)
    {
        $videoUrl = $currentUrl;
        $videoType = $currentType;

        if ($data->video_file) {
            if ($currentType === 'local' && \Str::startsWith($currentUrl, '/uploads/videos/')) {
                $oldFilePath = public_path($currentUrl);
                if (file_exists($oldFilePath)) {
                    @unlink($oldFilePath);
                }
            }
            $videoUrl = R2Helper::upload($data->video_file, 'videos');
            $videoType = 'local';
        } elseif ($data->video_url && $data->video_url !== $currentUrl) {
            if ($currentType === 'local' && \Str::startsWith($currentUrl, '/uploads/videos/')) {
                $oldFilePath = public_path($currentUrl);
                if (file_exists($oldFilePath)) {
                    @unlink($oldFilePath);
                }
            }
            list($videoUrl, $videoType) = $this->resolveVideoDetails(null, $data->video_url);
        }

        return $this->updateAction->execute($id, $data, $videoUrl, $videoType, $status);
    }

    public function approve($id)
    {
        return EateryApiService::approveVideo($id);
    }

    public function reject($id)
    {
        return EateryApiService::rejectVideo($id);
    }

    public function delete($id): bool
    {
        return EateryApiService::deleteVideo($id);
    }

    protected function resolveVideoDetails($videoFile, ?string $videoUrl): array
    {
        $resolvedUrl = '';
        $resolvedType = 'local';

        if ($videoFile) {
            $resolvedUrl = R2Helper::upload($videoFile, 'videos');
            $resolvedType = 'local';
        } elseif ($videoUrl) {
            $url = $videoUrl;
            if (preg_match('/(?:drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=))([a-zA-Z0-9_-]{25,50})/i', $url, $matches)) {
                $resolvedType = 'local';
                $resolvedUrl = 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            }
            elseif (preg_match('/tiktok\.com/i', $url)) {
                $resolvedType = 'tiktok';
                $resolvedUrl = $url;
                if (preg_match('/(?:vt|vm)\.tiktok\.com/i', $url) || preg_match('/tiktok\.com\/t\//i', $url)) {
                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_HEADER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
                        $response = curl_exec($ch);
                        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                        curl_close($ch);
                        if ($finalUrl) {
                            $resolvedUrl = $finalUrl;
                        }
                    } catch (\Exception $e) {
                        // Fallback
                    }
                }
            } 
            elseif (preg_match('/(?:youtube\.com\/(?:shorts\/|watch\?v=)|youtu\.be\/)([a-zA-Z0-9_-]+)/i', $url, $matches)) {
                $resolvedType = 'youtube_shorts';
                $resolvedUrl = $url;
            } else {
                $resolvedType = 'local';
                $resolvedUrl = $url;
            }
        }

        return [$resolvedUrl, $resolvedType];
    }
}
