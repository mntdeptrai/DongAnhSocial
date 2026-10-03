<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\HasMultiConnectionAccess;
use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use App\Models\Dish;
use App\Models\Review;
use App\Models\Room;
use App\Models\WellnessService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EateryApiController extends Controller
{
    use HasMultiConnectionAccess;

    private function getConnection($categorySlug)
    {
        switch ($categorySlug) {
            case 'stay-in-dong-anh':
                return 'mysql_stay';
            case 'wellness-care':
                return 'mysql_wellness';
            case 'traditional-market':
            case 'dong-anh-market':
                return 'mysql_market';
            case 'smart-education-map':
                return 'mysql_education';
            case 'discover-dong-anh-community-culture-hub':
                return 'mysql_culture';
            default:
                return 'mysql';
        }
    }

    public function getCategories()
    {
        return response()->json(Category::select('id', 'name', 'slug', 'icon', 'description')->get());
    }

    public function getCommunes()
    {
        return response()->json(Commune::select('id', 'name', 'slug')->get());
    }

    public function index($category, Request $request)
    {
        $conn = $this->getConnection($category);
        $query = Eatery::on($conn)->with(['category', 'commune', 'reviewVideos' => function($q) {
            $q->where('status', 'approved');
        }])->active();

        if ($category) {
            $query->whereHas('category', function($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        if ($request->query('is_featured') !== null) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        if ($request->query('commune_id')) {
            $query->where('commune_id', $request->query('commune_id'));
        }

        if ($request->query('q')) {
            $keyword = $request->query('q');
            $query->where(function($q) use ($keyword) {
                $q->where('slug', 'like', "{$keyword}%")
                  ->orWhere('name', 'like', "{$keyword}%")
                  ->orWhere('address', 'like', "{$keyword}%");
            });
        }

        $eateries = $query->get();
        return response()->json($eateries);
    }

    public function show($category, $slug)
    {
        $conn = $this->getConnection($category);
        
        $baseRelations = [
            'category', 
            'commune', 
            'reviews' => function($q) {
                $q->orderBy('created_at', 'desc');
            }
        ];

        $eatery = Eatery::on($conn)->with($baseRelations)->where('slug', $slug)->firstOrFail();

        $optionalRelations = [
            'dishes', 'rooms', 'wellnessServices', 'ocopProducts', 
            'educationPrograms', 'foodSafetyCertificate', 'foodSupplyContracts', 
            'purchaseInvoices', 'dailyFoodLogs'
        ];

        foreach ($optionalRelations as $rel) {
            try {
                $eatery->load($rel);
            } catch (\Exception $e) {
                // Table or relationship might not exist on this specific db connection
            }
        }

        // Lấy tất cả ID của địa điểm này trên mọi connection (tránh lệch ID giữa mysql và mysql_culture)
        $sameEateryIds = [$eatery->id];
        foreach (['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'] as $c) {
            try {
                $ids = Eatery::on($c)->where('slug', $slug)->pluck('id')->toArray();
                $sameEateryIds = array_merge($sameEateryIds, $ids);
            } catch (\Exception $e) {
                // Ignore connection errors
            }
        }
        $sameEateryIds = array_values(array_unique(array_filter($sameEateryIds)));

        // Lấy các ảnh check-in thực tế của thực khách tại quán
        $checkinPhotos = Checkin::with('user')
            ->whereIn('eatery_id', $sameEateryIds)
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->latest()
            ->take(15)
            ->get();

        // Lấy các checkin làm review thực tế
        $checkinReviews = Checkin::with('user')
            ->whereIn('eatery_id', $sameEateryIds)
            ->latest()
            ->get();

        $data = $eatery->toArray();
        $data['checkin_photos'] = $checkinPhotos;
        $data['checkin_reviews'] = $checkinReviews;

        return response()->json($data);
    }

    public function store($category, Request $request)
    {
        $role = session('user_role') ?? (auth()->check() ? auth()->user()->role : 'user');
        $userId = session('user_id') ?? (auth()->check() ? auth()->user()->id : null);

        if ($role !== 'admin' && $role !== 'seller') {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền tạo địa điểm!'], 403);
        }

        if ($role === 'seller') {
            $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
            $hasEatery = false;
            foreach ($connections as $c) {
                if (Eatery::on($c)->where('user_id', $userId)->exists()) {
                    $hasEatery = true;
                    break;
                }
            }
            if ($hasEatery) {
                return response()->json(['success' => false, 'message' => 'Mỗi chủ quán chỉ được đăng ký tối đa 1 địa điểm!'], 403);
            }
        }

        $conn = $this->getConnection($category);
        
        $data = $request->all();
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
        }

        // Đảm bảo user_id của cơ sở trùng với seller đang tạo nếu là seller
        if ($role === 'seller') {
            $data['user_id'] = $userId;
        }

        $eatery = new Eatery();
        $eatery->setConnection($conn);
        $eatery->fill($data);
        $eatery->save();

        return response()->json($eatery, 201);
    }

    public function update($category, $id, Request $request)
    {
        if (!$this->checkAccess($id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa địa điểm này!'], 403);
        }

        $conn = $this->getConnection($category);
        $eatery = Eatery::on($conn)->findOrFail($id);
        
        $data = $request->all();
        if (isset($data['name']) && $data['name'] !== $eatery->name) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
        }

        // Tránh bypass đổi chủ sở hữu trừ khi là admin
        $role = session('user_role') ?? (auth()->check() ? auth()->user()->role : 'user');
        if ($role !== 'admin') {
            unset($data['user_id']);
        }

        $eatery->update($data);
        return response()->json($eatery);
    }

    public function destroy($category, $id)
    {
        $role = session('user_role') ?? (auth()->check() ? auth()->user()->role : 'user');
        if ($role === 'seller') {
            return response()->json(['success' => false, 'message' => 'Chủ quán không được phép tự xóa địa điểm của mình! Vui lòng liên hệ Admin.'], 403);
        }
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa địa điểm này!'], 403);
        }

        $conn = $this->getConnection($category);
        $eatery = Eatery::on($conn)->findOrFail($id);
        $eatery->delete();

        return response()->json(['success' => true]);
    }

    public function storeReview($category, $id, Request $request)
    {
        $conn = $this->getConnection($category);
        $eatery = Eatery::on($conn)->findOrFail($id);

        $review = new Review();
        $review->setConnection($conn);
        $review->fill([
            'eatery_id' => $eatery->id,
            'user_name' => $request->user_name ?? (auth()->check() ? auth()->user()->name : 'Thực khách'),
            'rating' => $request->rating,
            'comment' => $request->comment,
            'user_id' => auth()->id()
        ]);
        $review->save();

        // Xử lý media
        if ($request->has('media_files')) {
            foreach ($request->input('media_files') as $file) {
                $review->media()->create([
                    'file_path' => $file['path'],
                    'file_type' => $file['type']
                ]);
            }
        }

        // Tính toán lại rating trung bình
        $avgRating = Review::on($conn)->where('eatery_id', $eatery->id)->avg('rating');
        if ($avgRating !== null) {
            $eatery->update([
                'rating' => round($avgRating, 2)
            ]);
        }

        return response()->json($review, 201);
    }

    public function destroyReview($id)
    {
        $role = session('user_role') ?? (auth()->check() ? auth()->user()->role : 'user');
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa đánh giá!'], 403);
        }

        list($review, $conn) = $this->findModelAndConnection(Review::class, $id);
        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Review không tồn tại'], 404);
        }

        $eateryId = $review->eatery_id;
        $review->delete();

        // Tính toán lại rating trung bình
        $eatery = Eatery::on($conn)->find($eateryId);
        if ($eatery) {
            $avgRating = Review::on($conn)->where('eatery_id', $eateryId)->avg('rating');
            $eatery->update([
                'rating' => $avgRating ? round($avgRating, 2) : 5.00
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function replyReview($id, Request $request)
    {
        list($review, $conn) = $this->findModelAndConnection(Review::class, $id);
        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Review không tồn tại'], 404);
        }

        if (!$this->checkAccess($review->eatery_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền phản hồi đánh giá của cơ sở này!'], 403);
        }

        $review->update([
            'seller_reply' => $request->seller_reply
        ]);

        return response()->json($review);
    }

    // CRUD Dishes
    public function storeDish(Request $request)
    {
        if (!$this->checkAccess($request->eatery_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền quản lý thực đơn của cơ sở này!'], 403);
        }

        list($eatery, $conn) = $this->findEateryAndConnection($request->eatery_id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Eatery không tồn tại'], 404);
        }

        $dish = new Dish();
        $dish->setConnection($conn);
        $dish->fill($request->all());
        $dish->save();

        return response()->json($dish, 201);
    }

    public function updateDish($id, Request $request)
    {
        if (!$this->checkModelAccess(Dish::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa món ăn này!'], 403);
        }

        list($dish, $conn) = $this->findModelAndConnection(Dish::class, $id);
        if (!$dish) {
            return response()->json(['success' => false, 'message' => 'Món ăn không tồn tại'], 404);
        }

        $dish->update($request->all());
        return response()->json($dish);
    }

    public function toggleSignatureDish($id)
    {
        if (!$this->checkModelAccess(Dish::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa món ăn này!'], 403);
        }

        list($dish, $conn) = $this->findModelAndConnection(Dish::class, $id);
        if (!$dish) {
            return response()->json(['success' => false, 'message' => 'Món ăn không tồn tại'], 404);
        }

        $dish->update([
            'is_signature' => !$dish->is_signature
        ]);

        return response()->json($dish);
    }

    public function destroyDish($id)
    {
        if (!$this->checkModelAccess(Dish::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa món ăn này!'], 403);
        }

        list($dish, $conn) = $this->findModelAndConnection(Dish::class, $id);
        if (!$dish) {
            return response()->json(['success' => false, 'message' => 'Món ăn không tồn tại'], 404);
        }

        $dish->delete();
    }

    // --- CRUD Rooms ---
    public function storeRoom(Request $request)
    {
        if (!$this->checkAccess($request->eatery_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền quản lý phòng nghỉ của cơ sở này!'], 403);
        }

        list($eatery, $conn) = $this->findEateryAndConnection($request->eatery_id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Eatery không tồn tại'], 404);
        }

        $room = new Room();
        $room->setConnection($conn);
        $room->fill($request->all());
        $room->save();

        return response()->json($room, 201);
    }

    public function updateRoom($id, Request $request)
    {
        if (!$this->checkModelAccess(Room::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa phòng nghỉ này!'], 403);
        }

        list($room, $conn) = $this->findModelAndConnection(Room::class, $id);
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Phòng nghỉ không tồn tại'], 404);
        }

        $room->update($request->all());
        return response()->json($room);
    }

    public function destroyRoom($id)
    {
        if (!$this->checkModelAccess(Room::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa phòng nghỉ này!'], 403);
        }

        list($room, $conn) = $this->findModelAndConnection(Room::class, $id);
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Phòng nghỉ không tồn tại'], 404);
        }

        $room->delete();
        return response()->json(['success' => true]);
    }

    // --- CRUD Wellness Services ---
    public function storeWellnessService(Request $request)
    {
        if (!$this->checkAccess($request->eatery_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền quản lý dịch vụ của cơ sở này!'], 403);
        }

        list($eatery, $conn) = $this->findEateryAndConnection($request->eatery_id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Eatery không tồn tại'], 404);
        }

        $service = new WellnessService();
        $service->setConnection($conn);
        $service->fill($request->all());
        $service->save();

        return response()->json($service, 201);
    }

    public function updateWellnessService($id, Request $request)
    {
        if (!$this->checkModelAccess(WellnessService::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa dịch vụ này!'], 403);
        }

        list($service, $conn) = $this->findModelAndConnection(WellnessService::class, $id);
        if (!$service) {
            return response()->json(['success' => false, 'message' => 'Dịch vụ sức khỏe không tồn tại'], 404);
        }

        $service->update($request->all());
        return response()->json($service);
    }

    public function destroyWellnessService($id)
    {
        if (!$this->checkModelAccess(WellnessService::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa dịch vụ này!'], 403);
        }

        list($service, $conn) = $this->findModelAndConnection(WellnessService::class, $id);
        if (!$service) {
            return response()->json(['success' => false, 'message' => 'Dịch vụ sức khỏe không tồn tại'], 404);
        }

        $service->delete();
        return response()->json(['success' => true]);
    }

    /**
     * API Lấy danh sách sản phẩm OCOP & Đặc sản chợ (Dành cho Mobile App)
     */
    public function getMarketProducts(Request $request)
    {
        try {
            $products = collect();

            // =====================================================================
            // 1. Query TRỰC TIẾP từ bảng ocop_products (mysql_market DB)
            //    Đây là nguồn chính — bypass hoàn toàn EateryApiService HTTP mode
            // =====================================================================
            foreach (['mysql_market', 'mysql'] as $conn) {
                try {
                    $items = \App\Models\OcopProduct::on($conn)
                        ->with(['eatery'])
                        ->orderBy('id', 'desc')
                        ->get();

                    foreach ($items as $item) {
                        if (!$products->contains('name', $item->name)) {
                            $eatery = $item->eatery;
                            $pName  = mb_strtolower($item->name ?? '');
                            $sName  = mb_strtolower($item->stall_name ?? '');
                            $seller = mb_strtolower($item->seller_name ?? '');
                            $desc   = mb_strtolower($item->description ?? '');

                            $hasStarRating = !empty($item->star_rating);
                            $isOcop = $hasStarRating ||
                                      !empty($item->heritage_year) ||
                                      str_contains($pName, 'ocop') || str_contains($sName, 'ocop') || str_contains($seller, 'ocop') || str_contains($desc, 'ocop') ||
                                      str_contains($seller, 'htx') || str_contains($seller, 'hợp tác xã') || str_contains($sName, 'htx') || str_contains($sName, 'hợp tác xã') ||
                                      str_contains($desc, 'qđ số') || str_contains($desc, 'quuyết định') || str_contains($desc, 'chủ thể sản xuất');

                            $starRating = $item->star_rating;

                            $products->push([
                                'id'            => $item->id,
                                'eatery_id'     => $item->eatery_id,
                                'eatery_slug'   => $eatery?->slug ?? '',
                                'category_slug' => 'dong-anh-market',
                                'name'          => $item->name,
                                'price'         => $item->price,
                                'stall_name'    => $item->stall_name ?: ($eatery?->name ?? 'Gian hàng Đông Anh'),
                                'seller_name'   => $item->seller_name ?: 'Chủ hộ kinh doanh',
                                'seller_phone'  => $item->seller_phone ?: ($eatery?->phone ?? ''),
                                'star_rating'   => $starRating,
                                'is_ocop'       => $isOcop,
                                'image_path'    => $item->image_path ?: ($eatery?->image_path ?? ''),
                                'description'   => $item->description ?: ('Đặc sản & Nông sản của ' . ($eatery?->name ?? 'Đông Anh')),
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("getMarketProducts: lỗi query OcopProduct trên [{$conn}]: " . $e->getMessage());
                }
            }

            // =====================================================================
            // 2. Fallback: Nếu OcopProduct table trống → gọi eatery detail từng cái
            //    để lấy ocop_products đính kèm (data đã có trên web)
            // =====================================================================
            if ($products->isEmpty()) {
                try {
                    // Lấy danh sách eatery slug từ dong-anh-market
                    $listResponse = \Illuminate\Support\Facades\Http::timeout(10)
                        ->get(config('app.url') . '/api/v1/dong-anh-market/eateries');

                    if ($listResponse->successful()) {
                        $eateryList = collect($listResponse->json());

                        foreach ($eateryList as $e) {
                            $slug = $e['slug'] ?? '';
                            if (empty($slug)) continue;

                            try {
                                $detailResp = \Illuminate\Support\Facades\Http::timeout(8)
                                    ->get(config('app.url') . "/api/v1/dong-anh-market/eateries/{$slug}");

                                if (!$detailResp->successful()) continue;
                                $detail = $detailResp->json();

                                $stallName   = $detail['name'] ?? 'Gian hàng OCOP';
                                $sellerPhone = $detail['phone'] ?? '';
                                $eateryImg   = $detail['image_path'] ?? '';
                                $eaterySlug  = $detail['slug'] ?? $slug;
                                $eateryId    = $detail['id'] ?? null;

                                // ocop_products từ detail
                                $ocopList = $detail['ocop_products'] ?? [];
                                foreach ($ocopList as $p) {
                                    $pName = $p['name'] ?? 'Sản phẩm OCOP';
                                    if (!$products->contains('name', $pName)) {
                                        $products->push([
                                            'id'            => $p['id'] ?? rand(10000, 99999),
                                            'eatery_id'     => $eateryId,
                                            'eatery_slug'   => $eaterySlug,
                                            'category_slug' => 'dong-anh-market',
                                            'name'          => $pName,
                                            'price'         => $p['price'] ?? null,
                                            'stall_name'    => !empty($p['stall_name']) ? $p['stall_name'] : $stallName,
                                            'seller_name'   => $p['seller_name'] ?? 'Chủ hộ kinh doanh',
                                            'seller_phone'  => $p['seller_phone'] ?? $sellerPhone,
                                            'star_rating'   => $p['star_rating'] ?? null,
                                            'is_ocop'       => !empty($p['star_rating']) || !empty($p['heritage_year']),
                                            'image_path'    => $p['image_path'] ?? $eateryImg,
                                            'description'   => $p['description'] ?? ('Sản phẩm OCOP của ' . $stallName),
                                        ]);
                                    }
                                }

                                // dishes từ detail
                                $dishes = $detail['dishes'] ?? [];
                                foreach ($dishes as $d) {
                                    $pName = $d['name'] ?? ($d['dish_name'] ?? 'Đặc sản');
                                    if (!$products->contains('name', $pName)) {
                                        $isOcopDish = str_contains(strtolower($pName), 'ocop') || str_contains(strtolower($stallName), 'ocop') || str_contains(strtolower($stallName), 'htx');
                                        $products->push([
                                            'id'            => $d['id'] ?? rand(10000, 99999),
                                            'eatery_id'     => $eateryId,
                                            'eatery_slug'   => $eaterySlug,
                                            'category_slug' => 'dong-anh-market',
                                            'name'          => $pName,
                                            'price'         => $d['price'] ?? null,
                                            'stall_name'    => $stallName,
                                            'seller_name'   => 'Chủ hộ kinh doanh',
                                            'seller_phone'  => $sellerPhone,
                                            'star_rating'   => $isOcopDish ? '4 sao' : null,
                                            'is_ocop'       => $isOcopDish,
                                            'image_path'    => $d['image_path'] ?? $eateryImg,
                                            'description'   => $d['description'] ?? ('Đặc sản của ' . $stallName),
                                        ]);
                                    }
                                }
                            } catch (\Exception $inner) {}
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("getMarketProducts fallback HTTP: " . $e->getMessage());
                }
            }

            // =====================================================================
            // 3. Last-resort fallback: expose chính các gian hàng HTX/HKD/Cơ sở
            //    như card sản phẩm khi cả hai bước trên đều rỗng
            // =====================================================================
            if ($products->isEmpty()) {
                try {
                    $listResponse = \Illuminate\Support\Facades\Http::timeout(10)
                        ->get(config('app.url') . '/api/v1/dong-anh-market/eateries');

                    if ($listResponse->successful()) {
                        foreach ($listResponse->json() as $e) {
                            $eName = $e['name'] ?? 'Gian hàng Đông Anh';
                            $actualDesc = $e['description'] ?? '';
                            $desc  = !empty($actualDesc) ? $actualDesc : ('Gian hàng & đặc sản của ' . $eName . ' tại Đông Anh, Hà Nội');
                            $isOcopEatery = str_contains(strtolower($eName), 'ocop') ||
                                            str_contains(strtolower($eName), 'htx') ||
                                            str_contains(strtolower($eName), 'hợp tác xã') ||
                                            str_contains(strtolower($actualDesc), 'ocop');

                            $products->push([
                                'id'            => 'e_' . ($e['id'] ?? rand(1, 999)),
                                'eatery_id'     => $e['id'] ?? null,
                                'eatery_slug'   => $e['slug'] ?? '',
                                'category_slug' => 'dong-anh-market',
                                'name'          => $eName,
                                'price'         => null,
                                'stall_name'    => $eName,
                                'seller_name'   => 'Chủ hộ kinh doanh',
                                'seller_phone'  => $e['phone'] ?? '',
                                'star_rating'   => $isOcopEatery ? (!empty($e['rating']) ? round((float)$e['rating'], 1) . ' sao' : '4 sao') : null,
                                'is_ocop'       => $isOcopEatery,
                                'image_path'    => $e['image_path'] ?? '',
                                'description'   => mb_substr($desc, 0, 200),
                                'address'       => $e['address'] ?? '',
                                'is_eatery'     => true,
                            ]);
                        }
                    }
                } catch (\Exception $e) {}
            }

            return response()->json($products->values(), 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * GET /api/v1/exp-corner — Lấy thông tin Góc Trải Nghiệm Thực Tế (Làng nghề & Vui chơi bản địa Đông Anh)
     */
    public function getExpCorner(Request $request)
    {
        $activities = [];
        try {
            $culturalList = \App\Services\EateryApiService::getAllCulturalActivities();
            foreach ($culturalList as $act) {
                $activities[] = [
                    'id'          => $act->id,
                    'name'        => $act->name,
                    'description' => $act->description ?? '',
                    'location'    => $act->eatery ? $act->eatery->name : ($act->location ?? 'Khu Di Tích Cổ Loa'),
                    'price'       => $act->price ? (number_format($act->price, 0, ',', '.') . 'đ/người') : 'Miễn phí / Giá niêm yết',
                    'unit'        => $act->unit ?? '1 người',
                    'tag'         => $act->category ?? 'Trải nghiệm',
                    'image_path'  => $act->image_path ?? ($act->eatery ? $act->eatery->image_path : null),
                ];
            }
        } catch (\Throwable $e) {}

        if (empty($activities)) {
            $activities = [
                [
                    'id'          => 1,
                    'name'        => 'Bắn nỏ, làm bông chủ, oản xôi lá mít dâng vua, đúc các hiện vật tiêu biểu xưởng thủ công Âu Lạc',
                    'description' => 'Bắn nỏ là biểu tượng cho công nghệ quân sự đỉnh cao của Nhà nước Âu Lạc, được minh chứng qua truyền thuyết nỏ thần An Dương Vương...',
                    'location'    => 'Khu Di Tích Cổ Loa',
                    'price'       => '30.000đ/người',
                    'unit'        => '1 người',
                    'tag'         => 'Trải nghiệm',
                    'image_path'  => 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80',
                ],
                [
                    'id'          => 2,
                    'name'        => 'Tham quan di tích lịch sử thành Cổ Loa',
                    'description' => 'Tham quan quần thể di tích lịch sử đặc biệt quốc gia Cổ Loa, tìm hiểu văn hóa Phùng Nguyên, Đồng Đậu, Gò Mun.',
                    'location'    => 'Khu Di Tích Cổ Loa',
                    'price'       => 'Vé tham quan',
                    'unit'        => '1 lượt',
                    'tag'         => 'Vé tham quan',
                    'image_path'  => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&q=80',
                ],
                [
                    'id'          => 3,
                    'name'        => 'Dịch vụ dâng hương & Trải nghiệm làng nghề gốm sứ Cổ Loa',
                    'description' => 'Hành trình dâng hương tưởng niệm vua An Dương Vương và tự tay nặn gốm truyền thống cùng nghệ nhân bản địa.',
                    'location'    => 'Khu Di Tích Cổ Loa',
                    'price'       => 'Trọn gói',
                    'unit'        => 'Đoàn / Cá nhân',
                    'tag'         => 'Dịch vụ di tích',
                    'image_path'  => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=800&q=80',
                ],
                [
                    'id'          => 4,
                    'name'        => 'Tự tay làm bún Mạch Tràng & Trải nghiệm đan lát truyền thống',
                    'description' => 'Học bí quyết làm bún sẫm màu đặc sản tiến vua Mạch Tràng và trải nghiệm làm sản phẩm đan lát mây tre thủ công.',
                    'location'    => 'Làng Nghề Mạch Tràng',
                    'price'       => '50.000đ/người',
                    'unit'        => '1 người',
                    'tag'         => 'Làng nghề',
                    'image_path'  => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80',
                ]
            ];
        }

        return response()->json([
            'title'       => 'Góc Trải Nghiệm Thực Tế Làng Nghề & Vui Chơi Bản Địa Đông Anh',
            'subtitle'    => 'Không chỉ là ăn uống, đây là hành trình nhập vai thực tế! Bạn đồng hành cùng người bản xứ, tự tay học các nghề truyền thống (làm bún, đan lát, gốm sứ), tham gia các trò chơi dân gian và vui chơi giải trí sống động.',
            'stats'       => [
                'villages'    => '12+',
                'visitors'    => '500+',
                'rating'      => '4.9 ⭐',
                'experience'  => '100%',
            ],
            'activities'  => $activities,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

}
