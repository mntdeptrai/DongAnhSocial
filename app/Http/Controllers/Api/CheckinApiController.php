<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\CheckinReaction;
use App\Models\FoodTourDiary;
use App\Models\Eatery;
use App\Models\User;
use App\Services\CheckinService;
use App\Services\CommentService;
use App\Domain\Checkin\CheckinData;
use App\Domain\Comment\CommentData;
use App\Events\CheckinReacted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CheckinApiController extends Controller
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

    /**
     * Lấy feed Check-in hỗn hợp (gồm Check-in và Nhật ký hành trình)
     */
    public function getFeed(Request $request)
    {
        $limit = (int) $request->query('limit', 20);

        // 1. Lấy Standalone Checkins
        $checkins = Checkin::with(['user', 'eatery.category', 'eatery.commune', 'comments.user'])
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // 2. Lấy Food Tour Diaries
        $diaries = FoodTourDiary::with(['user', 'foodTour.stops', 'comments.user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // 3. Chuẩn hóa cấu trúc feed trước khi gửi về app
        $feed = collect();

        foreach ($checkins as $c) {
            $feed->push([
                'id'                => $c->id,
                'type'              => 'checkin',
                'display_name'      => $c->display_name,
                'avatar_char'       => $c->user ? mb_substr($c->user->name, 0, 1, 'UTF-8') : '👤',
                'role'              => $c->user?->role ?? 'guest',
                'rating'            => $c->rating,
                'comment'           => $c->comment,
                'image_path'        => $c->image_path,
                'created_at_human'  => $c->created_at->diffForHumans(),
                'created_at_format' => $c->created_at->format('d/m/Y H:i'),
                'created_at_ts'     => $c->created_at->timestamp,
                'eatery'            => $c->eatery ? [
                    'name'     => $c->eatery->name,
                    'slug'     => $c->eatery->slug,
                    'category' => $c->eatery->category?->name,
                    'commune'  => $c->eatery->commune?->name,
                ] : null,
                'comments'          => $c->comments->map(function ($comment) {
                    return [
                        'id'           => $comment->id,
                        'display_name' => $comment->display_name,
                        'content'      => $comment->content,
                        'created_at'   => $comment->created_at->diffForHumans(),
                    ];
                }),
            ]);
        }

        foreach ($diaries as $d) {
            // Chặng đi và địa điểm
            $stops = $d->foodTour?->stops ?? collect();
            $stopsData = $stops->map(function ($stop) {
                return [
                    'stop_index' => $stop->stop_index,
                    'name'       => $stop->eatery?->name ?? 'Điểm dừng',
                    'category'   => $stop->eatery?->category?->name,
                    'commune'    => $stop->eatery?->commune?->name,
                ];
            });

            $feed->push([
                'id'                => $d->id,
                'type'              => 'diary',
                'display_name'      => $d->user?->name ?? 'Thực khách',
                'avatar_char'       => $d->user ? mb_substr($d->user->name, 0, 1, 'UTF-8') : '👤',
                'role'              => $d->user?->role ?? 'guest',
                'rating'            => $d->rating,
                'comment'           => $d->comment,
                'image_path'        => $d->image_path,
                'created_at_human'  => $d->created_at->diffForHumans(),
                'created_at_format' => $d->created_at->format('d/m/Y H:i'),
                'created_at_ts'     => $d->created_at->timestamp,
                'food_tour'         => $d->foodTour ? [
                    'name'  => $d->foodTour->name,
                    'slug'  => $d->foodTour->slug,
                    'stops' => $stopsData,
                ] : null,
                'comments'          => $d->comments->map(function ($comment) {
                    return [
                        'id'           => $comment->id,
                        'display_name' => $comment->display_name,
                        'content'      => $comment->content,
                        'created_at'   => $comment->created_at->diffForHumans(),
                    ];
                }),
            ]);
        }

        // Sắp xếp feed theo thời gian mới nhất
        $sortedFeed = $feed->sortByDesc('created_at_ts')->values();

        return response()->json([
            'success' => true,
            'feed'    => $sortedFeed
        ]);
    }

    /**
     * Lưu check-in từ Mobile App (có token hoặc dạng Khách vãng lai)
     */
    public function storeCheckin(Request $request, CheckinService $checkinService)
    {
        $request->validate([
            'eatery_id'  => 'required|exists:eateries,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:2000',
            'guest_name' => 'nullable|string|max:100',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $user = $request->user('sanctum');

        $data = new CheckinData(
            eatery_id: (int) $request->input('eatery_id'),
            rating: (int) $request->input('rating'),
            comment: $request->input('comment'),
            guest_name: $user ? null : $request->input('guest_name'),
            user_id: $user ? $user->id : null,
            image: $request->file('image')
        );

        $checkin = $checkinService->createCheckin($data);

        return response()->json([
            'success' => true,
            'message' => 'Check-in của bạn đã được đăng thành công!',
            'checkin' => [
                'id'         => $checkin->id,
                'image_path' => $checkin->image_path,
                'rating'     => $checkin->rating,
                'comment'    => $checkin->comment
            ]
        ], 201);
    }

    /**
     * Đăng bình luận từ Mobile App
     */
    public function storeComment(Request $request, CommentService $commentService)
    {
        $request->validate([
            'content'          => 'required|string|max:1000',
            'commentable_id'   => 'required|integer',
            'commentable_type' => 'required|string|in:App\Models\Checkin,App\Models\FoodTourDiary',
            'guest_name'       => 'nullable|string|max:100',
        ]);

        $user = $request->user('sanctum');

        $data = new CommentData(
            user_id: $user ? $user->id : null,
            guest_name: $user ? null : ($request->input('guest_name') ?? 'Khách vãng lai'),
            content: $request->input('content'),
            commentable_id: (int) $request->input('commentable_id'),
            commentable_type: $request->input('commentable_type')
        );

        $comment = $commentService->createComment($data);

        return response()->json([
            'success' => true,
            'message' => 'Gửi bình luận thành công.',
            'comment' => [
                'id'           => $comment->id,
                'display_name' => $comment->display_name,
                'content'      => $comment->content,
            ]
        ], 201);
    }

    public function getMyCheckins(Request $request)
    {
        $user = $request->user();
        
        $checkins = Checkin::with(['user', 'eatery.category', 'eatery.commune'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $feed = collect();
        foreach ($checkins as $c) {
            $feed->push([
                'id'                => $c->id,
                'type'              => 'checkin',
                'display_name'      => $c->display_name,
                'avatar_char'       => mb_substr($user->name, 0, 1, 'UTF-8'),
                'role'              => $user->role,
                'rating'            => $c->rating,
                'comment'           => $c->comment,
                'image_path'        => $c->image_path,
                'created_at_human'  => $c->created_at->diffForHumans(),
                'created_at_format' => $c->created_at->format('d/m/Y H:i'),
                'eatery'            => $c->eatery ? [
                    'name'     => $c->eatery->name,
                    'slug'     => $c->eatery->slug,
                    'category' => $c->eatery->category?->name,
                    'commune'  => $c->eatery->commune?->name,
                ] : null,
            ]);
        }
        
        return response()->json([
            'success' => true,
            'checkins' => $feed
        ]);
    }

    /**
     * Gửi reaction từ Mobile App / Web
     */
    public function reactToCheckin(Request $request, $id)
    {
        $request->validate([
            'emoji' => 'required|string|max:10',
            'type'  => 'required|string|in:checkin,diary',
        ]);

        $userId = Auth::id() ?? session('user_id');
        $sessionId = $request->header('X-Session-ID') ?? session()->getId();

        $query = CheckinReaction::where('reactionable_type', $request->type)
            ->where('reactionable_id', (int) $id);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $existing = $query->first();

        if ($existing) {
            if ($existing->emoji === $request->emoji) {
                $existing->delete();
            } else {
                $existing->update(['emoji' => $request->emoji]);
            }
        } else {
            CheckinReaction::create([
                'reactionable_type' => $request->type,
                'reactionable_id'   => (int) $id,
                'user_id'           => $userId,
                'session_id'        => $sessionId,
                'emoji'             => $request->emoji,
            ]);
        }

        $allReactions = CheckinReaction::where('reactionable_type', $request->type)
            ->where('reactionable_id', (int) $id)
            ->selectRaw('emoji, count(*) as count')
            ->groupBy('emoji')
            ->pluck('count', 'emoji')
            ->toArray();

        $emojis = ['❤️', '🔥', '👍', '😂', '😍', '🤤'];
        $counts = [];
        $total = 0;
        foreach ($emojis as $e) {
            $cnt = (int) ($allReactions[$e] ?? 0);
            $counts[$e] = $cnt;
            $total += $cnt;
        }

        event(new CheckinReacted((int) $id, $request->type, $request->emoji, $counts, $total));

        // Gửi thông báo tổng hợp kiểu Facebook & FCM push notification tới chủ bài viết
        \App\Services\NotificationService::notifyReaction((int) $id, $request->type, $request->emoji, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Thả cảm xúc thành công.',
            'counts'  => $counts,
            'total'   => $total,
        ]);
    }

    /**
     * Lấy danh sách người dùng cho Admin Dashboard
     */
    public function getAdminUsers(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'role', 'status', 'created_at')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'users'   => $users
        ]);
    }

    /**
     * Cập nhật phân quyền người dùng từ Admin
     */
    public function updateUserRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|in:user,seller,manager,admin',
        ]);

        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Người dùng không tồn tại'], 404);
        }

        $user->role = $request->role;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật quyền thành công'
        ]);
    }

    /**
     * Lấy toàn bộ dữ liệu quản trị tổng quan cho Admin Dashboard Mobile App
     */
    public function getAdminDashboardData(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'role', 'status', 'created_at')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        $eateries = \App\Models\Eatery::with(['category', 'commune'])
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get()
            ->map(function($e) {
                return [
                    'id'            => $e->id,
                    'name'          => $e->name,
                    'slug'          => $e->slug,
                    'address'       => $e->address,
                    'is_featured'   => (bool)$e->is_featured,
                    'category_name' => $e->category?->name ?? 'Chưa phân loại',
                    'category_slug' => $e->category?->slug ?? 'dong-anh-food-map',
                    'commune_name'  => $e->commune?->name ?? 'Đông Anh',
                    'image_path'    => $e->image_path ?? $e->image,
                    'rating'        => (float)($e->rating ?? 4.5),
                    'reviews_count' => (int)($e->reviews_count ?? 0),
                ];
            });

        $categories = \App\Models\Category::select('id', 'name', 'slug', 'description', 'icon')
            ->orderBy('id', 'asc')
            ->get();

        $reviews = \App\Models\Review::orderBy('id', 'desc')
            ->limit(50)
            ->get()
            ->map(function($r) {
                return [
                    'id'         => $r->id,
                    'user_name'  => $r->user_name ?? 'Một khách hàng',
                    'rating'     => (int)$r->rating,
                    'comment'    => $r->comment,
                    'eatery_id'  => $r->eatery_id,
                    'created_at' => $r->created_at ? $r->created_at->diffForHumans() : 'Vừa xong',
                ];
            });

        $stats = [
            'total_users'      => User::count(),
            'total_eateries'   => \App\Models\Eatery::count(),
            'total_categories' => \App\Models\Category::count(),
            'total_reviews'    => \App\Models\Review::count(),
            'total_sellers'    => User::where('role', 'seller')->count(),
            'total_managers'   => User::where('role', 'manager')->count(),
        ];

        return response()->json([
            'success'    => true,
            'stats'      => $stats,
            'users'      => $users,
            'eateries'   => $eateries,
            'categories' => $categories,
            'reviews'    => $reviews,
        ]);
    }

    /**
     * Bật / Tắt trạng thái Địa điểm Nổi bật (Featured)
     */
    public function toggleEateryFeatured(Request $request, $id)
    {
        $eatery = \App\Models\Eatery::find($id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Địa điểm không tồn tại'], 404);
        }

        $eatery->is_featured = !$eatery->is_featured;
        $eatery->save();

        return response()->json([
            'success'     => true,
            'is_featured' => (bool)$eatery->is_featured,
            'message'     => 'Cập nhật trạng thái nổi bật thành công',
        ]);
    }

    /**
     * Xóa địa điểm từ Admin Mobile App
     */
    public function deleteEatery(Request $request, $id)
    {
        $eatery = \App\Models\Eatery::find($id);
        if ($eatery) {
            $eatery->delete();
        }
        return response()->json(['success' => true, 'message' => 'Đã xóa địa điểm']);
    }

    /**
     * Xóa đánh giá vi phạm từ Admin Mobile App
     */
    public function deleteReview(Request $request, $id)
    {
        $review = \App\Models\Review::find($id);
        if ($review) {
            $review->delete();
        }
        return response()->json(['success' => true, 'message' => 'Đã xóa đánh giá']);
    }

    /**
     * Thêm danh mục mới từ Admin Mobile App
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = \App\Models\Category::create([
            'name'        => $request->name,
            'slug'        => \Illuminate\Support\Str::slug($request->name),
            'description' => $request->description ?? 'Danh mục mới',
            'icon'        => $request->icon ?? '📍',
        ]);

        return response()->json([
            'success'  => true,
            'category' => $category,
            'message'  => 'Tạo danh mục mới thành công',
        ]);
    }

    /**
     * Thêm User mới từ Admin Mobile App
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|string|in:user,seller,manager,admin',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => \Hash::make($request->password),
            'role'     => $request->role,
            'phone'    => $request->phone ?? null,
            'status'   => 'active',
        ]);

        return response()->json([
            'success' => true,
            'user'    => $user,
            'message' => 'Tạo tài khoản mới thành công!',
        ]);
    }

    /**
     * Xóa User từ Admin Mobile App
     */
    public function deleteUser(Request $request, $id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
        }
        return response()->json(['success' => true, 'message' => 'Đã xóa tài khoản']);
    }

    /**
     * Đăng ký Cơ sở / Địa điểm mới từ Admin Mobile App (Full Fields)
     */
    public function storeEatery(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required',
            'commune_id'  => 'required',
            'address'     => 'required|string|max:255',
        ]);

        $category = \App\Models\Category::find($request->category_id) ?? \App\Models\Category::first();
        $commune  = \App\Models\Commune::find($request->commune_id) ?? \App\Models\Commune::first();

        $eatery = \App\Models\Eatery::create([
            'name'          => $request->name,
            'slug'          => \Illuminate\Support\Str::slug($request->name) . '-' . time(),
            'category_id'   => $category ? $category->id : 1,
            'commune_id'    => $commune ? $commune->id : 1,
            'address'       => $request->address,
            'phone'         => $request->phone ?? null,
            'opening_hours' => $request->opening_hours ?? '06:00 - 22:00',
            'price_range'   => $request->price_range ?? '30.000đ - 100.000đ',
            'latitude'      => $request->latitude ?? 21.117158,
            'longitude'     => $request->longitude ?? 105.895619,
            'is_featured'   => $request->boolean('is_featured', false),
            'image_path'    => $request->image_url ?? 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80',
        ]);

        return response()->json([
            'success' => true,
            'eatery'  => $eatery,
            'message' => 'Đăng ký cơ sở bản đồ số mới thành công!',
        ]);
    }

    /**
     * Cập nhật thông tin Cơ sở / Địa điểm từ Admin Mobile App
     */
    public function updateEatery(Request $request, $id)
    {
        $eatery = \App\Models\Eatery::find($id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy địa điểm'], 404);
        }

        if ($request->has('name')) $eatery->name = $request->name;
        if ($request->has('address')) $eatery->address = $request->address;
        if ($request->has('phone')) $eatery->phone = $request->phone;
        if ($request->has('opening_hours')) $eatery->opening_hours = $request->opening_hours;
        if ($request->has('price_range')) $eatery->price_range = $request->price_range;
        if ($request->has('latitude')) $eatery->latitude = $request->latitude;
        if ($request->has('longitude')) $eatery->longitude = $request->longitude;
        if ($request->has('is_featured')) $eatery->is_featured = $request->boolean('is_featured');
        if ($request->has('category_id')) $eatery->category_id = $request->category_id;
        if ($request->has('commune_id')) $eatery->commune_id = $request->commune_id;

        $eatery->save();

        return response()->json([
            'success' => true,
            'eatery'  => $eatery,
            'message' => 'Cập nhật địa điểm thành công!',
        ]);
    }
}
