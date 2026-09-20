<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use App\Services\NotificationService;

/**
 * NotificationApiController — Quản lý thông báo thời gian thực & đánh dấu đã đọc cho Mobile App
 */
class NotificationApiController extends Controller
{
    /**
     * GET /api/v1/notifications — Lấy danh sách thông báo cá nhân hóa thời gian thực cho User
     */
    public function getAppNotifications(Request $request)
    {
        $notifications = [];

        try {
            $user = $request->user('sanctum') 
                ?: Auth::guard('sanctum')->user() 
                ?: Auth::user();

            if (!$user && $request->bearerToken()) {
                $pat = PersonalAccessToken::findToken($request->bearerToken());
                if ($pat) {
                    $user = $pat->tokenable;
                }
            }

            if ($user) {
                $notifications = NotificationService::getNotificationsForUser($user->id);
            }

            if (empty($notifications)) {
                if ($user) {
                    $notifications[] = [
                        'id'        => 'user_welcome',
                        'title'     => '👋 Chào mừng ' . $user->name . '!',
                        'body'      => 'Tất cả thông báo đơn hàng cá nhân, tương tác cảm xúc, bình luận bài viết và kết bạn mới sẽ hiển thị tại đây.',
                        'time'      => 'Hôm nay',
                        'type'      => 'system',
                        'icon'      => 'notifications_active',
                        'is_read'   => false,
                    ];
                } else {
                    $notifications[] = [
                        'id'        => 'guest_welcome',
                        'title'     => '🔔 Thông báo Đông Anh Social',
                        'body'      => 'Vui lòng đăng nhập để nhận thông báo đơn hàng cá nhân, tương tác bài viết và lời mời kết bạn.',
                        'time'      => 'Hôm nay',
                        'type'      => 'system',
                        'icon'      => 'notifications_active',
                        'is_read'   => false,
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::error('getAppNotifications Exception: ' . $e->getMessage());
        }

        return response()->json($notifications, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * POST /api/v1/notifications/read — Đánh dấu đã đọc tất cả thông báo người dùng
     */
    public function markAppNotificationsRead(Request $request)
    {
        try {
            $user = $request->user('sanctum') 
                ?: Auth::guard('sanctum')->user() 
                ?: Auth::user();

            if (!$user && $request->bearerToken()) {
                $pat = PersonalAccessToken::findToken($request->bearerToken());
                if ($pat) {
                    $user = $pat->tokenable;
                }
            }

            if ($user) {
                NotificationService::markAsRead($user->id);
            }
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
