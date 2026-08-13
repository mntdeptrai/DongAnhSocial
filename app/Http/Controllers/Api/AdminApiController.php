<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * AdminApiController — Quản trị hệ thống cho Mobile Admin Dashboard
 *
 * Chịu trách nhiệm duy nhất: Dashboard tổng quan, CRUD Users/Eateries/Categories/Reviews
 * phục vụ màn hình Admin trong Mobile App.
 */
class AdminApiController extends Controller
{
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

        // Lấy danh sách Trường học từ Database (mysql_education hoặc mysql)
        $schools = [];
        try {
            $schoolsQuery = \App\Models\Eatery::on('mysql_education')
                ->with('commune')
                ->whereHas('category', function($q) {
                    $q->where('slug', 'smart-education-map');
                })->get();
            if ($schoolsQuery->isEmpty()) {
                $schoolsQuery = \App\Models\Eatery::on('mysql')
                    ->with('commune')
                    ->whereHas('category', function($q) {
                        $q->where('slug', 'smart-education-map');
                    })->get();
            }
            $schools = $schoolsQuery->map(function($s) {
                return [
                    'id'      => $s->id,
                    'name'    => $s->name,
                    'address' => $s->address ?? ('Xã ' . ($s->commune?->name ?? 'Đông Anh')),
                    'level'   => 'Trường học',
                ];
            });
        } catch (\Throwable $ex) {
            $schools = [];
        }

        // Lấy danh sách Gian hàng OCOP & Chợ truyền thống từ Database (mysql_market hoặc mysql)
        $stalls = [];
        try {
            $stallsQuery = \App\Models\Eatery::on('mysql_market')
                ->whereHas('category', function($q) {
                    $q->whereIn('slug', ['traditional-market', 'market', 'ocop-products', 'cho-truyen-thong', 'san-pham-ocop', 'ocop']);
                })
                ->select('id', 'name', 'address', 'phone', 'rating', 'status', 'user_id', 'created_at', 'category_id')
                ->latest()->get();
            if ($stallsQuery->isEmpty()) {
                $stallsQuery = \App\Models\Eatery::on('mysql')
                    ->whereHas('category', function($q) {
                        $q->whereIn('slug', ['traditional-market', 'market', 'ocop-products', 'cho-truyen-thong', 'san-pham-ocop', 'ocop']);
                    })->get();
            }
            $stalls = $stallsQuery->map(function($st) {
                return [
                    'id'     => $st->id,
                    'name'   => $st->name,
                    'vendor' => $st->address ?? 'Chợ Đông Anh',
                    'phone'  => $st->phone ?? '0987xxx',
                    'status' => $st->status ?? 'approved',
                ];
            });
        } catch (\Throwable $ex) {
            $stalls = [];
        }

        $totalStallsCount = 0;
        try {
            $totalStallsCount = \Illuminate\Support\Facades\DB::connection('mysql_market')->table('ocop_products')->count();
        } catch (\Throwable $ex) {
            $totalStallsCount = is_countable($stalls) ? count($stalls) : 0;
        }

        $stats = [
            'total_users'      => User::count(),
            'total_eateries'   => \App\Models\Eatery::count(),
            'total_categories' => \App\Models\Category::count(),
            'total_reviews'    => \App\Models\Review::count(),
            'total_sellers'    => User::where('role', 'seller')->count(),
            'total_managers'   => User::where('role', 'manager')->count(),
            'total_schools'    => is_countable($schools) ? count($schools) : 0,
            'total_stalls'     => $totalStallsCount,
        ];

