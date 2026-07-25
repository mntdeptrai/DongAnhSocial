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
        
        $ordersCount = 0;
        $totalRevenue = 0;
        $recentOrders = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            $orderQuery = DB::table('orders')
                ->where(function($q) use ($context) {
                    $q->where('stall_name', $context['stallName']);
                    if (!empty($context['sellerPhone'])) {
                        $q->orWhere('seller_phone', $context['sellerPhone']);
                    }
                });

            $recentOrders = (clone $orderQuery)->latest()->take(10)->get();
            $ordersCount = (clone $orderQuery)->count();
            $totalRevenue = (clone $orderQuery)->where('status', '!=', 'cancelled')->sum('total_price');
        }

        return view('seller.dashboard', array_merge($context, [
            'productsCount' => $productsCount,
            'ordersCount' => $ordersCount,
            'totalRevenue' => $totalRevenue,
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
        $cleaned = preg_replace('/[^\d,.]/', '', $rawPrice);
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
            $orders = DB::table('orders')
                ->where(function($q) use ($context) {
                    $q->where('stall_name', $context['stallName']);
                    if (!empty($context['sellerPhone'])) {
                        $q->orWhere('seller_phone', $context['sellerPhone']);
                    }
                })
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
     * Cập nhật trạng thái đơn hàng cho Tiểu Thương (Chỉ được chọn: Chấp nhận -> confirmed, Hủy/Từ chối -> cancelled)
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $this->verifyVendor();
        $request->validate([
            'status' => 'required|string|in:confirmed,cancelled'
        ], [
            'status.in' => 'Tiểu thương chỉ có thể cập nhật 2 trạng thái đơn hàng: Chấp nhận (confirmed) hoặc Hủy (cancelled)!'
        ]);

        if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            DB::table('orders')->where('id', $id)->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);
        }

        $msg = $request->status === 'confirmed' ? '🎉 Đã chấp nhận đơn hàng thành công!' : '❌ Đã từ chối / hủy đơn hàng thành công!';
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
                ->where(function($q) use ($context) {
                    $q->where('stall_name', $context['stallName']);
                    if (!empty($context['sellerPhone'])) {
                        $q->orWhere('seller_phone', $context['sellerPhone']);
                    }
                })
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
