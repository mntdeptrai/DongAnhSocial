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

        $eatery = null;
        $stallRecord = null; // Bản ghi sản phẩm đại diện cho gian hàng của seller
        $resolvedStallName = null;

        // === BƯỚC 1: Xác định gian hàng cụ thể của Seller ===

        // 1a. Ưu tiên dùng stall_id từ bảng users (nếu đã gán)
        if ($user && $user->stall_id) {
            $stallRecord = DB::table('ocop_products')->where('id', $user->stall_id)->first();
            if ($stallRecord) {
                $resolvedStallName = $stallRecord->stall_name;
                $eatery = DB::table('eateries')->where('id', $stallRecord->eatery_id)->first();
            }
        }

        // 1b. Tìm theo seller_phone
        if (!$stallRecord && !empty($userPhone)) {
            $stallRecord = DB::table('ocop_products')->where('seller_phone', $userPhone)->first();
            if ($stallRecord) {
                $resolvedStallName = $stallRecord->stall_name;
                $eatery = DB::table('eateries')->where('id', $stallRecord->eatery_id)->first();
            }
        }

        // 1c. Tìm theo seller_name
        if (!$stallRecord && !empty($userName)) {
            $stallRecord = DB::table('ocop_products')->where('seller_name', 'LIKE', '%' . $userName . '%')->first();
            if ($stallRecord) {
                $resolvedStallName = $stallRecord->stall_name;
                $eatery = DB::table('eateries')->where('id', $stallRecord->eatery_id)->first();
            }
        }

        // 1d. Fallback: Tìm eatery sở hữu bởi User (qua user_id trên eateries)
        if (!$eatery && $userId) {
            $eatery = DB::table('eateries')->where('user_id', $userId)->first();
        }

        // 1e. Tìm eatery qua eatery_id được gắn cho user (khi admin cấp tài khoản)
        if (!$eatery && $user && $user->eatery_id) {
            $eatery = DB::table('eateries')->where('id', $user->eatery_id)->first();
        }

        // 1f. Session stall_name (được set bởi TenantAuthMiddleware)
        if (!$resolvedStallName && session('stall_name')) {
            $resolvedStallName = session('stall_name');
        }

        // 1g. Nếu vẫn không có, lấy địa điểm mặc định đầu tiên
        if (!$eatery) {
            $eatery = DB::table('eateries')->first();
        }

        $eateryId = $eatery ? $eatery->id : 1;
        $stallName = $resolvedStallName ?: ($eatery ? $eatery->name : 'Gian hàng Số của tôi');
        $sellerName = $userName ?: ($eatery ? $eatery->name : 'Chủ gian hàng');
        $sellerPhone = $userPhone ?: ($eatery ? $eatery->phone : '');

        // Lấy danh mục địa điểm
        $category = null;
        if ($eatery && $eatery->category_id) {
            $category = DB::table('categories')->where('id', $eatery->category_id)->first();
        }
        $categorySlug = $category ? $category->slug : '';

        // === BƯỚC 2: Query sản phẩm CHỈ CỦA GIAN HÀNG NÀY ===
        // Dùng AND (eatery_id + stall_name) thay vì chỉ eatery_id
        $products = collect();
        if ($resolvedStallName) {
            // Seller có gian hàng rõ ràng → chỉ lấy sản phẩm của gian hàng này
            $products = DB::table('ocop_products')
                ->where('eatery_id', $eateryId)
                ->where('stall_name', $resolvedStallName)
                ->get();
        } else {
            // Fallback cho trường hợp eatery chỉ có 1 gian hàng (non-market seller)
            $products = DB::table('ocop_products')->where('eatery_id', $eateryId)->get();
        }

        // Phân biệt chính xác:
        // - Doanh nghiệp / HTX Đặc sản OCOP (categorySlug === 'dong-anh-market') -> $isOcopSeller = true
        // - Tiểu thương kinh doanh trong Chợ truyền thống (categorySlug === 'traditional-market') -> $isOcopSeller = false
        $isOcopSeller = ($categorySlug === 'dong-anh-market');
        $primaryProduct = $products->first();

        if ($products->isEmpty() && $eatery) {
            $dishes = DB::table('dishes')->where('eatery_id', $eatery->id)->get();
            if ($dishes->isNotEmpty()) {
                $products = $dishes->map(function($d) use ($eatery) {
                    return (object)[
                        'id' => $d->id,
                        'eatery_id' => $eatery->id,
                        'stall_name' => $eatery->name,
                        'seller_name' => $eatery->name,
                        'seller_phone' => $eatery->phone,
                        'name' => $d->name,
                        'price' => $d->price,
                        'unit' => 'suất',
                        'description' => $d->description,
                        'image_path' => $d->image_path,
                        'star_rating' => null,
                    ];
                });
            }
        }

        return [
            'stallName' => $stallName,
            'sellerName' => $sellerName,
            'sellerPhone' => $sellerPhone,
            'eateryId' => $eateryId,
            'market' => $eatery,
            'products' => $products,
            'isOcopSeller' => $isOcopSeller,
            'primaryProduct' => $primaryProduct,
            'categorySlug' => $categorySlug,
        ];
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
            DB::table('eateries')->where('id', $eateryId)->update($eateryUpdate);
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

        DB::table('ocop_products')->where('eatery_id', $eateryId)->update($ocopUpdate);

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

        DB::table('ocop_products')->insert([
            'eatery_id' => $context['eateryId'],
            'stall_name' => $context['stallName'],
            'seller_name' => $context['sellerName'],
            'seller_phone' => $context['sellerPhone'],
            'name' => $request->name,
            'price' => $numericPrice,
            'unit' => $request->unit ?: 'kg',
            'star_rating' => $request->star_rating ?: '4 sao',
            'image_path' => $imagePath,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Đã thêm sản phẩm mới thành công!');
    }

    /**
     * Cập nhật thông tin / giá / ảnh sản phẩm
     */
    public function updateProduct(Request $request, $id)
    {
        $this->verifyVendor();

        $product = DB::table('ocop_products')->where('id', $id)->first();
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

        DB::table('ocop_products')->where('id', $id)->update($updateData);

        return redirect()->back()->with('success', 'Đã cập nhật sản phẩm OCOP thành công!');
    }

    /**
     * Xóa sản phẩm khỏi Gian hàng
     */
    public function destroyProduct($id)
    {
        $this->verifyVendor();

        DB::table('ocop_products')->where('id', $id)->delete();

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
            $rawOrders = DB::table('orders')
                ->where('stall_name', $context['stallName'])
                ->latest()
                ->limit(50)
                ->get();

            $orderIds = $rawOrders->pluck('id');
            $allItems = DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->groupBy('order_id');

            $rawOrders = $rawOrders->map(function ($ord) use ($allItems) {
                $ord->items = $allItems->get($ord->id, collect())->values();
                return $ord;
            });
        }

        return response()->json([
            'stall'     => $context['stallName'],
            'orders'    => $rawOrders->values(),
            'polled_at' => now()->toDateTimeString(),
            'count'     => $rawOrders->count(),
        ]);
    }
}
