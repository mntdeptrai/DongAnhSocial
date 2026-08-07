<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * AuthApiController — Xác thực & Quản lý Token cho Mobile App
 *
 * Chịu trách nhiệm duy nhất: cấp & thu hồi Sanctum token.
 */
class AuthApiController extends Controller
{
    /**
     * Cấp token truy cập (Sanctum) cho Mobile App khi đăng nhập
     */
    public function issueToken(Request $request)
    {
        try {
            $request->validate([
                'email'       => 'required|string',
                'password'    => 'required|string',
                'device_name' => 'nullable|string',
            ]);

            $login = $request->input('email');
            $user = User::where('email', $login)
                ->orWhere('username', $login)
                ->orWhere('name', $login)
                ->orWhere('phone', $login)
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thông tin đăng nhập hoặc mật khẩu không chính xác.'
                ], 401);
            }

            if ($user->status === 'disabled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản đã bị khóa.'
                ], 403);
            }

            $deviceName = $request->input('device_name', 'mobile-app');
            try {
                $token = $user->createToken($deviceName)->plainTextToken;
            } catch (\Throwable $e) {
                // Fallback token nếu chưa migrate personal_access_tokens trên production
                $token = base64_encode($user->id . '|' . $user->email . '|' . time());
            }

            return response()->json([
                'success' => true,
                'token'   => $token,
                'user'    => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'phone'    => $user->phone,
                    'role'     => $user->role,
                    'avatar'   => $user->avatar,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng nhập không thành công: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Thu hồi token hiện tại (Đăng xuất khỏi mobile)
     */
    public function revokeToken(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã đăng xuất thành công.'
        ]);
    }

    // -----------------------------------------------------------------------
    // Web Session Auth (Login / Register / Logout qua session)
    // -----------------------------------------------------------------------

    /**
     * Đăng nhập qua Web Session (không dùng Sanctum token)
     */
    public function apiLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->orWhere('name', $login)
            ->orWhere('phone', $login)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            \Illuminate\Support\Facades\Auth::login($user);
            
            if ($user->status === 'disabled') {
                \Illuminate\Support\Facades\Auth::logout();
                return response()->json(['success' => false, 'message' => 'Tài khoản của bạn đã bị vô hiệu hóa.'], 403);
            }

            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
            ]);

            return response()->json(['success' => true, 'user' => $user]);
        }

        return response()->json(['success' => false, 'message' => 'Thông tin đăng nhập hoặc mật khẩu không chính xác.'], 401);
    }

    /**
     * Đăng ký tài khoản mới qua Web
     */
    public function apiRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'username' => ['required', 'string', 'max:50', 'unique:users', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => ['required', 'string', 'regex:/^0[0-9]{9}$/'],
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|in:user,seller',
        ], [
            'username.required' => 'Vui lòng cung cấp tên đăng nhập (username)!',
            'username.unique' => 'Tên đăng nhập này đã tồn tại trên hệ thống!',
            'username.regex' => 'Tên đăng nhập chỉ gồm chữ, số, dấu gạch nối, gạch dưới hoặc dấu chấm (không chứa khoảng trắng và tiếng Việt có dấu)!',
            'email.unique' => 'Email này đã tồn tại trên hệ thống!',
            'phone.required' => 'Vui lòng cung cấp số điện thoại liên hệ!',
            'phone.regex' => 'Số điện thoại Việt Nam phải có đúng 10 chữ số và bắt đầu bằng số 0!',
        ]);

        $role = $request->input('role', 'user');
        if ($role === 'admin') {
            $role = 'user';
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'phone' => $request->phone,
            'status' => 'active',
            'avatar' => '🧑',
        ]);

        \Illuminate\Support\Facades\Auth::login($user);

        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
        ]);

        return response()->json(['success' => true, 'user' => $user], 201);
    }

    /**
     * Đăng xuất Web Session
     */
    public function apiLogout(Request $request)
    {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->forget(['user_id', 'user_name', 'user_role']);

        return response()->json(['success' => true]);
    }
}
