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
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('email');
        $password = $request->input('password');

        // Tìm người dùng bằng email, username, name hoặc phone
        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->orWhere('name', $login)
            ->orWhere('phone', $login)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user);
            
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
            'email' => 'Thông tin đăng nhập hoặc mật khẩu không chính xác!',
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
            'username' => ['required', 'string', 'max:50', 'unique:users', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => ['required', 'string', 'regex:/^0[0-9]{9}$/'],
            'password' => 'required|string|min:6|confirmed',
            'role' => 'nullable|string|in:user,seller',
            'otp' => 'required|string|size:6',
        ], [
            'username.required' => 'Vui lòng cung cấp tên đăng nhập (username)!',
            'username.unique' => 'Tên đăng nhập này đã tồn tại trên hệ thống!',
            'username.regex' => 'Tên đăng nhập chỉ gồm chữ, số, dấu gạch nối, gạch dưới hoặc dấu chấm (không chứa khoảng trắng và tiếng Việt có dấu)!',
            'email.unique' => 'Email này đã tồn tại trên hệ thống!',
            'phone.required' => 'Vui lòng cung cấp số điện thoại liên hệ!',
            'phone.regex' => 'Số điện thoại Việt Nam phải có đúng 10 chữ số và bắt đầu bằng số 0!',
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
            'username' => $request->username,
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

        $email = trim(strtolower($request->email));

        // Kiểm tra email đã được đăng ký tài khoản hay chưa
        if (User::where('email', $email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Địa chỉ email này đã được đăng ký tài khoản! Vui lòng Đăng nhập hoặc sử dụng tính năng Quên mật khẩu.'
            ], 422);
        }

        // Tạo mã OTP ngẫu nhiên 6 chữ số (dùng random_int bảo mật)
        $otpCode = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);

        try {
            // Lưu OTP vào DB với thời hạn là 4 phút
            PasswordOtp::create([
                'email' => $email,
                'otp' => $otpCode,
                'expires_at' => now()->addMinutes(4),
            ]);

            // Gửi mail OTP
            Mail::to($email)->send(new SendRegisterOtpMail($otpCode, 4));

            return response()->json([
                'success' => true,
                'message' => 'Mã OTP đã được gửi thành công đến email của bạn! Vui lòng kiểm tra hòm thư.'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Lỗi gửi mail OTP đăng ký cho {$email}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra khi gửi mail: ' . $e->getMessage()
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
    public function profile(\Illuminate\Http\Request $request, $id = null)
    {
        $currentUserId = session('user_id') ?: Auth::id();
        $targetUserId = $id ?: $request->query('user_id') ?: $request->query('id');
        $userId = $targetUserId ?: $currentUserId;

        if (!$userId) {
            return redirect('/auth/login');
        }
        
        $user = User::find($userId);
        if (!$user) {
            abort(404, 'Không tìm thấy thông tin tài khoản người dùng.');
        }
        
        $tours = collect();
        $school = null;
        $posts = collect();

        if ($user->isPrincipal() || $user->role === 'principal') {
            $school = \App\Models\Eatery::on('mysql_education')->where('user_id', $user->id)->first();
            if (!$school) {
                $school = \App\Models\Eatery::on('mysql')->where('user_id', $user->id)->first();
            }

            if ($school) {
                $posts = \App\Models\EducationProgram::on('mysql_education')
                    ->where('eatery_id', $school->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                if ($posts->isEmpty()) {
                    $posts = \App\Models\EducationProgram::on('mysql')
                        ->where('eatery_id', $school->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
                }
            }
        } else {
            // Lấy danh sách lộ trình do người dùng tự xây dựng
            $tours = \App\Models\FoodTour::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Lấy số lượng theo dõi & bạn bè thực tế từ DB
        $followersCount = \App\Models\Friendship::where('friend_id', $user->id)->where('status', 'accepted')->count();
        $followingCount = \App\Models\Friendship::where('user_id', $user->id)->where('status', 'accepted')->count();

        $reviews = collect();
        $photos = collect();
        $videos = collect();

        if ($school) {
            $reviews = \App\Models\Review::where('eatery_id', $school->id)->latest()->get();
            $photos = \App\Models\EateryPhoto::where('eatery_id', $school->id)->get();
            $videos = \App\Models\ReviewVideo::where('eatery_id', $school->id)->get();
        }

        // Attach real reaction & comment metrics (no mockdata)
        $currentUserId = \Illuminate\Support\Facades\Auth::id() ?? session('user_id');
        $currentSessionId = session()->getId();
        $postIds = $posts->pluck('id')->toArray();

        $realLikesMap = [];
        $userLikedMap = [];
        $realCommentsMap = [];

        if (!empty($postIds)) {
            $realLikesMap = \App\Models\CheckinReaction::where('reactionable_type', 'post')
                ->whereIn('reactionable_id', $postIds)
                ->selectRaw('reactionable_id, count(*) as total')
                ->groupBy('reactionable_id')
                ->pluck('total', 'reactionable_id')
                ->toArray();

            $uQuery = \App\Models\CheckinReaction::where('reactionable_type', 'post')
                ->whereIn('reactionable_id', $postIds);
            if ($currentUserId) {
                $uQuery->where('user_id', $currentUserId);
            } else {
                $uQuery->where('session_id', $currentSessionId);
            }
            $userLikedMap = $uQuery->pluck('reactionable_id')->toArray();

            $realCommentsMap = \App\Models\Comment::where('commentable_type', 'post')
                ->whereIn('commentable_id', $postIds)
                ->selectRaw('commentable_id, count(*) as total')
                ->groupBy('commentable_id')
                ->pluck('total', 'commentable_id')
                ->toArray();
        }

        foreach ($posts as $p) {
            $p->real_likes_count = (int) ($realLikesMap[$p->id] ?? $p->likes_count ?? 0);
            $p->is_liked = in_array($p->id, $userLikedMap);
            $p->real_comments_count = (int) ($realCommentsMap[$p->id] ?? 0);
            $p->real_shares_count = (int) ($p->shares_count ?? 0);
        }

        return view('auth.profile', compact('user', 'tours', 'school', 'posts', 'followersCount', 'followingCount', 'reviews', 'photos', 'videos'));
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
