<?php

namespace App\Http\Controllers;

use App\Domain\Social\FriendshipData;
use App\Domain\Social\MessageData;
use App\Models\User;
use App\Services\Interfaces\SocialHubServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SocialHubController extends Controller
{
    public function __construct(
        protected SocialHubServiceInterface $socialHubService
    ) {}

    /**
     * View chính Social Hub (Kết nối & Nhắn tin) bằng Inertia + React
     */
    public function index()
    {
        $userId = Auth::id() ?? session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $data = $this->socialHubService->getIndexData($userId);

        return Inertia::render('SocialHub', $data);
    }

    /**
     * Tìm kiếm người dùng theo tên hoặc email
     */
    public function searchUsers(Request $request)
    {
        $query = trim($request->get('q', ''));
        $userId = Auth::id();

        if (mb_strlen($query) < 2 || !$userId) {
            return response()->json([]);
        }

        $results = $this->socialHubService->searchUsers($userId, $query);
        return response()->json($results);
    }

    /**
     * Cập nhật vị trí GPS hiện tại của user và lấy danh sách người ở gần
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $nearby = $this->socialHubService->updateUserLocation(
            $userId,
            (float) $request->latitude,
            (float) $request->longitude
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Cập nhật vị trí thành công',
            'nearby'  => $nearby,
        ]);
    }

    /**
     * API Lấy danh sách người ở gần (dưới 10km)
     */
    public function getNearby(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->latitude || !$user->longitude) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Vui lòng chia sẻ vị trí của bạn trước.',
                'nearby'  => [],
            ]);
        }

        $nearby = $this->socialHubService->getNearbyUsers($user->id);

        return response()->json([
            'status' => 'success',
            'nearby' => $nearby,
        ]);
    }

    /**
     * Gửi lời mời kết bạn
     */
    public function sendFriendRequest(Request $request)
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $isPureJson = !$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax());

        try {
            $data = FriendshipData::fromRequest($request);
            $friendship = $this->socialHubService->sendFriendRequest($data);

            if ($isPureJson) {
                return response()->json(['success' => true, 'message' => 'Đã gửi lời mời kết bạn.', 'friendship_id' => $friendship->id]);
            }
            return back()->with('success', 'Đã gửi lời mời kết bạn.');
        } catch (\Exception $e) {
            Log::error('sendFriendRequest error: ' . $e->getMessage());
            if ($isPureJson) {
                return response()->json(['success' => false, 'message' => 'Không thể gửi lời mời kết bạn lúc này. Vui lòng thử lại sau.'], 400);
            }
            return back()->with('error', 'Không thể gửi lời mời kết bạn lúc này. Vui lòng thử lại sau.');
        }
    }

    /**
     * Đồng ý lời mời kết bạn
     */
    public function acceptFriendRequest(Request $request, $id)
    {
        $isPureJson = !$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax());

        try {
            $this->socialHubService->acceptFriendRequest((int)$id, (int)Auth::id());
            if ($isPureJson) {
                return response()->json(['success' => true, 'message' => 'Đã đồng ý kết bạn.']);
            }
            return back()->with('success', 'Đã đồng ý kết bạn.');
        } catch (\Exception $e) {
            Log::error('acceptFriendRequest error: ' . $e->getMessage());
            if ($isPureJson) {
                return response()->json(['success' => false, 'message' => 'Không thể xử lý yêu cầu kết bạn lúc này. Vui lòng thử lại sau.'], 400);
            }
            return back()->with('error', 'Không thể xử lý yêu cầu kết bạn lúc này. Vui lòng thử lại sau.');
        }
    }

    /**
     * Từ chối / Hủy lời mời kết bạn
     */
    public function declineFriendRequest(Request $request, $id)
    {
        $isPureJson = !$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax());

        try {
            $this->socialHubService->declineFriendRequest((int)$id, (int)Auth::id());
            if ($isPureJson) {
                return response()->json(['success' => true, 'message' => 'Đã hủy yêu cầu hoặc xóa kết bạn.']);
            }
            return back()->with('success', 'Đã hủy yêu cầu hoặc xóa kết bạn.');
        } catch (\Exception $e) {
            Log::error('declineFriendRequest error: ' . $e->getMessage());
            if ($isPureJson) {
                return response()->json(['success' => false, 'message' => 'Không thể thực hiện thao tác lúc này. Vui lòng thử lại sau.'], 400);
            }
            return back()->with('error', 'Không thể thực hiện thao tác lúc này. Vui lòng thử lại sau.');
        }
    }

    /**
     * Quick unread check for native Android background polling.
     */
    public function checkUnread()
    {
        $result = $this->socialHubService->checkUnread(Auth::id());
        return response()->json($result);
    }

    /**
     * Lấy danh sách bạn bè kèm tin nhắn gần nhất
     */
    public function getFriends(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([]);
        }

        $search = $request->query('query') ?: $request->query('search') ?: $request->query('q');
        $friends = $this->socialHubService->getFriends($userId, $search);

        return response()->json($friends);
    }

    /**
     * API Tìm kiếm tổng hợp: Người dùng, Quán ăn, Sản phẩm OCOP
     */
    public function searchAll(Request $request)
    {
        $q = trim($request->query('q') ?: $request->query('query') ?: $request->query('search') ?: '');
        $data = $this->socialHubService->searchAll($q);

        return response()->json(
            array_merge(['success' => true], $data),
            200,
            [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Lấy tin nhắn giữa hai người dùng
     */
    public function getMessages($friendId, Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['messages' => [], 'has_more' => false]);
        }

        $beforeId = $request->query('before_id');
        $data = $this->socialHubService->getMessages($userId, (int)$friendId, $beforeId ? (int)$beforeId : null);

        return response()->json($data);
    }

    /**
     * Gửi tin nhắn mới
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id'  => 'required|exists:users,id',
            'message'      => 'required_without_all:food_tour_id,media_path|nullable|string',
            'food_tour_id' => 'nullable|exists:food_tours,id',
            'media_path'   => 'nullable|string',
            'media_type'   => 'nullable|string|in:image,video',
        ]);

        try {
            $data = MessageData::fromRequest($request);
            $message = $this->socialHubService->sendMessage($data, Auth::user()?->name);

            return response()->json([
                'status'  => 'success',
                'message' => [
                    'id'               => $message->id,
                    'sender_id'        => $message->sender_id,
                    'receiver_id'      => $message->receiver_id,
                    'message'          => $message->message,
                    'food_tour_id'     => $message->food_tour_id,
                    'media_path'       => $message->media_path,
                    'media_type'       => $message->media_type,
                    'food_tour'        => $message->foodTour ? [
                        'id'          => $message->foodTour->id,
                        'name'        => $message->foodTour->name,
                        'slug'        => $message->foodTour->slug,
                        'description' => $message->foodTour->description,
                        'duration'    => $message->foodTour->duration,
                        'distance'    => $message->foodTour->distance,
                        'budget'      => $message->foodTour->budget,
                        'difficulty'  => $message->foodTour->difficulty,
                        'best_time'   => $message->foodTour->best_time,
                        'thumbnail'   => $message->foodTour->thumbnail,
                    ] : null,
                    'is_read'           => false,
                    'created_at_human'  => $message->created_at->diffForHumans(),
                    'created_at_format' => $message->created_at->format('d/m/Y H:i'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('sendMessage error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Không thể gửi tin nhắn lúc này. Vui lòng thử lại sau.',
            ], 500);
        }
    }

    /**
     * Lấy danh sách ID của các bạn bè đang online
     */
    public function getFriendsPresence()
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['online_ids' => []]);
        }

        $onlineFriendIds = $this->socialHubService->getFriendsPresence($userId);

        return response()->json([
            'online_ids' => $onlineFriendIds,
        ]);
    }

    /**
     * Lấy danh sách bạn bè kèm tin nhắn gần nhất cho header dropdown
     */
    public function getRecentChats(Request $request)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return redirect()->route('social.index');
        }

        $userId = Auth::id();
        if (!$userId) {
            return response()->json([], 401);
        }

        $sortedChats = $this->socialHubService->getRecentChats($userId);

        return response()->json($sortedChats, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Cập nhật FCM Token
     */
    public function updateFcmToken(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string']);

        $user = $request->user('sanctum') 
            ?? Auth::guard('sanctum')->user() 
            ?? Auth::user();

        if ($user) {
            $this->socialHubService->updateFcmToken($user->id, $request->fcm_token);
            return response()->json([
                'status'  => 'success',
                'message' => 'Cập nhật FCM Token thành công',
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
    }

    /**
     * WebRTC P2P Call: Khởi tạo cuộc gọi
     */
    public function initiateCall(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'type'        => 'required|in:audio,video',
            'signal_data' => 'required|string',
        ]);

        $callerId = Auth::id() ?? session('user_id');
        if (!$callerId) {
            return response()->json(['status' => 'error', 'message' => 'Bạn chưa đăng nhập.'], 401);
        }

        $callId = $this->socialHubService->initiateCall(
            (int) $callerId,
            (int) $request->receiver_id,
            $request->type,
            $request->signal_data
        );

        return response()->json([
            'status'  => 'success',
            'call_id' => $callId,
        ]);
    }

    /**
     * API Kiểm tra Cuộc gọi đến đang đổ chuông
     */
    public function getPendingCall(Request $request)
    {
        $userId = Auth::id() ?? session('user_id');
        if (!$userId) {
            return response()->json(['has_call' => false]);
        }

        $result = $this->socialHubService->getPendingCall($userId);
        return response()->json($result);
    }

    /**
     * WebRTC P2P Call: Chuyển tiếp signal data
     */
    public function signalCall(Request $request)
    {
        $request->validate([
            'call_id'        => 'required|exists:call_logs,id',
            'target_user_id' => 'required|exists:users,id',
            'signal_data'    => 'required|string',
        ]);

        $userId = Auth::id() ?? session('user_id');
        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $this->socialHubService->signalCall(
            $userId,
            (int) $request->call_id,
            (int) $request->target_user_id,
            $request->signal_data
        );

        return response()->json(['status' => 'success']);
    }

    /**
     * API Kiểm tra trạng thái cuộc gọi
     */
    public function getCallStatus($callId, Request $request)
    {
        $userId = Auth::id() ?? session('user_id');
        $result = $this->socialHubService->getCallStatus((int)$callId, $userId);

        return response()->json($result);
    }

    /**
     * WebRTC P2P Call: Kết thúc hoặc từ chối cuộc gọi
     */
    public function hangupCall(Request $request)
    {
        $request->validate([
            'call_id'        => 'required|exists:call_logs,id',
            'target_user_id' => 'required|exists:users,id',
            'reason'         => 'nullable|string|in:ended,rejected,missed,busy',
        ]);

        $this->socialHubService->hangupCall(
            (int) $request->call_id,
            (int) $request->target_user_id,
            $request->reason ?? 'ended'
        );

        return response()->json(['status' => 'success']);
    }

    /**
     * Lấy lịch sử cuộc gọi của người dùng hiện tại
     */
    public function callHistory(Request $request)
    {
        $userId = Auth::id() ?? session('user_id');
        if (!$userId) {
            return response()->json([], 401);
        }

        $logs = $this->socialHubService->getCallHistory($userId);
        return response()->json($logs);
    }
}
