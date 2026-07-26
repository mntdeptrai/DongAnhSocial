<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\EateryApiService;
use App\Helpers\R2Helper;

class AdminController extends Controller
{
    /**
     * Xác minh quyền truy cập Admin / Seller trước khi thực hiện bất cứ action nào
     */
    private function verifyAdmin()
    {
        $role = session('user_role');
        if (!in_array($role, ['admin', 'seller', 'manager'])) {
            abort(403, 'Bạn không có quyền truy cập trang quản lý này!');
        }
    }

    /**
     * Hiển thị Dashboard Admin / Ban Quản lý Chợ (Manager)
     */
    public function dashboard()
    {
        $this->verifyAdmin();

        $role = session('user_role');
        $isSeller = in_array($role, ['seller', 'manager']);
        $sellerId = session('user_id');

        $allEateries = EateryApiService::getEateries();
        $sellerEateries = $isSeller ? $allEateries->where('user_id', $sellerId) : $allEateries;

        // Nếu là Manager: Chỉ lọc các địa điểm chợ truyền thống thuộc quản lý của họ
        if ($role === 'manager') {
            $sellerEateries = $sellerEateries->filter(function($e) {
                return $e->category && $e->category->slug === 'traditional-market';
            });
        }

        $marketStats = null;
        if ($role === 'manager') {
            $managedMarket = $sellerEateries->first();
            $marketId = $managedMarket ? $managedMarket->id : null;

            $stallsCount = 0;
            $totalOrdersCount = 0;
            $totalRevenue = 0;
            $stallBreakdown = collect();

            if ($marketId) {
                $prods = \Illuminate\Support\Facades\DB::connection('mysql_market')
                    ->table('ocop_products')
                    ->where('eatery_id', $marketId)
                    ->get();
                $stallsCount = $prods->pluck('stall_name')->filter()->unique()->count();

                if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
                    $orders = \Illuminate\Support\Facades\DB::table('orders')
                        ->where('eatery_id', $marketId)
                        ->get();
                    $totalOrdersCount = $orders->count();
                    $totalRevenue = $orders->sum('total_price');

                    $stallBreakdown = $orders->groupBy('stall_name')->map(function($group, $stallName) {
                        return [
                            'stall_name' => $stallName ?: 'Gian hàng chung',
                            'orders_count' => $group->count(),
                            'revenue' => $group->sum('total_price')
                        ];
                    })->values();
                }
            }

            $marketStats = [
                'managed_market' => $managedMarket,
                'stalls_count' => $stallsCount,
                'total_orders' => $totalOrdersCount,
                'total_revenue' => $totalRevenue,
                'stall_breakdown' => $stallBreakdown
            ];
        }

        $stats = [
            'total_eateries' => $sellerEateries->count(),
            'total_categories' => EateryApiService::getCategories()->count(),
            'total_communes' => EateryApiService::getCommunes()->count(),
            'total_reviews' => $sellerEateries->sum('reviews_count'),
        ];

        // Lấy danh sách quán ăn có áp dụng bộ lọc tìm kiếm
        $filteredEateries = $sellerEateries;

        if ($q = request('q')) {
            $filteredEateries = $filteredEateries->filter(function($e) use ($q) {
                return Str::contains(Str::lower($e->name), Str::lower($q))
                    || Str::contains(Str::lower($e->address), Str::lower($q))
                    || Str::contains(Str::lower($e->phone), Str::lower($q));
            });
        }

        if ($categoryName = request('category')) {
            $filteredEateries = $filteredEateries->filter(function($e) use ($categoryName) {
                return $e->category && $e->category->name === $categoryName;
            });
        }

        if ($communeName = request('commune')) {
            $filteredEateries = $filteredEateries->filter(function($e) use ($communeName) {
                return $e->commune && $e->commune->name === $communeName;
            });
        }

        // Tự phân trang Collection bằng LengthAwarePaginator
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 10;
        $currentItems = $filteredEateries->sortByDesc('created_at')->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $eateries = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $filteredEateries->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
        $eateries->withQueryString();

        // Lấy danh sách Video Reviews (Admin xem hết, Seller/Manager xem các cơ sở của họ)
        $videos = EateryApiService::getVideos();
        if ($isSeller) {
            $sellerEateryIds = $sellerEateries->pluck('id')->toArray();
            $videos = $videos->whereIn('eatery_id', $sellerEateryIds)->values();
        }

        if ($role === 'manager') {
            return view('admin.dashboard-manager', compact('stats', 'eateries', 'videos', 'marketStats'));
        }

