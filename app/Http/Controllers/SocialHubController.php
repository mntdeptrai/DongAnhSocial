<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\FoodTour;
use App\Events\MessageSent;
use App\Services\SocialService;
use App\Domain\Social\FriendshipData;
use App\Domain\Social\MessageData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SocialHubController extends Controller
{
    public function __construct(
        protected SocialService $socialService
    ) {}

    /**
     * View chính Social Hub (Kết nối & Nhắn tin) bằng Inertia + React
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Danh sách bạn bè (Đã đồng ý kết bạn ở cả 2 hướng)
        $sentFriendIds = Friendship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('friend_id');
            
        $receivedFriendIds = Friendship::where('friend_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('user_id');

        $friendIds = $sentFriendIds->merge($receivedFriendIds)->unique();
        $friends = User::whereIn('id', $friendIds)->get();

        // 2. Yêu cầu kết bạn đang chờ (Pending)
        $pendingReceived = Friendship::where('friend_id', $user->id)
            ->where('status', 'pending')
            ->with('sender')
            ->get();

        $pendingSent = Friendship::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('receiver')
            ->get();

        // 3. Gợi ý kết bạn (Những user chưa kết bạn và không phải chính mình)
        $nonSuggestions = $friendIds->merge([$user->id])->merge(
            Friendship::where('user_id', $user->id)->pluck('friend_id')
        )->merge(
            Friendship::where('friend_id', $user->id)->pluck('user_id')
        )->unique();

        $suggestions = User::whereNotIn('id', $nonSuggestions)
            ->limit(10)
            ->get();

        $myFoodTours = FoodTour::where('user_id', $user->id)->get();

        return Inertia::render('SocialHub', [
            'friends' => $friends,
            'pendingReceived' => $pendingReceived,
            'pendingSent' => $pendingSent,
            'suggestions' => $suggestions,
            'myFoodTours' => $myFoodTours,
        ]);
    }

    /**
     * Tìm kiếm người dùng theo tên hoặc email — Tối ưu hóa tránh N+1 query và sử dụng index
     */
    public function searchUsers(Request $request)
    {
        $query = trim($request->get('q', ''));
        $user  = Auth::user();

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Tìm 15 user trước (sử dụng index users_name_index hoặc users_email_unique)
        $users = User::where('id', '!=', $user->id)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(15)
            ->get();

        if ($users->isEmpty()) {
            return response()->json([]);
        }

        $userIds = $users->pluck('id')->toArray();

        // Tối ưu tránh N+1 query: Lấy toàn bộ quan hệ kết bạn liên quan trong 1 query duy nhất dùng index unique
        $friendships = Friendship::where(function ($q) use ($user, $userIds) {
            $q->where('user_id', $user->id)->whereIn('friend_id', $userIds);
        })->orWhere(function ($q) use ($user, $userIds) {
            $q->where('friend_id', $user->id)->whereIn('user_id', $userIds);
        })->get();

        // Ánh xạ thành mảng để truy xuất nhanh O(1)
        $friendshipMap = [];
        foreach ($friendships as $f) {
            $key = $f->user_id . '-' . $f->friend_id;
            $friendshipMap[$key] = $f;
        }

        $results = $users->map(function ($u) use ($user, $friendshipMap) {
            $key1 = $user->id . '-' . $u->id;
            $key2 = $u->id . '-' . $user->id;
            $friendship = $friendshipMap[$key1] ?? $friendshipMap[$key2] ?? null;

            // Xây dựng avatar URL nếu là ảnh R2
            $avatarUrl = null;
            if ($u->avatar && str_starts_with($u->avatar, 'avatars/')) {
                $avatarUrl = rtrim(env('R2_PUBLIC_URL', ''), '/') . '/' . $u->avatar;
            }

            return [
                'id'                => $u->id,
                'name'              => $u->name,
                'avatar'            => $u->avatar,
                'avatar_url'        => $avatarUrl,
                'friendship_status' => $friendship ? $friendship->status : 'none',
                'friendship_id'     => $friendship ? $friendship->id : null,
                'is_sender'         => $friendship ? ((int)$friendship->user_id === (int)$user->id) : false,
                'is_online'         => $u->is_online,
            ];
        });

        return response()->json($results);
    }

    /**
     * Cập nhật vị trí GPS hiện tại của user và lấy danh sách người ở gần
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        // Làm tròn tọa độ GPS đến 3 chữ số thập phân (~110m) để bảo vệ quyền riêng tư
        $user->update([
            'latitude'       => round((float)$request->latitude, 3),
            'longitude'      => round((float)$request->longitude, 3),
            'last_active_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật vị trí thành công',
            'nearby' => $this->fetchNearbyUsers($user),
        ]);
    }

    /**
     * API Lấy danh sách người ở gần (dưới 20km)
     */
    public function getNearby(Request $request)
    {
        $user = Auth::user();
        if (!$user->latitude || !$user->longitude) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng chia sẻ vị trí của bạn trước.',
                'nearby' => []
            ]);
        }

        return response()->json([
            'status' => 'success',
            'nearby' => $this->fetchNearbyUsers($user),
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

        try {
            $data = FriendshipData::fromRequest($request);
            $this->socialService->sendFriendRequest($data);
            return back()->with('success', 'Đã gửi lời mời kết bạn.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Đồng ý lời mời kết bạn
     */
    public function acceptFriendRequest($id)
    {
        try {
            $this->socialService->acceptFriendRequest((int)$id, (int)Auth::id());
            return back()->with('success', 'Đã đồng ý kết bạn.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Từ chối / Hủy lời mời kết bạn
     */
    public function declineFriendRequest($id)
    {
        try {
            $this->socialService->declineFriendRequest((int)$id, (int)Auth::id());
            return back()->with('success', 'Đã hủy yêu cầu hoặc xóa kết bạn.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    /**
     * Quick unread check for native Android background polling.
     * Returns {"has_unread": true/false, "sender_name": "...", "last_message": "..."}
     */
    public function checkUnread()
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['has_unread' => false]);
        }

        // Find the most recent UNREAD message sent TO the current user
        $lastIncoming = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastIncoming) {
            return response()->json(['has_unread' => false]);
        }

        $sender = User::find($lastIncoming->sender_id);

        return response()->json([
            'has_unread' => true,
            'sender_name' => $sender ? $sender->name : 'Bạn bè',
            'last_message' => $lastIncoming->message ?? 'Tin nhắn mới',
            'message_id' => $lastIncoming->id,
        ]);
    }

    public function getFriends()
    {
        $user = Auth::user();
        if ($user) {
            $user->update(['last_active_at' => now()]);
        }
        
        $sentFriendIds = Friendship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('friend_id');
            
        $receivedFriendIds = Friendship::where('friend_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('user_id');

        $friendIds = $sentFriendIds->merge($receivedFriendIds)->unique();
        $friends = User::whereIn('id', $friendIds)->get()->map(function($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'email' => $f->email,
                'avatar' => $f->avatar,
                'avatar_url' => $f->avatar_url,
                'is_online' => $f->is_online ?? false,
            ];
        });

        return response()->json($friends);
    }

    public function getMessages($friendId, Request $request)
    {
        $userId   = Auth::id();
        if ($userId) {
            User::where('id', $userId)->update(['last_active_at' => now()]);
        }
        $limit    = 50;
        $beforeId = $request->query('before_id');

        $query = Message::with('foodTour')
            ->where(function ($q) use ($userId, $friendId) {
                $q->where(function ($sub) use ($userId, $friendId) {
                    $sub->where('sender_id', $userId)->where('receiver_id', $friendId);
                })->orWhere(function ($sub) use ($userId, $friendId) {
                    $sub->where('sender_id', $friendId)->where('receiver_id', $userId);
                });
            });

        // Cursor-based: nếu có before_id thì lấy tin cũ hơn cursor đó
        if ($beforeId) {
            $query->where('id', '<', (int) $beforeId);
        }

        // Lấy $limit+1 để biết còn tin cũ không
        $rawMessages = $query
            ->orderBy('created_at', 'desc')
            ->take($limit + 1)
            ->get();

        $hasMore = $rawMessages->count() > $limit;
        $rawMessages = $rawMessages->take($limit)->reverse()->values();

        $messages = $rawMessages->map(function ($msg) {
            return [
                'id'               => $msg->id,
                'sender_id'        => $msg->sender_id,
                'receiver_id'      => $msg->receiver_id,
                'message'          => $msg->message,
                'food_tour_id'     => $msg->food_tour_id,
                'media_path'       => $msg->media_path,
                'media_type'       => $msg->media_type,
                'food_tour'        => $msg->foodTour ? [
                    'id'          => $msg->foodTour->id,
                    'name'        => $msg->foodTour->name,
                    'slug'        => $msg->foodTour->slug,
                    'description' => $msg->foodTour->description,
                    'duration'    => $msg->foodTour->duration,
                    'distance'    => $msg->foodTour->distance,
                    'budget'      => $msg->foodTour->budget,
                    'difficulty'  => $msg->foodTour->difficulty,
                    'best_time'   => $msg->foodTour->best_time,
                    'thumbnail'   => $msg->foodTour->thumbnail,
                ] : null,
                'is_read'           => (bool) $msg->is_read,
                'created_at_human'  => $msg->created_at->diffForHumans(),
                'created_at_format' => $msg->created_at->format('d/m/Y H:i'),
            ];
        });

        // Đánh dấu tin nhắn nhận từ bạn bè là đã đọc
        Message::where('sender_id', $friendId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'messages' => $messages,
            'has_more' => $hasMore,
        ]);
    }

    /**
     * Gửi tin nhắn mới (Broadcasting)
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required_without_all:food_tour_id,media_path|nullable|string',
            'food_tour_id' => 'nullable|exists:food_tours,id',
            'media_path' => 'nullable|string',
            'media_type' => 'nullable|string|in:image,video',
        ]);

        try {
            $data = MessageData::fromRequest($request);
            $message = $this->socialService->sendMessage($data);
            
            // Tải quan hệ foodTour mới tạo
            $message->loadMissing('foodTour');

            return response()->json([
                'status' => 'success',
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'receiver_id' => $message->receiver_id,
                    'message' => $message->message,
                    'food_tour_id' => $message->food_tour_id,
                    'media_path' => $message->media_path,
                    'media_type' => $message->media_type,
                    'food_tour' => $message->foodTour ? [
                        'id' => $message->foodTour->id,
                        'name' => $message->foodTour->name,
                        'slug' => $message->foodTour->slug,
                        'description' => $message->foodTour->description,
                        'duration' => $message->foodTour->duration,
                        'distance' => $message->foodTour->distance,
                        'budget' => $message->foodTour->budget,
                        'difficulty' => $message->foodTour->difficulty,
                        'best_time' => $message->foodTour->best_time,
                        'thumbnail' => $message->foodTour->thumbnail,
                    ] : null,
                    'is_read' => false,
                    'created_at_human' => $message->created_at->diffForHumans(),
                    'created_at_format' => $message->created_at->format('d/m/Y H:i'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * Hàm phụ: Lấy và tính toán khoảng cách của những người dùng khác gần đó (Haversine)
     */
    private function fetchNearbyUsers($user)
    {
        // 1. Tính toán bounding box (hộp giới hạn) để lọc nhanh bằng index vĩ độ/kinh độ
        // Bán kính 10km: vĩ độ lệch khoảng 10/111 = 0.09 độ
        // Kinh độ lệch khoảng 10 / (111 * cos(latitude)) = 0.095 độ ở khu vực Đông Anh / Hà Nội
        $latRange = 10 / 111.0;
        $cosLat = cos(deg2rad($user->latitude));
        $lonRange = $cosLat > 0 ? (10 / (111.0 * $cosLat)) : (10 / 111.0);

        // Lấy danh sách user khác nằm trong bounding box và hoạt động trong vòng 24 giờ qua
        // Truy vấn này sẽ tối ưu hóa sử dụng index 'users_latitude_longitude_index' và 'users_last_active_at_index'
        $others = User::where('id', '!=', $user->id)
            ->whereBetween('latitude', [$user->latitude - $latRange, $user->latitude + $latRange])
            ->whereBetween('longitude', [$user->longitude - $lonRange, $user->longitude + $lonRange])
            ->where('last_active_at', '>=', now()->subHours(24))
            ->get();

        if ($others->isEmpty()) {
            return [];
        }

        $otherIds = $others->pluck('id')->toArray();

        // Tối ưu hóa tránh N+1 query: Lấy toàn bộ quan hệ bạn bè của các user này với $user trong 1 query duy nhất
        $friendships = Friendship::where(function($q) use ($user, $otherIds) {
            $q->where('user_id', $user->id)->whereIn('friend_id', $otherIds);
        })->orWhere(function($q) use ($user, $otherIds) {
            $q->where('friend_id', $user->id)->whereIn('user_id', $otherIds);
        })->get();

        // Ánh xạ thành mảng tra cứu nhanh O(1)
        $friendshipMap = [];
        foreach ($friendships as $f) {
            $key = $f->user_id . '-' . $f->friend_id;
            $friendshipMap[$key] = $f;
        }

        $nearby = [];
        foreach ($others as $other) {
            $dist = $this->calculateDistance(
                $user->latitude,
                $user->longitude,
                $other->latitude,
                $other->longitude
            );

            // Giới hạn chính xác trong bán kính 10km sau khi đã lọc sơ bộ bằng index
            if ($dist <= 10.0) {
                $key1 = $user->id . '-' . $other->id;
                $key2 = $other->id . '-' . $user->id;
                $friendship = $friendshipMap[$key1] ?? $friendshipMap[$key2] ?? null;

                // Xây dựng avatar URL nếu là ảnh R2
                $avatarUrl = null;
                if ($other->avatar && str_starts_with($other->avatar, 'avatars/')) {
                    $avatarUrl = rtrim(env('R2_PUBLIC_URL', ''), '/') . '/' . $other->avatar;
                }

                $nearby[] = [
                    'id'                => $other->id,
                    'name'              => $other->name,
                    'avatar'            => $other->avatar ?? '👤',
                    'avatar_url'        => $avatarUrl,
                    'distance'          => round($dist, 2),
                    // Không trả về tọa độ GPS chính xác của user khác để bảo vệ privacy
                    'last_active'       => $other->last_active_at ? ($other->last_active_at instanceof \Carbon\Carbon ? $other->last_active_at->diffForHumans() : \Carbon\Carbon::parse($other->last_active_at)->diffForHumans()) : 'Không rõ',
                    'friendship_status' => $friendship ? $friendship->status : 'none',
                    'friendship_id'     => $friendship ? $friendship->id : null,
                    'is_sender'         => $friendship ? ((int)$friendship->user_id === (int)$user->id) : false,
                    'is_online'         => $other->is_online,
                ];
            }
        }

        // Sắp xếp theo khoảng cách từ gần đến xa
        usort($nearby, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return $nearby;
    }

    /**
     * Công thức Haversine tính khoảng cách giữa 2 điểm GPS (Km)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Km
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Lấy danh sách ID của các bạn bè đang online (hoạt động trong 2 phút qua)
     */
    public function getFriendsPresence()
    {
        $user = Auth::user();
        
        $sentFriendIds = Friendship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('friend_id');
            
        $receivedFriendIds = Friendship::where('friend_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('user_id');

        $friendIds = $sentFriendIds->merge($receivedFriendIds)->unique();
        
        $onlineFriendIds = User::whereIn('id', $friendIds)
            ->where('last_active_at', '>=', now()->subMinutes(2))
            ->pluck('id')
            ->toArray();
            
        return response()->json([
            'online_ids' => $onlineFriendIds
        ]);
    }

    /**
     * Lấy danh sách bạn bè kèm tin nhắn gần nhất để hiển thị ở header dropdown
     */
    public function getRecentChats(Request $request)
    {
        // Chống truy cập trực tiếp URL API qua trình duyệt web
        if (!$request->expectsJson() && !$request->ajax()) {
            return redirect()->route('social.index');
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([], 401);
        }

        // 1. Danh sách bạn bè (Đã đồng ý kết bạn ở cả 2 hướng)
        $sentFriendIds = Friendship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('friend_id');
            
        $receivedFriendIds = Friendship::where('friend_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('user_id');

        $friendIds = $sentFriendIds->merge($receivedFriendIds)->unique();

        $friends = User::whereIn('id', $friendIds)->get();

        $recentChats = $friends->map(function($friend) use ($user) {
            // Lấy tin nhắn mới nhất giữa $user và $friend
            $latestMessage = Message::where(function($q) use ($user, $friend) {
                $q->where('sender_id', $user->id)->where('receiver_id', $friend->id);
            })->orWhere(function($q) use ($user, $friend) {
                $q->where('sender_id', $friend->id)->where('receiver_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->first();

            // Xây dựng avatar URL nếu là ảnh R2
            $avatarUrl = null;
            if ($friend->avatar && str_starts_with($friend->avatar, 'avatars/')) {
                $avatarUrl = rtrim(env('R2_PUBLIC_URL', ''), '/') . '/' . $friend->avatar;
            }

            return [
                'id'                       => $friend->id,
                'name'                     => $friend->name,
                'avatar'                   => $friend->avatar ?? '👤',
                'avatar_url'               => $avatarUrl,
                'is_online'                => $friend->is_online,
                'latest_message'           => $latestMessage ? $latestMessage->message : null,
                'latest_message_time'      => $latestMessage ? $latestMessage->created_at->diffForHumans() : null,
                'latest_message_timestamp' => $latestMessage ? $latestMessage->created_at->timestamp : 0,
                'is_read'                  => $latestMessage ? (bool)$latestMessage->is_read : true,
                'latest_message_sender_id' => $latestMessage ? (int)$latestMessage->sender_id : null,
            ];
        });

        // Sắp xếp các đoạn chat: Có tin nhắn mới xếp trước, sắp xếp theo timestamp giảm dần
        $sortedChats = $recentChats->sortByDesc('latest_message_timestamp')->values();

        return response()->json($sortedChats, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
