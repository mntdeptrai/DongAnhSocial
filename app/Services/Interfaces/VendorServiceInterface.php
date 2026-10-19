<?php

namespace App\Services\Interfaces;

use Illuminate\Http\UploadedFile;

interface VendorServiceInterface
{
    /**
     * Xác minh quyền hạn Vendor / Chủ gian hàng / Tiểu thương
     */
    public function verifyVendor(?string $role): void;

    /**
     * Lấy thông tin ngữ cảnh Gian hàng và danh sách sản phẩm của Tenant
     */
    public function getVendorStallContext(?int $userId, ?string $userPhone, ?string $userName, ?string $activeEntityKey = null): array;

    /**
     * Cập nhật hồ sơ cơ sở kinh doanh
     */
    public function updateBusinessProfile(array $context, array $data, ?UploadedFile $image, ?int $userId): void;

    /**
     * Dữ liệu thống kê và danh sách đơn hàng cho dashboard
     */
    public function getDashboardData(array $context): array;

    /**
     * Cập nhật hồ sơ di sản OCOP và thông tin địa điểm
     */
    public function updateDossier(int $eateryId, array $eateryData, array $ocopData): void;

    /**
     * Thêm sản phẩm mới cho gian hàng
     */
    public function storeProduct(array $context, array $data, ?UploadedFile $imageFile): void;

    /**
     * Cập nhật sản phẩm gian hàng
     */
    public function updateProduct(int $id, array $data, ?UploadedFile $imageFile): bool;

    /**
     * Xóa sản phẩm khỏi gian hàng
     */
    public function destroyProduct(int $id): void;

    /**
     * Danh sách đơn hàng phân trang
     */
    public function getOrders(array $context, int $perPage = 20);

    /**
     * Xem chi tiết một đơn hàng
     */
    public function showOrder(int $orderId, array $context, string $userRole);

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateOrderStatus(int $orderId, string $status, ?string $cancelReason = null): string;

    /**
     * Danh sách đơn hàng dạng JSON cho polling
     */
    public function getOrdersJson(array $context): array;

    /**
     * Dữ liệu trang cấu hình gian hàng & VietQR
     */
    public function getProfileData(array $context, ?int $userId): array;

    /**
     * Cập nhật thông tin gian hàng & tạo VietQR 4.0
     */
    public function updateProfile(array $context, array $data, ?int $userId, ?UploadedFile $stallImage, ?string $stallImageUrl, ?UploadedFile $avatarFile, ?string $avatarUrl): void;

    /**
     * Lấy danh sách hội thoại khách hàng với gian hàng
     */
    public function getChatConversations(array $context, ?int $sellerUserId): array;

    /**
     * Lấy lịch sử tin nhắn phòng chat
     */
    public function getChatMessages(array $context, string $room, ?int $customerId, ?int $sellerUserId): array;

    /**
     * Gửi tin nhắn từ gian hàng
     */
    public function sendChatMessage(array $context, ?int $userId, array $data, ?UploadedFile $imageFile): array;

    /**
     * Lấy số tin nhắn chưa đọc và tin mới nhất
     */
    public function getChatUnreadCount(array $context, ?int $sellerUserId): array;
}
