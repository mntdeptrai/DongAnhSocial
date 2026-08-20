<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\R2Helper;

class VendorController extends Controller
{
    /**
     * Xác minh quyền truy cập của Chủ Gian Hàng Số (Stall Tenant Vendor)
     */
    private function verifyVendor()
    {
        $role = session('user_role') ?: (Auth::user() ? Auth::user()->role : null);
        if (!in_array($role, ['seller', 'admin', 'manager'])) {
            abort(403, 'Bạn không có quyền truy cập Kênh Điều Hành Chủ Gian Hàng!');
        }
    }

    /**
     * Lấy thông tin Gian hàng và danh sách sản phẩm thuộc Stall Tenant hiện tại
     */
    private function getVendorStallContext()
    {
        $user = Auth::user();
        $userId = $user ? $user->id : null;
        $userPhone = $user->phone ?? session('user_phone') ?? '';
        $userName = $user->name ?? session('user_name') ?? '';

        $db = DB::connection('mysql_market');

        // === BƯỚC 1: Thu thập tất cả các thực thể kinh doanh thuộc sở hữu của User ===
        $stallRecord = null;
        $market = null;
        $businessEatery = null;
        $managedEntities = [];

        // 1a. Tìm Gian hàng Chợ (ocop_products)
        if ($user && $user->stall_id) {
            $stallRecord = $db->table('ocop_products')->where('id', $user->stall_id)->first();
        }
        if (!$stallRecord && $userId) {
            $stallRecord = $db->table('ocop_products')->where('user_id', $userId)->first();
        }
        if (!$stallRecord && !empty($userPhone)) {
            $stallRecord = $db->table('ocop_products')->where('seller_phone', $userPhone)->first();
        }

        if ($stallRecord) {
            $market = $db->table('eateries')->where('id', $stallRecord->eatery_id)->first();
            $managedEntities[] = [
                'key' => 'stall_' . $stallRecord->id,
                'type' => 'market_stall',
                'badge' => '🛒 Gian hàng Chợ',
                'name' => $stallRecord->stall_name,
                'sub' => $market ? ('🏛️ ' . $market->name) : 'Chợ truyền thống',
                'id' => $stallRecord->id,
                'market_id' => $market ? $market->id : null,
                'market_slug' => $market ? $market->slug : '',
            ];
        }

        // 1b. Tìm Cơ sở kinh doanh / Doanh nghiệp độc lập (Category 9 hoặc khác chợ)
        if ($userId) {
            $businessEatery = $db->table('eateries')
                ->where('user_id', $userId)
                ->where('category_id', 9)
                ->first();
        }
        if (!$businessEatery && !empty($userPhone)) {
            $businessEatery = $db->table('eateries')
                ->where('phone', $userPhone)
                ->where('category_id', 9)
                ->first();
        }
        if (!$businessEatery && $user && $user->eatery_id) {
            $eateryCandidate = $db->table('eateries')->where('id', $user->eatery_id)->first();
            if ($eateryCandidate && $eateryCandidate->category_id == 9) {
                $businessEatery = $eateryCandidate;
            }
        }

        if ($businessEatery) {
            $managedEntities[] = [
                'key' => 'business_' . $businessEatery->id,
                'type' => 'business',
                'badge' => '🏢 Hộ kinh doanh / Doanh nghiệp',
                'name' => $businessEatery->name,
                'sub' => '📍 ' . ($businessEatery->address ?: 'Đông Anh, Hà Nội'),
                'id' => $businessEatery->id,
                'slug' => $businessEatery->slug,
            ];
        }

        // 1c. Tuyến đường 4.0
        $routeBusinesses = $user ? $user->getRouteBusinesses() : collect();
        if ($routeBusinesses && $routeBusinesses->count() > 0) {
            foreach ($routeBusinesses as $rb) {
                $managedEntities[] = [
                    'key' => 'route_' . $rb->id,
                    'type' => 'route',
                    'badge' => '🛣️ Tuyến đường 4.0',
                    'name' => $rb->name,
                    'sub' => '🛣️ Tuyến ' . $rb->village_name,
                    'id' => $rb->id,
                ];
            }
        }

        // Xác định thực thể đang kích hoạt (Active Entity)
        $activeEntityKey = session('active_seller_entity');
        if (!$activeEntityKey || !collect($managedEntities)->contains('key', $activeEntityKey)) {
            $activeEntityKey = !empty($managedEntities) ? $managedEntities[0]['key'] : null;
        }

        // === BƯỚC 2: Cấu hình dữ liệu hiển thị theo Active Entity ===
        $isBusinessMode = str_starts_with($activeEntityKey ?? '', 'business_');
        $eatery = null;
        $stallName = null;

        if ($isBusinessMode && $businessEatery) {
            $eatery = $businessEatery;
            $stallName = $businessEatery->name;
            $eateryId = $businessEatery->id;
        } elseif ($stallRecord && $market) {
            $eatery = $market;
            $stallName = $stallRecord->stall_name;
            $eateryId = $market->id;
        } elseif ($businessEatery) {
            $eatery = $businessEatery;
            $stallName = $businessEatery->name;
            $eateryId = $businessEatery->id;
        } else {
            $eatery = $db->table('eateries')->first();
            $stallName = $eatery ? $eatery->name : 'Gian hàng Số của tôi';
            $eateryId = $eatery ? $eatery->id : 1;
        }

        $sellerName = $userName ?: ($eatery ? $eatery->name : 'Chủ gian hàng');
        $sellerPhone = $userPhone ?: ($eatery ? $eatery->phone : '');

        // Lấy danh mục địa điểm
        $category = null;
        if ($eatery && $eatery->category_id) {
            $category = $db->table('categories')->where('id', $eatery->category_id)->first();
        }
        $categorySlug = $category ? $category->slug : '';
        $isOcopSeller = ($categorySlug === 'dong-anh-market');

        // Query sản phẩm
        $products = collect();
        if ($stallRecord && !$isBusinessMode) {
            $products = $db->table('ocop_products')
                ->where('eatery_id', $eateryId)
                ->where('stall_name', $stallName)
                ->get();
        } elseif ($isBusinessMode && $businessEatery) {
            $dishes = $db->table('dishes')->where('eatery_id', $businessEatery->id)->get();
            $products = $dishes->map(function($d) use ($businessEatery) {
                return (object)[
                    'id' => $d->id,
                    'eatery_id' => $businessEatery->id,
                    'stall_name' => $businessEatery->name,
                    'seller_name' => $businessEatery->name,
                    'seller_phone' => $businessEatery->phone,
                    'name' => $d->name,
                    'price' => $d->price,
                    'unit' => 'mặt hàng',
                    'description' => $d->description,
                    'image_path' => $d->image_path,
                    'star_rating' => null,
                ];
            });
        } else {
            $products = $db->table('ocop_products')->where('eatery_id', $eateryId)->get();
        }
        $primaryProduct = $products->first();

        return [
            'stallName' => $stallName,
            'sellerName' => $sellerName,
            'sellerPhone' => $sellerPhone,
            'eateryId' => $eateryId,
            'market' => $market ?: $eatery,
            'businessEatery' => $businessEatery,
            'stallRecord' => $stallRecord,
            'managedEntities' => $managedEntities,
            'activeEntityKey' => $activeEntityKey,
            'isBusinessMode' => $isBusinessMode,
            'products' => $products,
            'isOcopSeller' => $isOcopSeller,
            'primaryProduct' => $primaryProduct,
            'categorySlug' => $categorySlug,
            'routeBusinesses' => $routeBusinesses,
        ];
    }

