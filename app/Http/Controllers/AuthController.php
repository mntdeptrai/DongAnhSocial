<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        protected AuthServiceInterface $authService
    ) {}

    public function showLogin(Request $request)
    {
        if (Auth::check() || session()->has('user_id')) {
            $user = Auth::user() ?: User::find(session('user_id'));
            return redirect($this->authService->getLoginRedirectUrl($user));
        }

        if ($request->has('redirect')) {
            session(['url.intended' => $request->query('redirect')]);
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $result = $this->authService->attemptLogin($request->input('email'), $request->input('password'));

        if ($result['success']) {
            $request->session()->regenerate();
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $result['message']]);
            }
            return redirect($this->authService->getLoginRedirectUrl($result['user']));
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $result['message']], $result['status_code']);
        }

        return back()->withErrors(['email' => $result['message']])->onlyInput('email');
    }

    public function showRegister(Request $request)
    {
        if (Auth::check() || session()->has('user_id')) {
            $role = session('user_role') ?: (Auth::user() ? Auth::user()->role : 'user');
            if ($role === 'admin' || $role === 'seller') {
                return redirect('/admin/dashboard');
            }
            return redirect('/');
        }
        if ($request->has('redirect')) {
            session(['url.intended' => $request->query('redirect')]);
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:50',
            'username' => ['required', 'string', 'max:50', 'unique:users', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'email'    => 'required|string|email|max:100|unique:users',
            'phone'    => ['required', 'string', 'regex:/^0[0-9]{9}$/'],
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'nullable|string|in:user,seller',
            'otp'      => 'required|string|size:6',
        ], [
            'username.required' => 'Vui lòng cung cấp tên đăng nhập (username)!',
            'username.unique'   => 'Tên đăng nhập này đã tồn tại trên hệ thống!',
            'username.regex'    => 'Tên đăng nhập chỉ gồm chữ, số, dấu gạch nối, gạch dưới hoặc dấu chấm!',
            'email.unique'      => 'Email này đã tồn tại trên hệ thống!',
            'phone.required'    => 'Vui lòng cung cấp số điện thoại liên hệ!',
            'phone.regex'       => 'Số điện thoại Việt Nam phải có đúng 10 chữ số và bắt đầu bằng số 0!',
            'otp.required'      => 'Vui lòng nhập mã xác thực OTP!',
            'otp.size'          => 'Mã OTP phải có độ dài 6 ký tự!',
        ]);

        try {
            $user = $this->authService->register($validated);
            if ($user->role === 'seller' || $user->role === 'admin') {
                return redirect('/admin/dashboard')->with('success', 'Đăng ký tài khoản thành công!');
            }
            return redirect('/')->with('success', 'Đăng ký tài khoản thành công!');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['otp' => $e->getMessage()])->withInput();
        }
    }

    public function sendRegisterOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:100',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email!',
            'email.email'    => 'Địa chỉ email không đúng định dạng!',
        ]);

        $result = $this->authService->sendRegisterOtp($request->email);
        $code = $result['code'] ?? 200;
        unset($result['code']);

        return response()->json($result, $code);
    }

    public function logout(Request $request)
    {
        $this->authService->logout();
        return redirect('/');
    }

    public function profile(Request $request, $identifier = null)
    {
        $currentUserId = session('user_id') ?: Auth::id();
        $data = $this->authService->getProfileData($identifier, $currentUserId);

        if (!$data) {
            if ($identifier) {
                abort(404, 'Trang cá nhân hoặc cơ sở không tồn tại.');
            }
            return redirect('/auth/login');
        }

        return view('auth.profile', $data);
    }

    public function updateProfile(Request $request)
    {
        $userId = session('user_id') ?: Auth::id();
        if (!$userId) {
            return redirect('/auth/login');
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'required|string|email|max:100|unique:users,email,' . $userId,
            'phone'         => 'nullable|string|max:25',
            'address'       => 'nullable|string|max:255',
            'website'       => 'nullable|string|max:255',
            'opening_hours' => 'nullable|string|max:100',
            'bank_account'  => 'nullable|string',
            'bank_name'     => 'nullable|string',
        ], [
            'email.unique' => 'Email này đã tồn tại trên hệ thống!',
        ]);

        $this->authService->updateProfile($userId, $validated);
        return redirect()->back()->with('success', 'Cập nhật thông tin cá nhân và thông tin gian hàng liên kết thành công!');
    }

    public function deleteAccount(Request $request)
    {
        $userId = session('user_id') ?: Auth::id();
        if (!$userId) {
            return redirect('/auth/login');
        }

        try {
            $this->authService->deleteAccount($userId, (string)$request->input('confirmation_keyword', ''));
            return redirect('/')->with('success', 'Tài khoản và toàn bộ dữ liệu cá nhân của bạn đã được xóa vĩnh viễn khỏi hệ thống.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function updateAvatar(Request $request)
    {
        $user = $request->user('sanctum') ?: Auth::user() ?: User::find(session('user_id'));
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
        }

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $publicUrl = $this->authService->updateAvatar($user, $request->file('avatar'));

        return response()->json([
            'success'    => true,
            'message'    => 'Cập nhật ảnh đại diện thành công!',
            'avatar_url' => $publicUrl,
        ]);
    }

    public function updateCoverPhoto(Request $request)
    {
        $user = $request->user('sanctum') ?: Auth::user() ?: User::find(session('user_id'));
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
        }

        $request->validate([
            'cover' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $publicUrl = $this->authService->updateCover($user, $request->file('cover'));

        return response()->json([
            'success'   => true,
            'message'   => 'Cập nhật ảnh bìa thành công!',
            'cover_url' => $publicUrl,
        ]);
    }

    public function changePassword(Request $request)
    {
        $userId = session('user_id') ?: Auth::id();
        $user = User::find($userId);
        if (!$user) {
            return redirect('/auth/login');
        }

        $request->validate([
            'otp'      => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $result = $this->authService->changePassword($user, $request->otp, $request->password);

        if (!$result['success']) {
            return redirect()->back()->withErrors(['otp' => $result['message']])->withInput();
        }

        return redirect()->back()->with('success', $result['message']);
    }

    public function sendOtp(Request $request)
    {
        $userId = session('user_id') ?: Auth::id();
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Người dùng chưa đăng nhập!'], 401);
        }

        $result = $this->authService->sendOtp($user);
        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function showForgotPassword(Request $request)
    {
        if (Auth::check() || session()->has('user_id')) {
            return redirect('/');
        }
        return view('auth.forgot-password');
    }

    public function sendForgotPasswordOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:100',
        ]);

        $result = $this->authService->sendForgotPasswordOtp($request->email);
        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email|max:100',
            'otp'      => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $result = $this->authService->resetPassword($request->email, $request->otp, $request->password);

        if (!$result['success']) {
            return redirect()->back()->withErrors(['otp' => $result['message']])->withInput();
        }

        return redirect('/auth/login')->with('success', $result['message']);
    }

    public function heartbeat(Request $request)
    {
        $this->authService->heartbeat(session('user_id') ?: Auth::id());
        return response()->json(['status' => 'success']);
    }
}
