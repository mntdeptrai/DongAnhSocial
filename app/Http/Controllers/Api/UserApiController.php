<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Checkin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

/**
 * UserApiController — API dành riêng cho Role User (Thành viên / Khách hàng)
 */
class UserApiController extends Controller
{
    /**
     * Lấy thông tin cá nhân của User đang đăng nhập
     */
    public function getProfile(Request $request)
    {
        $user = Auth::user() ?: auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $postsCount = \App\Models\Post::on('mysql_education')->where('user_id', $user->id)->count();
        if ($postsCount === 0) {
            $postsCount = \App\Models\Post::on('mysql')->where('user_id', $user->id)->count();
        }

        $checkinsCount = Checkin::where('user_id', $user->id)->count();
        $followersCount = \App\Models\Friendship::where('friend_id', $user->id)->where('status', 'accepted')->count();
        $followingCount = \App\Models\Friendship::where('user_id', $user->id)->where('status', 'accepted')->count();

        return response()->json([
            'success' => true,
            'user' => [
                'id'              => $user->id,
                'name'            => $user->name,
                'username'        => $user->username,
                'email'           => $user->email,
                'phone'           => $user->phone,
                'role'            => $user->role,
                'avatar'          => $user->avatar ?? '👤',
                'avatar_url'      => $user->avatar_url,
                'cover'           => $user->cover,
                'cover_url'       => $user->cover_url,
                'created_at'      => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
                'posts_count'     => $postsCount,
                'checkins_count'  => $checkinsCount,
                'followers_count' => $followersCount,
                'following_count' => $followingCount,
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Cập nhật thông tin hồ sơ cá nhân (Tên, Số điện thoại, Avatar)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user() ?: auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'name'   => 'nullable|string|max:255',
            'phone'  => 'nullable|string|max:20',
            'avatar' => 'nullable|string',
        ]);

        if ($request->has('name') && !empty($request->name)) {
            $user->name = $request->name;
        }
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        if ($request->has('avatar')) {
            $user->avatar = $request->avatar;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin cá nhân thành công!',
            'user'    => $user,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Đổi mật khẩu tài khoản
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user() ?: auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không chính xác.',
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công!',
        ]);
    }