    /**
     * Chuyển đổi thực thể kinh doanh đang quản lý (Gian hàng Chợ <-> Cơ sở kinh doanh <-> Tuyến đường)
     */
    public function switchEntity(Request $request)
    {
        $this->verifyVendor();
        $entityKey = $request->input('entity_key');
        if ($entityKey) {
            session(['active_seller_entity' => $entityKey]);
        }
        return redirect()->back()->with('success', 'Đã chuyển đổi sang quản lý: ' . ($request->input('entity_name') ?: 'Cơ sở được chọn'));
    }

    /**
     * Xem & Cập nhật Thông tin Hồ Sơ Cơ Sở Kinh Doanh / Doanh Nghiệp
     */
    public function showBusinessProfile()
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();
        return view('seller.business-profile', $context);
    }

    /**
     * Lưu cập nhật Hồ Sơ Cơ Sở Kinh Doanh
     */
    public function updateBusinessProfile(Request $request)
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();
        $businessEatery = $context['businessEatery'];

        if (!$businessEatery) {
            return redirect()->back()->with('error', 'Không tìm thấy thông tin Cơ sở kinh doanh thuộc tài khoản này!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'opening_hours' => 'nullable|string|max:100',
            'price_range' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $updateData = [
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'opening_hours' => $request->opening_hours,
            'price_range' => $request->price_range,
            'description' => $request->description,
            'updated_at' => now(),
        ];

        if ($request->hasFile('image')) {
            $updateData['image_path'] = R2Helper::upload($request->file('image'), 'eateries');
        }

        DB::table('eateries')->where('id', $businessEatery->id)->update($updateData);

        // Đồng bộ số điện thoại vào tài khoản nếu cần
        if ($request->filled('phone') && Auth::user()) {
            Auth::user()->update([
                'phone' => preg_replace('/[^0-9]/', '', $request->phone),
            ]);
        }

        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->back()->with('success', '🎉 Đã cập nhật thông tin Cơ sở kinh doanh thành công! Toàn bộ thông tin đã được đồng bộ lên Bản đồ số toàn huyện.');
    }

    /**
     * Dashboard Tiểu Thương / Chủ Gian Hàng / Chủ Cơ Sở OCOP
     */
    public function dashboard()
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();
        
        $productsCount = $context['products']->count();
        
        $ordersCount = 0;
        $totalRevenue = 0;
        $recentOrders = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            $orderQuery = DB::table('orders')
                ->where('stall_name', $context['stallName']);

            $recentOrders = (clone $orderQuery)->latest()->take(10)->get();
            $ordersCount = (clone $orderQuery)->count();
            $totalRevenue = (clone $orderQuery)->where('status', '!=', 'cancelled')->sum('total_amount');
        }

        $viewName = !empty($context['isOcopSeller']) ? 'seller.dashboard-ocop' : 'seller.dashboard';

        return view($viewName, array_merge($context, [
            'productsCount' => $productsCount,
            'ordersCount' => $ordersCount,
            'totalRevenue' => $totalRevenue,
            'recentOrders' => $recentOrders
        ]));
    }

    /**
     * Cập nhật Hồ sơ di sản & Thông tin liên hệ OCOP (Dossier Update)
     */
    public function updateDossier(Request $request)
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();
        $eateryId = $context['eateryId'];

        // 1. Cập nhật thông tin liên hệ địa điểm (eateries)
        $eateryUpdate = [];
        if ($request->has('phone')) {
            $eateryUpdate['phone'] = $request->phone;
        }
        if ($request->has('opening_hours')) {
            $eateryUpdate['opening_hours'] = $request->opening_hours;
        }
        if ($request->has('price_range')) {
            $eateryUpdate['price_range'] = $request->price_range;
        }
        if ($request->has('description')) {
            $eateryUpdate['description'] = $request->description;
        }
        if (!empty($eateryUpdate)) {
            $eateryUpdate['updated_at'] = now();
            DB::connection('mysql_market')->table('eateries')->where('id', $eateryId)->update($eateryUpdate);
        }

        // 2. Cập nhật Hồ Sơ Di Sản OCOP (dossier fields) cho các sản phẩm OCOP thuộc địa điểm này
        $ocopUpdate = [
            'updated_at' => now()
        ];
        if ($request->has('audio_narrative')) {
            $ocopUpdate['audio_narrative'] = $request->audio_narrative;
        }
        if ($request->has('story')) {
            $ocopUpdate['story'] = $request->story;
        }
        if ($request->has('artisans')) {
            $ocopUpdate['artisans'] = $request->artisans;
        }
        if ($request->has('ingredients')) {
            $ocopUpdate['ingredients'] = $request->ingredients;
        }
        if ($request->has('timeline')) {
            $ocopUpdate['timeline'] = $request->timeline;
        }
        if ($request->has('fun_fact')) {
            $ocopUpdate['fun_fact'] = $request->fun_fact;
        }
        if ($request->has('heritage_year')) {
            $ocopUpdate['heritage_year'] = $request->heritage_year;
        }
        if ($request->has('star_rating')) {
            $ocopUpdate['star_rating'] = $request->star_rating;
        }

        DB::connection('mysql_market')->table('ocop_products')->where('eatery_id', $eateryId)->update($ocopUpdate);

        // Xóa sạch Cache ứng dụng để dữ liệu gian hàng mới lập tức hiển thị khắp hệ thống & bản đồ
        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->back()->with('success', '🎉 Đã cập nhật Hồ Sơ Di Sản & Thuyết Minh OCOP thành công! Tất cả thông tin đã được phát hành trực tiếp lên bản đồ.');
    }

    /**
     * Danh sách & Quản lý Sản phẩm / Món ăn của Gian hàng
     */
    public function products()
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();

        return view('seller.products', $context);
    }

    /**
     * Parse giá từ chuỗi hoặc số thuần về giá trị số (DECIMAL)
     */
    private function parsePriceToDecimal($rawPrice): float
    {
        if (is_numeric($rawPrice)) {
            return (float) $rawPrice;
        }
        $cleaned = preg_replace('/[^\d,.]/', '', $rawPrice);
        $cleaned = str_replace(['.', ','], '', $cleaned);
        return (float) $cleaned ?: 0;
    }

    /**
     * Thêm sản phẩm mới cho Gian hàng
     */
    public function storeProduct(Request $request)
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();

        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required',
            'unit' => 'nullable|string|max:20',
            'star_rating' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'description' => 'nullable|string'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = R2Helper::upload($request->file('image'), 'products');
        }

        $numericPrice = $this->parsePriceToDecimal($request->price);

        $description = $request->description ?: '';
        if ($request->filled('origin')) {
            $originText = 'Nguồn gốc: ' . trim($request->origin);
            $description = $description ? ($originText . '. ' . $description) : $originText;
        }

        DB::connection('mysql_market')->table('ocop_products')->insert([
            'eatery_id' => $context['eateryId'],
            'stall_name' => $context['stallName'],
            'seller_name' => $context['sellerName'],
            'seller_phone' => $context['sellerPhone'],
            'name' => $request->name,
            'price' => $numericPrice,
            'unit' => $request->unit ?: 'kg',
            'star_rating' => $request->star_rating ?: null,
            'image_path' => $imagePath,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->back()->with('success', 'Đã thêm sản phẩm mới thành công!');
    }

    /**
     * Cập nhật thông tin / giá / ảnh sản phẩm
     */
    public function updateProduct(Request $request, $id)
    {
        $this->verifyVendor();

        $product = DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->first();
        if (!$product) {
            return redirect()->back()->with('error', 'Sản phẩm không tồn tại!');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required',
            'unit' => 'nullable|string|max:20',
            'origin' => 'nullable|string|max:255',
            'star_rating' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'description' => 'nullable|string'
        ]);

        $imagePath = $product->image_path;
        if ($request->hasFile('image')) {
            $imagePath = R2Helper::upload($request->file('image'), 'products');
        }

        $numericPrice = $this->parsePriceToDecimal($request->price);

        $description = $request->description ?: '';
        if ($request->filled('origin')) {
            $originText = 'Nguồn gốc: ' . trim($request->origin);
            $description = $description ? ($originText . '. ' . $description) : $originText;
        }

        $updateData = [
            'name' => $request->name,
            'price' => $numericPrice,
            'description' => $description,
            'image_path' => $imagePath,
            'updated_at' => now(),
        ];
        if ($request->filled('unit')) {
            $updateData['unit'] = $request->unit;
        }
        if ($request->filled('star_rating')) {
            $updateData['star_rating'] = $request->star_rating;
        }

        DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->update($updateData);

        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->back()->with('success', 'Đã cập nhật sản phẩm OCOP thành công!');
    }

    /**
     * Xóa sản phẩm khỏi Gian hàng
     */
    public function destroyProduct($id)
    {
        $this->verifyVendor();

        DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->delete();

        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->back()->with('success', 'Đã xóa sản phẩm khỏi gian hàng!');
    }

    /**
     * Quản lý đơn hàng phát sinh của Gian hàng
     */
    public function orders()
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();

        $orders = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            $orders = DB::table('orders')
                ->where('stall_name', $context['stallName'])
                ->latest()
                ->paginate(20);

            $orderIds = $orders->pluck('id');
            $allItems = DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->groupBy('order_id');

            $orders->each(function ($ord) use ($allItems) {
                $ord->items = $allItems->get($ord->id, collect());
            });
        }

        return view('seller.orders', array_merge($context, ['orders' => $orders]));
    }

    /**
     * Trang xem chi tiết đơn hàng cho Tiểu Thương
     * GET /seller/orders/{id}
     */
    public function showOrder($id)
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();

        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) {
            return redirect()->route('seller.orders.index')->with('error', 'Đơn hàng không tồn tại!');
        }

        // Bảo mật context
        if ($context['stallName'] && $order->stall_name !== $context['stallName']) {
            abort(403, 'Gian hàng của bạn không có quyền xem đơn hàng này!');
        }

        $items = DB::table('order_items')->where('order_id', $order->id)->get();
        $order->items = $items;

        return view('seller.order-detail', array_merge($context, ['order' => $order]));
    }

    /**
     * Cập nhật trạng thái đơn hàng cho Tiểu Thương
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $this->verifyVendor();
        $request->validate([
            'status' => 'required|string|in:confirmed,ready,completed,cancelled'
        ], [
            'status.in' => 'Trạng thái đơn hàng không hợp lệ!'
        ]);

        if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            DB::table('orders')->where('id', $id)->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);
        }

        $msgMap = [
            'confirmed' => '✅ Đã nhận đơn và chuyển sang trạng thái đang chuẩn bị!',
            'ready' => '🏪 Đã chuyển đơn hàng sang trạng thái: Sẵn sàng tại sạp (Chờ khách lấy)!',
            'completed' => '🎉 Đã hoàn thành đơn hàng thành công!',
            'cancelled' => '❌ Đã từ chối / hủy đơn hàng!',
        ];

        $msg = $msgMap[$request->status] ?? 'Đã cập nhật trạng thái đơn hàng!';
        return redirect()->back()->with('success', $msg);
    }

    /**
     * API JSON: real-time polling cho trang quản lý đơn hàng Seller
     * GET /seller/api/orders
     */
    public function ordersJson()
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();

        $rawOrders = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            $query = DB::table('orders');
            if ($context['stallName']) {
                $query->where(function($q) use ($context) {
                    $q->where('stall_name', $context['stallName']);
                    if (!empty($context['eateryId'])) {
                        $q->orWhere(function($sub) use ($context) {
                            $sub->where('eatery_id', $context['eateryId'])
                                ->where(function($s2) use ($context) {
                                    $s2->whereNull('stall_name')
                                       ->orWhere('stall_name', '')
                                       ->orWhere('stall_name', $context['stallName']);
                                });
                        });
                    }
                });
            } elseif (!empty($context['eateryId'])) {
                $query->where('eatery_id', $context['eateryId']);
            }

            $rawOrders = $query->latest()->limit(50)->get();

            $orderIds = $rawOrders->pluck('id');
            $allItems = DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->groupBy('order_id');

            $rawOrders = $rawOrders->map(function ($ord) use ($allItems) {
                $ord->items = $allItems->get($ord->id, collect())->values();
                $ord->created_at_formatted = \Carbon\Carbon::parse($ord->created_at)->format('H:i d/m/Y');
                return $ord;
            });
        }

        return response()->json([
            'success'   => true,
            'stall'     => $context['stallName'],
            'orders'    => $rawOrders->values(),
            'polled_at' => now()->toDateTimeString(),
            'count'     => $rawOrders->count(),
        ]);
    }

    /**
     * Giao diện Cấu Hình & Cập Nhật Thông Tin Gian Hàng / Thanh Toán VietQR 4.0
     * GET /seller/profile
     */
    public function showProfile()
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();
        
        $primaryProduct = $context['primaryProduct'];
        $user = Auth::user();

        return view('seller.profile', array_merge($context, [
            'user' => $user,
            'primaryProduct' => $primaryProduct
        ]));
    }

    /**
     * Xử lý Cập Nhật Thông Tin Gian Hàng, SĐT/Zalo, Ngân Hàng & Mã VietQR 4.0
     * POST /seller/profile
     */
    public function updateProfile(Request $request)
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();
        $user = Auth::user();
        $db = DB::connection('mysql_market');

        $request->validate([
            'seller_name'  => 'required|string|max:255',
            'seller_phone' => 'required|string|max:50',
            'stall_name'   => 'nullable|string|max:255',
            'address'      => 'nullable|string|max:500',
            'map_link'     => 'nullable|string|max:1000',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
            'bank_name'    => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:100',
            'bank_holder'  => 'nullable|string|max:255',
            'origin'       => 'nullable|string|max:500',
            'attp'         => 'nullable|string|max:255',
            'description'  => 'nullable|string|max:1000',
        ]);

        $sellerName  = trim($request->input('seller_name'));
        $sellerPhone = trim($request->input('seller_phone'));
        $stallName   = trim($request->input('stall_name')) ?: $context['stallName'];
        $address     = trim($request->input('address'));
        $mapLink     = trim($request->input('map_link'));
        $latitude    = $request->input('latitude') !== null && $request->input('latitude') !== '' ? (float)$request->input('latitude') : null;
        $longitude   = $request->input('longitude') !== null && $request->input('longitude') !== '' ? (float)$request->input('longitude') : null;

        // Tự động phân tích & giải mã link Google Maps ra Tọa độ Lat, Lng
        if (!empty($mapLink)) {
            list($autoLat, $autoLng) = $this->parseGoogleMapsUrl($mapLink);
            if ($autoLat !== null && $autoLng !== null) {
                $latitude = $autoLat;
                $longitude = $autoLng;
            }
        }

        $bankName    = trim($request->input('bank_name'));
        $bankAccount = trim($request->input('bank_account'));
        $bankHolder  = trim($request->input('bank_holder'));
        $origin      = trim($request->input('origin'));
        $attp        = trim($request->input('attp'));
        $description = trim($request->input('description'));

        // Xử lý tạo URL VietQR Napas247 tự động nếu có thông tin tài khoản
        $qrCodeUrl = null;
        if (!empty($bankName) && !empty($bankAccount)) {
            $holderUpper = mb_strtoupper($bankHolder ?: $sellerName, 'UTF-8');
            $addInfo = "TT " . Str::slug($stallName, ' ');
            $qrCodeUrl = "https://img.vietqr.io/image/{$bankName}-{$bankAccount}-compact.png?accountName=" . urlencode($holderUpper) . "&addInfo=" . urlencode($addInfo);
        }

        $stallImagePath = null;
        if ($request->hasFile('stall_image')) {
            $stallImagePath = R2Helper::upload($request->file('stall_image'), 'stalls');
        } elseif ($request->filled('stall_image_url')) {
            $stallImagePath = trim($request->input('stall_image_url'));
        }

        // 1. Cập nhật thông tin trên bảng User hiện tại
        if ($user) {
            $userUpdate = [
                'name' => $sellerName, 
                'phone' => $sellerPhone,
                'bank_account' => $bankAccount ?: null,
                'bank_name' => $bankName ?: null,
                'updated_at' => now()
            ];
            if ($stallImagePath) {
                $userUpdate['avatar'] = $stallImagePath;
            }
            DB::table('users')->where('id', $user->id)->update($userUpdate);

            // Cập nhật đồng bộ Hộ kinh doanh Tuyến đường 4.0 (RouteBusiness)
            try {
                $routeUpdate = [
                    'user_id' => $user->id,
                    'owner' => $sellerName,
                    'phone' => $sellerPhone,
                    'bank_account' => $bankAccount ?: null,
                    'bank_name' => $bankName ?: null,
                    'updated_at' => now(),
                ];
                if ($stallImagePath) {
                    $routeUpdate['image_url'] = $stallImagePath;
                }
                \App\Models\RouteBusiness::where('user_id', $user->id)
                    ->orWhere('phone', $sellerPhone)
                    ->update($routeUpdate);
            } catch (\Exception $ex) {}
        }

        // 2. Cập nhật sản phẩm đại diện gian hàng (ocop_products)
        $productQuery = $db->table('ocop_products')->where('eatery_id', $context['eateryId']);
        if ($user) {
            $productQuery->where(function($q) use ($user, $sellerPhone, $context) {
                $q->where('user_id', $user->id)
                  ->orWhere('seller_phone', $sellerPhone)
                  ->orWhere('stall_name', $context['stallName']);
            });
        } else {
            $productQuery->where('stall_name', $context['stallName']);
        }

        $ocopData = [
            'stall_name'   => $stallName,
            'seller_name'  => $sellerName,
            'seller_phone' => $sellerPhone,
            'address'      => $address ?: null,
            'map_link'     => $mapLink ?: null,
            'latitude'     => $latitude,
            'longitude'    => $longitude,
            'bank_name'    => $bankName ?: null,
            'bank_account' => $bankAccount ?: null,
            'bank_holder'  => $bankHolder ?: null,
            'qr_code_path' => $qrCodeUrl,
            'updated_at'   => now(),
        ];
        if ($stallImagePath) {
            $ocopData['image_path'] = $stallImagePath;
        }

        // Tạo chuỗi mô tả kết hợp đầy đủ Nguồn gốc & ATTP
        if (!empty($origin) || !empty($attp) || !empty($description)) {
            $descParts = [];
            if (!empty($origin)) {
                $descParts[] = "Nguồn gốc: " . $origin;
            }
            if (!empty($attp)) {
                $descParts[] = "Cam kết ATTP: " . $attp;
            }
            if (!empty($description)) {
                $descParts[] = $description;
            }
            $ocopData['description'] = implode('. ', $descParts);
        }

        $productQuery->update($ocopData);

        // Cập nhật lại session thông tin người dùng
        session(['stall_name' => $stallName, 'user_name' => $sellerName]);

        // Xóa sạch Cache để thay đổi lập tức có hiệu lực công khai
        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->back()->with('success', '🎉 Đã cập nhật thành công Cấu hình Gian hàng & Thanh toán VietQR! Thông tin mới đã được cập nhật trực tiếp trên bản đồ & gian hàng công khai.');
    }

    /**
     * Tự động giải mã Link Google Maps để trích xuất tọa độ Vĩ độ (Lat) & Kinh độ (Lng)
     */
    private function parseGoogleMapsUrl($url)
    {
        if (empty($url)) return [null, null];

        if (str_contains($url, 'maps.app.goo.gl') || str_contains($url, 'goo.gl/maps')) {
            $headers = @get_headers($url, 1);
            if (isset($headers['Location'])) {
                $url = is_array($headers['Location']) ? (is_array(end($headers['Location'])) ? end(end($headers['Location'])) : end($headers['Location'])) : $headers['Location'];
            }
        }

        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }

        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }

        if (preg_match('/[?&](?:q|ll)=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }

        if (preg_match('/^(-?\d+\.\d+)\s*,\s*(-?\d+\.\d+)$/', trim($url), $m)) {
            return [(float)$m[1], (float)$m[2]];
        }

        return [null, null];
    }

    /**
     * GET /seller/chat
     * Trang Trò Chuyện & Nhắn Tin Khách Hàng cho Gian Hàng Số
     */
    public function chatIndex(Request $request)
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();
        $selectedCustomerId = $request->query('customer_id');

        return view('seller.chat', array_merge($context, [
            'selectedCustomerId' => $selectedCustomerId
        ]));
    }

    /**
     * GET /seller/api/chat/conversations
     * Lấy danh sách các cuộc hội thoại của khách hàng với gian hàng này
     */
    public function apiChatConversations(Request $request)
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();
        $stallName = $context['stallName'];
        $sellerUserId = Auth::id() ?? session('user_id');

        // Tìm tất cả ID khách hàng đã từng nhắn cho sạp này hoặc sạp đã nhắn cho họ
        $customerUserIds = \App\Models\MarketMessage::where(function($q) use ($stallName) {
                $q->where('private_stall_name', $stallName)
                  ->orWhere('stall_name', $stallName);
            })
            ->whereNotNull('user_id')
            ->where('user_id', '!=', $sellerUserId)
            ->pluck('user_id')
            ->merge(
                \App\Models\MarketMessage::where('stall_name', $stallName)
                    ->whereNotNull('private_user_id')
                    ->where('private_user_id', '!=', $sellerUserId)
                    ->pluck('private_user_id')
            )
            ->unique()
            ->filter();

        $conversations = [];
        foreach ($customerUserIds as $cId) {
            $cust = \App\Models\User::find($cId);
            $lastMsg = \App\Models\MarketMessage::where(function($q) use ($stallName, $cId) {
                    $q->where(function($sub) use ($stallName, $cId) {
                        $sub->where('private_stall_name', $stallName)->where('user_id', $cId);
                    })->orWhere(function($sub) use ($stallName, $cId) {
                        $sub->where('stall_name', $stallName)->where('private_user_id', $cId);
                    });
                })
                ->latest('id')
                ->first();
                
            if ($cust && $lastMsg) {
                $conversations[] = [
                    'customer_id' => $cust->id,
                    'customer_name' => $cust->name,
                    'customer_phone' => $cust->phone ?? '',
                    'avatar_char' => mb_substr($cust->name, 0, 1, 'UTF-8'),
                    'last_message' => $lastMsg->message_text ?: ($lastMsg->image_path ? '📷 [Hình ảnh]' : ($lastMsg->product_id ? '🏷️ [Sản phẩm đính kèm]' : '...')),
                    'last_time' => $lastMsg->created_at ? $lastMsg->created_at->diffForHumans() : 'Vừa xong',
                    'last_time_formatted' => $lastMsg->created_at ? $lastMsg->created_at->format('H:i d/m') : '',
                    'last_msg_id' => $lastMsg->id,
                    'is_from_customer' => ($lastMsg->user_id == $cust->id)
                ];
            }
        }

        // Sắp xếp theo tin nhắn mới nhất
        usort($conversations, fn($a, $b) => $b['last_msg_id'] <=> $a['last_msg_id']);

        return response()->json([
            'success' => true,
            'stall_name' => $stallName,
            'conversations' => $conversations
        ]);
    }

    /**
     * GET /seller/api/chat/messages
     * Lấy lịch sử tin nhắn với một khách hàng cụ thể hoặc phòng chat chung
     */
    public function apiChatMessages(Request $request)
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();
        $stallName = $context['stallName'];
        $eateryId = $context['eateryId'];
        $room = $request->input('room', 'private'); // 'private' or 'public'
        $customerId = $request->input('customer_id');

        $query = \App\Models\MarketMessage::where('eatery_id', $eateryId);

        if ($room === 'private') {
            if (!$customerId) {
                return response()->json(['success' => true, 'messages' => []]);
            }

            $query->where(function ($q) use ($stallName, $customerId) {
                $q->where(function ($sub) use ($stallName, $customerId) {
                    $sub->where('private_stall_name', $stallName)
                        ->where('user_id', $customerId);
                })
                ->orWhere(function ($sub) use ($stallName, $customerId) {
                    $sub->where('stall_name', $stallName)
                        ->where('private_user_id', $customerId);
                });
            });
        } else {
            // Phòng chat chung của Chợ
            $query->whereNull('private_stall_name')->whereNull('private_user_id');
        }

        $messages = $query->orderBy('id', 'asc')->take(80)->get();

        $sellerUserId = Auth::id() ?? session('user_id');

        $formatted = $messages->map(function ($msg) use ($sellerUserId, $stallName) {
            $productData = null;
            if ($msg->product_id && $msg->product) {
                $p = $msg->product;
                $productData = [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (float)$p->price,
                    'image' => $p->image_path ? asset($p->image_path) : 'https://placehold.co/80x80/00A86B/ffffff?text=Product',
                    'stall_name' => $p->stall_name
                ];
            }

            $dateGroup = 'Lịch sử';
            if ($msg->created_at->isToday()) {
                $dateGroup = 'Hôm nay';
            } elseif ($msg->created_at->isYesterday()) {
                $dateGroup = 'Hôm qua';
            } else {
                $dateGroup = $msg->created_at->format('d/m/Y');
            }

            $isOwn = ($msg->sender_role === 'merchant' && $msg->stall_name === $stallName) || ($msg->user_id == $sellerUserId);

            return [
                'id' => $msg->id,
                'sender_name' => $msg->sender_name,
                'sender_role' => $msg->sender_role,
                'stall_name' => $msg->stall_name,
                'message_text' => $msg->message_text,
                'image_url' => $msg->image_path ? asset($msg->image_path) : null,
                'product' => $productData,
                'time_formatted' => $msg->created_at->format('H:i'),
                'is_own' => $isOwn,
                'date_group' => $dateGroup
            ];
        });

        return response()->json([
            'success' => true,
            'messages' => $formatted,
            'count' => $formatted->count()
        ]);
    }

    /**
     * POST /seller/api/chat/send
     * Gửi tin nhắn từ Chủ Gian Hàng
     */
    public function apiChatSend(Request $request)
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();
        $stallName = $context['stallName'];
        $sellerName = $context['sellerName'] ?? 'Chủ sạp';
        $eateryId = $context['eateryId'];
        $userId = Auth::id() ?? session('user_id');

        $request->validate([
            'message_text' => 'required_without_all:product_id,image|nullable|string|max:500',
            'product_id' => 'nullable|integer',
            'image' => 'nullable|image|max:5120',
            'customer_id' => 'nullable|integer',
            'room' => 'nullable|string'
        ]);

        $room = $request->input('room', 'private');
        $customerId = $request->input('customer_id');

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = R2Helper::upload($request->file('image'), 'market_chat');
        }

        $messageText = $request->input('message_text');
        if (empty($messageText) && $request->filled('product_id')) {
            $product = \App\Models\OcopProduct::on('mysql_market')->find($request->product_id);
            if ($product) {
                $messageText = "Sạp gửi bạn thông tin sản phẩm: {$product->name}";
            }
        }

        $message = \App\Models\MarketMessage::create([
            'eatery_id' => $eateryId,
            'user_id' => $userId,
            'sender_name' => "Chủ sạp {$sellerName}",
            'sender_role' => 'merchant',
            'stall_name' => $stallName,
            'message_text' => $messageText ?: '',
            'image_path' => $imagePath,
            'product_id' => $request->input('product_id'),
            'private_stall_name' => ($room === 'private') ? $stallName : null,
            'private_user_id' => ($room === 'private' && $customerId) ? (int)$customerId : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_name' => $message->sender_name,
                'sender_role' => 'merchant',
                'stall_name' => $stallName,
                'message_text' => $message->message_text,
                'image_url' => $message->image_path ? asset($message->image_path) : null,
                'time_formatted' => $message->created_at->format('H:i'),
                'is_own' => true
            ]
        ]);
    }

    /**
     * GET /seller/api/chat/unread
     * Lấy số tin nhắn chưa đọc & tin mới nhất cho thông báo real-time
     */
    public function apiChatUnreadCount(Request $request)
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();
        $stallName = $context['stallName'];
        $sellerUserId = Auth::id() ?? session('user_id');

        $latestMsg = \App\Models\MarketMessage::where('private_stall_name', $stallName)
            ->whereNotNull('user_id')
            ->where('user_id', '!=', $sellerUserId)
            ->latest('id')
            ->first();

        return response()->json([
            'success' => true,
            'stall_name' => $stallName,
            'latest_message' => $latestMsg ? [
                'id' => $latestMsg->id,
                'sender_name' => $latestMsg->sender_name,
                'user_id' => $latestMsg->user_id,
                'message_text' => $latestMsg->message_text ?: '📷 [Hình ảnh]',
                'created_at_formatted' => $latestMsg->created_at ? $latestMsg->created_at->diffForHumans() : 'Vừa xong'
            ] : null
        ]);
    }
}
