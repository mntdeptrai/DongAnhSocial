<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Helpers\R2Helper;

class BusinessManagementController extends Controller
{
    /**
     * Verify access right for Hộ kinh doanh & Doanh nghiệp
     */
    private function verifyHkdAccess()
    {
        if (!Auth::check() && session()->has('user_id')) {
            $u = \App\Models\User::find(session('user_id'));
            if ($u) {
                Auth::login($u);
            }
        }
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập!');
        }
        $role = session('user_role') ?: $user->role;
        if (!in_array($role, ['seller', 'hkd', 'dn', 'business', 'admin', 'manager'])) {
            abort(403, 'Bạn không có quyền truy cập Kênh Điều Hành Hộ Kinh Doanh & Doanh Nghiệp!');
        }
    }

    /**
     * Get business context for logged in user
     */
    private function getBusinessContext()
    {
        $user = Auth::user();
        $userId = $user ? $user->id : null;
        $userPhone = $user ? $user->phone : (session('user_phone') ?? '');
        $userName = $user ? $user->name : (session('user_name') ?? 'Hộ kinh doanh');

        // Find business eatery linked to user
        $eatery = null;

        if ($userId) {
            $eatery = DB::table('eateries')->where('user_id', $userId)->first();
        }
        if (!$eatery && !empty($userPhone)) {
            $eatery = DB::table('eateries')->where('phone', $userPhone)->first();
        }
        if (!$eatery && $user && $user->eatery_id) {
            $eatery = DB::table('eateries')->where('id', $user->eatery_id)->first();
        }

        // If no eatery exists yet for this HKD user, create one automatically
        if (!$eatery) {
            $cat = DB::table('categories')->where('slug', 'co-so-kinh-doanh')->first();
            $catId = $cat ? $cat->id : 1;
            $slug = Str::slug($userName) . '-' . Str::random(5);

            $eateryId = DB::table('eateries')->insertGetId([
                'user_id' => $userId,
                'name' => $userName,
                'slug' => $slug,
                'category_id' => $catId,
                'phone' => $userPhone ?: '0900000000',
                'address' => 'Xã Đông Anh, TP Hà Nội',
                'description' => 'Hộ kinh doanh, doanh nghiệp cung cấp hàng hóa và dịch vụ tại xã Đông Anh.',
                'rating' => 5.0,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $eatery = DB::table('eateries')->where('id', $eateryId)->first();

            if ($user) {
                DB::table('users')->where('id', $userId)->update(['eatery_id' => $eateryId]);
            }
        }

        // Parse storytelling data
        $storyData = [];
        if (!empty($eatery->storytelling_data)) {
            $storyData = is_string($eatery->storytelling_data) ? json_decode($eatery->storytelling_data, true) : (array) $eatery->storytelling_data;
        }

        return [
            'user' => $user,
            'eatery' => $eatery,
            'storyData' => $storyData,
        ];
    }

    /**
     * Dashboard Overview (Trang Tổng quan Kênh Điều Hành HKD & Doanh Nghiệp)
     */
    public function dashboard()
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];
        $storyData = $ctx['storyData'];

        // Products count & list (using ocop_products table)
        $productsCount = DB::table('ocop_products')->where('eatery_id', $eatery->id)->count();
        $rawProducts = DB::table('ocop_products')->where('eatery_id', $eatery->id)->orderBy('id', 'desc')->take(6)->get();

        $products = $rawProducts->map(function($p) {
            $p->image = $p->image_path ?? null;
            return $p;
        });

        // Orders count & list (safely checking if orders table exists)
        $ordersCount = 0;
        $totalRevenue = 0;
        $recentOrders = collect();

        if (Schema::hasTable('orders')) {
            $ordersQuery = DB::table('orders')->where('eatery_id', $eatery->id);
            $ordersCount = (clone $ordersQuery)->count();
            $totalRevenue = (clone $ordersQuery)->whereIn('status', ['completed', 'approved', 'delivered'])->sum('total_amount');
            $recentOrders = (clone $ordersQuery)->orderBy('id', 'desc')->take(5)->get();
        }

        // Check VietQR config
        $bankAccount = $storyData['bank_account'] ?? ($ctx['user']->bank_account ?? '');
        $bankName = $storyData['bank_name'] ?? ($ctx['user']->bank_name ?? '');
        $hasVietQr = !empty($bankAccount) && !empty($bankName);

        // HT10 Digital Transformation Index (Calculated)
        $ht10Score = 40; // Base score
        if (!empty($eatery->latitude) && !empty($eatery->longitude)) $ht10Score += 15;
        if ($productsCount > 0) $ht10Score += 15;
        if ($hasVietQr) $ht10Score += 15;
        if (!empty($storyData['mst'])) $ht10Score += 15;

        return view('hkd.dashboard', [
            'eatery' => $eatery,
            'storyData' => $storyData,
            'productsCount' => $productsCount,
            'products' => $products,
            'ordersCount' => $ordersCount,
            'totalRevenue' => $totalRevenue,
            'recentOrders' => $recentOrders,
            'hasVietQr' => $hasVietQr,
            'ht10Score' => min(100, $ht10Score),
            'bankAccount' => $bankAccount,
            'bankName' => $bankName,
        ]);
    }

    /**
     * Show Business Profile (Hồ Sơ Cơ Sở Kinh Doanh & Định Vị)
     */
    public function showProfile()
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $communes = DB::table('communes')->select('id', 'name')->get();

        return view('hkd.profile', [
            'eatery' => $ctx['eatery'],
            'storyData' => $ctx['storyData'],
            'communes' => $communes,
        ]);
    }

    /**
     * Update Business Profile
     */
    public function updateProfile(Request $request)
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);

        $storyData = $ctx['storyData'];
        $storyData['mst'] = $request->input('mst', $storyData['mst'] ?? '');
        $storyData['industry'] = $request->input('industry', $storyData['industry'] ?? '');
        $storyData['owner_name'] = $request->input('owner_name', $storyData['owner_name'] ?? '');
        $storyData['story'] = $request->input('story', $storyData['story'] ?? '');
        $storyData['bank_account'] = $request->input('bank_account', $storyData['bank_account'] ?? '');
        $storyData['bank_name'] = $request->input('bank_name', $storyData['bank_name'] ?? '');
        $storyData['bank_holder'] = $request->input('bank_holder', $storyData['bank_holder'] ?? '');

        // Handle Image upload if present
        $imagePath = $eatery->image_path;
        if ($request->hasFile('image')) {
            $uploaded = $request->file('image');
            $imagePath = R2Helper::upload($uploaded, 'hkd/covers');
        }

        // Update eatery record
        DB::table('eateries')->where('id', $eatery->id)->update([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'description' => $request->input('description', $eatery->description),
            'opening_hours' => $request->input('opening_hours', $eatery->opening_hours ?? '07:30 - 21:00'),
            'price_range' => $request->input('price_range', $eatery->price_range ?? 'Liên hệ'),
            'latitude' => $request->filled('latitude') ? (float) $request->input('latitude') : $eatery->latitude,
            'longitude' => $request->filled('longitude') ? (float) $request->input('longitude') : $eatery->longitude,
            'image_path' => $imagePath,
            'storytelling_data' => json_encode($storyData, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);

        // Also update linked user phone/name if applicable
        if (Auth::check()) {
            DB::table('users')->where('id', Auth::id())->update([
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'bank_account' => $storyData['bank_account'],
                'bank_name' => $storyData['bank_name'],
            ]);
        }

        return back()->with('success', 'Đã cập nhật Hồ sơ Cơ sở kinh doanh thành công!');
    }

    /**
     * Products List (Danh Mục Sản Phẩm & Hàng Hóa Kinh Doanh)
     */
    public function products()
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $paginated = DB::table('ocop_products')
            ->where('eatery_id', $eatery->id)
            ->orderBy('id', 'desc')
            ->paginate(15);

        // Map image_path -> image for blade compatibility
        $paginated->getCollection()->transform(function($p) {
            $p->image = $p->image_path ?? null;
            return $p;
        });

        return view('hkd.products', [
            'eatery' => $eatery,
            'products' => $paginated,
        ]);
    }

    /**
     * Store New Product
     */
    public function storeProduct(Request $request)
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = R2Helper::upload($request->file('image'), 'hkd/products');
        }

        DB::table('ocop_products')->insert([
            'eatery_id' => $eatery->id,
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'unit' => $request->input('unit', 'Cái'),
            'star_rating' => $request->input('star_rating'),
            'description' => $request->input('description', ''),
            'image_path' => $imagePath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Đã thêm sản phẩm / mặt hàng kinh doanh mới thành công!');
    }

    /**
     * Update Product
     */
    public function updateProduct(Request $request, $id)
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $product = DB::table('ocop_products')->where('id', $id)->where('eatery_id', $eatery->id)->first();
        if (!$product) {
            return back()->with('error', 'Sản phẩm không tồn tại!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $imagePath = $product->image_path;
        if ($request->hasFile('image')) {
            $imagePath = R2Helper::upload($request->file('image'), 'hkd/products');
        }

        DB::table('ocop_products')->where('id', $id)->update([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'unit' => $request->input('unit', $product->unit ?? 'Cái'),
            'star_rating' => $request->input('star_rating', $product->star_rating),
            'description' => $request->input('description', ''),
            'image_path' => $imagePath,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Đã cập nhật thông tin sản phẩm!');
    }

    /**
     * Delete Product
     */
    public function destroyProduct($id)
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        DB::table('ocop_products')->where('id', $id)->where('eatery_id', $eatery->id)->delete();
        return back()->with('success', 'Đã xóa sản phẩm khỏi gian hàng!');
    }

    /**
     * Orders List (Quản Lý Đơn Hàng & Tiếp Nhận/Duyệt Đơn)
     */
    public function orders(Request $request)
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $status = $request->query('status');
        $orders = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

        if (Schema::hasTable('orders')) {
            $query = DB::table('orders')->where('eatery_id', $eatery->id);
            if ($status) {
                $query->where('status', $status);
            }
            $orders = $query->orderBy('id', 'desc')->paginate(15);
        }

        return view('hkd.orders', [
            'eatery' => $eatery,
            'orders' => $orders,
            'activeStatus' => $status,
        ]);
    }

    /**
     * Show Order Detail
     */
    public function showOrder($id)
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        if (!Schema::hasTable('orders')) {
            return back()->with('error', 'Chức năng đơn hàng chưa sẵn sàng!');
        }

        $order = DB::table('orders')->where('id', $id)->where('eatery_id', $eatery->id)->first();
        if (!$order) {
            return back()->with('error', 'Đơn hàng không tồn tại!');
        }

        $orderItems = collect();
        if (Schema::hasTable('order_items')) {
            $orderItems = DB::table('order_items')->where('order_id', $id)->get();
        }

        return view('hkd.order-detail', [
            'eatery' => $eatery,
            'order' => $order,
            'orderItems' => $orderItems,
        ]);
    }

    /**
     * Update Order Status
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        if (!Schema::hasTable('orders')) {
            return back()->with('error', 'Chức năng đơn hàng chưa sẵn sàng!');
        }

        $order = DB::table('orders')->where('id', $id)->where('eatery_id', $eatery->id)->first();
        if (!$order) {
            return back()->with('error', 'Đơn hàng không tồn tại!');
        }

        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        DB::table('orders')->where('id', $id)->update([
            'status' => $request->input('status'),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng!');
    }

    /**
     * Digital Transformation & Financial Reports (Báo Cáo HT10)
     */
    public function reports(Request $request)
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $orders = collect();
        if (Schema::hasTable('orders')) {
            $orders = DB::table('orders')->where('eatery_id', $eatery->id)->get();
        }
        
        $totalRevenue = $orders->whereIn('status', ['completed', 'delivered', 'approved'])->sum('total_amount');
        $totalOrders = $orders->count();
        $completedOrders = $orders->whereIn('status', ['completed', 'delivered'])->count();
        $pendingOrders = $orders->where('status', 'pending')->count();
        $cancelledOrders = $orders->where('status', 'cancelled')->count();

        // Estimated financial metrics for HT10 report
        $estExpenses = $totalRevenue * 0.65; // Estimated 65% cost of goods & operation
        $estProfit = $totalRevenue - $estExpenses;
        $productsCount = DB::table('ocop_products')->where('eatery_id', $eatery->id)->count();

        return view('hkd.reports', [
            'eatery' => $eatery,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'completedOrders' => $completedOrders,
            'pendingOrders' => $pendingOrders,
            'cancelledOrders' => $cancelledOrders,
            'estExpenses' => $estExpenses,
            'estProfit' => $estProfit,
            'productsCount' => $productsCount,
        ]);
    }

    /**
     * QR Code & VietQR Management (Mã QR & Thanh Toán VietQR Ngân Hàng)
     */
    public function qr()
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];
        $storyData = $ctx['storyData'];

        $publicUrl = url('/dia-diem/' . $eatery->slug);
        $qrLocationUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($publicUrl);

        $bankAccount = $storyData['bank_account'] ?? ($ctx['user']->bank_account ?? '');
        $bankName = $storyData['bank_name'] ?? ($ctx['user']->bank_name ?? '');
        $bankHolder = $storyData['bank_holder'] ?? ($ctx['user']->name ?? '');

        // VietQR Image API Generator
        $vietQrUrl = '';
        if (!empty($bankAccount) && !empty($bankName)) {
            $vietQrUrl = "https://img.vietqr.io/image/" . urlencode($bankName) . "-" . urlencode($bankAccount) . "-compact2.png?accountName=" . urlencode($bankHolder);
        }

        return view('hkd.qr', [
            'eatery' => $eatery,
            'storyData' => $storyData,
            'publicUrl' => $publicUrl,
            'qrLocationUrl' => $qrLocationUrl,
            'bankAccount' => $bankAccount,
            'bankName' => $bankName,
            'bankHolder' => $bankHolder,
            'vietQrUrl' => $vietQrUrl,
        ]);
    }

    /**
     * Customer Chat Interface (Nhắn Tin & Tư Vấn Khách Hàng)
     */
    public function chatIndex()
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();

        return view('hkd.chat', [
            'eatery' => $ctx['eatery'],
        ]);
    }
}