        return view('admin.dashboard-admin', compact('stats', 'eateries', 'videos'));
    }

    /**
     * Mở form thêm quán mới
     */
    public function createEatery()
    {
        $this->verifyAdmin();

        $role = session('user_role');
        if (in_array($role, ['seller', 'manager'])) {
            $allEateries = EateryApiService::getEateries();
            $hasEatery = $allEateries->where('user_id', session('user_id'))->isNotEmpty();
            if ($hasEatery) {
                return redirect('/admin/dashboard')->with('error', 'Mỗi Ban Quản Lý Chợ chỉ được điều hành duy nhất 1 địa điểm Chợ!');
            }
        }

        $categories = EateryApiService::getCategories();
        $communes = EateryApiService::getCommunes();
        $eatery = null; // Phân biệt Form Thêm và Form Sửa

        return view('admin.eatery-form', compact('categories', 'communes', 'eatery'));
    }

    /**
     * Lưu trữ quán mới
     */
    public function storeEatery(Request $request, \App\Services\EateryService $eateryService)
    {
        $this->verifyAdmin();

        $role = session('user_role');
        if (in_array($role, ['seller', 'manager'])) {
            $allEateries = EateryApiService::getEateries();
            $hasEatery = $allEateries->where('user_id', session('user_id'))->isNotEmpty();
            if ($hasEatery) {
                return redirect('/admin/dashboard')->with('error', 'Mỗi Ban Quản Lý Chợ chỉ được điều hành duy nhất 1 địa điểm Chợ!');
            }
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'category_id' => 'required',
            'commune_id' => 'required',
            'address' => 'required|string|max:200',
            'phone' => 'nullable|string|max:20',
            'opening_hours' => 'nullable|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'price_range' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url' => 'nullable|url',
            'description' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'heritage_year' => 'nullable|string|max:100',
            'story' => 'nullable|string',
            'artisans' => 'nullable|string',
            'fun_fact' => 'nullable|string',
            'audio_narrative' => 'nullable|string',
            'ocop_stars' => 'nullable|integer|between:3,5',
            'ingredients_raw' => 'nullable|string',
            'timeline_raw' => 'nullable|string',
        ]);

        // Lấy Category để xác định slug và kết nối database tương ứng
        $categories = EateryApiService::getCategories();
        $category = $categories->firstWhere('id', $request->category_id);
        if (!$category) {
            return redirect()->back()->withErrors(['category_id' => 'Danh mục không hợp lệ!']);
        }

        $dto = \App\Domain\Eatery\EateryData::fromRequest($request);
        $eateryService->create($dto, $category->slug);

        return redirect('/admin/dashboard')->with('success', 'Thêm mới địa điểm ẩm thực thành công!');
    }

    /**
     * Mở form sửa thông tin quán
     */
    public function editEatery($slug)
    {
        $this->verifyAdmin();

        // Dùng slug để tra cứu trực tiếp — slug là duy nhất xuyên toàn bộ database
        $eatery = EateryApiService::getEateryBySlug($slug);
        if (!$eatery) {
            abort(404, 'Địa điểm không tồn tại!');
        }

        // Ngăn chặn Ban Quản lý Chợ (Manager) chỉnh sửa Chợ của đơn vị khác
        if (in_array(session('user_role'), ['seller', 'manager']) && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền sửa đổi cơ sở này! Bạn chỉ có quyền điều hành Ban Quản lý Chợ được phân công.');
        }

        $categories = EateryApiService::getCategories();
        $communes = EateryApiService::getCommunes();

        return view('admin.eatery-form', compact('eatery', 'categories', 'communes'));
    }

    /**
     * Cập nhật thông tin quán / chợ
     */
    public function updateEatery(Request $request, $slug, \App\Services\EateryService $eateryService)
    {
        $this->verifyAdmin();

        // Dùng slug để tìm đúng địa điểm bất kể database nào
        $eatery = EateryApiService::getEateryBySlug($slug);
        if (!$eatery) {
            abort(404, 'Địa điểm không tồn tại!');
        }

        // Ngăn chặn Ban Quản lý Chợ (Manager) cập nhật Chợ của đơn vị khác
        if (in_array(session('user_role'), ['seller', 'manager']) && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền sửa đổi cơ sở này! Bạn chỉ có quyền điều hành Ban Quản lý Chợ được phân công.');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'category_id' => 'required',
            'commune_id' => 'required',
            'address' => 'required|string|max:200',
            'phone' => 'nullable|string|max:20',
            'opening_hours' => 'nullable|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'price_range' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url' => 'nullable|url',
            'description' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'heritage_year' => 'nullable|string|max:100',
            'story' => 'nullable|string',
            'artisans' => 'nullable|string',
            'fun_fact' => 'nullable|string',
            'audio_narrative' => 'nullable|string',
            'ocop_stars' => 'nullable|integer|between:3,5',
            'ingredients_raw' => 'nullable|string',
            'timeline_raw' => 'nullable|string',
        ]);

        $dto = \App\Domain\Eatery\EateryData::fromRequest($request);
        $eateryService->update($eatery->id, $dto, $eatery->category->slug, $eatery->image_path);

        return redirect()->back()->with('success', 'Cập nhật thông tin quán thành công!');
    }

    public function destroyEatery($slug, \App\Services\EateryService $eateryService)
    {
        $this->verifyAdmin();

        $eatery = EateryApiService::getEateryBySlug($slug);
        if (!$eatery) {
            abort(404, 'Địa điểm không tồn tại!');
        }

        // Ngăn chặn Seller tự ý xóa địa điểm kinh doanh của mình
        if (session('user_role') === 'seller') {
            abort(403, 'Chủ quán không được phép tự xóa địa điểm kinh doanh của mình! Vui lòng liên hệ Quản trị viên.');
        }

        $eateryService->delete($eatery->category->slug, $eatery->id);

        return redirect('/admin/dashboard')->with('success', 'Đã xóa địa điểm khỏi hệ thống bản đồ số!');
    }

    /**
     * Tự động giải mã đường dẫn Google Maps (kể cả link rút gọn) và rút trích Tọa độ Kinh/Vĩ
     */
    public function parseGoogleMapsUrl(Request $request)
    {
        $this->verifyAdmin();

        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->url;

        if (Str::contains($url, ['maps.app.goo.gl', 'goo.gl'])) {
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
                curl_exec($ch);
                $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                curl_close($ch);
                if ($finalUrl) {
                    $url = $finalUrl;
                }
            } catch (\Exception $e) {
                // Keep original
            }
        }

        $lat = null;
        $lng = null;

        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
        }
        elseif (preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
        }
        elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            $lat = $matches[1];
            $lng = $matches[2];
        }

        if ($lat && $lng) {
            return response()->json([
                'success' => true,
                'latitude' => (float)$lat,
                'longitude' => (float)$lng,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy tọa độ trong đường dẫn này. Vui lòng nhập link chuẩn chứa tọa độ hoặc chọn trực tiếp trên Bản đồ.',
        ]);
    }

    /**
     * Thêm món ăn mới vào thực đơn của quán
     */
    public function storeDish(Request $request, \App\Services\DishService $dishService)
    {
        $this->verifyAdmin();

        $request->validate([
            'eatery_id' => 'required',
            'dish_name' => 'required|string|max:100',
            'dish_price' => 'required|numeric|min:0',
            'dish_description' => 'nullable|string',
            'dish_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'dish_image_url' => 'nullable|url',
            'is_signature' => 'nullable|boolean',
        ]);

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $request->eatery_id);
        if (!$eatery) {
            abort(404, 'Quán không tồn tại!');
        }

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền quản trị thực đơn của cơ sở này!');
        }

        $dto = \App\Domain\Dish\DishData::fromRequest($request);
        $dishService->create($dto);

        return redirect()->back()->with('success', 'Thêm món ăn vào thực đơn thành công!');
    }

    /**
     * Cập nhật thông tin món ăn trong thực đơn
     */
    public function updateDish(Request $request, $id, \App\Services\DishService $dishService)
    {
        $this->verifyAdmin();

        $request->validate([
            'dish_name' => 'required|string|max:100',
            'dish_price' => 'required|numeric|min:0',
            'dish_description' => 'nullable|string',
            'dish_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'dish_image_url' => 'nullable|url',
            'is_signature' => 'nullable|boolean',
        ]);

        // Kiểm tra Seller chỉ được sửa món ăn thuộc quán của mình
        if (session('user_role') === 'seller') {
            $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
            $dish = null;
            foreach ($connections as $conn) {
                $d = \App\Models\Dish::on($conn)->find($id);
                if ($d) { $dish = $d; break; }
            }
            if (!$dish) abort(404, 'Món ăn không tồn tại!');

            $eateries = EateryApiService::getEateries();
            $eatery = $eateries->firstWhere('id', $dish->eatery_id);
            if (!$eatery || $eatery->user_id !== session('user_id')) {
                abort(403, 'Bạn không có quyền sửa món ăn của quán người khác!');
            }
        }

        $dto = \App\Domain\Dish\DishData::fromRequest($request);
        $dishService->update($id, $dto);

        return redirect()->back()->with('success', 'Cập nhật món ăn thành công!');
    }

    /**
     * Bật/tắt trạng thái Món ăn đặc trưng
     */
    public function toggleSignatureDish($id, \App\Services\DishService $dishService)
    {
        $this->verifyAdmin();

        // Seller chỉ được toggle món ăn thuộc quán của mình
        if (session('user_role') === 'seller') {
            $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
            $dish = null;
            foreach ($connections as $conn) {
                $d = \App\Models\Dish::on($conn)->find($id);
                if ($d) { $dish = $d; break; }
            }
            if (!$dish) abort(404, 'Món ăn không tồn tại!');

            $eateries = EateryApiService::getEateries();
            $eatery = $eateries->firstWhere('id', $dish->eatery_id);
            if (!$eatery || $eatery->user_id !== session('user_id')) {
                abort(403, 'Bạn không có quyền thay đổi trạng thái món ăn của quán người khác!');
            }
        }

        $dishService->toggleSignature($id);
        return redirect()->back()->with('success', 'Cập nhật trạng thái món ăn thành công!');
    }

    /**
     * Xóa món ăn khỏi thực đơn
     */
    public function destroyDish($id, \App\Services\DishService $dishService)
    {
        $this->verifyAdmin();

        // Seller chỉ được xóa món ăn thuộc quán của mình
        if (session('user_role') === 'seller') {
            $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
            $dish = null;
            foreach ($connections as $conn) {
                $d = \App\Models\Dish::on($conn)->find($id);
                if ($d) { $dish = $d; break; }
            }
            if (!$dish) abort(404, 'Món ăn không tồn tại!');

            $eateries = EateryApiService::getEateries();
            $eatery = $eateries->firstWhere('id', $dish->eatery_id);
            if (!$eatery || $eatery->user_id !== session('user_id')) {
                abort(403, 'Bạn không có quyền xóa món ăn của quán người khác!');
            }
        }

        $dishService->delete($id);
        return redirect()->back()->with('success', 'Xóa món ăn khỏi thực đơn thành công!');
    }

    /**
     * Lưu ảnh thực tế của cơ sở (Gallery)
     */
    public function storeEateryPhoto(Request $request)
    {
        $this->verifyAdmin();

        $request->validate([
            'eatery_id' => 'required',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_url' => 'nullable|url',
            'caption'   => 'nullable|string|max:200',
        ]);

        $eateries = EateryApiService::getEateries();
        $eatery   = $eateries->firstWhere('id', $request->eatery_id);
        if (!$eatery) abort(404, 'Cơ sở không tồn tại!');

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền quản trị ảnh của cơ sở này!');
        }

        $imagePath = $this->resolveImagePath($request->file('image'), $request->image_url, 'gallery');
        if (!$imagePath) {
            return redirect()->back()->withErrors(['image' => 'Vui lòng chọn ảnh hoặc nhập URL ảnh!']);
        }

        EateryApiService::storeEateryPhoto([
            'eatery_id'  => $request->eatery_id,
            'image_path' => $imagePath,
            'caption'    => $request->caption,
            'sort_order' => 0,
        ]);

        return redirect()->back()->with('success', 'Thêm ảnh thực tế thành công!');
    }

    /**
     * Xóa ảnh thực tế của cơ sở
     */
    public function destroyEateryPhoto($id)
    {
        $this->verifyAdmin();

        EateryApiService::deleteEateryPhoto((int) $id);

        return redirect()->back()->with('success', 'Đã xóa ảnh thành công!');
    }

    /**
     * Helper to resolve image path from uploaded file or URL
     */
    protected function resolveImagePath($imageFile, ?string $imageUrl, string $folder = 'general'): ?string
    {
        if ($imageFile) {
            return R2Helper::upload($imageFile, $folder);
        }

        if ($imageUrl) {
            if (preg_match('/(?:drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=))([a-zA-Z0-9_-]{25,50})/i', $imageUrl, $matches)) {
                return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            }
            return $imageUrl;
        }

        return null;
    }

    /**
     * OCOP Products CRUD (Chỉ dành cho Admin hoặc Seller chủ thể OCOP, Manager Chợ không có quyền)
     */
    public function storeOcopProduct(Request $request, \App\Services\OcopProductService $ocopProductService)
    {
        $this->verifyAdmin();

        if (session('user_role') === 'manager') {
            abort(403, 'Ban Quản Lý Chợ không có quyền quản lý hay chỉnh sửa các mặt hàng OCOP!');
        }

        $request->validate([
            'eatery_id' => 'required',
            'name' => 'required|string|max:100',
            'stall_name' => 'nullable|string|max:100',
            'seller_name' => 'nullable|string|max:100',
            'seller_phone' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url',
            'star_rating' => 'nullable|string',
            'heritage_year' => 'nullable|string|max:100',
            'story' => 'nullable|string',
            'artisans' => 'nullable|string',
            'fun_fact' => 'nullable|string',
            'audio_narrative' => 'nullable|string',
            'ingredients_raw' => 'nullable|string',
            'timeline_raw' => 'nullable|string',
        ]);

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $request->eatery_id);
        if (!$eatery) {
            abort(404, 'Cơ sở không tồn tại!');
        }

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền quản trị sản phẩm OCOP của cơ sở này!');
        }

        $dto = \App\Domain\OcopProduct\OcopProductData::fromRequest($request);
        $ocopProductService->create($dto, 'mysql_market');

        return redirect()->back()->with('success', 'Thêm sản phẩm OCOP thành công!');
    }

    public function updateOcopProduct(Request $request, $id, \App\Services\OcopProductService $ocopProductService)
    {
        $this->verifyAdmin();

        if (session('user_role') === 'manager') {
            abort(403, 'Ban Quản Lý Chợ không có quyền quản lý hay chỉnh sửa các mặt hàng OCOP!');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'stall_name' => 'nullable|string|max:100',
            'seller_name' => 'nullable|string|max:100',
            'seller_phone' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_url' => 'nullable|string',
            'star_rating' => 'nullable|string',
            'heritage_year' => 'nullable|string|max:100',
            'story' => 'nullable|string',
            'artisans' => 'nullable|string',
            'fun_fact' => 'nullable|string',
            'audio_narrative' => 'nullable|string',
            'ingredients_raw' => 'nullable|string',
            'timeline_raw' => 'nullable|string',
        ]);

        $connections = ['mysql_market', 'mysql', 'mysql_stay', 'mysql_wellness', 'mysql_education', 'mysql_culture'];
        $product = null;
        $activeConn = 'mysql_market';
        foreach ($connections as $conn) {
            $p = \App\Models\OcopProduct::on($conn)->find($id);
            if ($p) {
                $product = $p;
                $activeConn = $conn;
                break;
            }
        }

        if (!$product) abort(404, 'Sản phẩm OCOP không tồn tại!');

        if (session('user_role') === 'seller') {
            $eateries = EateryApiService::getEateries();
            $eatery = $eateries->firstWhere('id', $product->eatery_id);
            if (!$eatery || $eatery->user_id !== session('user_id')) {
                abort(403, 'Bạn không có quyền chỉnh sửa sản phẩm của cơ sở này!');
            }
        }

        if (!$request->filled('eatery_id')) {
            $request->merge(['eatery_id' => $product->eatery_id]);
        }

        $dto = \App\Domain\OcopProduct\OcopProductData::fromRequest($request);
        $ocopProductService->update($id, $dto, $activeConn);

        return redirect()->back()->with('success', 'Cập nhật sản phẩm OCOP thành công!');
    }

    public function destroyOcopProduct($id, \App\Services\OcopProductService $ocopProductService)
    {
        $this->verifyAdmin();

        if (session('user_role') === 'manager') {
            abort(403, 'Ban Quản Lý Chợ không có quyền quản lý hay chỉnh sửa các mặt hàng OCOP!');
        }

        $product = \App\Models\OcopProduct::on('mysql_market')->find($id);
        if (!$product) abort(404, 'Sản phẩm OCOP không tồn tại!');

        if (session('user_role') === 'seller') {
            $eateries = EateryApiService::getEateries();
            $eatery = $eateries->firstWhere('id', $product->eatery_id);
            if (!$eatery || $eatery->user_id !== session('user_id')) {
                abort(403, 'Bạn không có quyền xóa sản phẩm của cơ sở này!');
            }
        }

        $ocopProductService->delete($id);

        return redirect()->back()->with('success', 'Xóa sản phẩm OCOP thành công!');
    }

    /**
     * Rooms CRUD
     */
    public function storeRoom(Request $request, \App\Services\RoomService $roomService)
    {
        $this->verifyAdmin();

        $request->validate([
            'eatery_id' => 'required',
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url',
            'bed_type' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
        ]);

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $request->eatery_id);
        if (!$eatery) abort(404, 'Cơ sở không tồn tại!');

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền quản trị phòng của cơ sở này!');
        }

        $dto = \App\Domain\Room\RoomData::fromRequest($request);
        $roomService->create($dto, 'mysql_stay');

        return redirect()->back()->with('success', 'Thêm phòng nghỉ thành công!');
    }

    public function updateRoom(Request $request, $id, \App\Services\RoomService $roomService)
    {
        $this->verifyAdmin();

        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url',
            'bed_type' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
        ]);

        $room = \App\Models\Room::on('mysql_stay')->find($id);
        if (!$room) abort(404, 'Phòng nghỉ không tồn tại!');

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $room->eatery_id);
        if (!$eatery) abort(404, 'Cơ sở liên kết không tồn tại!');

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền chỉnh sửa phòng của cơ sở này!');
        }

        $dto = \App\Domain\Room\RoomData::fromRequest($request);
        $roomService->update($id, $dto, 'mysql_stay');

        return redirect()->back()->with('success', 'Cập nhật thông tin phòng thành công!');
    }

    public function destroyRoom($id, \App\Services\RoomService $roomService)
    {
        $this->verifyAdmin();

        $room = \App\Models\Room::on('mysql_stay')->find($id);
        if (!$room) abort(404, 'Phòng nghỉ không tồn tại!');

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $room->eatery_id);
        if (!$eatery) abort(404, 'Cơ sở liên kết không tồn tại!');

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền xóa phòng của cơ sở này!');
        }

        $roomService->delete($id);

        return redirect()->back()->with('success', 'Xóa phòng nghỉ thành công!');
    }

    /**
     * Wellness Services CRUD
     */
    public function storeWellnessService(Request $request, \App\Services\WellnessMapService $wellnessService)
    {
        $this->verifyAdmin();

        $request->validate([
            'eatery_id' => 'required',
            'name' => 'required|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url',
            'duration' => 'nullable|string',
        ]);

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $request->eatery_id);
        if (!$eatery) abort(404, 'Cơ sở không tồn tại!');

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền quản trị dịch vụ của cơ sở này!');
        }

        $dto = \App\Domain\Wellness\WellnessServiceData::fromRequest($request);
        $wellnessService->create($dto, 'mysql_wellness');

        return redirect()->back()->with('success', 'Thêm dịch vụ chăm sóc thành công!');
    }

    public function updateWellnessService(Request $request, $id, \App\Services\WellnessMapService $wellnessService)
    {
        $this->verifyAdmin();

        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url',
            'duration' => 'nullable|string',
        ]);

        $service = \App\Models\WellnessService::on('mysql_wellness')->find($id);
        if (!$service) abort(404, 'Dịch vụ không tồn tại!');

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $service->eatery_id);
        if (!$eatery) abort(404, 'Cơ sở liên kết không tồn tại!');

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền chỉnh sửa dịch vụ của cơ sở này!');
        }

        $dto = \App\Domain\Wellness\WellnessServiceData::fromRequest($request);
        $wellnessService->update($id, $dto, 'mysql_wellness');

        return redirect()->back()->with('success', 'Cập nhật dịch vụ thành công!');
    }

    public function destroyWellnessService($id, \App\Services\WellnessMapService $wellnessService)
    {
        $this->verifyAdmin();

        $service = \App\Models\WellnessService::on('mysql_wellness')->find($id);
        if (!$service) abort(404, 'Dịch vụ không tồn tại!');

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $service->eatery_id);
        if (!$eatery) abort(404, 'Cơ sở liên kết không tồn tại!');

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền xóa dịch vụ của cơ sở này!');
        }

        $wellnessService->delete($id);

        return redirect()->back()->with('success', 'Xóa dịch vụ thành công!');
    }

    /**
     * Education Programs CRUD
     */
    public function storeEducationProgram(Request $request, \App\Services\EducationProgramService $educationService)
    {
        $this->verifyAdmin();

        $request->validate([
            'eatery_id' => 'required',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url',
            'duration' => 'nullable|string',
            'tuition_fee' => 'nullable|string',
        ]);

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $request->eatery_id);
        if (!$eatery) abort(404, 'Trường học/Cơ sở không tồn tại!');

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền quản trị chương trình đào tạo của cơ sở này!');
        }

        $dto = \App\Domain\Education\EducationProgramData::fromRequest($request);
        $educationService->create($dto, 'mysql_education');

        return redirect()->back()->with('success', 'Thêm chương trình đào tạo thành công!');
    }

    public function updateEducationProgram(Request $request, $id, \App\Services\EducationProgramService $educationService)
    {
        $this->verifyAdmin();

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url',
            'duration' => 'nullable|string',
            'tuition_fee' => 'nullable|string',
        ]);

        $program = \App\Models\EducationProgram::on('mysql_education')->find($id);
        if (!$program) abort(404, 'Chương trình đào tạo không tồn tại!');

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $program->eatery_id);
        if (!$eatery) abort(404, 'Cơ sở liên kết không tồn tại!');

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền chỉnh sửa chương trình của cơ sở này!');
        }

        $dto = \App\Domain\Education\EducationProgramData::fromRequest($request);
        $educationService->update($id, $dto, 'mysql_education');

        return redirect()->back()->with('success', 'Cập nhật chương trình đào tạo thành công!');
    }

    public function destroyEducationProgram($id, \App\Services\EducationProgramService $educationService)
    {
        $this->verifyAdmin();

        $program = \App\Models\EducationProgram::on('mysql_education')->find($id);
        if (!$program) abort(404, 'Chương trình đào tạo không tồn tại!');

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $program->eatery_id);
        if (!$eatery) abort(404, 'Cơ sở liên kết không tồn tại!');

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền xóa chương trình của cơ sở này!');
        }

        $educationService->delete($id);

        return redirect()->back()->with('success', 'Xóa chương trình đào tạo thành công!');
    }

    /**
     * Đăng Video Review
     */
    public function storeVideo(Request $request, \App\Services\ReviewVideoService $videoService)
    {
        $this->verifyAdmin();

        $request->validate([
            'title' => 'required|string|max:255',
            'eatery_id' => 'required',
            'video_file' => 'nullable|file|mimes:mp4,mov,ogg,qt|max:20480',
            'video_url' => 'nullable|url',
        ]);

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $request->eatery_id);
        if (!$eatery) abort(404);

        $role = session('user_role');
        $userId = session('user_id');

        if ($role === 'seller' && $eatery->user_id !== $userId) {
            abort(403, 'Bạn không thể đăng video review cho cơ sở không thuộc sở hữu của bạn!');
        }

        $status = 'approved';

        $dto = \App\Domain\ReviewVideo\ReviewVideoData::fromRequest($request);
        $videoService->create($dto, $status);

        $message = '🎉 Đăng video review thành công và đã được công khai hiển thị trên bản đồ!';
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Cập nhật Video Review
     */
    public function updateVideo(Request $request, $id, \App\Services\ReviewVideoService $videoService)
    {
        $this->verifyAdmin();
        
        $videos = EateryApiService::getVideos(); // note: Admin view sees all, but we can query them
        // For video updates, we locate video connection
        // We find the video first:
        $video = null;
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        foreach ($connections as $conn) {
            $v = \App\Models\ReviewVideo::on($conn)->find($id);
            if ($v) {
                $video = $v;
                break;
            }
        }
        if (!$video) abort(404, 'Video không tồn tại');

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $video->eatery_id);
        
        $role = session('user_role');
        $userId = session('user_id');

        if ($role === 'seller' && $video->user_id !== $userId && $eatery->user_id !== $userId) {
            abort(403, 'Bạn không thể sửa video review không thuộc sở hữu của bạn!');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'eatery_id' => 'required',
            'video_file' => 'nullable|file|mimes:mp4,mov,ogg,qt|max:20480',
            'video_url' => 'nullable|url',
        ]);

        $newEatery = $eateries->firstWhere('id', $request->eatery_id);
        if (!$newEatery) {
            abort(404, 'Cơ sở kinh doanh không tồn tại!');
        }
        if ($role === 'seller' && $newEatery->user_id !== $userId) {
            abort(403, 'Bạn không thể liên kết video với cơ sở không thuộc sở hữu của bạn!');
        }

        $status = 'approved';

        $dto = \App\Domain\ReviewVideo\ReviewVideoData::fromRequest($request);
        $videoService->update($id, $dto, $video->video_url, $video->video_type, $status);

        $message = '🎉 Cập nhật video review thành công và đã được công khai trên bản đồ!';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Phê duyệt Video Review
     */
    public function approveVideo($id, \App\Services\ReviewVideoService $videoService)
    {
        $this->verifyAdmin();
        if (session('user_role') !== 'admin') {
            abort(403, 'Chỉ Quản trị viên hệ thống mới có quyền phê duyệt video!');
        }

        $videoService->approve($id);
        return redirect()->back()->with('success', '🎉 Phê duyệt video thành công! Video đã được công khai.');
    }

    /**
     * Từ chối Video Review
     */
    public function rejectVideo($id, \App\Services\ReviewVideoService $videoService)
    {
        $this->verifyAdmin();
        if (session('user_role') !== 'admin') {
            abort(403, 'Chỉ Quản trị viên hệ thống mới có quyền từ chối video!');
        }

        $videoService->reject($id);
        return redirect()->back()->with('success', '❌ Đã từ chối video review.');
    }

    /**
     * Xóa Video Review
     */
    public function destroyVideo($id, \App\Services\ReviewVideoService $videoService)
    {
        $this->verifyAdmin();

        // Seller chỉ được xóa video thuộc quán của mình
        if (session('user_role') === 'seller') {
            $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
            $video = null;
            foreach ($connections as $conn) {
                $v = \App\Models\ReviewVideo::on($conn)->find($id);
                if ($v) { $video = $v; break; }
            }
            if (!$video) abort(404, 'Video không tồn tại!');

            $eateries = EateryApiService::getEateries();
            $eatery = $eateries->firstWhere('id', $video->eatery_id);
            if ($video->user_id !== session('user_id') && (!$eatery || $eatery->user_id !== session('user_id'))) {
                abort(403, 'Bạn không có quyền xóa video không thuộc sở hữu của bạn!');
            }
        }

        $videoService->delete($id);
        return redirect()->back()->with('success', '🗑️ Xóa video review thành công!');
    }

    /**
     * Cập nhật Giấy Chứng Nhận An Toàn Thực Phẩm
     */
    public function storeFoodSafetyCertificate(Request $request, \App\Services\TrustHubService $trustHubService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id' => 'required',
            'certificate_number' => 'required|string|max:100',
            'issued_by' => 'required|string|max:150',
            'issued_at' => 'required|date',
            'expired_at' => 'required|date|after:issued_at',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url' => 'nullable|url',
        ]);

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $request->eatery_id);
        if (!$eatery) abort(404);

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền cập nhật hồ sơ của cơ sở này!');
        }

        $imagePath = $request->image_url ?: '/uploads/certificates/default-cert.jpg';

        $trustHubService->storeCertificate([
            'eatery_id' => $request->eatery_id,
            'certificate_number' => $request->certificate_number,
            'issued_by' => $request->issued_by,
            'issued_at' => $request->issued_at,
            'expired_at' => $request->expired_at,
            'image_path' => $imagePath,
        ], $request->file('image'));

        return redirect()->back()->with('success', 'Cập nhật Giấy chứng nhận ATTP thành công!');
    }

    /**
     * Ghi nhật ký kiểm tra an toàn thực phẩm hàng ngày
     */
    public function storeDailyFoodLog(Request $request, \App\Services\TrustHubService $trustHubService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id' => 'required',
            'log_date' => 'required|date',
            'ingredients_origin' => 'required|string|max:255',
            'storage_condition' => 'required|string|max:255',
            'checker_name' => 'required|string|max:100',
        ]);

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $request->eatery_id);
        if (!$eatery) abort(404);

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền quản lý nhật ký của cơ sở này!');
        }

        $trustHubService->storeDailyLog([
            'eatery_id' => $request->eatery_id,
            'log_date' => $request->log_date,
            'ingredients_origin' => $request->ingredients_origin,
            'storage_condition' => $request->storage_condition,
            'checker_name' => $request->checker_name,
        ], null);

        return redirect()->back()->with('success', 'Ghi nhật ký kiểm tra vệ sinh hàng ngày thành công!');
    }

    /**
     * Thêm hợp đồng cung cấp thực phẩm
     */
    public function storeFoodSupplyContract(Request $request, \App\Services\TrustHubService $trustHubService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id' => 'required',
            'supplier_name' => 'required|string|max:150',
            'items_supplied' => 'required|string|max:255',
            'signed_at' => 'required|date',
            'expired_at' => 'required|date|after:signed_at',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url' => 'nullable|url',
        ]);

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $request->eatery_id);
        if (!$eatery) abort(404);

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền quản lý hợp đồng của cơ sở này!');
        }

        $imagePath = $request->image_url ?: 'https://images.unsplash.com/photo-1450133064473-71024230f91b?auto=format&fit=crop&w=800&q=80';

        $trustHubService->storeContract([
            'eatery_id' => $request->eatery_id,
            'supplier_name' => $request->supplier_name,
            'items_supplied' => $request->items_supplied,
            'signed_at' => $request->signed_at,
            'expired_at' => $request->expired_at,
            'image_path' => $imagePath,
        ], $request->file('image'));

        return redirect()->back()->with('success', 'Thêm mới hợp đồng cung cấp thành công!');
    }

    /**
     * Thêm hóa đơn mua bán thực phẩm
     */
    public function storePurchaseInvoice(Request $request, \App\Services\TrustHubService $trustHubService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id' => 'required',
            'supplier_name' => 'required|string|max:150',
            'items_summary' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url' => 'nullable|url',
        ]);

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $request->eatery_id);
        if (!$eatery) abort(404);

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền quản lý hóa đơn của cơ sở này!');
        }

        $imagePath = $request->image_url ?: 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=800&q=80';

        $trustHubService->storeInvoice([
            'eatery_id' => $request->eatery_id,
            'supplier_name' => $request->supplier_name,
            'items_summary' => $request->items_summary,
            'invoice_date' => $request->invoice_date,
            'image_path' => $imagePath,
        ], $request->file('image'));

        return redirect()->back()->with('success', 'Thêm mới hóa đơn mua bán thành công!');
    }

    public function destroyFoodSupplyContract($id, \App\Services\TrustHubService $trustHubService)
    {
        $this->verifyAdmin();

        // Seller chỉ được xóa hợp đồng thuộc quán của mình
        if (session('user_role') === 'seller') {
            $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
            $contract = null;
            foreach ($connections as $conn) {
                $c = \App\Models\FoodSupplyContract::on($conn)->find($id);
                if ($c) { $contract = $c; break; }
            }
            if (!$contract) abort(404, 'Hợp đồng không tồn tại!');

            $eateries = EateryApiService::getEateries();
            $eatery = $eateries->firstWhere('id', $contract->eatery_id);
            if (!$eatery || $eatery->user_id !== session('user_id')) {
                abort(403, 'Bạn không có quyền xóa hợp đồng của cơ sở người khác!');
            }
        }

        $trustHubService->deleteContract($id);
        return redirect()->back()->with('success', 'Xóa hợp đồng thành công!');
    }

    public function destroyPurchaseInvoice($id, \App\Services\TrustHubService $trustHubService)
    {
        $this->verifyAdmin();

        // Seller chỉ được xóa hóa đơn thuộc quán của mình
        if (session('user_role') === 'seller') {
            $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
            $invoice = null;
            foreach ($connections as $conn) {
                $inv = \App\Models\PurchaseInvoice::on($conn)->find($id);
                if ($inv) { $invoice = $inv; break; }
            }
            if (!$invoice) abort(404, 'Hóa đơn không tồn tại!');

            $eateries = EateryApiService::getEateries();
            $eatery = $eateries->firstWhere('id', $invoice->eatery_id);
            if (!$eatery || $eatery->user_id !== session('user_id')) {
                abort(403, 'Bạn không có quyền xóa hóa đơn của cơ sở người khác!');
            }
        }

        $trustHubService->deleteInvoice($id);
        return redirect()->back()->with('success', 'Xóa hóa đơn thành công!');
    }

    public function destroyDailyFoodLog($id, \App\Services\TrustHubService $trustHubService)
    {
        $this->verifyAdmin();

        // Seller chỉ được xóa nhật ký thuộc quán của mình
        if (session('user_role') === 'seller') {
            $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
            $log = null;
            foreach ($connections as $conn) {
                $l = \App\Models\DailyFoodLog::on($conn)->find($id);
                if ($l) { $log = $l; break; }
            }
            if (!$log) abort(404, 'Nhật ký không tồn tại!');

            $eateries = EateryApiService::getEateries();
            $eatery = $eateries->firstWhere('id', $log->eatery_id);
            if (!$eatery || $eatery->user_id !== session('user_id')) {
                abort(403, 'Bạn không có quyền xóa nhật ký của cơ sở người khác!');
            }
        }

        $trustHubService->deleteDailyLog($id);
        return redirect()->back()->with('success', 'Xóa nhật ký kiểm tra thành công!');
    }

    /**
     * Xóa đánh giá spam
     */
    public function destroyReview($id)
    {
        $this->verifyAdmin();
        if (session('user_role') !== 'admin') {
            abort(403, 'Bạn không có quyền xóa đánh giá của khách hàng!');
        }

        EateryApiService::deleteReview($id);
        return redirect()->back()->with('success', 'Đã xóa đánh giá khỏi hệ thống!');
    }

    /**
     * Phản hồi nhận xét
     */
    public function replyReview(Request $request, $id)
    {
        $this->verifyAdmin();
        $request->validate([
            'seller_reply' => 'nullable|string|max:1000',
        ]);

        // Seller must own this eatery
        $eateries = EateryApiService::getEateries();
        
        $review = null;
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        foreach ($connections as $conn) {
            $r = \App\Models\Review::on($conn)->find($id);
            if ($r) {
                $review = $r;
                break;
            }
        }
        if (!$review) abort(404);

        $eatery = $eateries->firstWhere('id', $review->eatery_id);
        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền phản hồi nhận xét của cơ sở này!');
        }

        EateryApiService::replyReview($id, $request->seller_reply);
        return redirect()->back()->with('success', 'Đã lưu phản hồi của bạn tới khách hàng!');
    }

    /**
     * Danh sách tài khoản User / Tiểu thương Chợ
     */
    public function indexUsers(Request $request)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền quản lý tài khoản người dùng!');
        }

        $query = User::query();

        if ($role === 'manager') {
            // Manager chỉ xem & quản lý tiểu thương (seller) thuộc chợ truyền thống của mình
            $managerUserId = session('user_id');
            $managerEatery = EateryApiService::getEateries('traditional-market')->firstWhere('user_id', $managerUserId);
            $eateryId = $managerEatery ? $managerEatery->id : 0;

            $sellerPhones = \Illuminate\Support\Facades\DB::connection('mysql_market')
                ->table('ocop_products')
                ->where('eatery_id', $eateryId)
                ->whereNotNull('seller_phone')
                ->pluck('seller_phone')
                ->filter()
                ->unique();

            $query->where('role', 'seller')->whereIn('phone', $sellerPhones);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $totalUsers = (clone $query)->count();
        $adminCount = $role === 'admin' ? User::where('role', 'admin')->count() : 0;
        $sellerCount = (clone $query)->where('role', 'seller')->count();
        $userCount = $role === 'admin' ? User::where('role', 'user')->count() : 0;

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('admin.users.partial-table', compact('users'))->render();
        }

        return view('admin.users.index', compact('users', 'totalUsers', 'adminCount', 'sellerCount', 'userCount'));
    }

    public function createUser()
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền thêm người dùng mới!');
        }
        $eateries = EateryApiService::getEateries();
        if ($role === 'manager') {
            $eateries = $eateries->where('user_id', session('user_id'));
        }
        return view('admin.users.create', compact('eateries'));
    }

    public function storeUser(Request $request)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền thêm người dùng mới!');
        }

        $allowedRoles = $role === 'admin' ? 'admin,manager,seller,user' : 'seller';

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:' . $allowedRoles,
            'phone' => 'nullable|string|max:15',
            'avatar' => 'nullable|string|max:10',
            'eatery_id' => 'nullable|integer',
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

        if ($request->role === 'seller' && $request->eatery_id) {
            $eatery = EateryApiService::getEateries()->firstWhere('id', $request->eatery_id);
            if ($eatery) {
                EateryApiService::updateEatery($eatery->category->slug, $eatery->id, [
                    'user_id' => $user->id
                ]);
            }
        }

        return redirect('/admin/users')->with('success', 'Thêm mới tài khoản người dùng/tiểu thương thành công!');
    }

    public function showUser($id)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền xem thông tin chi tiết người dùng!');
        }

        $user = User::findOrFail($id);
        if ($role === 'manager' && $user->role !== 'seller') {
            abort(403, 'Ban Quản Lý Chợ chỉ được quyền quản lý tiểu thương trong chợ của mình!');
        }

        return view('admin.users.show', compact('user'));
    }

    public function editUser($id)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền chỉnh sửa tài khoản người dùng!');
        }

        $user = User::findOrFail($id);
        if ($role === 'manager' && $user->role !== 'seller') {
            abort(403, 'Ban Quản Lý Chợ chỉ được quyền quản lý tiểu thương trong chợ của mình!');
        }

        $eateries = EateryApiService::getEateries();
        if ($role === 'manager') {
            $eateries = $eateries->where('user_id', session('user_id'));
        }
        
        $currentEatery = $eateries->firstWhere('user_id', $user->id);
        $currentEateryId = $currentEatery ? $currentEatery->id : null;

        return view('admin.users.edit', compact('user', 'eateries', 'currentEateryId'));
    }

    public function updateUser(Request $request, $id)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền cập nhật tài khoản người dùng!');
        }

        $user = User::findOrFail($id);
        if ($role === 'manager' && $user->role !== 'seller') {
            abort(403, 'Ban Quản Lý Chợ chỉ được quyền quản lý tiểu thương trong chợ của mình!');
        }

        $allowedRoles = $role === 'admin' ? 'admin,manager,seller,user' : 'seller';

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|string|in:' . $allowedRoles,
            'phone' => 'nullable|string|max:15',
            'avatar' => 'nullable|string|max:10',
            'status' => 'required|string|in:active,disabled',
            'password' => 'nullable|string|min:6',
            'eatery_id' => 'nullable|integer',
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

        // Hủy liên kết cũ nếu có
        $allEateries = EateryApiService::getEateries();
        foreach ($allEateries as $eat) {
            if ($eat->user_id === $user->id) {
                EateryApiService::updateEatery($eat->category->slug, $eat->id, [
                    'user_id' => null
                ]);
            }
        }

        // Tạo liên kết mới nếu role là seller và chọn cửa hàng
        if ($request->role === 'seller' && $request->eatery_id) {
            $eatery = $allEateries->firstWhere('id', $request->eatery_id);
            if ($eatery) {
                EateryApiService::updateEatery($eatery->category->slug, $eatery->id, [
                    'user_id' => $user->id
                ]);
            }
        }

        return redirect('/admin/users')->with('success', 'Cập nhật tài khoản người dùng thành công!');
    }

    public function destroyUser($id)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền xóa tài khoản người dùng!');
        }

        $user = User::findOrFail($id);

        if ($role === 'manager' && $user->role !== 'seller') {
            abort(403, 'Ban Quản Lý Chợ chỉ được quyền quản lý tiểu thương trong chợ của mình!');
        }
        
        if ($user->id === session('user_id')) {
            return redirect()->back()->with('error', 'Bạn không được phép tự xóa tài khoản của chính mình!');
        }

        $user->delete();

        return redirect('/admin/users')->with('success', 'Đã xóa tài khoản người dùng khỏi hệ thống!');
    }

    public function toggleUserStatus($id)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền thay đổi trạng thái tài khoản!');
        }

        $user = User::findOrFail($id);

        if ($role === 'manager' && $user->role !== 'seller') {
            abort(403, 'Ban Quản Lý Chợ chỉ được quyền quản lý tiểu thương trong chợ của mình!');
        }

        if ($user->id === session('user_id')) {
            return redirect()->back()->with('error', 'Bạn không thể tự vô hiệu hóa tài khoản của chính mình!');
        }

        $user->status = $user->status === 'active' ? 'disabled' : 'active';
        $user->save();

        $message = $user->status === 'active' ? 'Kích hoạt tài khoản thành công!' : 'Đã vô hiệu hóa tài khoản thành công!';
        return redirect()->back()->with('success', $message);
    }

    /**
     * Thêm hoạt động văn hóa/trải nghiệm mới
     */
    public function storeCulturalActivity(Request $request, \App\Services\CulturalActivityService $service)
    {
        $this->verifyAdmin();

        $request->validate([
            'eatery_id'              => 'required',
            'eatery_slug'            => 'required|string',
            'activity_name'          => 'required|string|max:255',
            'activity_type'          => 'required|string|in:experience,ticket,service,other',
            'activity_price'         => 'nullable|numeric|min:0',
            'activity_unit'          => 'required|string|max:100',
            'activity_discount_note' => 'nullable|string|max:255',
            'activity_description'   => 'nullable|string',
            'activity_image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'activity_image_url'     => 'nullable|url',
        ]);

        // Dùng slug để xác định đúng eatery và đúng database connection
        $eatery = EateryApiService::getEateryBySlug($request->eatery_slug);
        if (!$eatery) {
            abort(404, 'Cơ sở không tồn tại!');
        }

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền quản trị cơ sở này!');
        }

        // Tạo DTO
        $dto = new \App\Domain\CulturalActivity\CulturalActivityData(
            eatery_id: $eatery->id,
            name: $request->activity_name,
            type: $request->activity_type,
            price: $request->filled('activity_price') ? (float) $request->activity_price : null,
            unit: $request->activity_unit,
            discount_note: $request->activity_discount_note,
            description: $request->activity_description,
            image: $request->file('activity_image'),
            image_url: $request->activity_image_url
        );

        $conn = EateryApiService::getConnection($eatery->category->slug);
        $service->create($dto, $conn);

        return redirect()->back()->with('success', 'Thêm hoạt động văn hóa thành công!');
    }

    /**
     * Cập nhật hoạt động văn hóa/trải nghiệm
     */
    public function updateCulturalActivity(Request $request, $id, \App\Services\CulturalActivityService $service)
    {
        $this->verifyAdmin();

        $request->validate([
            'activity_name' => 'required|string|max:255',
            'activity_type' => 'required|string|in:experience,ticket,service,other',
            'activity_price' => 'nullable|numeric|min:0',
            'activity_unit' => 'required|string|max:100',
            'activity_discount_note' => 'nullable|string|max:255',
            'activity_description' => 'nullable|string',
            'activity_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'activity_image_url' => 'nullable|url',
        ]);

        // Authorization checks for seller
        $activity = null;
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        foreach ($connections as $conn) {
            $act = \App\Models\CulturalActivity::on($conn)->find($id);
            if ($act) { $activity = $act; break; }
        }
        if (!$activity) abort(404, 'Hoạt động không tồn tại!');

        if (session('user_role') === 'seller') {
            $eateries = EateryApiService::getEateries();
            $eatery = $eateries->firstWhere('id', $activity->eatery_id);
            if (!$eatery || $eatery->user_id !== session('user_id')) {
                abort(403, 'Bạn không có quyền sửa hoạt động của cơ sở này!');
            }
        }

        // Tạo DTO
        $dto = new \App\Domain\CulturalActivity\CulturalActivityData(
            eatery_id: $activity->eatery_id,
            name: $request->activity_name,
            type: $request->activity_type,
            price: $request->filled('activity_price') ? (float) $request->activity_price : null,
            unit: $request->activity_unit,
            discount_note: $request->activity_discount_note,
            description: $request->activity_description,
            image: $request->file('activity_image'),
            image_url: $request->activity_image_url
        );

        $service->update($id, $dto);

        return redirect()->back()->with('success', 'Cập nhật hoạt động văn hóa thành công!');
    }

    /**
     * Xóa hoạt động văn hóa/trải nghiệm
     */
    public function destroyCulturalActivity($id, \App\Services\CulturalActivityService $service)
    {
        $this->verifyAdmin();

        if (session('user_role') === 'seller') {
            $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
            $activity = null;
            foreach ($connections as $conn) {
                $act = \App\Models\CulturalActivity::on($conn)->find($id);
                if ($act) { $activity = $act; break; }
            }
            if (!$activity) abort(404, 'Hoạt động không tồn tại!');

            $eateries = EateryApiService::getEateries();
            $eatery = $eateries->firstWhere('id', $activity->eatery_id);
            if (!$eatery || $eatery->user_id !== session('user_id')) {
                abort(403, 'Bạn không có quyền xóa hoạt động của cơ sở này!');
            }
        }

        $service->delete($id);

        return redirect()->back()->with('success', 'Xóa hoạt động văn hóa thành công!');
    }

    /**
     * Đăng bản tin số mới cho Ban Quản Lý Chợ
     */
    public function storeAnnouncement(Request $request, $id)
    {
        $this->verifyAdmin();
        $request->validate([
            'tag' => 'required|string|max:100',
            'title' => 'required|string|max:200',
            'content' => 'required|string|max:500',
            'time' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:30',
        ]);

        $eatery = null;
        $connName = null;
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        foreach ($connections as $conn) {
            $e = \Illuminate\Support\Facades\DB::connection($conn)->table('eateries')->where('id', $id)->first();
            if ($e) {
                $eatery = $e;
                $connName = $conn;
                break;
            }
        }

        if (!$eatery) abort(404, 'Chợ / Cơ sở không tồn tại!');

        if (in_array(session('user_role'), ['seller', 'manager']) && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền đăng bản tin số cho Chợ này!');
        }

        $existing = json_decode($eatery->announcements ?? '[]', true) ?: [];
        $newId = count($existing) > 0 ? (max(array_column($existing, 'id') ?: [0]) + 1) : 1;

        $item = [
            'id' => $newId,
            'tag' => $request->tag,
            'title' => $request->title,
            'content' => $request->content,
            'time' => $request->time ?: 'Mới cập nhật',
            'color' => $request->color ?: '#10B981',
            'created_at' => now()->format('H:i d/m/Y')
        ];

        array_unshift($existing, $item);

        \Illuminate\Support\Facades\DB::connection($connName)->table('eateries')->where('id', $id)->update([
            'announcements' => json_encode($existing, JSON_UNESCAPED_UNICODE)
        ]);

        return redirect()->back()->with('success', '🎉 Đã phát bản tin số mới thành công trên Loa Chợ Số & Bảng Tin BQL Chợ!');
    }

    /**
     * Xóa bản tin số của Ban Quản Lý Chợ
     */
    public function destroyAnnouncement($eateryId, $announcementId)
    {
        $this->verifyAdmin();
        $eatery = null;
        $connName = null;
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        foreach ($connections as $conn) {
            $e = \Illuminate\Support\Facades\DB::connection($conn)->table('eateries')->where('id', $eateryId)->first();
            if ($e) {
                $eatery = $e;
                $connName = $conn;
                break;
            }
        }

        if (!$eatery) abort(404, 'Chợ / Cơ sở không tồn tại!');

        if (in_array(session('user_role'), ['seller', 'manager']) && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền xóa bản tin này!');
        }

        $existing = json_decode($eatery->announcements ?? '[]', true) ?: [];
        $filtered = array_values(array_filter($existing, fn($item) => (int)($item['id'] ?? 0) !== (int)$announcementId));

        \Illuminate\Support\Facades\DB::connection($connName)->table('eateries')->where('id', $eateryId)->update([
            'announcements' => json_encode($filtered, JSON_UNESCAPED_UNICODE)
        ]);

        return redirect()->back()->with('success', 'Đã xóa bản tin số thành công!');
    }
}