    /**
     * Lấy danh sách các đơn hàng đã đặt của User
     */
    public function getMyOrders(Request $request)
    {
        $user = Auth::user() ?: auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $orders = DB::table('orders')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'orders'  => $orders,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Lấy chi tiết một đơn hàng cụ thể
     */
    public function getOrderDetail(Request $request, $id)
    {
        $user = Auth::user() ?: auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $order = Order::with('items')->where('id', $id)->where('user_id', $user->id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng.'], 404);
        }

        return response()->json([
            'success' => true,
            'order'   => $order,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Lấy danh sách checkin cá nhân
     */
    public function getMyCheckins(Request $request)
    {
        $user = Auth::user() ?: auth('sanctum')->user() ?: $request->user('sanctum');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $checkins = Checkin::with(['eatery.category', 'eatery.commune'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($c) use ($user) {
                return [
                    'id'                => $c->id,
                    'type'              => 'checkin',
                    'display_name'      => $c->display_name ?: $user->name,
                    'avatar'            => $user->avatar_url ?: $user->avatar,
                    'rating'            => $c->rating,
                    'comment'           => $c->comment,
                    'image_path'        => $c->image_path,
                    'created_at_human'  => $c->created_at ? $c->created_at->diffForHumans() : 'Vừa xong',
                    'created_at_format' => $c->created_at ? $c->created_at->format('d/m/Y H:i') : '',
                    'eatery_name'       => $c->eatery?->name ?? 'Địa điểm Đông Anh',
                    'eatery'            => $c->eatery ? [
                        'name'     => $c->eatery->name,
                        'slug'     => $c->eatery->slug,
                        'category' => $c->eatery->category?->name,
                        'commune'  => $c->eatery->commune?->name,
                    ] : null,
                ];
            });

        $diaries = \App\Models\FoodTourDiary::with(['foodTour.stops'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($d) use ($user) {
                return [
                    'id'                => $d->id,
                    'type'              => 'diary',
                    'display_name'      => $user->name,
                    'avatar'            => $user->avatar_url ?: $user->avatar,
                    'rating'            => $d->rating,
                    'comment'           => $d->comment,
                    'image_path'        => $d->image_path,
                    'created_at_human'  => $d->created_at ? $d->created_at->diffForHumans() : 'Vừa xong',
                    'created_at_format' => $d->created_at ? $d->created_at->format('d/m/Y H:i') : '',
                    'eatery_name'       => $d->foodTour?->name ?? 'Nhật ký Foodtour',
                    'food_tour'         => $d->foodTour ? ['name' => $d->foodTour->name] : null,
                ];
            });

        $all = $checkins->concat($diaries)->sortByDesc('created_at_human')->values();

        return response()->json([
            'success'  => true,
            'checkins' => $all,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Lấy danh sách bài viết cá nhân của User
     */
    public function getMyPosts(Request $request)
    {
        $user = Auth::user() ?: auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $postsEdu = collect();
        $postsMysql = collect();

        try {
            $postsEdu = \App\Models\Post::on('mysql_education')
                ->with(['user'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Throwable $e) {}

        try {
            $postsMysql = \App\Models\Post::on('mysql')
                ->with(['user'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Throwable $e) {}

        $posts = $postsEdu->concat($postsMysql)->unique('id')->sortByDesc('created_at')->values();

        if ($posts->isEmpty()) {
            try {
                $allPostsEdu = \App\Models\Post::on('mysql_education')->with(['user'])->orderBy('created_at', 'desc')->get();
                $allPostsMysql = \App\Models\Post::on('mysql')->with(['user'])->orderBy('created_at', 'desc')->get();
                $all = $allPostsEdu->concat($allPostsMysql);

                $userName = mb_strtolower(trim($user->name));
                $posts = $all->filter(function($p) use ($user, $userName) {
                    if ($p->user_id && (string)$p->user_id === (string)$user->id) return true;
                    if ($p->user && (mb_strtolower(trim($p->user->name)) === $userName || $p->user->email === $user->email)) return true;
                    return false;
                })->values();
            } catch (\Throwable $e) {}
        }

        $r2PublicUrl = rtrim(env('R2_PUBLIC_URL', 'https://media.xadonganh.com'), '/');

        $formatted = $posts->map(function ($p) use ($r2PublicUrl) {
            $img = $p->image_path ?: ($p->image ?? '');
            if (!empty($img) && !str_starts_with($img, 'http')) {
                $img = str_starts_with($img, 'posts/') ? ($r2PublicUrl . '/' . $img) : ('https://donganhdiscovery.xadonganh.com/' . ltrim($img, '/'));
            }
            return [
                'id'               => $p->id,
                'name'             => $p->name ?? $p->title ?? '',
                'description'      => $p->description ?? $p->content ?? '',
                'author'           => $p->user ? $p->user->name : 'Thành viên',
                'user_id'          => $p->user_id,
                'image_path'       => $img,
                'likes_count'      => $p->likes_count ?? 0,
                'comments_count'   => $p->comments_count ?? 0,
                'created_at_human' => $p->created_at ? $p->created_at->diffForHumans() : 'Vừa xong',
            ];
        });

        return response()->json([
            'success' => true,
            'posts'   => $formatted,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Tìm kiếm người dùng theo từ khóa (Tên, Email, Số điện thoại, Username)
     */
    public function searchUsers(Request $request)
    {
        $user = Auth::user() ?: auth('sanctum')->user() ?: $request->user('sanctum');
        $query = trim($request->query('q', ''));

        $usersQuery = \App\Models\User::query();

        if (!empty($query)) {
            $words = array_filter(explode(' ', $query));
            $usersQuery->where(function($q) use ($query, $words) {
                // 1. Khớp chính xác Index (Email, SĐT, Username)
                $q->where('email', $query)
                  ->orWhere('phone', $query)
                  ->orWhere('username', $query)
                  // 2. Khớp tiền tố B-Tree Index (Range Scan - KHÔNG dùng % ở đầu để tránh Full Table Scan)
                  ->orWhere('name', 'LIKE', "{$query}%")
                  ->orWhere('username', 'LIKE', "{$query}%")
                  ->orWhere('phone', 'LIKE', "{$query}%");

                // 3. Khớp tiền tố theo từng từ đơn trong tên (Vẫn sử dụng B-Tree Index)
                if (!empty($words)) {
                    foreach ($words as $w) {
                        if (mb_strlen($w) >= 2) {
                            $q->orWhere('name', 'LIKE', "{$w}%");
                        }
                    }
                }
            });
        }

        if ($user) {
            $usersQuery->where('id', '!=', $user->id);
        }

        $users = $usersQuery->orderBy('name', 'asc')->limit(40)->get();

        $friendshipsMap = [];
        if ($user) {
            $friendships = \App\Models\Friendship::where('user_id', $user->id)
                ->orWhere('friend_id', $user->id)
                ->get();
            foreach ($friendships as $f) {
                $otherId = ($f->user_id == $user->id) ? $f->friend_id : $f->user_id;
                if ($f->status === 'accepted') {
                    $friendshipsMap[$otherId] = 'accepted';
                } else if ($f->status === 'pending') {
                    $friendshipsMap[$otherId] = ($f->user_id == $user->id) ? 'pending_sent' : 'pending_received';
                }
            }
        }

        $result = $users->map(function($u) use ($friendshipsMap) {
            return [
                'id'                => $u->id,
                'name'              => $u->name,
                'username'          => $u->username,
                'email'             => $u->email,
                'phone'             => $u->phone,
                'role'              => $u->role,
                'avatar'            => $u->avatar ?? '👤',
                'avatar_url'        => $u->avatar_url,
                'cover'             => $u->cover,
                'cover_url'         => $u->cover_url,
                'is_verified'       => $u->is_verified ?? false,
                'friendship_status' => $friendshipsMap[$u->id] ?? 'none',
            ];
        });

        return response()->json([
            'success' => true,
            'users'   => $result,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Lấy xem hồ sơ công khai của một người dùng khác (Public Profile)
     */
    public function getPublicProfile(Request $request, $id)
    {
        $currentUser = Auth::user() ?: auth('sanctum')->user() ?: $request->user('sanctum');
        $targetUser = \App\Models\User::find($id);

        if (!$targetUser) {
            return response()->json(['success' => false, 'message' => 'Người dùng không tồn tại.'], 404);
        }

        $friendshipStatus = 'none';
        if ($currentUser) {
            if ($currentUser->id == $targetUser->id) {
                $friendshipStatus = 'self';
            } else {
                $f = \App\Models\Friendship::where(function($q) use ($currentUser, $targetUser) {
                    $q->where('user_id', $currentUser->id)->where('friend_id', $targetUser->id);
                })->orWhere(function($q) use ($currentUser, $targetUser) {
                    $q->where('user_id', $targetUser->id)->where('friend_id', $currentUser->id);
                })->first();

                if ($f) {
                    if ($f->status === 'accepted') {
                        $friendshipStatus = 'accepted';
                    } else if ($f->status === 'pending') {
                        $friendshipStatus = ($f->user_id == $currentUser->id) ? 'pending_sent' : 'pending_received';
                    }
                }
            }
        }

        // Posts
        $postsEdu = collect();
        $postsMysql = collect();
        try {
            $postsEdu = \App\Models\Post::on('mysql_education')->with(['user'])->where('user_id', $targetUser->id)->orderBy('created_at', 'desc')->get();
        } catch (\Throwable $e) {}
        try {
            $postsMysql = \App\Models\Post::on('mysql')->with(['user'])->where('user_id', $targetUser->id)->orderBy('created_at', 'desc')->get();
        } catch (\Throwable $e) {}
        $posts = $postsEdu->concat($postsMysql)->unique('id')->sortByDesc('created_at')->values();

        $r2PublicUrl = rtrim(env('R2_PUBLIC_URL', 'https://media.xadonganh.com'), '/');
        $formattedPosts = $posts->map(function ($p) use ($r2PublicUrl) {
            $img = $p->image_path ?: ($p->image ?? '');
            if (!empty($img) && !str_starts_with($img, 'http')) {
                $img = str_starts_with($img, 'posts/') ? ($r2PublicUrl . '/' . $img) : ('https://donganhdiscovery.xadonganh.com/' . ltrim($img, '/'));
            }
            return [
                'id'               => $p->id,
                'name'             => $p->name ?? $p->title ?? '',
                'description'      => $p->description ?? $p->content ?? '',
                'author'           => $p->user ? $p->user->name : 'Thành viên',
                'image_path'       => $img,
                'likes_count'      => $p->likes_count ?? 0,
                'comments_count'   => $p->comments_count ?? 0,
                'created_at_human' => $p->created_at ? $p->created_at->diffForHumans() : 'Vừa xong',
            ];
        });

        // Checkins
        $checkins = \App\Models\Checkin::with(['eatery'])
            ->where('user_id', $targetUser->id)
            ->latest()
            ->get()
            ->map(function ($c) use ($targetUser) {
                return [
                    'id'                => $c->id,
                    'type'              => 'checkin',
                    'display_name'      => $c->display_name ?: $targetUser->name,
                    'rating'            => $c->rating,
                    'comment'           => $c->comment,
                    'image_path'        => $c->image_path,
                    'created_at_human'  => $c->created_at ? $c->created_at->diffForHumans() : 'Vừa xong',
                    'eatery_name'       => $c->eatery?->name ?? 'Địa điểm Đông Anh',
                ];
            });

        $followersCount = \App\Models\Friendship::where('friend_id', $targetUser->id)->where('status', 'accepted')->count();
        $followingCount = \App\Models\Friendship::where('user_id', $targetUser->id)->where('status', 'accepted')->count();

        return response()->json([
            'success' => true,
            'user' => [
                'id'                => $targetUser->id,
                'name'              => $targetUser->name,
                'username'          => $targetUser->username,
                'email'             => $targetUser->email,
                'phone'             => $targetUser->phone,
                'role'              => $targetUser->role,
                'avatar'            => $targetUser->avatar ?? '👤',
                'avatar_url'        => $targetUser->avatar_url,
                'cover'             => $targetUser->cover,
                'cover_url'         => $targetUser->cover_url,
                'is_verified'       => $targetUser->is_verified ?? false,
                'friendship_status' => $friendshipStatus,
                'posts_count'       => $formattedPosts->count(),
                'checkins_count'    => $checkins->count(),
                'followers_count'   => $followersCount,
                'following_count'   => $followingCount,
            ],
            'posts'    => $formattedPosts,
            'checkins' => $checkins,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Gửi yêu cầu kết bạn tới một tài khoản người dùng khác
     */
    public function sendFriendRequest(Request $request)
    {
        $user = Auth::user() ?: auth('sanctum')->user() ?: $request->user('sanctum');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập!'], 401);
        }

        $friendId = (int) $request->input('friend_id');
        if (!$friendId || $friendId === $user->id) {
            return response()->json(['success' => false, 'message' => 'Tài khoản không hợp lệ.'], 400);
        }

        $existing = \App\Models\Friendship::where(function($q) use ($user, $friendId) {
            $q->where('user_id', $user->id)->where('friend_id', $friendId);
        })->orWhere(function($q) use ($user, $friendId) {
            $q->where('user_id', $friendId)->where('friend_id', $user->id);
        })->first();

        if ($existing) {
            if ($existing->status === 'accepted') {
                return response()->json(['success' => true, 'message' => 'Hai bạn đã là bạn bè!', 'status' => 'accepted']);
            }
            if ($existing->status === 'pending') {
                return response()->json(['success' => true, 'message' => 'Đã gửi lời mời kết bạn trước đó.', 'status' => 'pending_sent']);
            }
            $existing->status = 'pending';
            $existing->user_id = $user->id;
            $existing->friend_id = $friendId;
            $existing->save();
        } else {
            \App\Models\Friendship::create([
                'user_id'   => $user->id,
                'friend_id' => $friendId,
                'status'    => 'pending',
            ]);
        }

        // Tự động gửi thông báo đẩy
        try {
            \App\Services\NotificationService::sendPushNotification(
                userId: $friendId,
                title: '🤝 Lời mời kết bạn mới',
                body: "{$user->name} đã gửi cho bạn một lời mời kết bạn!",
                data: ['type' => 'friend_request', 'sender_id' => $user->id]
            );
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi lời mời kết bạn thành công!',
            'status'  => 'pending_sent',
        ]);
    }
}
