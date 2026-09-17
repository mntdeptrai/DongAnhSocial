<?php

namespace App\Services;

use App\Domain\ReviewVideo\ReviewVideoData;
use App\Helpers\R2Helper;
use App\Models\Eatery;
use App\Models\ReviewVideo;
use App\Services\YouTubeService;
use Illuminate\Support\Str;

class ReviewVideoService
{
    /**
     * Lấy toàn bộ video review đã được phê duyệt kèm thông tin cơ sở và người đăng.
     */
    public function getVideos()
    {
        return ReviewVideo::with(['eatery.category', 'user'])
            ->where('status', 'approved')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function like(int $id): bool
    {
        $video = ReviewVideo::find($id);
        if (!$video) return false;

        $video->increment('likes_count');
        return true;
    }

    public function likeVideo(int $id): bool
    {
        return $this->like($id);
    }

    public function create(ReviewVideoData|array $data, string $status = 'pending'): ?ReviewVideo
    {
        if ($data instanceof ReviewVideoData) {
            list($videoUrl, $videoType) = $this->resolveVideoDetails($data->video_file, $data->video_url, $data->title ?? 'Video Review Đông Anh');
            $attributes = [
                'eatery_id' => $data->eatery_id,
                'user_id' => $data->user_id,
                'title' => $data->title,
                'video_url' => $videoUrl,
                'video_type' => $videoType,
                'thumbnail_path' => $data->thumbnail_path,
                'likes_count' => 0,
                'status' => $status,
            ];
        } else {
            $attributes = $data;
            if (!isset($attributes['status'])) {
                $attributes['status'] = $status;
            }
        }

        return $this->storeVideo($attributes);
    }

    public function update($id, ReviewVideoData|array $data, ?string $currentUrl = null, ?string $currentType = null, string $status = 'pending'): ?ReviewVideo
    {
        $video = ReviewVideo::find($id);
        if (!$video) return null;

        if ($data instanceof ReviewVideoData) {
            $videoUrl = $currentUrl ?? $video->video_url;
            $videoType = $currentType ?? $video->video_type;

            if ($data->video_file) {
                if ($videoType === 'local' && Str::startsWith($videoUrl, '/uploads/videos/')) {
                    $oldFilePath = public_path($videoUrl);
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                list($videoUrl, $videoType) = $this->resolveVideoDetails($data->video_file, null, $data->title ?? 'Video Review Đông Anh');
            } elseif ($data->video_url && $data->video_url !== $videoUrl) {
                if ($videoType === 'local' && Str::startsWith($videoUrl, '/uploads/videos/')) {
                    $oldFilePath = public_path($videoUrl);
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                list($videoUrl, $videoType) = $this->resolveVideoDetails(null, $data->video_url, $data->title ?? 'Video Review Đông Anh');
            }

            $attributes = [
                'eatery_id' => $data->eatery_id,
                'title' => $data->title,
                'video_url' => $videoUrl,
                'video_type' => $videoType,
                'thumbnail_path' => $data->thumbnail_path ?? $video->thumbnail_path,
                'status' => $status,
            ];
        } else {
            $attributes = $data;
        }

        $video->update($attributes);
        return $video;
    }

    public function storeVideo(array $data): ?ReviewVideo
    {
        $eatery = Eatery::find($data['eatery_id'] ?? null);
        if (!$eatery) return null;

        return ReviewVideo::create($data);
    }

    public function updateVideo(int $id, array $data): ?ReviewVideo
    {
        $video = ReviewVideo::find($id);
        if (!$video) return null;

        $video->update($data);
        return $video;
    }

    public function approve(int $id): ?ReviewVideo
    {
        $video = ReviewVideo::find($id);
        if (!$video) return null;

        $video->update(['status' => 'approved']);
        return $video;
    }

    public function approveVideo(int $id): ?ReviewVideo
    {
        return $this->approve($id);
    }

    public function reject(int $id): ?ReviewVideo
    {
        $video = ReviewVideo::find($id);
        if (!$video) return null;

        $video->update(['status' => 'rejected']);
        return $video;
    }

    public function rejectVideo(int $id): ?ReviewVideo
    {
        return $this->reject($id);
    }

    public function delete(int $id): bool
    {
        $video = ReviewVideo::find($id);
        if (!$video) return false;

        return (bool) $video->delete();
    }

    public function deleteVideo(int $id): bool
    {
        return $this->delete($id);
    }

    protected function resolveVideoDetails($videoFile, ?string $videoUrl, string $title = 'Video Review Đông Anh'): array
    {
        $resolvedUrl = '';
        $resolvedType = 'local';

        if ($videoFile) {
            if (YouTubeService::isConfigured()) {
                $ytResult = YouTubeService::uploadVideo($videoFile, $title, 'Video trải nghiệm ẩm thực và du lịch trên DongAnh Discovery');
                if ($ytResult && !empty($ytResult['url'])) {
                    return [$ytResult['url'], 'youtube_shorts'];
                }
            }

            $resolvedUrl = R2Helper::upload($videoFile, 'videos');
            $resolvedType = 'local';
        } elseif ($videoUrl) {
            $url = $videoUrl;
            if (preg_match('/(?:drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=))([a-zA-Z0-9_-]{25,50})/i', $url, $matches)) {
                $resolvedType = 'local';
                $resolvedUrl = 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            } elseif (preg_match('/tiktok\.com/i', $url)) {
                $resolvedType = 'tiktok';
                $resolvedUrl = $url;
            } elseif (YouTubeService::isYouTubeUrl($url)) {
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