        return response()->json([
            'success'    => true,
            'stats'      => $stats,
            'users'      => $users,
            'eateries'   => $eateries,
            'categories' => $categories,
            'reviews'    => $reviews,
            'schools'    => $schools,
            'stalls'     => $stalls,
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
            'slug'        => Str::slug($request->name),
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
            'slug'          => Str::slug($request->name) . '-' . time(),
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

    // -----------------------------------------------------------------------
    // Web Admin — User Management (Full CRUD qua session auth)
    // -----------------------------------------------------------------------

    /**
     * Lấy danh sách tất cả Users (Web Admin)
     */
    public function getUsers()
    {
        $role = session('user_role') ?? (auth()->check() ? auth()->user()->role : 'user');
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Chỉ Admin mới có quyền truy cập thông tin tài khoản!'], 403);
        }

        $users = User::orderBy('created_at', 'desc')->get();
        return response()->json($users);
    }

    /**
     * Tạo User mới (Web Admin)
     */
    public function storeUserWeb(Request $request)
    {
        $role = session('user_role') ?? (auth()->check() ? auth()->user()->role : 'user');
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Chỉ Admin mới có quyền tạo tài khoản!'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,seller,user',
            'phone' => 'nullable|string|max:15',
            'avatar' => 'nullable|string|max:10',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
            'avatar' => $request->avatar ?: '🧑',
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        return response()->json($user, 201);
    }

    /**
     * Cập nhật User (Web Admin)
     */
    public function updateUserWeb($id, Request $request)
    {
        $role = session('user_role') ?? (auth()->check() ? auth()->user()->role : 'user');
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Chỉ Admin mới có quyền chỉnh sửa tài khoản!'], 403);
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|string|in:admin,seller,user',
            'phone' => 'nullable|string|max:15',
            'avatar' => 'nullable|string|max:10',
            'status' => 'required|string|in:active,disabled',
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'avatar' => $request->avatar ?: '🧑',
            'phone' => $request->phone,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->update($data);

        return response()->json($user);
    }

    /**
     * Xóa User (Web Admin)
     */
    public function destroyUser($id)
    {
        $role = session('user_role') ?? (auth()->check() ? auth()->user()->role : 'user');
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Chỉ Admin mới có quyền xóa tài khoản!'], 403);
        }

        $user = User::findOrFail($id);
        
        if ($user->id === session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Bạn không được phép tự xóa tài khoản của chính mình.'], 400);
        }

        $user->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Bật/Tắt trạng thái User (Web Admin)
     */
    public function toggleUserStatus($id)
    {
        $role = session('user_role') ?? (auth()->check() ? auth()->user()->role : 'user');
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Chỉ Admin mới có quyền đổi trạng thái tài khoản!'], 403);
        }

        $user = User::findOrFail($id);

        if ($user->id === session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Bạn không thể tự vô hiệu hóa tài khoản của chính mình.'], 400);
        }

        $user->status = ($user->status === 'suspended') ? 'active' : 'suspended';
        $user->save();

        return response()->json(['success' => true, 'status' => $user->status]);
    }

    /**
     * Admin: Cấp sao chứng nhận OCOP (3 sao, 4 sao, 5 sao hoặc Xoá sao/Chợ dân sinh)
     */
    public function updateStallStarRating(Request $request, $id)
    {
        $starRating = $request->input('star_rating');
        if ($starRating === '' || $starRating === 'none' || $starRating === 'null') {
            $starRating = null;
        }

        try {
            \Illuminate\Support\Facades\DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->update(['star_rating' => $starRating]);
            \Illuminate\Support\Facades\DB::connection('mysql')->table('ocop_products')->where('id', $id)->update(['star_rating' => $starRating]);
        } catch (\Throwable $e) {}

        try {
            \Illuminate\Support\Facades\DB::connection('mysql_market')->table('eateries')->where('id', $id)->update(['rating' => $starRating ? 5.0 : 4.5]);
            \Illuminate\Support\Facades\DB::connection('mysql')->table('eateries')->where('id', $id)->update(['rating' => $starRating ? 5.0 : 4.5]);
        } catch (\Throwable $e) {}

        \Illuminate\Support\Facades\Cache::flush();

        return response()->json([
            'success' => true,
            'message' => $starRating ? "🎉 Đã cấp chứng nhận ⭐ {$starRating} OCOP thành công!" : "ℹ️ Đã chuyển về Gian hàng Chợ Dân Sinh (Không cấp sao)",
            'star_rating' => $starRating,
        ]);
    }
}
