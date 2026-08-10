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
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Tài khoản của bạn đã bị ban quản trị vô hiệu hóa.'], 403);
                }
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

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Đăng nhập thành công!']);
            }

            if (in_array($user->role, ['admin', 'manager'])) {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->role === 'seller') {
                return redirect()->intended('/seller/dashboard');
            } elseif ($user->role === 'principal') {
                return redirect()->intended('/principal/schools');
            }
            return redirect()->intended('/');
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Sai tài khoản hoặc mật khẩu!'], 422);
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
    public function profile(\Illuminate\Http\Request $request, $identifier = null)
    {
        $currentUserId = session('user_id') ?: Auth::id();
        $user = null;

        if ($identifier) {
            // 1. Tìm theo username
            $user = User::where('username', $identifier)->first();

            // 2. Nếu không tìm thấy theo username, tìm trường học/cơ sở theo slug
            if (!$user) {
                $schoolBySlug = \App\Models\Eatery::where('slug', $identifier)->first();
                if (!$schoolBySlug) {
                    try {
                        $schoolBySlug = \App\Models\Eatery::on('mysql_education')->where('slug', $identifier)->first();
                    } catch (\Exception $e) {}
                }

                if ($schoolBySlug) {
                    $school = $schoolBySlug;
                    if ($schoolBySlug->user_id) {
                        $user = User::find($schoolBySlug->user_id);
                    }
                    if (!$user) {
                        $user = new User([
                            'name' => $schoolBySlug->name,
                            'username' => $schoolBySlug->slug,
                            'role' => 'principal',
                        ]);
                        $user->id = $schoolBySlug->user_id ?: 0;
                    }
                }
            }

            // 3. Tìm theo slug tên người dùng (VD: /profile/tuan-anh)
            if (!$user) {
                $allUsers = User::all();
                foreach ($allUsers as $u) {
                    if (\Illuminate\Support\Str::slug($u->name) === $identifier || \Illuminate\Support\Str::slug($u->username ?? '') === $identifier) {
                        $user = $u;
                        break;
                    }
                }
            }

            // 4. Fallback tìm theo ID nếu tham số truyền vào là số (cho tương thích cũ)
            if (!$user && is_numeric($identifier)) {
                $user = User::find($identifier);
            }
        }

        // Nếu không có identifier hoặc truy cập trang cá nhân của mình
        if (!$user) {
            $user = $currentUserId ? User::find($currentUserId) : null;
        }

        if (!$user) {
            if ($identifier) {
                abort(404, 'Trang cá nhân hoặc cơ sở không tồn tại.');
            }
            return redirect('/auth/login');
        }
        
        $tours = collect();
        if (!isset($school) || !$school) {
            $school = null;
        }

        if ($user->isPrincipal() || $user->role === 'principal') {
            if (!$school) {
                $school = \App\Models\Eatery::on('mysql_education')->where('user_id', $user->id)->first();
                if (!$school) {
                    $school = \App\Models\Eatery::on('mysql')->where('user_id', $user->id)->first();
                }

                // Fallback thông minh: Tự động gán cơ sở nếu tên Tài khoản trùng khớp với tên Trường học
                if (!$school && !empty($user->name)) {
                    $searchName = trim(preg_replace('/^(trường\s+|tài\s+khoản\s+)/ui', '', $user->name));
                    if (mb_strlen($searchName) >= 3) {
                        $school = \App\Models\Eatery::on('mysql_education')
                            ->where(function($q) use ($searchName, $user) {
                                $q->where('name', $searchName)
                                  ->orWhere('name', 'Trường ' . $searchName)
                                  ->orWhere('name', 'Trường Tiểu học ' . $searchName)
                                  ->orWhere('name', 'Trường THCS ' . $searchName)
                                  ->orWhere('name', 'Trường THPT ' . $searchName)
                                  ->orWhere('name', 'Trường Mầm non ' . $searchName);
                            })
                            ->where(function($q) use ($user) {
                                $q->whereNull('user_id')->orWhere('user_id', 0)->orWhere('user_id', $user->id);
                            })
                            ->first();

                        if ($school) {
                            $school->user_id = $user->id;
                            $school->save();
                        }
                    }
                }
            }
        }

        $tours = \App\Models\FoodTour::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($school) {
            $eduPosts = \App\Models\EducationProgram::on('mysql_education')
                ->where('eatery_id', $school->id)
                ->orderBy('created_at', 'desc')
                ->get();
            if ($eduPosts->isEmpty()) {
                $eduPosts = \App\Models\EducationProgram::on('mysql')
                    ->where('eatery_id', $school->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
            $userPosts = \App\Models\Post::on('mysql_education')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            if ($userPosts->isEmpty()) {
                $userPosts = \App\Models\Post::on('mysql')
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
            $posts = $eduPosts->concat($userPosts)->sortByDesc('created_at')->values();
        } else {
            $posts = \App\Models\Post::on('mysql_education')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            if ($posts->isEmpty()) {
                $posts = \App\Models\Post::on('mysql')
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
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
            } else if (!empty($currentSessionId)) {
                $uQuery->whereNull('user_id')->where('session_id', $currentSessionId);
            } else {
                $uQuery->whereRaw('1 = 0');
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

        $stall = null;
        $ocopProductsCount = 0;
        if ($user->isSeller() || $user->role === 'seller') {
            $stall = $user->getStall();
            try {
                $ocopProductsCount = \App\Models\OcopProduct::where('user_id', $user->id)->count();
            } catch (\Exception $e) {}
        }

        $friendsList = \App\Models\User::where('id', '!=', $user->id)
            ->select('id', 'name', 'avatar', 'role', 'username')
            ->limit(50)
            ->get();

        $locationConnections = [
            'mysql' => 'Ẩm thực',
            'mysql_education' => 'Trường học',
            'mysql_culture' => 'Văn hóa / Di sản',
            'mysql_stay' => 'Lưu trú',
            'mysql_wellness' => 'Y tế',
            'mysql_market' => 'Chợ / Gian hàng',
        ];

        $allLocations = [];
        foreach ($locationConnections as $conn => $catLabel) {
            try {
                $items = \App\Models\Eatery::on($conn)
                    ->select('id', 'name', 'address', 'image_path')
                    ->limit(40)
                    ->get();

                foreach ($items as $loc) {
                    if (!empty($loc->name)) {
                        $allLocations[] = [
                            'name' => $loc->name,
                            'address' => $loc->address ?: 'Đông Anh, Hà Nội',
                            'category' => $catLabel,
                            'image' => $loc->image_path ?: '',
                        ];
                    }
                }
            } catch (\Exception $e) {}
        }

        return view('auth.profile', compact('user', 'tours', 'school', 'posts', 'followersCount', 'followingCount', 'reviews', 'photos', 'videos', 'stall', 'ocopProductsCount', 'friendsList', 'allLocations'));
    }

    /**
     * Cập nhật thông tin tài khoản cá nhân & Thông tin địa điểm
     */
    public function updateProfile(Request $request)
    {
        $userId = session('user_id') ?: Auth::id();
        $user = User::find($userId);
        if (!$user) {
            return redirect('/auth/login');
        }

        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'opening_hours' => 'nullable|string|max:100',
        ], [
            'email.unique' => 'Email này đã tồn tại trên hệ thống!',
        ]);

        $oldPhone = $user->phone;
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('phone')) {
            $user->phone = $request->phone;
        }
        if ($request->filled('bank_account')) {
            $user->bank_account = $request->bank_account;
        }
        if ($request->filled('bank_name')) {
            $user->bank_name = $request->bank_name;
        }
        $user->save();

        // 1. Cập nhật đồng bộ toàn bộ Gian hàng / Địa điểm thuộc sở hữu của User này trên mọi Database connections
        $dbConnections = ['mysql', 'mysql_market', 'mysql_education', 'mysql_stay', 'mysql_wellness'];
        foreach ($dbConnections as $conn) {
            try {
                $eateries = \App\Models\Eatery::on($conn)->where('user_id', $user->id)->get();
                foreach ($eateries as $e) {
                    if ($request->has('address') && $request->filled('address')) $e->address = $request->address;
                    if ($request->has('phone') && $request->filled('phone')) $e->phone = $request->phone;
                    if ($request->has('website')) $e->website = $request->website;
                    if ($request->has('opening_hours')) $e->opening_hours = $request->opening_hours;
                    if ($request->filled('bank_account')) $e->bank_account = $request->bank_account;
                    if ($request->filled('bank_name')) $e->bank_name = $request->bank_name;
                    $e->save();
                }
            } catch (\Exception $ex) {
                // Ignore if connection not configured
            }
        }

        // 2. Cập nhật đồng bộ Hộ kinh doanh Tuyến đường 4.0 (RouteBusiness)
        try {
            $query = \App\Models\RouteBusiness::where('user_id', $user->id);
            if (!empty($oldPhone)) {
                $cleanOld = preg_replace('/[^0-9]/', '', $oldPhone);
                if (!empty($cleanOld)) {
                    $query->orWhere('phone', $oldPhone)->orWhere('phone', $cleanOld);
                }
            }
            $linkedRouteBusinesses = $query->get();

            foreach ($linkedRouteBusinesses as $rb) {
                $rb->user_id = $user->id;
                $rb->owner = $user->name;
                if ($request->filled('phone')) $rb->phone = $user->phone;
                if ($request->filled('bank_account')) $rb->bank_account = $user->bank_account;
                if ($request->filled('bank_name')) $rb->bank_name = $user->bank_name;
                $rb->save();
            }
        } catch (\Exception $ex) {}

        // 3. Cập nhật sản phẩm & thông tin người bán gian hàng chợ (ocop_products)
        try {
            \Illuminate\Support\Facades\DB::connection('mysql_market')
                ->table('ocop_products')
                ->where('user_id', $user->id)
                ->update([
                    'seller_name'  => $user->name,
                    'seller_phone' => $user->phone,
                    'bank_account' => $user->bank_account,
                    'bank_name'    => $user->bank_name,
                    'updated_at'   => now(),
                ]);
        } catch (\Exception $ex) {}

        // Cập nhật thông tin vào session
        session(['user_name' => $user->name]);

        return redirect()->back()->with('success', 'Cập nhật thông tin cá nhân và thông tin gian hàng liên kết thành công!');
    }

    /**
     * Cập nhật ảnh đại diện (avatar) của người dùng
     */
    public function updateAvatar(Request $request)
    {
        $user = $request->user('sanctum') ?: Auth::user() ?: User::find(session('user_id'));
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
        }

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'avatar.required' => 'Vui lòng chọn ảnh đại diện!',
            'avatar.image'    => 'File phải là hình ảnh!',
            'avatar.mimes'    => 'Chỉ chấp nhận định dạng: jpeg, png, jpg, gif, webp',
            'avatar.max'      => 'Kích thước ảnh tối đa là 5MB!',
        ]);

        try {
            // Xóa ảnh cũ trên R2 nếu là file path
            if ($user->avatar && str_starts_with($user->avatar, 'avatars/')) {
                try { \Storage::disk('r2')->delete($user->avatar); } catch (\Exception $e) {}
            }
            $path = $request->file('avatar')->store('avatars', 'r2');
            $publicUrl = rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $path;
        } catch (\Exception $e) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $publicUrl = asset('storage/' . $path);
        }

        $user->avatar = $path;
        $user->save();

        // Đồng bộ lưu ảnh đại diện mới vào Thư viện ảnh (EateryPhoto / Gallery)
        $school = \App\Models\Eatery::on('mysql_education')->where('user_id', $user->id)->first();
        if (!$school) {
            $school = \App\Models\Eatery::on('mysql')->where('user_id', $user->id)->first();
        }
        if ($school) {
            try {
                \App\Models\EateryPhoto::create([
                    'eatery_id'  => $school->id,
                    'image_path' => $publicUrl,
                    'caption'    => 'Ảnh đại diện - ' . $user->name,
                    'sort_order' => 1,
                ]);
            } catch (\Exception $e) {}
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Cập nhật ảnh đại diện thành công!',
            'avatar_url' => $publicUrl,
        ]);
    }

    /**
     * Cập nhật ảnh bìa (cover photo) của địa điểm / người dùng
     */
    public function updateCoverPhoto(Request $request)
    {
        $user = $request->user('sanctum') ?: Auth::user() ?: User::find(session('user_id'));
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
        }

        $request->validate([
            'cover' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'cover.required' => 'Vui lòng chọn ảnh bìa!',
            'cover.image'    => 'File phải là hình ảnh!',
            'cover.mimes'    => 'Chỉ chấp nhận định dạng: jpeg, png, jpg, gif, webp',
            'cover.max'      => 'Kích thước ảnh tối đa là 5MB!',
        ]);

        try {
            $path = $request->file('cover')->store('covers', 'r2');
            $publicUrl = rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $path;
        } catch (\Exception $e) {
            $path = $request->file('cover')->store('covers', 'public');
            $publicUrl = asset('storage/' . $path);
        }

        // Cập nhật ảnh bìa cho trường học / địa điểm liên kết nếu có
        $school = \App\Models\Eatery::on('mysql_education')->where('user_id', $user->id)->first();
        if (!$school) {
            $school = \App\Models\Eatery::on('mysql')->where('user_id', $user->id)->first();
        }

        if ($school) {
            $school->image_path = $publicUrl;
            $school->save();

            // Đồng bộ lưu ảnh bìa mới vào Thư viện ảnh (EateryPhoto / Gallery)
            try {
                \App\Models\EateryPhoto::create([
                    'eatery_id'  => $school->id,
                    'image_path' => $publicUrl,
                    'caption'    => 'Ảnh bìa - ' . $user->name,
                    'sort_order' => 0,
                ]);
            } catch (\Exception $e) {}
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Cập nhật ảnh bìa thành công!',
            'cover_url' => $publicUrl,
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
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'last_active_at')) {
                    $user = User::find($userId);
                    if ($user) {
                        $user->update([
                            'last_active_at' => now(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore missing table column error
            }
        }
        return response()->json(['status' => 'success']);
    }
}
