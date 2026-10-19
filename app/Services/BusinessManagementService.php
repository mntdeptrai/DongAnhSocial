<?php

namespace App\Services;

use App\Helpers\R2Helper;
use App\Models\User;
use App\Services\Interfaces\BusinessManagementServiceInterface;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class BusinessManagementService implements BusinessManagementServiceInterface
{
    public function verifyHkdAccess(): void
    {
        if (!Auth::check() && session()->has('user_id')) {
            $u = User::find(session('user_id'));
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

    public function getBusinessContext(): array
    {
        $user = Auth::user();
        $userId = $user ? $user->id : null;
        $userPhone = $user ? $user->phone : (session('user_phone') ?? '');
        $userName = $user ? $user->name : (session('user_name') ?? 'Hộ kinh doanh');

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

        if (!$eatery) {
            $cat = DB::table('categories')->where('slug', 'co-so-kinh-doanh')->first();
            $catId = $cat ? $cat->id : 1;
            $slug = Str::slug($userName) . '-' . Str::random(5);

            $eateryId = DB::table('eateries')->insertGetId([
                'user_id'     => $userId,
                'name'        => $userName,
                'slug'        => $slug,
                'category_id' => $catId,
                'phone'       => $userPhone ?: '0900000000',
                'address'     => 'Xã Đông Anh, TP Hà Nội',
                'description' => 'Hộ kinh doanh, doanh nghiệp cung cấp hàng hóa và dịch vụ tại xã Đông Anh.',
                'rating'      => 5.0,
                'status'      => 'active',
                'created_at'  => now(),
                'updated_at'  => now(),
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

        $storyData = [];
        if (!empty($eatery->storytelling_data)) {
            $storyData = is_string($eatery->storytelling_data) ? json_decode($eatery->storytelling_data, true) : (array) $eatery->storytelling_data;
        }

        View::share('allBusinesses', $allBusinesses);
        View::share('eatery', $eatery);

        return [
            'user'          => $user,
            'eatery'        => $eatery,
            'allBusinesses' => $allBusinesses,
            'storyData'     => $storyData,
        ];
    }

    public function switchBusiness($hkdId): void
    {
        if ($hkdId) {
            session(['active_hkd_id' => $hkdId]);
        }
    }

    public function getDashboardData(): array
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];
        $storyData = $ctx['storyData'];

        $productsCount = DB::table('ocop_products')->where('eatery_id', $eatery->id)->count();
        $rawProducts = DB::table('ocop_products')->where('eatery_id', $eatery->id)->orderBy('id', 'desc')->take(6)->get();

        $products = $rawProducts->map(function($p) {
            $p->image = $p->image_path ?? null;
            return $p;
        });

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

        $dailyTableData = [];

        if (Schema::hasTable('orders')) {
            $ordersQuery = DB::table('orders')->where('eatery_id', $eatery->id);
            $ordersCount = (clone $ordersQuery)->count();

            $totalRevenue = (clone $ordersQuery)->whereIn('status', ['completed', 'approved', 'delivered', 'paid'])->sum('total_amount');

            $todayDate = now()->format('Y-m-d');
            $todayOrdersQuery = (clone $ordersQuery)->whereDate('created_at', $todayDate);
            $todayOrdersCount = (clone $todayOrdersQuery)->count();
            $todayRevenue = (clone $todayOrdersQuery)->whereIn('status', ['completed', 'approved', 'delivered', 'paid'])->sum('total_amount');

            $pendingOrdersQuery = (clone $ordersQuery)->where('status', 'pending');
            $pendingOrdersCount = (clone $pendingOrdersQuery)->count();
            $pendingOrdersAmount = (clone $pendingOrdersQuery)->sum('total_amount');

            $processingOrdersQuery = (clone $ordersQuery)->whereIn('status', ['processing', 'shipping', 'approved']);
            $processingOrdersCount = (clone $processingOrdersQuery)->count();
            $processingOrdersAmount = (clone $processingOrdersQuery)->sum('total_amount');

            $completedOrdersCount = (clone $ordersQuery)->whereIn('status', ['completed', 'delivered', 'paid'])->count();
            $cancelledOrdersCount = (clone $ordersQuery)->where('status', 'cancelled')->count();

            $recentOrders = (clone $ordersQuery)->orderBy('id', 'desc')->take(6)->get();

            $startDate = now()->subDays(6)->startOfDay();
            $allCurrentWeekOrders = (clone $ordersQuery)
                ->where('created_at', '>=', $startDate)
                ->get();

            foreach ($allCurrentWeekOrders as $ord) {
                $d = Carbon::parse($ord->created_at)->format('Y-m-d');
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

            $prevWeekStart = now()->subDays(13)->startOfDay();
            $prevWeekEnd = now()->subDays(7)->endOfDay();
            $allPrevWeekOrders = (clone $ordersQuery)
                ->whereBetween('created_at', [$prevWeekStart, $prevWeekEnd])
                ->get();

            foreach ($allPrevWeekOrders as $pOrd) {
                $pd = Carbon::parse($pOrd->created_at)->format('Y-m-d');
                if (isset($prevWeekOrders[$pd])) {
                    $prevWeekOrders[$pd]++;
                    $prevWeekCustomers[$pd]++;
                }
                if (in_array($pOrd->status, ['completed', 'approved', 'delivered', 'paid'])) {
                    if (isset($prevWeekRevenue[$pd])) {
                        $prevWeekRevenue[$pd] += (float) $pOrd->total_amount;
                    }
                }
            }

            for ($j = 0; $j < 7; $j++) {
                $dateCarbon = now()->subDays(6 - $j);
                $dStr = $dateCarbon->format('Y-m-d');
                $dLabel = $dateCarbon->format('d/m/Y');
                $soDon = $sevenDaysOrders[$dStr] ?? 0;
                $tongTien = $sevenDaysRevenue[$dStr] ?? 0;

                $dailyTableData[] = [
                    'ngay'            => $dLabel,
                    'ngay_format'     => $dLabel,
                    'so_don'          => $soDon,
                    'so_khach'        => $sevenDaysCustomers[$dStr] ?? 0,
                    'doanh_thu_thuan' => $tongTien,
                    'tong_tien'       => $tongTien,
                ];
            }
        }

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

        $bankAccount = $storyData['bank_account'] ?? ($ctx['user']->bank_account ?? '');
        $bankName = $storyData['bank_name'] ?? ($ctx['user']->bank_name ?? '');
        $bankOwner = $storyData['bank_owner'] ?? ($ctx['user']->bank_owner ?? $eatery->name);
        $hasVietQr = !empty($bankAccount) && !empty($bankName);

        $profileChecklist = [
            'gps' => [
                'title' => 'Định vị tọa độ bản đồ (GPS)',
                'done'  => !empty($eatery->latitude) && !empty($eatery->longitude),
                'route' => route('hkd.profile'),
                'label' => 'Cập nhật bản đồ'
            ],
            'mst' => [
                'title' => 'Thông tin Mã Số Thuế / Giấy phép HKD',
                'done'  => !empty($storyData['mst']),
                'route' => route('hkd.profile'),
                'label' => 'Cập nhật MST'
            ],
            'products' => [
                'title' => 'Đăng tải danh mục sản phẩm kinh doanh',
                'done'  => $productsCount > 0,
                'route' => route('hkd.products.index'),
                'label' => 'Thêm sản phẩm'
            ],
            'vietqr' => [
                'title' => 'Tích hợp tài khoản thanh toán VietQR',
                'done'  => $hasVietQr,
                'route' => route('hkd.qr'),
                'label' => 'Cấu hình VietQR'
            ],
            'story' => [
                'title' => 'Cập nhật hình ảnh & mô tả giới thiệu cơ sở',
                'done'  => !empty($eatery->description) && !empty($eatery->cover_image),
                'route' => route('hkd.profile'),
                'label' => 'Hoàn thiện hồ sơ'
            ],
        ];

        $doneCount = collect($profileChecklist)->where('done', true)->count();
        $profileScore = round(($doneCount / count($profileChecklist)) * 100);

        return [
            'eatery'                  => $eatery,
            'storyData'               => $storyData,
            'productsCount'           => $productsCount,
            'products'                => $products,
            'topProducts'             => $topProductsByQty,
            'topProductsByQty'        => $topProductsByQty,
            'topProductsByRevenue'    => $topProductsByRevenue,
            'ordersCount'             => $ordersCount,
            'totalRevenue'            => $totalRevenue,
            'todayRevenue'            => $todayRevenue,
            'todayOrdersCount'        => $todayOrdersCount,
            'pendingOrdersCount'      => $pendingOrdersCount,
            'pendingOrdersAmount'     => $pendingOrdersAmount,
            'processingOrdersCount'   => $processingOrdersCount,
            'processingOrdersAmount'  => $processingOrdersAmount,
            'completedOrdersCount'    => $completedOrdersCount,
            'cancelledOrdersCount'    => $cancelledOrdersCount,
            'outOfStockCount'         => $outOfStockCount,
            'lowStockCount'           => $lowStockCount,
            'recentOrders'            => $recentOrders,
            'hasVietQr'               => $hasVietQr,
            'bankAccount'             => $bankAccount,
            'bankName'                => $bankName,
            'bankOwner'               => $bankOwner,
            'profileScore'            => $profileScore,
            'profileChecklist'        => $profileChecklist,
            'sevenDaysLabels'         => $sevenDaysLabels,
            'sevenDaysFullDates'      => $sevenDaysFullDates,
            'prevWeekFullDates'       => $prevWeekFullDates,
            'sevenDaysRevenueData'    => array_values($sevenDaysRevenue),
            'prevWeekRevenueData'     => array_values($prevWeekRevenue),
            'sevenDaysOrdersData'     => array_values($sevenDaysOrders),
            'prevWeekOrdersData'      => array_values($prevWeekOrders),
            'sevenDaysCustomersData'  => array_values($sevenDaysCustomers),
            'prevWeekCustomersData'   => array_values($prevWeekCustomers),
            'dailyTableData'          => $dailyTableData,
        ];
    }

    public function getProfileData(): array
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $communes = DB::table('communes')->select('id', 'name')->get();

        return [
            'eatery'    => $ctx['eatery'],
            'storyData' => $ctx['storyData'],
            'communes'  => $communes,
        ];
    }

    public function updateProfile(array $data, $imageFile): void
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $storyData = $ctx['storyData'];
        $storyData['mst'] = $data['mst'] ?? ($storyData['mst'] ?? '');
        $storyData['industry'] = $data['industry'] ?? ($storyData['industry'] ?? '');
        $storyData['owner_name'] = $data['owner_name'] ?? ($storyData['owner_name'] ?? '');
        $storyData['story'] = $data['story'] ?? ($storyData['story'] ?? '');
        $storyData['bank_account'] = $data['bank_account'] ?? ($storyData['bank_account'] ?? '');
        $storyData['bank_name'] = $data['bank_name'] ?? ($storyData['bank_name'] ?? '');
        $storyData['bank_holder'] = $data['bank_holder'] ?? ($storyData['bank_holder'] ?? '');

        $imagePath = $eatery->image_path;
        if ($imageFile) {
            $imagePath = R2Helper::upload($imageFile, 'hkd/covers');
        }

        DB::table('eateries')->where('id', $eatery->id)->update([
            'name'              => $data['name'],
            'phone'             => $data['phone'],
            'address'           => $data['address'],
            'description'       => $data['description'] ?? $eatery->description,
            'opening_hours'     => $data['opening_hours'] ?? ($eatery->opening_hours ?? '07:30 - 21:00'),
            'price_range'       => $data['price_range'] ?? ($eatery->price_range ?? 'Liên hệ'),
            'latitude'          => !empty($data['latitude']) ? (float) $data['latitude'] : $eatery->latitude,
            'longitude'         => !empty($data['longitude']) ? (float) $data['longitude'] : $eatery->longitude,
            'image_path'        => $imagePath,
            'storytelling_data' => json_encode($storyData, JSON_UNESCAPED_UNICODE),
            'updated_at'        => now(),
        ]);

        if (Auth::check()) {
            DB::table('users')->where('id', Auth::id())->update([
                'name'         => $data['name'],
                'phone'        => $data['phone'],
                'bank_account' => $storyData['bank_account'],
                'bank_name'    => $storyData['bank_name'],
            ]);
        }
    }

    public function getProductsPaginated(int $perPage = 15): array
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $paginated = DB::table('ocop_products')
            ->where('eatery_id', $eatery->id)
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $paginated->getCollection()->transform(function($p) {
            $p->image = $p->image_path ?? null;
            return $p;
        });

        return [
            'eatery'   => $eatery,
            'products' => $paginated,
        ];
    }

    public function getProductForEdit($id): ?array
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $product = DB::table('ocop_products')
            ->where('id', $id)
            ->where('eatery_id', $eatery->id)
            ->first();

        if (!$product) {
            return null;
        }

        $specData = [];
        if (!empty($product->ingredients)) {
            $specData = is_array($product->ingredients) ? $product->ingredients : (json_decode($product->ingredients, true) ?: []);
        }

        return [
            'eatery'   => $eatery,
            'product'  => $product,
            'specData' => $specData,
        ];
    }

    public function getProductDetails($id): ?array
    {
        return $this->getProductForEdit($id);
    }

    public function storeProduct(array $data, $imageFile): void
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $imagePath = null;
        if ($imageFile) {
            $imagePath = R2Helper::upload($imageFile, 'hkd/products');
        }

        $specData = array_filter([
            'sku'             => $data['sku'] ?? null,
            'stock_status'    => $data['stock_status'] ?? 'in_stock',
            'stock_qty'       => $data['stock_qty'] ?? null,
            'unit_conversion' => $data['unit_conversion'] ?? null,
            'wholesale_price' => $data['wholesale_price'] ?? null,
            'wholesale_qty'   => $data['wholesale_qty'] ?? null,
            'product_type'    => $data['product_type'] ?? null,
            'commitment_text' => $data['commitment_text'] ?? null,
            'delivery_text'   => $data['delivery_text'] ?? null,
            'certificate_info'=> $data['certificate_info'] ?? null,
            'order_policy'    => $data['order_policy'] ?? null,
            'payment_policy'  => $data['payment_policy'] ?? null,
            'is_signature'    => !empty($data['is_signature']) ? 1 : 0,
        ], fn($v) => $v !== null && $v !== '');

        DB::table('ocop_products')->insert([
            'eatery_id'   => $eatery->id,
            'name'        => $data['name'],
            'price'       => $data['price'],
            'unit'        => $data['unit'] ?? '',
            'star_rating' => $data['star_rating'] ?? null,
            'description' => $data['description'] ?? '',
            'ingredients' => !empty($specData) ? json_encode($specData, JSON_UNESCAPED_UNICODE) : null,
            'image_path'  => $imagePath,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function updateProduct($id, array $data, $imageFile): void
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $product = DB::table('ocop_products')->where('id', $id)->where('eatery_id', $eatery->id)->first();
        if (!$product) {
            throw new \InvalidArgumentException('Sản phẩm không tồn tại!');
        }

        $imagePath = $product->image_path;
        if ($imageFile) {
            $imagePath = R2Helper::upload($imageFile, 'hkd/products');
        }

        $existingSpecs = [];
        if (!empty($product->ingredients)) {
            $existingSpecs = is_array($product->ingredients) ? $product->ingredients : (json_decode($product->ingredients, true) ?: []);
        }

        $newInputs = [
            'sku'             => $data['sku'] ?? null,
            'stock_status'    => $data['stock_status'] ?? 'in_stock',
            'stock_qty'       => $data['stock_qty'] ?? null,
            'unit_conversion' => $data['unit_conversion'] ?? null,
            'wholesale_price' => $data['wholesale_price'] ?? null,
            'wholesale_qty'   => $data['wholesale_qty'] ?? null,
            'product_type'    => $data['product_type'] ?? null,
            'commitment_text' => $data['commitment_text'] ?? null,
            'delivery_text'   => $data['delivery_text'] ?? null,
            'certificate_info'=> $data['certificate_info'] ?? null,
            'order_policy'    => $data['order_policy'] ?? null,
            'payment_policy'  => $data['payment_policy'] ?? null,
            'is_signature'    => !empty($data['is_signature']) ? 1 : 0,
        ];
        foreach ($newInputs as $k => $v) {
            $existingSpecs[$k] = $v;
        }

        DB::table('ocop_products')->where('id', $id)->update([
            'name'        => $data['name'],
            'price'       => $data['price'],
            'unit'        => $data['unit'] ?? ($product->unit ?? ''),
            'star_rating' => $data['star_rating'] ?? $product->star_rating,
            'description' => $data['description'] ?? '',
            'ingredients' => !empty($existingSpecs) ? json_encode($existingSpecs, JSON_UNESCAPED_UNICODE) : null,
            'image_path'  => $imagePath,
            'updated_at'  => now(),
        ]);
    }

    public function deleteProduct($id): void
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        DB::table('ocop_products')->where('id', $id)->where('eatery_id', $eatery->id)->delete();
    }

    public function getOrdersPaginated(?string $status, int $perPage = 15): array
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        $orders = new LengthAwarePaginator([], 0, $perPage);

        if (Schema::hasTable('orders')) {
            $query = DB::table('orders')->where('eatery_id', $eatery->id);
            if ($status) {
                $query->where('status', $status);
            }
            $orders = $query->orderBy('id', 'desc')->paginate($perPage);

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

        return [
            'eatery'       => $eatery,
            'orders'       => $orders,
            'activeStatus' => $status,
        ];
    }

    public function getOrderDetails($id): ?array
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        if (!Schema::hasTable('orders')) {
            return null;
        }

        $order = DB::table('orders')->where('id', $id)->where('eatery_id', $eatery->id)->first();
        if (!$order) {
            return null;
        }

        $orderItems = collect();
        if (Schema::hasTable('order_items')) {
            $orderItems = DB::table('order_items')->where('order_id', $id)->get();
        }

        return [
            'eatery'     => $eatery,
            'order'      => $order,
            'orderItems' => $orderItems,
        ];
    }

    public function updateOrderStatus($id, string $status): bool
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();
        $eatery = $ctx['eatery'];

        if (!Schema::hasTable('orders')) {
            return false;
        }

        $order = DB::table('orders')->where('id', $id)->where('eatery_id', $eatery->id)->first();
        if (!$order) {
            return false;
        }

        DB::table('orders')->where('id', $id)->update([
            'status'     => $status,
            'updated_at' => now(),
        ]);

        return true;
    }

    public function getReportsData(): array
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

        $estExpenses = $totalRevenue * 0.65;
        $estProfit = $totalRevenue - $estExpenses;
        $productsCount = DB::table('ocop_products')->where('eatery_id', $eatery->id)->count();

        return [
            'eatery'          => $eatery,
            'totalRevenue'    => $totalRevenue,
            'totalOrders'     => $totalOrders,
            'completedOrders' => $completedOrders,
            'pendingOrders'   => $pendingOrders,
            'cancelledOrders' => $cancelledOrders,
            'estExpenses'     => $estExpenses,
            'estProfit'       => $estProfit,
            'productsCount'   => $productsCount,
        ];
    }

    public function getQrData(): array
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

        $vietQrUrl = '';
        if (!empty($bankAccount) && !empty($bankName)) {
            $vietQrUrl = "https://img.vietqr.io/image/" . urlencode($bankName) . "-" . urlencode($bankAccount) . "-compact2.png?accountName=" . urlencode($bankHolder);
        }

        return [
            'eatery'        => $eatery,
            'storyData'     => $storyData,
            'publicUrl'     => $publicUrl,
            'qrLocationUrl' => $qrLocationUrl,
            'bankAccount'   => $bankAccount,
            'bankName'      => $bankName,
            'bankHolder'    => $bankHolder,
            'vietQrUrl'     => $vietQrUrl,
        ];
    }

    public function getChatContext(): array
    {
        $this->verifyHkdAccess();
        $ctx = $this->getBusinessContext();

        return [
            'eatery' => $ctx['eatery'],
        ];
    }
}
