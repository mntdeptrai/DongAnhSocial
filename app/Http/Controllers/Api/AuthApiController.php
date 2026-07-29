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
                'email'       => 'required|email',
                'password'    => 'required|string',
                'device_name' => 'nullable|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email hoặc mật khẩu không chính xác.'
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
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
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
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
            $user = \Illuminate\Support\Facades\Auth::user();
            
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

        return response()->json(['success' => false, 'message' => 'Email hoặc mật khẩu không đúng.'], 401);
    }

    /**
     * Đăng ký tài khoản mới qua Web
     */
    public function apiRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'required|string|max:15',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|in:user,seller',
        ]);

        $role = $request->input('role', 'user');
        if ($role === 'admin') {
            $role = 'user';
        }

        $user = User::create([
            'name' => $request->name,
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
