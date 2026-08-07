<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use App\Models\Dish;
use App\Models\Review;
use App\Models\ReviewVideo;
use App\Models\FoodSafetyCertificate;
use App\Models\DailyFoodLog;
use App\Models\FoodSupplyContract;
use App\Models\PurchaseInvoice;
use App\Models\Room;
use App\Models\WellnessService;
use App\Models\OcopProduct;
use App\Models\EducationProgram;
use App\Models\FoodTour;
use App\Models\FoodTourStop;
use App\Models\FoodTourDiary;
use App\Models\User;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EateryApiController extends Controller
{
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

    private function findEateryAndConnection($eateryId)
    {
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        foreach ($connections as $conn) {
            $eatery = Eatery::on($conn)->find($eateryId);
            if ($eatery) {
                return [$eatery, $conn];
            }
        }
        return [null, null];
    }

    private function findModelAndConnection($modelClass, $id)
    {
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        foreach ($connections as $conn) {
            $model = $modelClass::on($conn)->find($id);
            if ($model) {
                return [$model, $conn];
            }
        }
        return [null, null];
    }

    private function checkAccess($eateryId)
    {
        $role = session('user_role') ?? (auth()->check() ? auth()->user()->role : 'user');
        $userId = session('user_id') ?? (auth()->check() ? auth()->user()->id : null);

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'seller') {
            list($eatery, $conn) = $this->findEateryAndConnection($eateryId);
            if ($eatery && (int)$eatery->user_id === (int)$userId) {
                return true;
            }
        }

        return false;
    }

    private function checkModelAccess($modelClass, $id)
    {
        list($model, $conn) = $this->findModelAndConnection($modelClass, $id);
        if (!$model) {
            return false;
        }

        if (isset($model->eatery_id)) {
            return $this->checkAccess($model->eatery_id);
        }

        return false;
    }

    public function getCategories()
    {
        return response()->json(Category::all());
    }

    public function getCommunes()
    {
        return response()->json(Commune::all());
    }

    public function getVideos()
    {
        // Thu thập video từ tất cả các connection
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        $allVideos = collect();

        foreach ($connections as $conn) {
            $vids = ReviewVideo::on($conn)
                ->with(['eatery.category', 'user'])
                ->where('status', 'approved')
                ->orderBy('id', 'desc')
                ->get();
            $allVideos = $allVideos->concat($vids);
        }

        return response()->json($allVideos->sortByDesc('id')->values());
    }

    public function likeVideo($id)
    {
        list($video, $conn) = $this->findModelAndConnection(ReviewVideo::class, $id);
        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video không tồn tại'], 404);
        }

        $video->increment('likes_count');
        return response()->json([
            'success' => true,
            'likes_count' => $video->likes_count
        ]);
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
        return response()->json(['success' => true]);
    }

    // Videos Reels
    public function storeVideo(Request $request)
    {
        if (!$this->checkAccess($request->eatery_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền đăng video cho cơ sở này!'], 403);
        }

        list($eatery, $conn) = $this->findEateryAndConnection($request->eatery_id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Eatery không tồn tại'], 404);
        }

        $video = new ReviewVideo();
        $video->setConnection($conn);
        $video->fill($request->all());
        
        $video->status = 'approved';
        $video->user_id = auth()->id();

        $video->save();

        return response()->json($video, 201);
    }

    public function updateVideo($id, Request $request)
    {
        if (!$this->checkModelAccess(ReviewVideo::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa video này!'], 403);
        }

        list($video, $conn) = $this->findModelAndConnection(ReviewVideo::class, $id);
        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video không tồn tại'], 404);
        }

        $video->update($request->all());
        return response()->json($video);
    }

    public function destroyVideo($id)
    {
        if (!$this->checkModelAccess(ReviewVideo::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa video này!'], 403);
        }

        list($video, $conn) = $this->findModelAndConnection(ReviewVideo::class, $id);
        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video không tồn tại'], 404);
        }

        $video->delete();
        return response()->json(['success' => true]);
    }

    public function approveVideo($id)
    {
        $role = session('user_role') ?? (auth()->check() ? auth()->user()->role : 'user');
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Chỉ Admin mới được phê duyệt video!'], 403);
        }

        list($video, $conn) = $this->findModelAndConnection(ReviewVideo::class, $id);
        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video không tồn tại'], 404);
        }

        $video->update(['status' => 'approved']);
        return response()->json($video);
    }

    public function rejectVideo($id)
    {
        $role = session('user_role') ?? (auth()->check() ? auth()->user()->role : 'user');
        if ($role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Chỉ Admin mới được từ chối video!'], 403);
        }

        list($video, $conn) = $this->findModelAndConnection(ReviewVideo::class, $id);
        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video không tồn tại'], 404);
        }

        $video->update(['status' => 'rejected']);
        return response()->json($video);
    }

    // Trust Hub Management
    public function storeFoodSafetyCertificate(Request $request)
    {
        if (!$this->checkAccess($request->eatery_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền cập nhật hồ sơ ATTP của cơ sở này!'], 403);
        }

        list($eatery, $conn) = $this->findEateryAndConnection($request->eatery_id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Eatery không tồn tại'], 404);
        }

        $cert = new FoodSafetyCertificate();
        $cert->setConnection($conn);
        $cert->fill($request->all());
        $cert->save();

        return response()->json($cert, 201);
    }

    public function storeDailyFoodLog(Request $request)
    {
        if (!$this->checkAccess($request->eatery_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền ghi nhật ký vệ sinh cho cơ sở này!'], 403);
        }

        list($eatery, $conn) = $this->findEateryAndConnection($request->eatery_id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Eatery không tồn tại'], 404);
        }

        $log = new DailyFoodLog();
        $log->setConnection($conn);
        $log->fill($request->all());
        $log->save();

        return response()->json($log, 201);
    }

    public function destroyDailyFoodLog($id)
    {
        if (!$this->checkModelAccess(DailyFoodLog::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa nhật ký của cơ sở này!'], 403);
        }

        list($log, $conn) = $this->findModelAndConnection(DailyFoodLog::class, $id);
        if (!$log) {
            return response()->json(['success' => false, 'message' => 'Log không tồn tại'], 404);
        }

        $log->delete();
        return response()->json(['success' => true]);
    }

    public function storeFoodSupplyContract(Request $request)
    {
        if (!$this->checkAccess($request->eatery_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thêm hợp đồng của cơ sở này!'], 403);
        }

        list($eatery, $conn) = $this->findEateryAndConnection($request->eatery_id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Eatery không tồn tại'], 404);
        }

        $contract = new FoodSupplyContract();
        $contract->setConnection($conn);
        $contract->fill($request->all());
        $contract->save();

        return response()->json($contract, 201);
    }

    public function destroyFoodSupplyContract($id)
    {
        if (!$this->checkModelAccess(FoodSupplyContract::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa hợp đồng của cơ sở này!'], 403);
        }

        list($contract, $conn) = $this->findModelAndConnection(FoodSupplyContract::class, $id);
        if (!$contract) {
            return response()->json(['success' => false, 'message' => 'Hợp đồng không tồn tại'], 404);
        }

        $contract->delete();
        return response()->json(['success' => true]);
    }

    public function storePurchaseInvoice(Request $request)
    {
        if (!$this->checkAccess($request->eatery_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thêm hóa đơn mua bán cho cơ sở này!'], 403);
        }

        list($eatery, $conn) = $this->findEateryAndConnection($request->eatery_id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Eatery không tồn tại'], 404);
        }

        $invoice = new PurchaseInvoice();
        $invoice->setConnection($conn);
        $invoice->fill($request->all());
        $invoice->save();

        return response()->json($invoice, 201);
    }

    public function destroyPurchaseInvoice($id)
    {
        if (!$this->checkModelAccess(PurchaseInvoice::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa hóa đơn của cơ sở này!'], 403);
        }

        list($invoice, $conn) = $this->findModelAndConnection(PurchaseInvoice::class, $id);
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Hóa đơn không tồn tại'], 404);
        }

        $invoice->delete();
        return response()->json(['success' => true]);
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

    // --- CRUD OCOP Products ---
    public function storeOcopProduct(Request $request)
    {
        if (!$this->checkAccess($request->eatery_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền bán sản phẩm của cơ sở này!'], 403);
        }

        list($eatery, $conn) = $this->findEateryAndConnection($request->eatery_id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Eatery không tồn tại'], 404);
        }

        $product = new OcopProduct();
        $product->setConnection($conn);
        $product->fill($request->all());
        $product->save();

        return response()->json($product, 201);
    }

    public function updateOcopProduct($id, Request $request)
    {
        if (!$this->checkModelAccess(OcopProduct::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa sản phẩm này!'], 403);
        }

        list($product, $conn) = $this->findModelAndConnection(OcopProduct::class, $id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm OCOP không tồn tại'], 404);
        }

        $product->update($request->all());
        return response()->json($product);
    }

    public function destroyOcopProduct($id)
    {
        if (!$this->checkModelAccess(OcopProduct::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa sản phẩm này!'], 403);
        }

        list($product, $conn) = $this->findModelAndConnection(OcopProduct::class, $id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm OCOP không tồn tại'], 404);
        }

        $product->delete();
        return response()->json(['success' => true]);
    }

    // --- CRUD Education Programs ---
    public function storeEducationProgram(Request $request)
    {
        if (!$this->checkAccess($request->eatery_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thêm chương trình của cơ sở này!'], 403);
        }

        list($eatery, $conn) = $this->findEateryAndConnection($request->eatery_id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Eatery không tồn tại'], 404);
        }

        $program = new EducationProgram();
        $program->setConnection($conn);
        $program->fill($request->all());
        $program->save();

        return response()->json($program, 201);
    }

    public function updateEducationProgram($id, Request $request)
    {
        if (!$this->checkModelAccess(EducationProgram::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa chương trình này!'], 403);
        }

        list($program, $conn) = $this->findModelAndConnection(EducationProgram::class, $id);
        if (!$program) {
            return response()->json(['success' => false, 'message' => 'Chương trình đào tạo không tồn tại'], 404);
        }

        $program->update($request->all());
        return response()->json($program);
    }

    public function destroyEducationProgram($id)
    {
        if (!$this->checkModelAccess(EducationProgram::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa chương trình này!'], 403);
        }

        list($program, $conn) = $this->findModelAndConnection(EducationProgram::class, $id);
        if (!$program) {
            return response()->json(['success' => false, 'message' => 'Chương trình đào tạo không tồn tại'], 404);
        }

        $program->delete();
        return response()->json(['success' => true]);
    }

    // --- Food Tours & Journeys ---
    public function getFoodTours(Request $request)
    {
        $mood = $request->query('mood');
        $query = FoodTour::public()->with(['stops.eatery', 'diaries.user'])->withCount('diaries');
        if ($mood) {
            $query->where('mood', $mood);
        }
        $tours = $query->get();

        $communityTours = FoodTour::community()
            ->with(['stops.eatery', 'diaries.user'])
            ->withCount('diaries')
            ->orderBy('shared_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'tours' => $tours,
            'community_tours' => $communityTours
        ]);
    }

    public function getFoodTour($slug)
    {
        $tour = FoodTour::where('slug', $slug)
            ->with(['stops' => function($q) {
                $q->orderBy('stop_order');
            }, 'stops.eatery.category', 'stops.eatery.commune'])
            ->firstOrFail();

        $diaries = FoodTourDiary::where('food_tour_id', $tour->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'tour' => $tour,
            'diaries' => $diaries
        ]);
    }

    public function generateAITour(Request $request)
    {
        $budgetLimit = (int) $request->input('budget', 300000);
        $mood = $request->input('mood', 'chill');
        
        $moodText = $mood;
        $extraConstraint = "ĐẶC BIỆT ƯU TIÊN các sản phẩm/cơ sở OCOP Tinh hoa bản địa";
        if ($mood === 'specialty') {
            $moodText = 'Khám phá đặc sản, Tinh hoa bản địa';
            $extraConstraint = "BẮT BUỘC ÍT NHẤT 2 TRONG 3 ĐỊA ĐIỂM PHẢI CÓ CATEGORY LÀ 'Tinh hoa bản địa' HOẶC LÀ CƠ SỞ OCOP";
        }
        
        $allEateries = \App\Services\EateryApiService::getEateries();
        
        $eateries = $allEateries->map(function($e) {
            return [
                'id' => $e->id,
                'name' => $e->name,
                'category' => $e->category->name ?? 'Khác',
                'price_range' => $e->price_range,
                'description' => $e->description
            ];
        });

        $prompt = "Tôi đang ở Đông Anh, Hà Nội. Tôi có ngân sách khoảng {$budgetLimit} VND. Tâm trạng của tôi là '{$moodText}'. 
Hãy đóng vai một chuyên gia bản địa. Chọn chính xác 3 địa điểm phù hợp nhất từ danh sách sau để tạo thành 1 lộ trình Food Tour / Trải nghiệm liên hoàn mang đậm bản sắc văn hóa. YÊU CẦU QUAN TRỌNG: {$extraConstraint}.
Danh sách địa điểm:
" . json_encode($eateries, JSON_UNESCAPED_UNICODE) . "
YÊU CẦU TRẢ VỀ CHỈ LÀ CHUỖI JSON ĐÚNG ĐỊNH DẠNG SAU, KHÔNG CHỨA BẤT KỲ TEXT NÀO KHÁC BÊN NGOÀI (KHÔNG CÓ DẤU ```json):
{
    \"tour_name\": \"Tên tour sáng tạo (VD: Hành trình Khám phá Chợ & Ẩm thực Đông Anh)\",
    \"description\": \"Mô tả ngắn gọn 2 câu về tour.\",
    \"story\": \"Câu chuyện 3 câu dẫn dắt vì sao lại chọn 3 địa điểm này.\",
    \"difficulty\": \"✨ Lộ trình AI\",
    \"stops\": [
        {
            \"eatery_id\": id_1,
            \"recommendation\": \"Gợi ý cụ thể nên ăn món gì hoặc làm hoạt động gì tại đây (VD: Thử bát phở tái nạm hoặc Đi dạo mua sắm đặc sản nông sản sạch).\"
        },
        {
            \"eatery_id\": id_2,
            \"recommendation\": \"...\"
        },
        {
            \"eatery_id\": id_3,
            \"recommendation\": \"...\"
        }
    ]
}";

        $apiKey = config('services.gemini.key');
        
        if (!$apiKey) {
            return response()->json(['success' => false, 'message' => 'Thiếu API Key Gemini.'], 500);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);
            
            $result = $response->json();
            $textResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
            
            if ($textResponse === null) {
                throw new \Exception('Phản hồi rỗng từ Gemini API');
            }
            
            if (preg_match('/\{.*\}/s', $textResponse, $matches)) {
                $jsonString = $matches[0];
            } else {
                $jsonString = $textResponse;
            }
            
            $aiData = json_decode(trim($jsonString), true);
            
            if (isset($aiData['eatery_ids']) && !isset($aiData['stops'])) {
                $aiData['stops'] = array_map(function($id) {
                    return ['eatery_id' => $id, 'recommendation' => 'Trải nghiệm ẩm thực địa phương hấp dẫn tại đây.'];
                }, $aiData['eatery_ids']);
            }

            if (!$aiData || !isset($aiData['stops']) || count($aiData['stops']) < 1) {
                throw new \Exception('Không giải mã được dữ liệu JSON từ AI');
            }
            
            $slug = Str::slug($aiData['tour_name']) . '-' . substr(md5(uniqid()), 0, 5);
            
            $tour = FoodTour::create([
                'user_id' => auth()->check() ? auth()->id() : (session('user_id') ?: null),
                'name' => $aiData['tour_name'],
                'slug' => $slug,
                'description' => $aiData['description'],
                'duration' => '2.5 giờ',
                'distance' => '5.0 km',
                'budget' => number_format($budgetLimit, 0, ',', '.') . 'đ',
                'difficulty' => $aiData['difficulty'],
                'best_time' => '17:00 - 21:00',
                'popularity' => 'Mới tạo',
                'mood' => $mood,
                'thumbnail' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80',
                'story' => $aiData['story'],
                'status' => 'draft',
                'is_ai_generated' => true,
            ]);
            
            $allEateries = \App\Models\Eatery::all();
            $validEateryIds = $allEateries->pluck('id')->toArray();
            $usedEateryIds = [];

            foreach ($aiData['stops'] as $index => $stop) {
                $rawId = $stop['eatery_id'] ?? null;
                $selectedId = null;

                if ($rawId && is_numeric($rawId) && in_array((int)$rawId, $validEateryIds) && !in_array((int)$rawId, $usedEateryIds)) {
                    $selectedId = (int)$rawId;
                }

                if (!$selectedId && is_string($rawId)) {
                    $found = $allEateries->first(function($e) use ($rawId) {
                        return \Illuminate\Support\Str::contains(mb_strtolower($e->name), mb_strtolower($rawId));
                    });
                    if ($found && !in_array($found->id, $usedEateryIds)) {
                        $selectedId = $found->id;
                    }
                }

                if (!$selectedId) {
                    $unused = array_diff($validEateryIds, $usedEateryIds);
                    if (!empty($unused)) {
                        $selectedId = reset($unused);
                    } else {
                        $selectedId = $validEateryIds[0] ?? 1;
                    }
                }

                $usedEateryIds[] = $selectedId;

                FoodTourStop::create([
                    'food_tour_id'   => $tour->id,
                    'eatery_id'      => $selectedId,
                    'stop_order'     => $index + 1,
                    'stop_story'     => $stop['recommendation'] ?? ("Điểm đến thứ " . ($index + 1) . " trong hành trình " . $aiData['tour_name'] . "."),
                    'estimated_time' => '45 phút'
                ]);
            }
            
            return response()->json(['success' => true, 'slug' => $slug]);
            
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi kết nối AI: ' . $e->getMessage()], 500);
        }
    }

    public function storeFoodTourDiary($id, Request $request)
    {
        $userId = $request->input('user_id') ?: (auth()->check() ? auth()->id() : null);
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập hoặc cung cấp user_id để lưu trữ nhật ký!'
            ], 401);
        }

        $imagePath = null;
        if ($request->input('image')) {
            $base64 = $request->input('image');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                $base64 = substr($base64, strpos($base64, ',') + 1);
                $type = strtolower($type[1]);

                if (in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    $base64 = base64_decode($base64);

                    if ($base64 !== false) {
                        $fileName = 'selfie_' . time() . '_' . uniqid() . '.' . $type;
                        $dir = public_path('uploads/diaries');
                        if (!file_exists($dir)) {
                            mkdir($dir, 0755, true);
                        }
                        file_put_contents($dir . '/' . $fileName, $base64);
                        $imagePath = '/uploads/diaries/' . $fileName;
                    }
                }
            }
        }

        $stopReviews = $request->input('stop_reviews', []);
        foreach ($stopReviews as $index => &$review) {
            if (!empty($review['image']) && preg_match('/^data:image\/(\w+);base64,/', $review['image'], $type)) {
                $imgBase64 = substr($review['image'], strpos($review['image'], ',') + 1);
                $type = strtolower($type[1]);

                if (in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    $imgBase64 = base64_decode($imgBase64);
                    if ($imgBase64 !== false) {
                        $fileName = 'stop_' . $index . '_' . time() . '_' . uniqid() . '.' . $type;
                        $dir = public_path('uploads/diaries');
                        if (!file_exists($dir)) {
                            mkdir($dir, 0755, true);
                        }
                        file_put_contents($dir . '/' . $fileName, $imgBase64);
                        $review['image_path'] = '/uploads/diaries/' . $fileName;
                        unset($review['image']);
                    }
                }
            }

            if (!empty($review['eatery_id'])) {
                $user = User::find($userId);
                $userName = $user ? $user->name : 'Thực khách Food Tour';
                
                $eatery = \App\Services\EateryApiService::getEateries()->firstWhere('id', $review['eatery_id']);
                if ($eatery) {
                    $mediaFiles = [];
                    if (!empty($review['image_path'])) {
                        $mediaFiles[] = [
                            'path' => $review['image_path'],
                            'type' => 'image'
                        ];
                    }
                    
                    \App\Services\EateryApiService::storeReview($eatery->category->slug, $eatery->id, [
                        'user_name' => $userName,
                        'rating' => $review['rating'] ?? null,
                        'comment' => $review['comment'] ?? '',
                        'media_files' => $mediaFiles
                    ]);
                }
            }
        }

        $diary = FoodTourDiary::create([
            'food_tour_id' => $id,
            'user_id' => $userId,
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
            'image_path' => $imagePath,
            'completed_stops' => $request->input('completed_stops', []),
            'stop_reviews' => $stopReviews,
        ]);

        $tour = FoodTour::find($id);
        if ($tour && $tour->is_ai_generated && $tour->status === 'draft') {
            $updateData = [
                'status' => 'saved',
                'user_id' => $tour->user_id ?: $userId
            ];
            if ($request->boolean('share_to_community')) {
                $updateData['shared_at'] = now();
                $updateData['expires_at'] = now()->addHours(72);
            }
            $tour->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lưu nhật ký hành trình thành công!',
            'image_url' => $imagePath,
            'diary' => $diary
        ]);
    }


    // --- CRUD Cultural Activities ---
    public function storeCulturalActivity(Request $request)
    {
        if (!$this->checkAccess($request->eatery_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền quản lý hoạt động của cơ sở này!'], 403);
        }

        list($eatery, $conn) = $this->findEateryAndConnection($request->eatery_id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Eatery không tồn tại'], 404);
        }

        $activity = new \App\Models\CulturalActivity();
        $activity->setConnection($conn);
        $activity->fill($request->all());
        $activity->save();

        return response()->json($activity, 201);
    }

    public function updateCulturalActivity($id, Request $request)
    {
        if (!$this->checkModelAccess(\App\Models\CulturalActivity::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa hoạt động này!'], 403);
        }

        list($activity, $conn) = $this->findModelAndConnection(\App\Models\CulturalActivity::class, $id);
        if (!$activity) {
            return response()->json(['success' => false, 'message' => 'Hoạt động không tồn tại'], 404);
        }

        $activity->update($request->all());
        return response()->json($activity);
    }

    public function destroyCulturalActivity($id)
    {
        if (!$this->checkModelAccess(\App\Models\CulturalActivity::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa hoạt động này!'], 403);
        }

        list($activity, $conn) = $this->findModelAndConnection(\App\Models\CulturalActivity::class, $id);
        if (!$activity) {
            return response()->json(['success' => false, 'message' => 'Hoạt động không tồn tại'], 404);
        }

        $activity->delete();
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
                            $products->push([
                                'id'            => $item->id,
                                'eatery_id'     => $item->eatery_id,
                                'eatery_slug'   => $eatery?->slug ?? '',
                                'category_slug' => 'dong-anh-market',
                                'name'          => $item->name,
                                'price'         => $item->price,
                                'stall_name'    => $item->stall_name ?: ($eatery?->name ?? 'Gian hàng OCOP Đông Anh'),
                                'seller_name'   => $item->seller_name ?: 'Chủ hộ kinh doanh',
                                'seller_phone'  => $item->seller_phone ?: ($eatery?->phone ?? ''),
                                'star_rating'   => $item->star_rating ?: '4 sao',
                                'image_path'    => $item->image_path ?: ($eatery?->image_path ?? ''),
                                'description'   => $item->description ?: ('Sản phẩm OCOP & Đặc sản của ' . ($eatery?->name ?? 'Đông Anh')),
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
                                            'star_rating'   => $p['star_rating'] ?? '4 sao',
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
                                            'star_rating'   => '4 sao',
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
                            $eName = $e['name'] ?? 'Cơ sở OCOP Đông Anh';
                            $desc  = $e['description'] ?? ('Gian hàng & sản phẩm OCOP của ' . $eName . ' tại Đông Anh, Hà Nội');
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
                                'star_rating'   => !empty($e['rating']) ? round((float)$e['rating'], 1) . ' sao' : '4 sao',
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
     * API Lấy danh sách thông báo CÁ NHÂN HÓA thời gian thực cho User (Mobile App)
     */
    public function getAppNotifications(Request $request)
    {
        $notifications = [];
        $user = Auth::guard('sanctum')->user() ?? Auth::user();

        try {
            if ($user) {
                $notifications = \App\Services\NotificationService::getNotificationsForUser($user->id);
            }

            if (empty($notifications)) {
                if ($user) {
                    $notifications[] = [
                        'id'        => 'user_welcome',
                        'title'     => '👋 Chào mừng ' . $user->name . '!',
                        'body'      => 'Tất cả thông báo đơn hàng cá nhân, tương tác cảm xúc, bình luận bài viết và kết bạn mới sẽ hiển thị tại đây.',
                        'time'      => 'Hôm nay',
                        'type'      => 'system',
                        'icon'      => 'notifications_active',
                        'is_read'   => false,
                    ];
                } else {
                    $notifications[] = [
                        'id'        => 'guest_welcome',
                        'title'     => '🔔 Thông báo Đông Anh Social',
                        'body'      => 'Vui lòng đăng nhập để nhận thông báo đơn hàng cá nhân, tương tác bài viết và lời mời kết bạn.',
                        'time'      => 'Hôm nay',
                        'type'      => 'system',
                        'icon'      => 'notifications_active',
                        'is_read'   => false,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('getAppNotifications Exception: ' . $e->getMessage());
        }

        return response()->json($notifications, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * GET /api/v1/newsfeed — Lấy tất cả bài viết Bản tin đa phân quyền (Post, Education, Checkin)
     */
    public function getNewsfeed(Request $request)
    {
        $postsList = [];

        // 1. Lấy từ bảng Post (gồm tin đăng của Trường học, User, Seller, Admin, Manager)
        try {
            $userPostsMysqlEdu = collect();
            $userPostsMysql = collect();

            try {
                $userPostsMysqlEdu = \App\Models\Post::on('mysql_education')
                    ->with(['user', 'eatery'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            } catch (\Throwable $e) {}

            try {
                $userPostsMysql = \App\Models\Post::on('mysql')
                    ->with(['user', 'eatery'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            } catch (\Throwable $e) {}

            $userPosts = $userPostsMysqlEdu->concat($userPostsMysql)->unique('id');

            foreach ($userPosts as $post) {
                try {
                    $authorName = $post->user ? $post->user->name : ($post->eatery ? $post->eatery->name : 'Thành viên Đông Anh');
                    $authorAvatar = $post->user ? ($post->user->avatar ?? null) : ($post->eatery ? ($post->eatery->image_path ?? null) : null);
                    $authorRole = $post->user ? ($post->user->role ?? 'user') : 'user';

                    $img = $post->image_path;
                    $imgs = $img ? [$img] : [];

                    $postsList[] = [
                        'id'               => $post->id,
                        'hashid'           => $post->hashid ?? ('post_' . $post->id),
                        'type'             => 'post',
                        'author_name'      => $authorName,
                        'author_avatar'    => $authorAvatar,
                        'author_role'      => $authorRole,
                        'title'            => $post->name ?? $post->title ?? '',
                        'description'      => $post->description ?? '',
                        'image_path'       => $img,
                        'images'           => $imgs,
                        'likes_count'      => (int) ($post->likes_count ?? 0),
                        'comments_count'   => (int) ($post->comments_count ?? 0),
                        'created_at_human' => $post->created_at ? $post->created_at->diffForHumans() : 'Vừa xong',
                        'comments'         => [],
                    ];
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {}

        // 2. Lấy từ bảng EducationProgram (Hiệu trưởng / Trường học)
        try {
            $excludedTitles = [
                'Hệ đào tạo THPT chính quy chuẩn quốc gia',
                'Lớp chọn ngoại ngữ (Tiếng Anh - Tiếng Trung tăng cường)',
                'Hệ THCS Chất lượng cao trọng điểm',
                'Câu lạc bộ Kỹ năng sống & STEM',
            ];

            $eduPostsMysqlEdu = collect();
            $eduPostsMysql = collect();

            try {
                $eduPostsMysqlEdu = \App\Models\EducationProgram::on('mysql_education')
                    ->with(['eatery'])
                    ->whereNotIn('name', $excludedTitles)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } catch (\Throwable $e) {}

            try {
                $eduPostsMysql = \App\Models\EducationProgram::on('mysql')
                    ->with(['eatery'])
                    ->whereNotIn('name', $excludedTitles)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } catch (\Throwable $e) {}

            $eduPosts = $eduPostsMysqlEdu->concat($eduPostsMysql)->unique('id');

            foreach ($eduPosts as $edu) {
                try {
                    $authorName = $edu->eatery ? $edu->eatery->name : 'Ban Giám Hiệu Trường';
                    $img = $edu->image_path ?? ($edu->eatery ? $edu->eatery->image_path : null);

                    $postsList[] = [
                        'id'               => 'edu_' . $edu->id,
                        'hashid'           => 'edu_' . $edu->id,
                        'type'             => 'education',
                        'author_name'      => $authorName,
                        'author_avatar'    => $edu->eatery ? $edu->eatery->image_path : null,
                        'author_role'      => 'principal',
                        'title'            => $edu->name ?? '',
                        'description'      => $edu->description ?? $edu->target_students ?? '',
                        'image_path'       => $img,
                        'images'           => $img ? [$img] : [],
                        'likes_count'      => 12,
                        'comments_count'   => 0,
                        'created_at_human' => $edu->created_at ? $edu->created_at->diffForHumans() : '2 ngày trước',
                        'comments'         => [],
                    ];
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {}

        // 3. Lấy bài Checkin công khai
        try {
            $checkins = \App\Models\Checkin::with(['user', 'eatery'])
                ->where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            foreach ($checkins as $chk) {
                try {
                    $authorName = $chk->user ? $chk->user->name : 'Thành viên Đông Anh';
                    $postsList[] = [
                        'id'               => 'chk_' . $chk->id,
                        'hashid'           => 'chk_' . $chk->id,
                        'type'             => 'checkin',
                        'author_name'      => $authorName,
                        'author_avatar'    => $chk->user ? $chk->user->avatar : null,
                        'author_role'      => $chk->user ? ($chk->user->role ?? 'user') : 'user',
                        'title'            => $chk->eatery ? ('Check-in tại ' . $chk->eatery->name) : 'Khoảnh khắc ẩm thực',
                        'description'      => $chk->comment ?? '',
                        'image_path'       => $chk->image_path,
                        'images'           => $chk->image_path ? [$chk->image_path] : [],
                        'likes_count'      => (int) ($chk->likes_count ?? 0),
                        'comments_count'   => 0,
                        'created_at_human' => $chk->created_at ? $chk->created_at->diffForHumans() : 'Vừa xong',
                        'comments'         => [],
                    ];
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {}

        return response()->json($postsList, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * POST /api/v1/posts — Đăng bài viết mới lên Bản tin (Dành cho tất cả các Role)
     */
    public function storePost(Request $request)
    {
        $user = auth('sanctum')->user() ?: Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để đăng bài lên Bản tin'], 401);
        }

        $request->validate([
            'description' => 'required|string',
            'name'        => 'nullable|string',
            'image_path'  => 'nullable|string',
        ]);

        $post = \App\Models\Post::create([
            'user_id'     => $user->id,
            'name'        => $request->input('name') ?: mb_substr($request->description, 0, 50) . '...',
            'description' => $request->description,
            'image_path'  => $request->image_path,
            'status'      => 'published',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã đăng bài viết thành công lên Bản tin!',
            'post'    => $post
        ], 201, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
