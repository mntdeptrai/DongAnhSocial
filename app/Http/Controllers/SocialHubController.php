<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\FoodTour;
use App\Models\CallLog;
use App\Events\MessageSent;
use App\Events\CallOffer;
use App\Events\CallSignal;
use App\Events\CallHangup;
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
        $user = Auth::user() ?? User::find(session('user_id'));
        if (!$user) {
            return redirect()->route('login');
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
            ->inRandomOrder()
            ->limit(20)
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

        // Sử dụng MySQL FULLTEXT Index (Nhanh O(1), 0% Full Table Scan, KHÔNG dùng LIKE %...%)
        $usersQuery = User::where('id', '!=', $user->id);
        try {
            $usersQuery->where(function($q) use ($query) {
                $q->whereFullText(['name', 'username', 'email', 'phone'], $query)
                  ->orWhere('email', $query)
                  ->orWhere('phone', $query)
                  ->orWhere('username', $query)
                  ->orWhere('name', 'LIKE', "{$query}%");
            });
        } catch (\Throwable $e) {
            $usersQuery->where(function($q) use ($query) {
                $q->where('email', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%")
                  ->orWhere('username', 'LIKE', "%{$query}%")
                  ->orWhere('name', 'LIKE', "%{$query}%");
            });
        }
        $users = $usersQuery->limit(30)->get();

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
                'username'          => $u->username,
                'email'             => $u->email,
                'phone'             => $u->phone,
                'role'              => $u->role,
                'is_verified'       => (bool) ($u->is_verified ?? false),
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
            $friendship = $this->socialService->sendFriendRequest($data);
            $isPureJson = !$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax());
            if ($isPureJson) {
                return response()->json(['success' => true, 'message' => 'Đã gửi lời mời kết bạn.', 'friendship_id' => $friendship->id]);
            }
            return back()->with('success', 'Đã gửi lời mời kết bạn.');
        } catch (\Exception $e) {
            $isPureJson = !$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax());
            if ($isPureJson) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Đồng ý lời mời kết bạn
     */
    public function acceptFriendRequest(Request $request, $id)
    {
        try {
            $this->socialService->acceptFriendRequest((int)$id, (int)Auth::id());
            $isPureJson = !$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax());
            if ($isPureJson) {
                return response()->json(['success' => true, 'message' => 'Đã đồng ý kết bạn.']);
            }
            return back()->with('success', 'Đã đồng ý kết bạn.');
        } catch (\Exception $e) {
            $isPureJson = !$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax());
            if ($isPureJson) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Từ chối / Hủy lời mời kết bạn
     */
    public function declineFriendRequest(Request $request, $id)
    {
        try {
            $this->socialService->declineFriendRequest((int)$id, (int)Auth::id());
            $isPureJson = !$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax());
            if ($isPureJson) {
                return response()->json(['success' => true, 'message' => 'Đã hủy yêu cầu hoặc xóa kết bạn.']);
            }
            return back()->with('success', 'Đã hủy yêu cầu hoặc xóa kết bạn.');
        } catch (\Exception $e) {
            $isPureJson = !$request->header('X-Inertia') && ($request->expectsJson() || $request->ajax());
            if ($isPureJson) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
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
            return response()->json(['has_unread' => false, 'unread_count' => 0]);
        }

        $unreadCount = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        $lastIncoming = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastIncoming) {
            return response()->json(['has_unread' => false, 'unread_count' => 0]);
        }

        $sender = User::find($lastIncoming->sender_id);

        return response()->json([
            'has_unread' => true,
            'unread_count' => $unreadCount,
            'sender_name' => $sender ? $sender->name : 'Bạn bè',
            'last_message' => $lastIncoming->message ?? 'Đã gửi một tin nhắn',
            'message_id' => $lastIncoming->id,
        ]);
    }

    public function getFriends(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->update(['last_active_at' => now()]);
        }
        
        $search = $request->query('query') ?: $request->query('search') ?: $request->query('q');

        if (!empty($search)) {
            $search = mb_strtolower(trim($search));
            // Tìm kiếm TẤT CẢ người dùng (người lạ, thành viên, chủ gian hàng, bạn bè)
            $allUsers = User::where('id', '!=', $user ? $user->id : 0)
                ->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                })
                ->limit(30)
                ->get()
                ->map(function($f) {
                    return [
                        'id' => $f->id,
                        'name' => $f->name,
                        'email' => $f->email,
                        'avatar' => $f->avatar,
                        'avatar_url' => $f->avatar_url,
                        'is_online' => $f->is_online ?? false,
                        'role' => $f->role ?? 'user',
                    ];
                });

            return response()->json($allUsers);
        }

        $sentFriendIds = Friendship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('friend_id');
            
        $receivedFriendIds = Friendship::where('friend_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('user_id');

        $friendIds = $sentFriendIds->merge($receivedFriendIds)->unique();

        $latestMessagesMap = collect();
        $unreadCountsMap = collect();
        if ($friendIds->isNotEmpty()) {
            $latestMessagesMap = Message::where(function($q) use ($user, $friendIds) {
                    $q->where('sender_id', $user->id)->whereIn('receiver_id', $friendIds);
                })->orWhere(function($q) use ($user, $friendIds) {
                    $q->whereIn('sender_id', $friendIds)->where('receiver_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function($msg) use ($user) {
                    return $msg->sender_id == $user->id ? $msg->receiver_id : $msg->sender_id;
                })
                ->map(function($messages) {
                    return $messages->first();
                });

            // Count unread messages FROM each friend TO current user
            $unreadCountsMap = Message::whereIn('sender_id', $friendIds)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->selectRaw('sender_id, COUNT(*) as cnt')
                ->groupBy('sender_id')
                ->pluck('cnt', 'sender_id');
        }

        $friends = User::whereIn('id', $friendIds)->get()->map(function($f) use ($latestMessagesMap, $unreadCountsMap) {
            $latestMessage = $latestMessagesMap->get($f->id);
            $unreadCount = $unreadCountsMap->get($f->id, 0);
            return [
                'id'                       => $f->id,
                'name'                     => $f->name,
                'email'                    => $f->email,
                'avatar'                   => $f->avatar,
                'avatar_url'               => $f->avatar_url,
                'is_online'                => $f->is_online ?? false,
                'role'                     => $f->role ?? 'user',
                'latest_message'           => $latestMessage ? $latestMessage->message : null,
                'latest_message_time'      => $latestMessage ? $latestMessage->created_at->diffForHumans() : null,
                'latest_message_timestamp' => $latestMessage ? $latestMessage->created_at->timestamp : 0,
                'unread_count'             => (int) $unreadCount,
            ];
        })->sortByDesc('latest_message_timestamp')->values();

        return response()->json($friends);
    }

    /**
     * API Tìm kiếm tổng hợp: Người dùng (người lạ & bạn bè), Địa điểm, Quán ăn, Món ăn, Sản phẩm OCOP
     */
    public function searchAll(Request $request)
    {
        $q = trim($request->query('q') ?: $request->query('query') ?: $request->query('search') ?: '');
        if (empty($q)) {
            return response()->json([
                'success'  => true,
                'users'    => [],
                'eateries' => [],
                'products' => [],
            ]);
        }

        // 1. Tìm kiếm Người dùng / Người lạ / Chủ gian hàng / Bạn bè
        $users = User::where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->orWhere('username', 'like', "%{$q}%")
            ->limit(20)
            ->get()
            ->map(function ($u) {
                return [
                    'id'         => $u->id,
                    'name'       => $u->name,
                    'email'      => $u->email,
                    'phone'      => $u->phone,
                    'avatar'     => $u->avatar,
                    'avatar_url' => $u->avatar_url,
                    'role'       => $u->role ?? 'user',
                    'type'       => 'user',
                ];
            });

        // 2. Tìm kiếm Địa điểm / Quán ăn / Di sản
        $eateries = \App\Models\Eatery::where('name', 'like', "%{$q}%")
            ->orWhere('address', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->limit(20)
            ->get()
            ->map(function ($e) {
                return [
                    'id'          => $e->id,
                    'name'        => $e->name,
                    'slug'        => $e->slug,
                    'address'     => $e->address,
                    'image_path'  => $e->image_path,
                    'star_rating' => $e->star_rating,
                    'type'        => 'eatery',
                ];
            });

        // 3. Tìm kiếm Sản phẩm OCOP / Món ăn / Đặc sản
        $products = \App\Models\OcopProduct::where('name', 'like', "%{$q}%")
            ->orWhere('stall_name', 'like', "%{$q}%")
            ->orWhere('seller_name', 'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->limit(20)
            ->get()
            ->map(function ($p) {
                return [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'stall_name'  => $p->stall_name,
                    'seller_name' => $p->seller_name,
                    'price'       => $p->price,
                    'image_path'  => $p->image_path,
                    'star_rating' => $p->star_rating,
                    'type'        => 'product',
                ];
            });

        return response()->json([
            'success'  => true,
            'users'    => $users,
            'eateries' => $eateries,
            'products' => $products,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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

            // Gửi FCM Push Notification cho người nhận nếu có fcm_token
            try {
                $receiver = User::find($message->receiver_id);
                if ($receiver && !empty($receiver->fcm_token)) {
                    $senderName = Auth::user()->name ?? 'Bạn bè';
                    $msgBody = $message->message ?? ($message->media_path ? '[Hình ảnh]' : 'Đã gửi một tin nhắn');
                    \App\Services\FcmService::sendNotification(
                        $receiver->fcm_token,
                        $senderName,
                        "💬 " . $msgBody,
                        [
                            'target' => 'chat',
                            'sender_id' => (int)Auth::id(),
                            'sender_name' => $senderName,
                            'message_id' => (int)$message->id,
                        ]
                    );
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('FCM Notification error on sendMessage: ' . $e->getMessage());
            }

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

        $latestMessagesMap = collect();
        if ($friendIds->isNotEmpty()) {
            $latestMessagesMap = Message::where(function($q) use ($user, $friendIds) {
                    $q->where('sender_id', $user->id)->whereIn('receiver_id', $friendIds);
                })->orWhere(function($q) use ($user, $friendIds) {
                    $q->whereIn('sender_id', $friendIds)->where('receiver_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function($msg) use ($user) {
                    return $msg->sender_id == $user->id ? $msg->receiver_id : $msg->sender_id;
                })
                ->map(function($messages) {
                    return $messages->first();
                });
        }

        $recentChats = $friends->map(function($friend) use ($user, $latestMessagesMap) {
            // Lấy tin nhắn mới nhất đã batch load sẵn
            $latestMessage = $latestMessagesMap->get($friend->id);

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

    /**
     * Cập nhật FCM Token của thiết bị người dùng
     */
    public function updateFcmToken(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string']);
        $user = $request->user('sanctum') 
            ?? \Illuminate\Support\Facades\Auth::guard('sanctum')->user() 
            ?? \Illuminate\Support\Facades\Auth::user();

        if ($user) {
            $user->update(['fcm_token' => $request->fcm_token]);
            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật FCM Token thành công'
            ]);
        }
        return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
    }

    /**
     * WebRTC P2P Call: Khởi tạo cuộc gọi & broadcast CallOffer (simple-peer)
     */
    public function initiateCall(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'type'        => 'required|in:audio,video',
            'signal_data' => 'required|string',
        ]);

        $caller = Auth::user() ?? User::find(session('user_id'));
        if (!$caller) {
            return response()->json(['status' => 'error', 'message' => 'Bạn chưa đăng nhập.'], 401);
        }

        // Tạo log cuộc gọi
        $call = CallLog::create([
            'caller_id'   => $caller->id,
            'receiver_id' => $request->receiver_id,
            'type'        => $request->type,
            'status'      => 'ringing',
            'started_at'  => now(),
        ]);

        $callerAvatar = $caller->avatar ? asset($caller->avatar) : '👤';

        // Broadcast Offer tới người nhận qua Reverb
        try {
            broadcast(new CallOffer(
                callId: $call->id,
                callerId: $caller->id,
                callerName: $caller->name,
                callerAvatar: $callerAvatar,
                receiverId: (int)$request->receiver_id,
                type: $request->type,
                signalData: $request->signal_data
            ))->toOthers();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('CallOffer broadcast warning: ' . $e->getMessage());
        }

        // Gửi FCM Push Notification cuộc gọi tới điện thoại người nhận
        try {
            $receiver = User::find($request->receiver_id);
            if ($receiver && !empty($receiver->fcm_token)) {
                $callTitle = $request->type === 'video' ? "📹 Cuộc gọi video từ {$caller->name}" : "📞 Cuộc gọi thoại từ {$caller->name}";
                $callBody = "Nhấn để trả lời cuộc gọi đàm thoại từ {$caller->name}";
                \App\Services\FcmService::sendNotification(
                    $receiver->fcm_token,
                    $callTitle,
                    $callBody,
                    [
                        'type'          => 'incoming_call',
                        'call_id'       => (string)$call->id,
                        'caller_id'     => (string)$caller->id,
                        'caller_name'   => (string)$caller->name,
                        'caller_avatar' => (string)$callerAvatar,
                        'call_type'     => (string)$request->type,
                        'signal_data'   => (string)$request->signal_data,
                    ]
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('FCM Call Notification error: ' . $e->getMessage());
        }

        // Lưu signal_data (SDP Offer) vào Cache 2 phút để Receiver dùng khi đàm thoại
        \Illuminate\Support\Facades\Cache::put("call_signal_{$call->id}", $request->signal_data, 120);

        return response()->json([
            'status'  => 'success',
            'call_id' => $call->id,
        ]);
    }

    /**
     * API Kiểm tra Cuộc gọi đến đang đổ chuông (dành cho Flutter App & Mobile Web)
     */
    public function getPendingCall(Request $request)
    {
        $userId = Auth::id() ?? session('user_id');
        if (!$userId) {
            return response()->json(['has_call' => false]);
        }

        $pendingCall = CallLog::where('receiver_id', $userId)
            ->where('status', 'ringing')
            ->where('created_at', '>=', now()->subSeconds(30))
            ->latest()
            ->first();

        if (!$pendingCall) {
            return response()->json(['has_call' => false]);
        }

        $caller = User::find($pendingCall->caller_id);
        $callerAvatar = $caller && $caller->avatar ? asset($caller->avatar) : '👤';
        $signalData = \Illuminate\Support\Facades\Cache::get("call_signal_{$pendingCall->id}");

        return response()->json([
            'has_call'      => true,
            'call_id'       => $pendingCall->id,
            'caller_id'     => $pendingCall->caller_id,
            'caller_name'   => $caller ? $caller->name : 'Người dùng',
            'caller_avatar' => $callerAvatar,
            'call_type'     => $pendingCall->type,
            'signal_data'   => $signalData,
        ]);
    }

    /**
     * WebRTC P2P Call: Chuyển tiếp signal data (SDP answer / ICE candidates) giữa 2 peer
     */
    public function signalCall(Request $request)
    {
        $request->validate([
            'call_id'        => 'required|exists:call_logs,id',
            'target_user_id' => 'required|exists:users,id',
            'signal_data'    => 'required|string',
        ]);

        $user = Auth::user() ?? User::find(session('user_id'));
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $call = CallLog::find($request->call_id);
        $signalJson = json_decode($request->signal_data, true);
        $isCaller = $call && $call->caller_id == $user->id;
        $role = $isCaller ? 'caller' : 'receiver';

        \Illuminate\Support\Facades\Log::info("[WebRTC signalCall] User={$user->id} Role={$role} CallId={$request->call_id} SignalType=" . ($signalJson['type'] ?? 'ice_candidate'));

        if (isset($signalJson['type']) && $signalJson['type'] === 'answer') {
            // SDP Answer từ Receiver
            if ($call) {
                $call->update(['status' => 'answered', 'started_at' => now()]);
            }
            \Illuminate\Support\Facades\Cache::put("call_signal_answer_{$request->call_id}", $request->signal_data, 120);
        } else {
            // ICE candidate → thêm vào danh sách theo role
            $cacheKey = "call_ice_{$role}_{$request->call_id}";
            $existing = \Illuminate\Support\Facades\Cache::get($cacheKey, []);
            $existing[] = $request->signal_data;
            \Illuminate\Support\Facades\Cache::put($cacheKey, $existing, 120);
        }

        try {
            broadcast(new \App\Events\CallSignal(
                callId: (int)$request->call_id,
                targetUserId: (int)$request->target_user_id,
                signalData: $request->signal_data
            ))->toOthers();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('CallSignal broadcast warning: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * API Kiểm tra trạng thái cuộc gọi (dành cho cả 2 bên Polling)
     * Caller gọi API này để lấy SDP Answer + ICE candidates từ Receiver
     * Receiver gọi API này để lấy ICE candidates từ Caller + phát hiện hangup
     */
    public function getCallStatus($callId, Request $request)
    {
        $call = CallLog::find($callId);
        if (!$call) {
            return response()->json(['status' => 'ended']);
        }

        $user = Auth::user() ?? User::find(session('user_id'));
        $isCaller = $user && $call->caller_id == $user->id;

        // Lấy SDP signal (Caller lấy SDP Answer, Receiver lấy SDP Offer)
        $offerSignal = \Illuminate\Support\Facades\Cache::get("call_signal_{$callId}");
        $answerSignal = \Illuminate\Support\Facades\Cache::get("call_signal_answer_{$callId}");
        $targetSignal = $isCaller ? $answerSignal : $offerSignal;

        // Lấy ICE candidates của bên đối diện
        // Caller lấy ICE từ receiver, Receiver lấy ICE từ caller
        $oppositeRole = $isCaller ? 'receiver' : 'caller';
        $iceCacheKey = "call_ice_{$oppositeRole}_{$callId}";

        // Lấy offset đã đọc trước đó → chỉ trả ICE mới
        $myRole = $isCaller ? 'caller' : 'receiver';
        $offsetKey = "call_ice_offset_{$myRole}_{$callId}";
        $offset = \Illuminate\Support\Facades\Cache::get($offsetKey, 0);

        $allIce = \Illuminate\Support\Facades\Cache::get($iceCacheKey, []);
        $newIce = array_slice($allIce, $offset);
        \Illuminate\Support\Facades\Cache::put($offsetKey, count($allIce), 120);

        \Illuminate\Support\Facades\Log::info("[WebRTC getCallStatus] User=" . ($user ? $user->id : 'null') . " IsCaller=" . ($isCaller ? 'Y' : 'N') . " CallId={$callId} Status={$call->status} HasSignal=" . ($targetSignal ? 'Y' : 'N') . " NewICE=" . count($newIce));

        return response()->json([
            'status'         => $call->status,
            'signal_data'    => $targetSignal,
            'ice_candidates' => $newIce,
        ]);
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

        $user = Auth::user() ?? User::find(session('user_id'));
        $call = CallLog::find($request->call_id);
        $reason = $request->reason ?? 'ended';

        if ($call) {
            $duration = null;
            if ($call->started_at && $call->status === 'answered') {
                $duration = now()->diffInSeconds($call->started_at);
            }
            $call->update([
                'status'   => $reason,
                'ended_at' => now(),
                'duration' => $duration,
            ]);
        }

        try {
            broadcast(new CallHangup(
                callId: (int)$request->call_id,
                targetUserId: (int)$request->target_user_id,
                reason: $reason
            ))->toOthers();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('CallHangup broadcast warning: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Lấy lịch sử cuộc gọi của người dùng hiện tại
     */
    public function callHistory(Request $request)
    {
        $user = Auth::user() ?? User::find(session('user_id'));
        if (!$user) {
            return response()->json([], 401);
        }

        $logs = CallLog::with(['caller:id,name,avatar', 'receiver:id,name,avatar'])
            ->where('caller_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        return response()->json($logs);
    }
}

