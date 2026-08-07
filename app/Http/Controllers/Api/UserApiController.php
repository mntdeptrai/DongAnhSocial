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

        return response()->json([
            'success' => true,
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'username'   => $user->username,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'role'       => $user->role,
                'avatar'     => $user->avatar ?? '👤',
                'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
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
}
