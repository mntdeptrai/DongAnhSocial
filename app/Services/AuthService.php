<?php

namespace App\Services;

use App\Helpers\R2Helper;
use App\Mail\SendPasswordOtpMail;
use App\Mail\SendRegisterOtpMail;
use App\Models\CheckinReaction;
use App\Models\Comment;
use App\Models\Eatery;
use App\Models\EateryPhoto;
use App\Models\EducationProgram;
use App\Models\FoodTour;
use App\Models\Friendship;
use App\Models\OcopProduct;
use App\Models\PasswordOtp;
use App\Models\Post;
use App\Models\Review;
use App\Models\ReviewVideo;
use App\Models\RouteBusiness;
use App\Models\User;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\ModerationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthService implements AuthServiceInterface
{
    public function getLoginRedirectUrl(?User $user): ?string
    {
        if (!$user) {
            return '/';
        }

        if (in_array($user->role, ['admin', 'manager'])) {
            return '/admin/dashboard';
        } elseif ($user->role === 'health_station') {
            return '/health-station/dashboard';
        } elseif ($user->role === 'principal') {
            return '/principal/schools';
        } elseif ($user->role === 'seller' || in_array($user->role, ['hkd', 'dn', 'business'])) {
            $hasWellness = DB::table('eateries')
                ->join('categories', 'eateries.category_id', '=', 'categories.id')
                ->where('categories.slug', 'wellness-care')
                ->where(function($q) use ($user) {
                    $q->where('eateries.user_id', $user->id);
                    if (!empty($user->phone)) {
                        $q->orWhere('eateries.phone', $user->phone);
                    }
                })->exists();

            if ($hasWellness && $user->role === 'health_station') {
                return '/health-station/dashboard';
            }

            return '/hkd/dashboard';
        }

        return '/';
    }

    public function attemptLogin(string $login, string $password): array
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $login);

        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->orWhere('name', $login)
            ->orWhere('phone', $login);

        if (!empty($cleanPhone) && strlen($cleanPhone) >= 8) {
            $phoneVariants = array_unique([
                $cleanPhone,
                '0' . ltrim($cleanPhone, '0'),
                '+84' . ltrim($cleanPhone, '0'),
                '84' . ltrim($cleanPhone, '0'),
            ]);
            $user->orWhereIn('phone', $phoneVariants);
        }

        $user = $user->first();

        if (!$user && !empty($login)) {
            $eatery = Eatery::where('storytelling_data->mst', $login)
                ->orWhere('storytelling_data->tax_code', $login)
                ->first();
            if ($eatery && $eatery->user_id) {
                $user = User::find($eatery->user_id);
            }
        }

        if ($user && Hash::check($password, $user->password)) {
            if ($user->status === 'disabled') {
                return [
                    'success'     => false,
                    'message'     => 'Tài khoản của bạn đã bị ban quản trị vô hiệu hóa.',
                    'user'        => null,
                    'status_code' => 403,
                ];
            }

            Auth::login($user);
            session([
                'user_id'   => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
            ]);

            return [
                'success'     => true,
                'message'     => 'Đăng nhập thành công!',
                'user'        => $user,
                'status_code' => 200,
            ];
        }

        return [
            'success'     => false,
            'message'     => 'Thông tin đăng nhập hoặc mật khẩu không chính xác!',
            'user'        => null,
            'status_code' => 422,
        ];
    }

    public function register(array $data): User
    {
        $otpRecord = PasswordOtp::where('email', $data['email'])
            ->where('otp', $data['otp'])
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            throw new \InvalidArgumentException('Mã xác thực OTP không chính xác hoặc đã hết hạn (4 phút)!');
        }

        $otpRecord->used_at = now();
        $otpRecord->save();

        $role = $data['role'] ?? 'user';
        if ($role === 'admin') {
            $role = 'user';
        }

        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $role,
            'phone'    => $data['phone'],
            'status'   => 'active',
            'avatar'   => '🧑',
        ]);

        Auth::login($user);

        session([
            'user_id'   => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
        ]);

        return $user;
    }

    public function sendRegisterOtp(string $email): array
    {
        $cleanEmail = trim(strtolower($email));

        if (User::where('email', $cleanEmail)->exists()) {
            return [
                'success' => false,
                'message' => 'Địa chỉ email này đã được đăng ký tài khoản! Vui lòng Đăng nhập hoặc sử dụng tính năng Quên mật khẩu.',
                'code'    => 422
            ];
        }

        $otpCode = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);

        try {
            PasswordOtp::updateOrCreate(
                ['email' => $cleanEmail],
                ['otp' => $otpCode, 'expires_at' => now()->addMinutes(4)]
            );

            Mail::to($cleanEmail)->send(new SendRegisterOtpMail($otpCode, 4));

            return [
                'success' => true,
                'message' => 'Mã OTP đã được gửi thành công đến email của bạn! Vui lòng kiểm tra hòm thư.'
            ];
        } catch (\Exception $e) {
            Log::error("Lỗi gửi mail OTP đăng ký cho {$cleanEmail}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Không thể gửi mã xác nhận lúc này. Vui lòng kiểm tra lại email hoặc thử lại sau.',
                'code'    => 500
            ];
        }
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        session()->forget(['user_id', 'user_name', 'user_role']);
    }

    public function getProfileData(?string $identifier, ?int $currentUserId): ?array
    {
        $user = null;

        if ($identifier) {
            $user = User::where('username', $identifier)->first();

            if (!$user) {
                $schoolBySlug = Eatery::where('slug', $identifier)->first();
                if (!$schoolBySlug) {
                    try {
                        $schoolBySlug = Eatery::on('mysql_education')->where('slug', $identifier)->first();
                    } catch (\Exception $e) {}
                }

                if ($schoolBySlug) {
                    $school = $schoolBySlug;
                    if ($schoolBySlug->user_id) {
                        $user = User::find($schoolBySlug->user_id);
                    }
                    if (!$user) {
                        $user = new User([
                            'name'     => $schoolBySlug->name,
                            'username' => $schoolBySlug->slug,
                            'role'     => 'principal',
                        ]);
                        $user->id = $schoolBySlug->user_id ?: 0;
                    }
                }
            }

            if (!$user) {
                $allUsers = User::select('id', 'name', 'username', 'email', 'avatar', 'phone', 'role', 'status', 'eatery_id')->get();
                foreach ($allUsers as $u) {
                    if (Str::slug($u->name) === $identifier || Str::slug($u->username ?? '') === $identifier) {
                        $user = $u;
                        break;
                    }
                }
            }

            if (!$user && is_numeric($identifier)) {
                $user = User::find($identifier);
            }
        }

        if (!$user && $currentUserId) {
            $user = User::find($currentUserId);
        }

        if (!$user) {
            return null;
        }

        $school = null;
        if ($user->isPrincipal() || $user->role === 'principal') {
            $school = Eatery::on('mysql_education')->where('user_id', $user->id)->first() ?: Eatery::on('mysql')->where('user_id', $user->id)->first();
            if (!$school && !empty($user->name)) {
                $searchName = trim(preg_replace('/^(trường\s+|tài\s+khoản\s+)/ui', '', $user->name));
                if (mb_strlen($searchName) >= 3) {
                    $school = Eatery::on('mysql_education')
                        ->where(function($q) use ($searchName) {
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

        $tours = FoodTour::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($school) {
            $eduPosts = EducationProgram::on('mysql_education')->where('eatery_id', $school->id)->orderBy('created_at', 'desc')->get();
            if ($eduPosts->isEmpty()) {
                $eduPosts = EducationProgram::on('mysql')->where('eatery_id', $school->id)->orderBy('created_at', 'desc')->get();
            }
            $userPosts = Post::on('mysql_education')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
            if ($userPosts->isEmpty()) {
                $userPosts = Post::on('mysql')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
            }
            $posts = $eduPosts->concat($userPosts)->sortByDesc('created_at')->values();
        } else {
            $posts = Post::on('mysql_education')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
            if ($posts->isEmpty()) {
                $posts = Post::on('mysql')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
            }
        }

        $followersCount = Friendship::where('friend_id', $user->id)->where('status', 'accepted')->count();
        $followingCount = Friendship::where('user_id', $user->id)->where('status', 'accepted')->count();

        $reviews = collect();
        $photos = collect();
        $videos = collect();

        if ($school) {
            $reviews = Review::where('eatery_id', $school->id)->latest()->get();
            $photos = EateryPhoto::where('eatery_id', $school->id)->get();
            $videos = ReviewVideo::where('eatery_id', $school->id)->get();
        }

        $sessionId = session()->getId();
        $postIds = $posts->pluck('id')->toArray();
        $realLikesMap = [];
        $userLikedMap = [];
        $realCommentsMap = [];

        if (!empty($postIds)) {
            $realLikesMap = CheckinReaction::where('reactionable_type', 'post')
                ->whereIn('reactionable_id', $postIds)
                ->selectRaw('reactionable_id, count(*) as total')
                ->groupBy('reactionable_id')
                ->pluck('total', 'reactionable_id')
                ->toArray();

            $uQuery = CheckinReaction::where('reactionable_type', 'post')->whereIn('reactionable_id', $postIds);
            if ($currentUserId) {
                $uQuery->where('user_id', $currentUserId);
            } else if (!empty($sessionId)) {
                $uQuery->whereNull('user_id')->where('session_id', $sessionId);
            } else {
                $uQuery->whereRaw('1 = 0');
            }
            $userLikedMap = $uQuery->pluck('reactionable_id')->toArray();

            $realCommentsMap = Comment::where('commentable_type', 'post')
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
                $ocopProductsCount = OcopProduct::where('user_id', $user->id)->count();
            } catch (\Exception $e) {}
        }

        $friendsList = User::where('id', '!=', $user->id)
            ->select('id', 'name', 'avatar', 'role', 'username')
            ->limit(50)
            ->get();

        $locationConnections = [
            'mysql'           => 'Ẩm thực',
            'mysql_education' => 'Trường học',
            'mysql_culture'   => 'Văn hóa / Di sản',
            'mysql_stay'      => 'Lưu trú',
            'mysql_wellness'  => 'Y tế',
            'mysql_market'    => 'Chợ / Gian hàng',
        ];

        $allLocations = [];
        foreach ($locationConnections as $conn => $catLabel) {
            try {
                $items = Eatery::on($conn)
                    ->select('id', 'name', 'address', 'image_path')
                    ->limit(40)
                    ->get();

                foreach ($items as $loc) {
                    if (!empty($loc->name)) {
                        $allLocations[] = [
                            'name'     => $loc->name,
                            'address'  => $loc->address ?: 'Đông Anh, Hà Nội',
                            'category' => $catLabel,
                            'image'    => $loc->image_path ?: '',
                        ];
                    }
                }
            } catch (\Exception $e) {}
        }

        return compact(
            'user', 'tours', 'school', 'posts', 'followersCount',
            'followingCount', 'reviews', 'photos', 'videos', 'stall',
            'ocopProductsCount', 'friendsList', 'allLocations'
        );
    }

    public function updateProfile(int $userId, array $data): User
    {
        $user = User::findOrFail($userId);
        $oldPhone = $user->phone;

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['phone'])) {
            $user->phone = $data['phone'];
        }
        if (!empty($data['bank_account'])) {
            $user->bank_account = $data['bank_account'];
        }
        if (!empty($data['bank_name'])) {
            $user->bank_name = $data['bank_name'];
        }
        $user->save();

        $eateryUpdate = array_filter([
            'address'       => !empty($data['address']) ? $data['address'] : null,
            'phone'         => !empty($data['phone']) ? $data['phone'] : null,
            'website'       => !empty($data['website']) ? $data['website'] : null,
            'opening_hours' => !empty($data['opening_hours']) ? $data['opening_hours'] : null,
            'bank_account'  => !empty($data['bank_account']) ? $data['bank_account'] : null,
            'bank_name'     => !empty($data['bank_name']) ? $data['bank_name'] : null,
        ], fn($v) => !is_null($v));

        if (!empty($eateryUpdate)) {
            foreach (['mysql', 'mysql_market', 'mysql_education', 'mysql_stay', 'mysql_wellness'] as $conn) {
                rescue(fn() => Eatery::on($conn)->where('user_id', $user->id)->update($eateryUpdate), report: false);
            }
        }

        rescue(function () use ($user, $oldPhone, $data) {
            $rbData = array_filter([
                'user_id'      => $user->id,
                'owner'        => $user->name,
                'phone'        => !empty($data['phone']) ? $user->phone : null,
                'bank_account' => !empty($data['bank_account']) ? $user->bank_account : null,
                'bank_name'    => !empty($data['bank_name']) ? $user->bank_name : null,
            ], fn($v) => !is_null($v));

            $cleanOld = $oldPhone ? preg_replace('/[^0-9]/', '', $oldPhone) : '';
            RouteBusiness::where('user_id', $user->id)
                ->when($oldPhone, fn($q) => $q->orWhere('phone', $oldPhone))
                ->when($cleanOld, fn($q) => $q->orWhere('phone', $cleanOld))
                ->update($rbData);
        }, report: false);

        rescue(fn() => DB::connection('mysql_market')
            ->table('ocop_products')
            ->where('user_id', $user->id)
            ->update([
                'seller_name'  => $user->name,
                'seller_phone' => $user->phone,
                'bank_account' => $user->bank_account,
                'bank_name'    => $user->bank_name,
                'updated_at'   => now(),
            ]), report: false);

        session(['user_name' => $user->name]);

        return $user;
    }

    public function deleteAccount(int $userId, string $keyword): bool
    {
        $user = User::findOrFail($userId);

        if (mb_strtoupper(trim($keyword)) !== 'XOA TAI KHOAN') {
            throw new \InvalidArgumentException('Cụm từ xác nhận không chính xác! Bạn cần nhập chính xác: XOA TAI KHOAN');
        }

        if ($user->role === 'admin') {
            throw new \InvalidArgumentException('Tài khoản Quản trị viên Tối cao không thể tự hủy qua kênh này. Vui lòng bàn giao quyền trước!');
        }

        ModerationService::deleteUserAccount($user->id);
        $this->logout();

        return true;
    }

    public function updateAvatar(User $user, $avatarFile): string
    {
        try {
            if ($user->avatar && str_starts_with($user->avatar, 'avatars/')) {
                try { Storage::disk('r2')->delete($user->avatar); } catch (\Exception $e) {}
            }
            $path = $avatarFile->store('avatars', 'r2');
            $publicUrl = rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $path;
        } catch (\Exception $e) {
            $path = $avatarFile->store('avatars', 'public');
            $publicUrl = asset('storage/' . $path);
        }

        $user->avatar = $path;
        $user->save();

        $school = Eatery::on('mysql_education')->where('user_id', $user->id)->first() ?: Eatery::on('mysql')->where('user_id', $user->id)->first();
        if ($school) {
            try {
                EateryPhoto::create([
                    'eatery_id'  => $school->id,
                    'image_path' => $publicUrl,
                    'caption'    => 'Ảnh đại diện - ' . $user->name,
                    'sort_order' => 1,
                ]);
            } catch (\Exception $e) {}
        }

        return $publicUrl;
    }

    public function updateCover(User $user, $coverFile): string
    {
        try {
            $path = $coverFile->store('covers', 'r2');
            $publicUrl = rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $path;
        } catch (\Exception $e) {
            $path = $coverFile->store('covers', 'public');
            $publicUrl = asset('storage/' . $path);
        }

        $user->cover = $path;
        $user->save();

        $school = Eatery::on('mysql_education')->where('user_id', $user->id)->first() ?: Eatery::on('mysql')->where('user_id', $user->id)->first();
        if ($school) {
            $school->image_path = $publicUrl;
            $school->save();

            try {
                EateryPhoto::create([
                    'eatery_id'  => $school->id,
                    'image_path' => $publicUrl,
                    'caption'    => 'Ảnh bìa - ' . $user->name,
                    'sort_order' => 0,
                ]);
            } catch (\Exception $e) {}
        }

        return $publicUrl;
    }

    public function changePassword(User $user, string $otp, string $newPassword): array
    {
        $recentFailKey = 'otp_fail_' . md5($user->email . '_change');
        $failCount = cache()->get($recentFailKey, 0);
        if ($failCount >= 5) {
            return [
                'success' => false,
                'message' => 'Quá nhiều lần nhập sai. Vui lòng yêu cầu mã OTP mới sau vài phút.'
            ];
        }

        $otpRecord = PasswordOtp::where('email', $user->email)
            ->where('otp', $otp)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            cache()->put($recentFailKey, $failCount + 1, now()->addMinutes(5));
            return [
                'success' => false,
                'message' => 'Mã xác thực OTP không chính xác hoặc đã hết hạn (4 phút)!'
            ];
        }

        cache()->forget($recentFailKey);
        $otpRecord->used_at = now();
        $otpRecord->save();

        $user->password = Hash::make($newPassword);
        $user->save();

        return [
            'success' => true,
            'message' => 'Thay đổi mật khẩu thành công!'
        ];
    }

    public function sendOtp(User $user): array
    {
        $otpCode = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);

        try {
            PasswordOtp::updateOrCreate(
                ['email' => $user->email],
                ['otp' => $otpCode, 'expires_at' => now()->addMinutes(4)]
            );

            Mail::to($user->email)->send(new SendPasswordOtpMail($otpCode, 4));

            return [
                'success' => true,
                'message' => 'Mã OTP đã được gửi thành công đến email của bạn!'
            ];
        } catch (\Exception $e) {
            Log::error("Lỗi gửi mail OTP cho {$user->email}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Không thể gửi mã xác nhận lúc này. Vui lòng kiểm tra lại email hoặc thử lại sau.'
            ];
        }
    }

    public function sendForgotPasswordOtp(string $email): array
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => true,
                'message' => 'Nếu địa chỉ email tồn tại trên hệ thống, mã OTP sẽ được gửi đến email của bạn trong vài giây.'
            ];
        }

        $otpCode = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);

        try {
            PasswordOtp::updateOrCreate(
                ['email' => $email],
                ['otp' => $otpCode, 'expires_at' => now()->addMinutes(4)]
            );

            Mail::to($email)->send(new SendPasswordOtpMail($otpCode, 4));

            return [
                'success' => true,
                'message' => 'Nếu địa chỉ email tồn tại trên hệ thống, mã OTP sẽ được gửi đến email của bạn trong vài giây.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Đã có lỗi xảy ra khi gửi mail. Vui lòng thử lại.'
            ];
        }
    }

    public function resetPassword(string $email, string $otp, string $newPassword): array
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Email này không tồn tại trên hệ thống!'
            ];
        }

        $recentFailKey = 'otp_fail_' . md5($email . '_reset');
        $failCount = cache()->get($recentFailKey, 0);
        if ($failCount >= 5) {
            return [
                'success' => false,
                'message' => 'Quá nhiều lần nhập sai. Vui lòng yêu cầu mã OTP mới sau vài phút.'
            ];
        }

        $otpRecord = PasswordOtp::where('email', $email)
            ->where('otp', $otp)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            cache()->put($recentFailKey, $failCount + 1, now()->addMinutes(5));
            return [
                'success' => false,
                'message' => 'Mã xác thực OTP không chính xác hoặc đã hết hạn (4 phút)!'
            ];
        }

        cache()->forget($recentFailKey);
        $otpRecord->used_at = now();
        $otpRecord->save();

        $user->password = Hash::make($newPassword);
        $user->save();

        return [
            'success' => true,
            'message' => 'Khôi phục mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.'
        ];
    }

    public function heartbeat(?int $userId): void
    {
        if ($userId) {
            try {
                if (Schema::hasColumn('users', 'last_active_at')) {
                    $user = User::find($userId);
                    if ($user) {
                        $user->update(['last_active_at' => now()]);
                    }
                }
            } catch (\Throwable $e) {}
        }
    }
}
