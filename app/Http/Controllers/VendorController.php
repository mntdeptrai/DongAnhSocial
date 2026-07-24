<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\EateryApiService;

class VendorController extends Controller
{
    /**
     * Xác minh quyền truy cập của Chủ Gian Hàng Số (Stall Tenant Vendor)
     */
    private function verifyVendor()
    {
        $role = session('user_role') ?: (Auth::user() ? Auth::user()->role : null);
        if (!in_array($role, ['seller', 'admin'])) {
            abort(403, 'Bạn không có quyền truy cập Kênh Điều Hành Chủ Gian Hàng!');
        }
    }

    /**
     * Lấy thông tin Gian hàng và danh sách sản phẩm thuộc Stall Tenant hiện tại
     */
    private function getVendorStallContext()
    {
        $user = Auth::user();
        $userPhone = $user->phone ?? session('user_phone') ?? '';
        $userName = $user->name ?? session('user_name') ?? '';

        // Tìm sản phẩm của tiểu thương trong mysql_market
        $query = DB::connection('mysql_market')->table('ocop_products');
        
        if (!empty($userPhone)) {
            $query->where('seller_phone', $userPhone);
        } else {
            $query->where('seller_name', 'LIKE', '%' . $userName . '%');
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            // Lấy gian hàng đầu tiên mặc định cho seller thử nghiệm
            $products = DB::connection('mysql_market')->table('ocop_products')
                ->where('stall_name', 'LIKE', '%Gian hàng Ăn uống Cô Sinh%')
                ->get();
        }

        $first = $products->first();
        $stallName = $first ? $first->stall_name : 'Gian hàng Số của tôi';
        $sellerName = $first ? $first->seller_name : $userName;
        $sellerPhone = $first ? $first->seller_phone : $userPhone;
        $eateryId = $first ? $first->eatery_id : 16; // Default to Chợ Mạch Tràng

        $market = DB::connection('mysql_market')->table('eateries')->where('id', $eateryId)->first();

        return [
            'stallName' => $stallName,
            'sellerName' => $sellerName,
            'sellerPhone' => $sellerPhone,
            'eateryId' => $eateryId,
            'market' => $market,
            'products' => $products
        ];
    }

    /**
     * Dashboard Tiểu Thương / Chủ Gian Hàng
     */
    public function dashboard()
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();
        
        $productsCount = $context['products']->count();
        
        // Lấy danh sách đơn hàng liên quan đến gian hàng này trong bảng orders (nếu có)
        $ordersCount = 0;
        $recentOrders = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            $recentOrders = DB::table('orders')
                ->where('stall_name', $context['stallName'])
                ->orWhere('eatery_id', $context['eateryId'])
                ->latest()
                ->take(5)
                ->get();
            $ordersCount = $recentOrders->count();
        }

        return view('seller.dashboard', array_merge($context, [
            'productsCount' => $productsCount,
            'ordersCount' => $ordersCount,
            'recentOrders' => $recentOrders
        ]));
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
     * Thêm sản phẩm mới cho Gian hàng
     */
    /**
     * Parse giá từ chuỗi hoặc số thuần về giá trị số (DECIMAL)
     */
    private function parsePriceToDecimal($rawPrice): float
    {
        if (is_numeric($rawPrice)) {
            return (float) $rawPrice;
        }
        // Xử lý chuỗi dạng "20.000đ/kg", "30,000đ" → 20000
        $cleaned = preg_replace('/[^\d,.]/', '', $rawPrice); // giữ lại số, dấu , và .
        // Loại bỏ dấu phân cách hàng nghìn (dấu chấm hoặc dấu phẩy không ở cuối)
        $cleaned = str_replace(['.', ','], '', $cleaned);
        return (float) $cleaned ?: 0;
    }

    public function storeProduct(Request $request)
    {
        $this->verifyVendor();
        $context = $this->getVendorStallContext();

        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|min:0',
            'unit' => 'nullable|string|max:20',
            'origin' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'description' => 'nullable|string'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $imagePath = '/uploads/products/' . $filename;
        }

        $numericPrice = $this->parsePriceToDecimal($request->price);
        $desc = "Nguồn gốc: " . ($request->origin ?: 'Tự sản xuất') . ". " . ($request->description ?: '');

        DB::connection('mysql_market')->table('ocop_products')->insert([
            'eatery_id' => $context['eateryId'],
            'stall_name' => $context['stallName'],
            'seller_name' => $context['sellerName'],
            'seller_phone' => $context['sellerPhone'],
            'name' => $request->name,
            'price' => $numericPrice,
            'unit' => $request->unit ?: 'kg',
            'image_path' => $imagePath,
            'description' => $desc,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Đã thêm sản phẩm mới thành công vào gian hàng!');
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'description' => 'nullable|string'
        ]);

        $imagePath = $product->image_path;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $imagePath = '/uploads/products/' . $filename;
        }

        // Luôn lưu price dạng số (DECIMAL) — parse chuỗi format nếu cần
        $numericPrice = $this->parsePriceToDecimal($request->price);

        $updateData = [
            'name' => $request->name,
            'price' => $numericPrice,
            'image_path' => $imagePath,
            'updated_at' => now(),
        ];
        if ($request->filled('unit')) {
            $updateData['unit'] = $request->unit;
        }
        if ($request->filled('description')) {
            $updateData['description'] = $request->description;
        }

        DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->update($updateData);

        return redirect()->back()->with('success', 'Đã cập nhật thông tin và giá sản phẩm thành công!');
    }

    /**
     * Xóa sản phẩm khỏi Gian hàng
     */
    public function destroyProduct($id)
    {
        $this->verifyVendor();

        DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->delete();

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
            // Chỉ lấy đơn hàng của đú ng gian hàng này (theo stall_name)
            // và thuộc luồng chợ truyền thống (category_slug = 'dong-anh-market')
            $orders = DB::table('orders')
                ->where('stall_name', $context['stallName'])
                ->where('category_slug', 'dong-anh-market')
                ->latest()
                ->paginate(20);

            // Eager-load order_items cho từng đơn
            $orderIds = $orders->pluck('id');
            $allItems = DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->groupBy('order_id');

            // Gắn items vào từng order
            $orders->each(function ($ord) use ($allItems) {
                $ord->items = $allItems->get($ord->id, collect());
            });
        }

        return view('seller.orders', array_merge($context, ['orders' => $orders]));
    }

    /**
     * Cập nhật trạng thái đơn hàng (Đang xử lý -> Đã xác nhận -> Đang giao -> Hoàn tất)
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $this->verifyVendor();
        $request->validate(['status' => 'required|string']);

        if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            DB::table('orders')->where('id', $id)->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn hàng thành công!');
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
                ->where('category_slug', 'dong-anh-market')
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
