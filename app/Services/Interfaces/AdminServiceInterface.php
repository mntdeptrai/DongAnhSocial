<?php

namespace App\Services\Interfaces;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface AdminServiceInterface
{
    /**
     * Xác minh quyền hạn truy cập Admin/Manager/Seller
     */
    public function verifyAdminAccess(?int $userId, ?string $role): void;

    /**
     * Lấy dữ liệu cho Dashboard Admin / Manager
     */
    public function getDashboardData(?int $userId, ?string $role, ?string $query, ?string $categoryName, ?string $communeName, int $currentPage = 1, int $perPage = 10): array;

    /**
     * Phân tích và trích xuất tọa độ từ URL Google Maps
     */
    public function parseGoogleMapsUrl(string $url): array;

    /**
     * Thêm ảnh mới cho cơ sở ẩm thực
     */
    public function storeEateryPhoto(int $eateryId, ?string $caption, ?UploadedFile $photoFile): void;

    /**
     * Xóa ảnh cơ sở ẩm thực
     */
    public function destroyEateryPhoto(int $id): void;

    /**
     * Xóa đánh giá
     */
    public function destroyReview(int $id): void;

    /**
     * Phản hồi đánh giá của khách hàng
     */
    public function replyReview(int $id, string $reply): void;

    /**
     * Danh sách tài khoản người dùng có phân trang & lọc
     */
    public function getUsersList(array $filters, ?int $managerUserId, ?string $role, int $perPage = 10): array;

    /**
     * Xuất danh sách tài khoản ra Excel XML / CSV
     */
    public function exportUsers(array $filters, ?int $managerUserId, ?string $role): StreamedResponse;

    /**
     * Dữ liệu form tạo tài khoản người dùng
     */
    public function getCreateUserData(?int $managerUserId, ?string $role): array;

    /**
     * Tạo tài khoản người dùng mới
     */
    public function storeUser(array $data, ?int $managerUserId, ?string $role): User;

    /**
     * Chi tiết tài khoản người dùng và gian hàng liên kết
     */
    public function getUserDetails(int $id, ?string $role): array;

    /**
     * Dữ liệu form chỉnh sửa tài khoản
     */
    public function getUserForEdit(int $id, ?int $managerUserId, ?string $role): array;

    /**
     * Cập nhật tài khoản người dùng
     */
    public function updateUser(int $id, array $data, ?int $managerUserId, ?string $role): User;

    /**
     * Xóa tài khoản người dùng
     */
    public function destroyUser(int $id, ?int $currentUserId, ?string $role): string;

    /**
     * Bật / tắt trạng thái tài khoản
     */
    public function toggleUserStatus(int $id, ?int $currentUserId, ?string $role): string;

    /**
     * Phát bản tin số mới cho Chợ Số / BQL Chợ
     */
    public function storeAnnouncement(int $eateryId, array $data, ?int $userId, ?string $userRole): void;

    /**
     * Xóa bản tin số của Chợ
     */
    public function destroyAnnouncement(int $eateryId, int $announcementId, ?int $userId, ?string $userRole): void;

    /**
     * Danh sách Gian hàng Chợ Số 4.0 và báo cáo thống kê
     */
    public function getStallsData(array $filters, ?int $managerUserId, ?string $role, int $page = 1, int $perPage = 10): array;

    /**
     * Dữ liệu form tạo mới gian hàng chợ số
     */
    public function getCreateStallData(?int $managerUserId, ?string $role): array;

    /**
     * Thêm mới sản phẩm trưng bày / gian hàng chợ số
     */
    public function storeStall(array $data, ?int $managerUserId, ?string $role, ?UploadedFile $qrFile, ?string $qrUrl, array $imageFiles, ?UploadedFile $singleImage, ?string $singleImageUrl, ?string $multipleImageUrls): void;

    /**
     * Dữ liệu form chỉnh sửa gian hàng chợ số
     */
    public function getStallForEdit(int $id, ?int $managerUserId, ?string $role): array;

    /**
     * Cập nhật thông tin gian hàng chợ số & đồng bộ VietQR
     */
    public function updateStall(int $id, array $data, ?int $managerUserId, ?string $role, ?UploadedFile $qrFile, ?string $qrUrl, array $imageFiles, ?UploadedFile $singleImage, ?string $singleImageUrl, ?string $multipleImageUrls): void;

    /**
     * Xóa gian hàng chợ số
     */
    public function destroyStall(int $id, ?int $managerUserId, ?string $role): void;

    /**
     * Xử lý báo cáo vi phạm nội dung
     */
    public function resolveReport(int $id, string $action, ?int $adminId, string $duration, ?string $note): string;
}
