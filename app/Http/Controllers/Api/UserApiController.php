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
        $user = Auth::user() ?: auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $checkins = Checkin::with(['eatery', 'comments.user'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'success'  => true,
            'checkins' => $checkins,
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
}
}
