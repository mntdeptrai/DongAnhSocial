<?php

namespace App\Services\Interfaces;

use App\Domain\Social\FriendshipData;
use App\Domain\Social\MessageData;
use App\Models\Friendship;
use App\Models\Message;

interface SocialHubServiceInterface
{
    /**
     * Dữ liệu khởi tạo cho view Social Hub
     */
    public function getIndexData(int $userId): array;

    /**
     * Tìm kiếm người dùng theo tên hoặc email
     */
    public function searchUsers(int $currentUserId, string $query): array;

    /**
     * Cập nhật tọa độ GPS và trả về danh sách lân cận
     */
    public function updateUserLocation(int $userId, float $latitude, float $longitude): array;

    /**
     * Lấy danh sách người dùng lân cận (< 10km / 20km)
     */
    public function getNearbyUsers(int $userId): array;

    /**
     * Gửi yêu cầu kết bạn
     */
    public function sendFriendRequest(FriendshipData $data): Friendship;

    /**
     * Chấp nhận yêu cầu kết bạn
     */
    public function acceptFriendRequest(int $friendshipId, int $authUserId): Friendship;

    /**
     * Từ chối / Hủy yêu cầu kết bạn
     */
    public function declineFriendRequest(int $friendshipId, int $authUserId): void;

    /**
     * Kiểm tra nhanh tin nhắn chưa đọc
     */
    public function checkUnread(?int $userId): array;

    /**
     * Lấy danh sách bạn bè kèm tin nhắn gần nhất và số tin chưa đọc
     */
    public function getFriends(int $userId, ?string $search = null);

    /**
     * Tìm kiếm tổng hợp người dùng, địa điểm/quán ăn, sản phẩm OCOP
     */
    public function searchAll(string $query): array;

    /**
     * Lấy tin nhắn giữa hai người dùng (cursor-based)
     */
    public function getMessages(int $userId, int $friendId, ?int $beforeId = null, int $limit = 50): array;

    /**
     * Gửi tin nhắn và broadcast, push notification FCM
     */
    public function sendMessage(MessageData $data, ?string $senderName = null): Message;

    /**
     * Lấy danh sách bạn bè đang hoạt động online
     */
    public function getFriendsPresence(int $userId): array;

    /**
     * Lấy danh sách đoạn chat gần nhất cho dropdown
     */
    public function getRecentChats(int $userId): array;

    /**
     * Cập nhật FCM token
     */
    public function updateFcmToken(int $userId, string $fcmToken): void;

    /**
     * WebRTC P2P Call: Khởi tạo cuộc gọi
     */
    public function initiateCall(int $callerId, int $receiverId, string $type, string $signalData): int;

    /**
     * Lấy cuộc gọi đang đổ chuông
     */
    public function getPendingCall(int $userId): array;

    /**
     * Chuyển tiếp signal data WebRTC giữa 2 peer
     */
    public function signalCall(int $userId, int $callId, int $targetUserId, string $signalData): void;

    /**
     * Lấy trạng thái cuộc gọi, signal data và ICE candidates
     */
    public function getCallStatus(int $callId, ?int $userId = null): array;

    /**
     * Kết thúc hoặc từ chối cuộc gọi
     */
    public function hangupCall(int $callId, int $targetUserId, string $reason): void;

    /**
     * Lịch sử cuộc gọi
     */
    public function getCallHistory(int $userId, int $limit = 30);
}
