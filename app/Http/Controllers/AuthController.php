<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordOtp;
use App\Mail\SendPasswordOtpMail;
use App\Mail\SendRegisterOtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::check() || session()->has('user_id')) {
            $role = session('user_role') ?: (Auth::user() ? Auth::user()->role : 'user');
            if (in_array($role, ['admin', 'manager'])) {
                return redirect('/admin/dashboard');
            } elseif ($role === 'seller') {
                return redirect('/seller/dashboard');
            } elseif ($role === 'principal') {
                return redirect('/principal/schools');
            }
            return redirect('/');
        }
        if ($request->has('redirect')) {
            session(['url.intended' => $request->query('redirect')]);
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            if ($user->status === 'disabled') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tài khoản của bạn đã bị ban quản trị vô hiệu hóa.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            
            // Lưu thông tin người dùng vào Session tiện dụng
            session([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
            ]);

            if (in_array($user->role, ['admin', 'manager'])) {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->role === 'seller') {
                return redirect()->intended('/seller/dashboard');
            } elseif ($user->role === 'principal') {
                return redirect()->intended('/principal/schools');
            }
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'email hoặc mật khẩu không chính xác !',
        ])->onlyInput('email');
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
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'required|string|max:15',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'nullable|string|in:user,seller',
            'otp' => 'required|string|size:6',
        ], [
            'email.unique' => 'Email này đã tồn tại trên hệ thống!',
            'phone.required' => 'Vui lòng cung cấp số điện thoại liên hệ!',
            'otp.required' => 'Vui lòng nhập mã xác thực OTP!',
            'otp.size' => 'Mã OTP phải có độ dài 6 ký tự!',
        ]);

        // Kiểm tra OTP hợp lệ
        $otpRecord = PasswordOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            return redirect()->back()
                ->withErrors(['otp' => 'Mã xác thực OTP không chính xác hoặc đã hết hạn (4 phút)!'])
                ->withInput();
        }

        // Đánh dấu OTP đã sử dụng
        $otpRecord->used_at = now();
        $otpRecord->save();

        $role = $request->input('role', 'user');
        if ($role === 'admin') {
            $role = 'user'; // Bảo mật: Không cho phép tự ý đăng ký làm Admin
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

        Auth::login($user);

        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
        ]);

        if ($user->role === 'seller' || $user->role === 'admin') {
            return redirect('/admin/dashboard')->with('success', 'Đăng ký tài khoản thành công!');
        }
        return redirect('/')->with('success', 'Đăng ký tài khoản thành công!');
    }

    public function sendRegisterOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:100',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email!',
            'email.email' => 'Địa chỉ email không đúng định dạng!',
        ]);

        // Bảo mật: Nếu email đã tồn tại, trả về thông báo trung tính để tránh email enumeration
        // Không tiết lộ email có tồn tại hay không cho kẻ tấn công
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => true,
                'message' => 'Nếu địa chỉ email hợp lệ, mã OTP sẽ được gửi đến email của bạn trong vài giây.'
            ]);
        }

        // Tạo mã OTP ngẫu nhiên 6 chữ số (dùng random_int bảo mật)
        $otpCode = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);

        try {
            // Lưu OTP vào DB với thời hạn là 4 phút
            PasswordOtp::create([
                'email' => $request->email,
                'otp' => $otpCode,
                'expires_at' => now()->addMinutes(4),
            ]);

            // Gửi mail OTP
            Mail::to($request->email)->send(new SendRegisterOtpMail($otpCode, 4));

            return response()->json([
                'success' => true,
                'message' => 'Nếu địa chỉ email hợp lệ, mã OTP sẽ được gửi đến email của bạn trong vài giây.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra khi gửi mail. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        session()->forget(['user_id', 'user_name', 'user_role']);

        return redirect('/');
    }

    /**
     * Hiển thị trang Hồ sơ cá nhân (Profile Dashboard)
     */
    public function profile()
    {
        $userId = session('user_id') ?: Auth::id();
        $user = User::find($userId);
        if (!$user) {
            return redirect('/auth/login');
        }
        
        // Lấy danh sách lộ trình do người dùng tự xây dựng
        $tours = \App\Models\FoodTour::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('auth.profile', compact('user', 'tours'));
    }

    /**
     * Cập nhật thông tin tài khoản cá nhân
     */
    public function updateProfile(Request $request)
    {
        $userId = session('user_id') ?: Auth::id();
        $user = User::find($userId);
        if (!$user) {
            return redirect('/auth/login');
        }

        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:100|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:15',
        ], [
            'email.unique' => 'Email này đã tồn tại trên hệ thống!',
            'phone.required' => 'Vui lòng cung cấp số điện thoại liên hệ!',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        // Cập nhật thông tin vào session
        session(['user_name' => $user->name]);

        return redirect()->back()->with('success', 'Cập nhật thông tin tài khoản thành công!');
    }

    /**
     * Cập nhật ảnh đại diện (avatar) của người dùng — lưu lên Cloudflare R2
     */
    public function updateAvatar(Request $request)
    {
        $userId = session('user_id') ?: Auth::id();
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
        }

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
        ], [
            'avatar.required' => 'Vui lòng chọn ảnh đại diện!',
            'avatar.image'    => 'File phải là hình ảnh!',
            'avatar.mimes'    => 'Chỉ chấp nhận định dạng: jpeg, png, jpg, gif, webp',
            'avatar.max'      => 'Kích thước ảnh tối đa là 3MB!',
        ]);

        // Xóa ảnh cũ trên R2 nếu là file path (không phải emoji)
        if ($user->avatar && str_starts_with($user->avatar, 'avatars/')) {
            \Storage::disk('r2')->delete($user->avatar);
        }

        // Lưu ảnh mới lên Cloudflare R2 (thư mục avatars/, công khai)
        $path = $request->file('avatar')->store('avatars', 'r2');

        // URL công khai = R2_PUBLIC_URL + path
        $publicUrl = rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $path;

        $user->avatar = $path;   // lưu path để xóa sau này
        $user->save();

        return response()->json([
            'success'    => true,
            'message'    => 'Cập nhật ảnh đại diện thành công!',
            'avatar_url' => $publicUrl,
        ]);
    }

    /**
     * Thay đổi mật khẩu người dùng (có xác thực mã OTP)
     */
    public function changePassword(Request $request)
    {
        $userId = session('user_id') ?: Auth::id();
        $user = User::find($userId);
        if (!$user) {
            return redirect('/auth/login');
        }

        $request->validate([
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'otp.required' => 'Vui lòng nhập mã xác thực OTP!',
            'otp.size' => 'Mã OTP phải có độ dài 6 ký tự!',
            'password.required' => 'Vui lòng nhập mật khẩu mới!',
            'password.min' => 'Mật khẩu phải từ 6 ký tự trở lên!',
            'password.confirmed' => 'Xác nhận mật khẩu mới không trùng khớp!',
        ]);

        // Chống brute-force OTP: Đếm số lần nhập sai trong cửa sổ 4 phút
        $recentFailKey = 'otp_fail_' . md5($user->email . '_change');
        $failCount = cache()->get($recentFailKey, 0);
        if ($failCount >= 5) {
            return redirect()->back()
                ->withErrors(['otp' => 'Quá nhiều lần nhập sai. Vui lòng yêu cầu mã OTP mới sau vài phút.'])
                ->withInput();
        }

        // Kiểm tra OTP hợp lệ
        $otpRecord = PasswordOtp::where('email', $user->email)
            ->where('otp', $request->otp)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            // Tăng bộ đếm thất bại, hết hạn sau 5 phút
            cache()->put($recentFailKey, $failCount + 1, now()->addMinutes(5));
            return redirect()->back()
                ->withErrors(['otp' => 'Mã xác thực OTP không chính xác hoặc đã hết hạn (4 phút)!'])
                ->withInput();
        }

        // Reset bộ đếm thất bại khi thành công
        cache()->forget($recentFailKey);

        // Đánh dấu OTP đã sử dụng
        $otpRecord->used_at = now();
        $otpRecord->save();

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Thay đổi mật khẩu thành công!');
    }

    /**
     * Gửi mã OTP xác thực đổi mật khẩu qua email
     */
    public function sendOtp(Request $request)
    {
        $userId = session('user_id') ?: Auth::id();
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng chưa đăng nhập hoặc không tồn tại!'
            ], 401);
        }

        // Tạo mã OTP ngẫu nhiên 6 chữ số (dùng random_int bảo mật)
        $otpCode = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);

        try {
            // Lưu OTP vào DB với thời hạn là 4 phút
            PasswordOtp::create([
                'email' => $user->email,
                'otp' => $otpCode,
                'expires_at' => now()->addMinutes(4),
            ]);

            // Gửi mail OTP
            Mail::to($user->email)->send(new SendPasswordOtpMail($otpCode, 4));

            return response()->json([
                'success' => true,
                'message' => 'Mã OTP đã được gửi thành công đến email của bạn!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra khi gửi mail: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị trang Quên mật khẩu
     */
    public function showForgotPassword(Request $request)
    {
        if (Auth::check() || session()->has('user_id')) {
            return redirect('/');
        }
        return view('auth.forgot-password');
    }

    /**
     * Gửi OTP khôi phục mật khẩu — Bảo mật: trả về thông báo trung tính tránh email enumeration
     */
    public function sendForgotPasswordOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:100',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email!',
            'email.email' => 'Địa chỉ email không đúng định dạng!',
        ]);

        // Bảo mật: Dùng constant-time response tránh email enumeration
        // Không tiết lộ email có tồn tại hay không
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Trả về response trung tính ngay cả khi email không tồn tại
            return response()->json([
                'success' => true,
                'message' => 'Nếu địa chỉ email tồn tại trên hệ thống, mã OTP sẽ được gửi đến email của bạn trong vài giây.'
            ]);
        }

        // Tạo mã OTP ngẫu nhiên 6 chữ số (dùng random_int bảo mật)
        $otpCode = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);

        try {
            // Lưu OTP vào DB với thời hạn là 4 phút
            PasswordOtp::create([
                'email' => $request->email,
                'otp' => $otpCode,
                'expires_at' => now()->addMinutes(4),
            ]);

            // Gửi mail OTP
            Mail::to($request->email)->send(new SendPasswordOtpMail($otpCode, 4));

            return response()->json([
                'success' => true,
                'message' => 'Nếu địa chỉ email tồn tại trên hệ thống, mã OTP sẽ được gửi đến email của bạn trong vài giây.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra khi gửi mail. Vui lòng thử lại.'
            ], 500);
        }
    }

    /**
     * Đặt lại mật khẩu mới bằng OTP
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:100',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email!',
            'email.email' => 'Địa chỉ email không đúng định dạng!',
            'otp.required' => 'Vui lòng nhập mã xác thực OTP!',
            'otp.size' => 'Mã OTP phải có độ dài 6 ký tự!',
            'password.required' => 'Vui lòng nhập mật khẩu mới!',
            'password.min' => 'Mật khẩu phải từ 6 ký tự trở lên!',
            'password.confirmed' => 'Xác nhận mật khẩu mới không trùng khớp!',
        ]);

        // Kiểm tra xem email có tồn tại không
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return redirect()->back()
                ->withErrors(['email' => 'Email này không tồn tại trên hệ thống!'])
                ->withInput();
        }

        // Chống brute-force OTP: Khoá sau 5 lần nhập sai liên tiếp
        $recentFailKey = 'otp_fail_' . md5($request->email . '_reset');
        $failCount = cache()->get($recentFailKey, 0);
        if ($failCount >= 5) {
            return redirect()->back()
                ->withErrors(['otp' => 'Quá nhiều lần nhập sai. Vui lòng yêu cầu mã OTP mới sau vài phút.'])
                ->withInput();
        }

        // Kiểm tra OTP hợp lệ
        $otpRecord = PasswordOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            // Tăng bộ đếm thất bại, hết hạn sau 5 phút
            cache()->put($recentFailKey, $failCount + 1, now()->addMinutes(5));
            return redirect()->back()
                ->withErrors(['otp' => 'Mã xác thực OTP không chính xác hoặc đã hết hạn (4 phút)!'])
                ->withInput();
        }

        // Reset bộ đếm thất bại khi thành công
        cache()->forget($recentFailKey);

        // Đánh dấu OTP đã sử dụng
        $otpRecord->used_at = now();
        $otpRecord->save();

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect('/auth/login')->with('success', 'Khôi phục mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.');
    }

    /**
     * Heartbeat endpoint to update user last active timestamp.
     */
    public function heartbeat(Request $request)
    {
        $userId = session('user_id') ?: Auth::id();
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $user->update([
                    'last_active_at' => now(),
                ]);
            }
        }
        return response()->json(['status' => 'success']);
    }
}
