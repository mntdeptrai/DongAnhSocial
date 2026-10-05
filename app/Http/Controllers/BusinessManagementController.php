<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
    /**
     * Get business context for logged in user
     */
    private function getBusinessContext()
    {
        $user = Auth::user();
        $userId = $user ? $user->id : null;
        $userPhone = $user ? $user->phone : (session('user_phone') ?? '');
        $userName = $user ? $user->name : (session('user_name') ?? 'Hộ kinh doanh');

        // Query all business eateries linked to user or user phone
        $query = DB::table('eateries');
        if ($userId) {
            $query->where(function($q) use ($userId, $userPhone, $user) {
                $q->where('user_id', $userId);
                if (!empty($userPhone)) {
                    $q->orWhere('phone', $userPhone);
                }
                if ($user && $user->eatery_id) {
                    $q->orWhere('id', $user->eatery_id);
                }
            });
        } elseif (!empty($userPhone)) {
            $query->where('phone', $userPhone);
        }
        $allBusinesses = $query->orderBy('name', 'asc')->get();

        $activeId = session('active_hkd_id');
        $eatery = null;

        if ($activeId) {
            $eatery = $allBusinesses->firstWhere('id', $activeId);
        }

        if (!$eatery) {
            $eatery = $allBusinesses->first();
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
            $allBusinesses = collect([$eatery]);

            if ($user) {
                DB::table('users')->where('id', $userId)->update(['eatery_id' => $eateryId]);
            }
        }

        if ($eatery) {
            session(['active_hkd_id' => $eatery->id]);
        }

        // Parse storytelling data
        $storyData = [];
        if (!empty($eatery->storytelling_data)) {
            $storyData = is_string($eatery->storytelling_data) ? json_decode($eatery->storytelling_data, true) : (array) $eatery->storytelling_data;
        }

        // Share to views so layouts/hkd.blade.php always receives them
        View::share('allBusinesses', $allBusinesses);
        View::share('eatery', $eatery);

        return [
            'user' => $user,
            'eatery' => $eatery,
            'allBusinesses' => $allBusinesses,
            'storyData' => $storyData,
        ];
    }

    /**
     * Chuyển đổi Hộ kinh doanh / Doanh nghiệp đang quản lý
     */
    public function switchBusiness(Request $request)
    {
        $this->verifyHkdAccess();
        $hkdId = $request->input('hkd_id');
        if ($hkdId) {
            session(['active_hkd_id' => $hkdId]);
        }
        return redirect()->back()->with('success', 'Đã chuyển đổi sang quản lý Hộ kinh doanh được chọn!');
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

        // Orders breakdown & analytics
        $ordersCount = 0;
        $totalRevenue = 0;
        $todayRevenue = 0;
        $todayOrdersCount = 0;
        $pendingOrdersCount = 0;
        $pendingOrdersAmount = 0;
        $processingOrdersCount = 0;
        $processingOrdersAmount = 0;
        $completedOrdersCount = 0;
        $cancelledOrdersCount = 0;
        $recentOrders = collect();

        // 7-day trend data initialization (Current Week vs Previous Week)
        $sevenDaysLabels = [];
        $sevenDaysFullDates = [];
        $prevWeekFullDates = [];
        $sevenDaysRevenue = [];
        $prevWeekRevenue = [];
        $sevenDaysOrders = [];
        $prevWeekOrders = [];
        $sevenDaysCustomers = [];
        $prevWeekCustomers = [];

        for ($i = 6; $i >= 0; $i--) {
            $dateCarbon = now()->subDays($i);
            $dateStr = $dateCarbon->format('Y-m-d');
            $sevenDaysLabels[] = $dateCarbon->format('d/m');
            $sevenDaysFullDates[] = $dateCarbon->format('d/m/Y');
            $sevenDaysRevenue[$dateStr] = 0;
            $sevenDaysOrders[$dateStr] = 0;
            $sevenDaysCustomers[$dateStr] = 0;

            $prevCarbon = now()->subDays($i + 7);
            $prevDateStr = $prevCarbon->format('Y-m-d');
            $prevWeekFullDates[] = $prevCarbon->format('d/m/Y');
            $prevWeekRevenue[$prevDateStr] = 0;
            $prevWeekOrders[$prevDateStr] = 0;
            $prevWeekCustomers[$prevDateStr] = 0;
        }

        // Inventory alerts: Out of Stock & Low Stock counts
        $outOfStockCount = 0;
        $lowStockCount = 0;
        $allProducts = DB::table('ocop_products')->where('eatery_id', $eatery->id)->get();
        foreach ($allProducts as $p) {
            $specs = [];
            if (!empty($p->ingredients)) {
                $specs = is_array($p->ingredients) ? $p->ingredients : (json_decode($p->ingredients, true) ?: []);
            }
            $st = $specs['stock_status'] ?? 'in_stock';
            if ($st === 'out_of_stock') {
                $outOfStockCount++;
            } elseif ($st === 'pre_order' || (!empty($specs['stock_qty']) && (int)$specs['stock_qty'] <= 5)) {
                $lowStockCount++;
            }
        }

        // Daily breakdown table
        $dailyTableData = [];

        if (Schema::hasTable('orders')) {
            $ordersQuery = DB::table('orders')->where('eatery_id', $eatery->id);
            $ordersCount = (clone $ordersQuery)->count();

            // Total & Today Revenue
            $totalRevenue = (clone $ordersQuery)->whereIn('status', ['completed', 'approved', 'delivered', 'paid'])->sum('total_amount');

            $todayDate = now()->format('Y-m-d');
            $todayOrdersQuery = (clone $ordersQuery)->whereDate('created_at', $todayDate);
            $todayOrdersCount = (clone $todayOrdersQuery)->count();
            $todayRevenue = (clone $todayOrdersQuery)->whereIn('status', ['completed', 'approved', 'delivered', 'paid'])->sum('total_amount');

            // Status counts & Amounts
            $pendingOrdersQuery = (clone $ordersQuery)->where('status', 'pending');
            $pendingOrdersCount = (clone $pendingOrdersQuery)->count();
            $pendingOrdersAmount = (clone $pendingOrdersQuery)->sum('total_amount');

            $processingOrdersQuery = (clone $ordersQuery)->whereIn('status', ['processing', 'shipping', 'approved']);
            $processingOrdersCount = (clone $processingOrdersQuery)->count();
            $processingOrdersAmount = (clone $processingOrdersQuery)->sum('total_amount');

            $completedOrdersCount = (clone $ordersQuery)->whereIn('status', ['completed', 'delivered', 'paid'])->count();
            $cancelledOrdersCount = (clone $ordersQuery)->where('status', 'cancelled')->count();

            // Recent orders
            $recentOrders = (clone $ordersQuery)->orderBy('id', 'desc')->take(6)->get();

            // Current week dataset
            $startDate = now()->subDays(6)->startOfDay();
            $allCurrentWeekOrders = (clone $ordersQuery)
                ->where('created_at', '>=', $startDate)
                ->get();

            foreach ($allCurrentWeekOrders as $ord) {
                $d = \Carbon\Carbon::parse($ord->created_at)->format('Y-m-d');
                if (isset($sevenDaysOrders[$d])) {
                    $sevenDaysOrders[$d]++;
                    $sevenDaysCustomers[$d]++;
                }
                if (in_array($ord->status, ['completed', 'approved', 'delivered', 'paid'])) {
                    if (isset($sevenDaysRevenue[$d])) {
                        $sevenDaysRevenue[$d] += (float) $ord->total_amount;
                    }
                }
            }

            // Previous week dataset
            $prevStartDate = now()->subDays(13)->startOfDay();
            $prevEndDate = now()->subDays(7)->endOfDay();
            $allPrevWeekOrders = (clone $ordersQuery)
                ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
                ->get();

            foreach ($allPrevWeekOrders as $ord) {
                $d = \Carbon\Carbon::parse($ord->created_at)->format('Y-m-d');
                if (isset($prevWeekOrders[$d])) {
                    $prevWeekOrders[$d]++;
                    $prevWeekCustomers[$d]++;
                }
                if (in_array($ord->status, ['completed', 'approved', 'delivered', 'paid'])) {
                    if (isset($prevWeekRevenue[$d])) {
                        $prevWeekRevenue[$d] += (float) $ord->total_amount;
                    }
                }
            }

            // Generate Daily Revenue Table
            for ($i = 0; $i < 7; $i++) {
                $dCarbon = now()->subDays($i);
                $dStr = $dCarbon->format('Y-m-d');
                $dDisplay = $dCarbon->format('d/m/Y');

                $dayOrders = (clone $ordersQuery)->whereDate('created_at', $dStr)->get();
                $slDat = $dayOrders->count();
                $tongTien = $dayOrders->whereIn('status', ['completed', 'approved', 'delivered', 'paid'])->sum('total_amount');

                $dailyTableData[] = [
                    'date' => $dDisplay,
                    'sl_dat' => $slDat,
                    'tra_lai' => 0,
                    'doanh_thu_thuan' => $tongTien,
                    'tong_tien' => $tongTien,
                ];
            }
        }

        $sevenDaysRevenueData = array_values($sevenDaysRevenue);
        $prevWeekRevenueData = array_values($prevWeekRevenue);
        $sevenDaysOrdersData = array_values($sevenDaysOrders);
        $prevWeekOrdersData = array_values($prevWeekOrders);
        $sevenDaysCustomersData = array_values($sevenDaysCustomers);
        $prevWeekCustomersData = array_values($prevWeekCustomers);

        // Top 5 selling products by Quantity & Revenue calculated directly from Database (order_items & ocop_products)
        $topProductsByQty = DB::table('ocop_products')
            ->leftJoin('order_items', 'ocop_products.id', '=', 'order_items.ocop_product_id')
            ->leftJoin('orders', function($join) use ($eatery) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->where('orders.eatery_id', '=', $eatery->id)
                     ->where('orders.status', '!=', 'cancelled');
            })
            ->where('ocop_products.eatery_id', $eatery->id)
            ->select(
                'ocop_products.id',
                'ocop_products.title',
                'ocop_products.price',
                'ocop_products.image_path',
                DB::raw('COALESCE(SUM(CASE WHEN orders.id IS NOT NULL THEN order_items.quantity ELSE 0 END), 0) as sold_qty'),
                DB::raw('COALESCE(SUM(CASE WHEN orders.id IS NOT NULL THEN order_items.quantity * order_items.price ELSE 0 END), 0) as total_sales')
            )
            ->groupBy('ocop_products.id', 'ocop_products.title', 'ocop_products.price', 'ocop_products.image_path')
            ->orderByDesc('sold_qty')
            ->orderByDesc('ocop_products.id')
            ->take(5)
            ->get()
            ->map(function($p) {
                $p->image = $p->image_path ?? null;
                $p->name = $p->title ?? ('Sản phẩm ' . $p->id);
                $p->sold_qty = (int) $p->sold_qty;
                $p->total_sales = (float) $p->total_sales;
                return $p;
            });

        $topProductsByRevenue = DB::table('ocop_products')
            ->leftJoin('order_items', 'ocop_products.id', '=', 'order_items.ocop_product_id')
            ->leftJoin('orders', function($join) use ($eatery) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->where('orders.eatery_id', '=', $eatery->id)
                     ->where('orders.status', '!=', 'cancelled');
            })
            ->where('ocop_products.eatery_id', $eatery->id)
            ->select(
                'ocop_products.id',
                'ocop_products.title',
                'ocop_products.price',
                'ocop_products.image_path',
                DB::raw('COALESCE(SUM(CASE WHEN orders.id IS NOT NULL THEN order_items.quantity ELSE 0 END), 0) as sold_qty'),
                DB::raw('COALESCE(SUM(CASE WHEN orders.id IS NOT NULL THEN order_items.quantity * order_items.price ELSE 0 END), 0) as total_sales')
            )
            ->groupBy('ocop_products.id', 'ocop_products.title', 'ocop_products.price', 'ocop_products.image_path')
            ->orderByDesc('total_sales')
            ->orderByDesc('ocop_products.id')
            ->take(5)
            ->get()
            ->map(function($p) {
                $p->image = $p->image_path ?? null;
                $p->name = $p->title ?? ('Sản phẩm ' . $p->id);
                $p->sold_qty = (int) $p->sold_qty;
                $p->total_sales = (float) $p->total_sales;
                return $p;
            });

        $topProducts = $topProductsByQty;

        // Check VietQR config
        $bankAccount = $storyData['bank_account'] ?? ($ctx['user']->bank_account ?? '');
        $bankName = $storyData['bank_name'] ?? ($ctx['user']->bank_name ?? '');
        $bankOwner = $storyData['bank_owner'] ?? ($ctx['user']->bank_owner ?? $eatery->name);
        $hasVietQr = !empty($bankAccount) && !empty($bankName);

        // Store Profile Completion Score (Mức độ hoàn thiện gian hàng)
        $profileChecklist = [
            'gps' => [
                'title' => 'Định vị tọa độ bản đồ (GPS)',
                'done' => !empty($eatery->latitude) && !empty($eatery->longitude),
                'route' => route('hkd.profile'),
                'label' => 'Cập nhật bản đồ'
            ],
            'mst' => [
                'title' => 'Thông tin Mã Số Thuế / Giấy phép HKD',
                'done' => !empty($storyData['mst']),
                'route' => route('hkd.profile'),
                'label' => 'Cập nhật MST'
            ],
            'products' => [
                'title' => 'Đăng tải danh mục sản phẩm kinh doanh',
                'done' => $productsCount > 0,
                'route' => route('hkd.products.index'),
                'label' => 'Thêm sản phẩm'
            ],
            'vietqr' => [
                'title' => 'Tích hợp tài khoản thanh toán VietQR',
                'done' => $hasVietQr,
                'route' => route('hkd.qr'),
                'label' => 'Cấu hình VietQR'
            ],
            'story' => [
                'title' => 'Cập nhật hình ảnh & mô tả giới thiệu cơ sở',
                'done' => !empty($eatery->description) && !empty($eatery->cover_image),
                'route' => route('hkd.profile'),
                'label' => 'Hoàn thiện hồ sơ'
            ],
        ];

        $doneCount = collect($profileChecklist)->where('done', true)->count();
        $profileScore = round(($doneCount / count($profileChecklist)) * 100);

        return view('hkd.dashboard', [
            'eatery' => $eatery,
            'storyData' => $storyData,
            'productsCount' => $productsCount,
            'products' => $products,
            'topProducts' => $topProducts,
            'topProductsByQty' => $topProductsByQty,
            'topProductsByRevenue' => $topProductsByRevenue,
            'ordersCount' => $ordersCount,
            'totalRevenue' => $totalRevenue,
            'todayRevenue' => $todayRevenue,
            'todayOrdersCount' => $todayOrdersCount,
            'pendingOrdersCount' => $pendingOrdersCount,
            'pendingOrdersAmount' => $pendingOrdersAmount,
            'processingOrdersCount' => $processingOrdersCount,
            'processingOrdersAmount' => $processingOrdersAmount,
            'completedOrdersCount' => $completedOrdersCount,
            'cancelledOrdersCount' => $cancelledOrdersCount,
            'outOfStockCount' => $outOfStockCount,
            'lowStockCount' => $lowStockCount,
            'recentOrders' => $recentOrders,
            'hasVietQr' => $hasVietQr,
            'bankAccount' => $bankAccount,
            'bankName' => $bankName,
            'bankOwner' => $bankOwner,
            'profileScore' => $profileScore,
            'profileChecklist' => $profileChecklist,
            'sevenDaysLabels' => $sevenDaysLabels,
            'sevenDaysFullDates' => $sevenDaysFullDates,
            'prevWeekFullDates' => $prevWeekFullDates,
            'sevenDaysRevenueData' => $sevenDaysRevenueData,
            'prevWeekRevenueData' => $prevWeekRevenueData,
            'sevenDaysOrdersData' => $sevenDaysOrdersData,
            'prevWeekOrdersData' => $prevWeekOrdersData,
            'sevenDaysCustomersData' => $sevenDaysCustomersData,
            'prevWeekCustomersData' => $prevWeekCustomersData,
            'dailyTableData' => $dailyTableData,
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
     * Show Create Product Form Page
     */
    public function createProduct(Request $request)
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        return view('hkd.product-create', [
            'eatery' => $eatery,
        ]);
    }

    /**
     * Show Edit Product Form Page
     */
    public function editProduct(Request $request, $id)
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $product = DB::table('ocop_products')
            ->where('id', $id)
            ->where('eatery_id', $eatery->id)
            ->first();

        if (!$product) {
            return redirect()->route('hkd.products.index')->with('error', 'Sản phẩm không tồn tại!');
        }

        $specData = [];
        if (!empty($product->ingredients)) {
            $specData = is_array($product->ingredients) ? $product->ingredients : (json_decode($product->ingredients, true) ?: []);
        }

        return view('hkd.product-edit', [
            'eatery' => $eatery,
            'product' => $product,
            'specData' => $specData,
        ]);
    }

    /**
     * Show Product Detail View Page
     */
    public function showProduct(Request $request, $id)
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $product = DB::table('ocop_products')
            ->where('id', $id)
            ->where('eatery_id', $eatery->id)
            ->first();

        if (!$product) {
            return redirect()->route('hkd.products.index')->with('error', 'Sản phẩm không tồn tại!');
        }

        $specData = [];
        if (!empty($product->ingredients)) {
            $specData = is_array($product->ingredients) ? $product->ingredients : (json_decode($product->ingredients, true) ?: []);
        }

        return view('hkd.product-show', [
            'eatery' => $eatery,
            'product' => $product,
            'specData' => $specData,
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

        $specData = array_filter([
            'sku' => $request->input('sku'),
            'stock_status' => $request->input('stock_status', 'in_stock'),
            'stock_qty' => $request->input('stock_qty'),
            'unit_conversion' => $request->input('unit_conversion'),
            'wholesale_price' => $request->input('wholesale_price'),
            'wholesale_qty' => $request->input('wholesale_qty'),
            'product_type' => $request->input('product_type'),
            'commitment_text' => $request->input('commitment_text'),
            'delivery_text' => $request->input('delivery_text'),
            'certificate_info' => $request->input('certificate_info'),
            'order_policy' => $request->input('order_policy'),
            'payment_policy' => $request->input('payment_policy'),
            'is_signature' => $request->has('is_signature') ? 1 : 0,
        ], function($v) { return $v !== null && $v !== ''; });

        DB::table('ocop_products')->insert([
            'eatery_id' => $eatery->id,
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'unit' => $request->input('unit', ''),
            'star_rating' => $request->input('star_rating'),
            'description' => $request->input('description', ''),
            'ingredients' => !empty($specData) ? json_encode($specData, JSON_UNESCAPED_UNICODE) : null,
            'image_path' => $imagePath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('hkd.products.index')->with('success', 'Đã thêm sản phẩm / mặt hàng kinh doanh mới thành công!');
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
            return redirect()->route('hkd.products.index')->with('error', 'Sản phẩm không tồn tại!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $imagePath = $product->image_path;
        if ($request->hasFile('image')) {
            $imagePath = R2Helper::upload($request->file('image'), 'hkd/products');
        }

        $existingSpecs = [];
        if (!empty($product->ingredients)) {
            $existingSpecs = is_array($product->ingredients) ? $product->ingredients : (json_decode($product->ingredients, true) ?: []);
        }

        $newInputs = [
            'sku' => $request->input('sku'),
            'stock_status' => $request->input('stock_status', 'in_stock'),
            'stock_qty' => $request->input('stock_qty'),
            'unit_conversion' => $request->input('unit_conversion'),
            'wholesale_price' => $request->input('wholesale_price'),
            'wholesale_qty' => $request->input('wholesale_qty'),
            'product_type' => $request->input('product_type'),
            'commitment_text' => $request->input('commitment_text'),
            'delivery_text' => $request->input('delivery_text'),
            'certificate_info' => $request->input('certificate_info'),
            'order_policy' => $request->input('order_policy'),
            'payment_policy' => $request->input('payment_policy'),
            'is_signature' => $request->has('is_signature') ? 1 : 0,
        ];
        foreach ($newInputs as $k => $v) {
            $existingSpecs[$k] = $v;
        }

        DB::table('ocop_products')->where('id', $id)->update([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'unit' => $request->input('unit', $product->unit ?? ''),
            'star_rating' => $request->input('star_rating', $product->star_rating),
            'description' => $request->input('description', ''),
            'ingredients' => !empty($existingSpecs) ? json_encode($existingSpecs, JSON_UNESCAPED_UNICODE) : null,
            'image_path' => $imagePath,
            'updated_at' => now(),
        ]);

        return redirect()->route('hkd.products.index')->with('success', 'Đã cập nhật thông tin sản phẩm thành công!');
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

            if (Schema::hasTable('order_items')) {
                $orderIds = $orders->pluck('id');
                $allItems = DB::table('order_items')
                    ->whereIn('order_id', $orderIds)
                    ->get()
                    ->groupBy('order_id');

                $orders->each(function ($ord) use ($allItems) {
                    $ord->items = $allItems->get($ord->id, collect());
                });
            }
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
