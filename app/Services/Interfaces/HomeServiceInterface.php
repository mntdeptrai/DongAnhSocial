<?php

namespace App\Services\Interfaces;

use App\Domain\Checkin\CheckinData;
use Illuminate\Support\Collection;

interface HomeServiceInterface
{
    /**
     * Dữ liệu trang chủ (Danh mục, Xã, Địa điểm phân trang, OCOP)
     */
    public function getHomeData(?string $selectedCatSlug, ?string $selectedComSlug, int $page = 1, int $perPage = 24): array;

    /**
     * Dữ liệu Sitemap XML
     */
    public function getSitemapData(): array;

    /**
     * Dữ liệu Tuyến đường số 4.0 (Đường số & Hộ kinh doanh)
     */
    public function getTuyenDuongData(): array;

    /**
     * Danh sách video review địa điểm
     */
    public function getVideoList(): Collection;

    /**
     * Thích video
     */
    public function likeVideo(int $id): ?array;

    /**
     * Dữ liệu Bảng tin Newsfeed đa thực thể cá nhân hóa
     */
    public function getNewsfeedData(?int $currentUserId, string $feedType = 'for_you'): array;

    /**
     * Dữ liệu trang Check-in feed
     */
    public function getCheckinFeedData(?int $userId, ?string $sessionId): array;

    /**
     * Tạo bài check-in mới và phát thông báo
     */
    public function storeCheckin(CheckinData $data, ?int $authUserId, ?int $eateryId, ?string $comment): void;

    /**
     * Tìm kiếm địa điểm nhanh
     */
    public function searchEateries(string $query);

    /**
     * Tìm địa điểm lân cận theo tọa độ GPS (Haversine SQL)
     */
    public function getNearbyEateries(float $lat, float $lng, float $radius = 3);

    /**
     * Lấy check-in mới hơn timestamp (Polling fallback)
     */
    public function getLatestCheckins(?int $afterTimestamp = null);

    /**
     * Thông báo cho Web Header
     */
    public function getWebNotifications(int $userId): array;

    /**
     * Đánh dấu đã đọc tất cả thông báo
     */
    public function markWebNotificationsRead(int $userId): void;

    /**
     * Thả emoji vào check-in/bài viết (WebSocket broadcast)
     */
    public function reactToCheckin(int $id, string $type, string $emoji, ?int $userId, string $sessionId): array;

    /**
     * Thích / Bỏ thích nội dung (Like toggle)
     */
    public function toggleReaction(int $id, string $type, string $emoji, ?int $userId, string $sessionId): array;

    /**
     * Danh sách người thích bài viết
     */
    public function getPostLikers(int $id, string $type): array;

    /**
     * Danh sách bình luận
     */
    public function getComments(int $id, string $type): array;

    /**
     * Lưu bình luận mới
     */
    public function storeComment(int $id, string $type, string $content, ?int $userId, ?string $guestName): array;

    /**
     * Tăng lượt chia sẻ
     */
    public function incrementShare(string|int $id, ?string $type, ?int $userId): int;
}
