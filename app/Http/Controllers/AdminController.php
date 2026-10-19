<?php

namespace App\Http\Controllers;

use App\Domain\Checkin\CheckinData;
use App\Domain\CulturalActivity\CulturalActivityData;
use App\Domain\Dish\DishData;
use App\Domain\Eatery\EateryData;
use App\Domain\EducationProgram\EducationProgramData;
use App\Domain\OcopProduct\OcopProductData;
use App\Domain\ReviewVideo\ReviewVideoData;
use App\Domain\Room\RoomData;
use App\Domain\TrustHub\TrustHubDocumentData;
use App\Domain\WellnessMap\WellnessMapData;
use App\Models\Category;
use App\Models\Commune;
use App\Models\CulturalActivity;
use App\Models\Eatery;
use App\Models\User;
use App\Services\CulturalActivityService;
use App\Services\DishService;
use App\Services\EateryApiService;
use App\Services\EateryService;
use App\Services\EducationProgramService;
use App\Services\Interfaces\AdminServiceInterface;
use App\Services\OcopProductService;
use App\Services\ReviewVideoService;
use App\Services\RoomService;
use App\Services\TrustHubService;
use App\Services\WellnessMapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct(
        protected AdminServiceInterface $adminService
    ) {}

    private function verifyAdmin(): void
    {
        if (!Auth::check() && session()->has('user_id')) {
            $u = User::find(session('user_id'));
            if ($u) {
                Auth::login($u);
            }
        }
        $user = Auth::user();
        $userId = $user ? $user->id : session('user_id');
        $role = session('user_role') ?: ($user ? $user->role : null);

        $this->adminService->verifyAdminAccess($userId, $role);

        if ($user && !session()->has('user_role')) {
            session([
                'user_id'   => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
            ]);
        }
    }

    /**
     * Hiển thị Dashboard Admin / Ban Quản lý Chợ (Manager)
     */
    public function dashboard(Request $request)
    {
        $this->verifyAdmin();

        $userId = Auth::id() ?? session('user_id');
        $role = session('user_role') ?: (Auth::user() ? Auth::user()->role : null);
        $page = (int) $request->input('page', 1);

        $data = $this->adminService->getDashboardData(
            $userId,
            $role,
            $request->input('q'),
            $request->input('category'),
            $request->input('commune'),
            $page,
            10
        );

        if (!empty($data['isManager'])) {
            return view('admin.dashboard-manager', [
                'stats'       => $data['stats'],
                'eateries'    => $data['eateries'],
                'videos'      => $data['videos'],
                'marketStats' => $data['marketStats'],
            ]);
        }

        return view('admin.dashboard-admin', [
            'stats'    => $data['stats'],
            'eateries' => $data['eateries'],
            'videos'   => $data['videos'],
        ]);
    }

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
        $eatery = null;

        return view('admin.eatery-form', compact('categories', 'communes', 'eatery'));
    }

    public function storeEatery(Request $request, EateryService $eateryService)
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
            'name'            => 'required|string|max:100',
            'category_id'     => 'required',
            'commune_id'      => 'required',
            'address'         => 'required|string|max:200',
            'phone'           => 'nullable|string|max:20',
            'opening_hours'   => 'nullable|string|max:50',
            'latitude'        => 'required|numeric|between:-90,90',
            'longitude'       => 'required|numeric|between:-180,180',
            'price_range'     => 'nullable|string|max:50',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url'       => 'nullable|url',
            'description'     => 'nullable|string',
            'is_featured'     => 'nullable|boolean',
            'heritage_year'   => 'nullable|string|max:100',
            'story'           => 'nullable|string',
            'artisans'        => 'nullable|string',
            'fun_fact'        => 'nullable|string',
            'audio_narrative' => 'nullable|string',
            'ocop_stars'      => 'nullable|integer|between:3,5',
            'ingredients_raw' => 'nullable|string',
            'timeline_raw'    => 'nullable|string',
        ]);

        $categories = EateryApiService::getCategories();
        $category = $categories->firstWhere('id', $request->category_id);
        if (!$category) {
            return redirect()->back()->withErrors(['category_id' => 'Danh mục không hợp lệ!']);
        }

        $dto = EateryData::fromRequest($request);
        $eateryService->create($dto, $category->slug);

        return redirect('/admin/dashboard')->with('success', 'Thêm mới địa điểm ẩm thực thành công!');
    }

    public function editEatery($slug)
    {
        $this->verifyAdmin();

        $eatery = EateryApiService::getEateryBySlug($slug);
        if (!$eatery) {
            abort(404, 'Địa điểm không tồn tại!');
        }

        if (in_array(session('user_role'), ['seller', 'manager']) && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền sửa đổi cơ sở này! Bạn chỉ có quyền điều hành Ban Quản lý Chợ được phân công.');
        }

        $categories = EateryApiService::getCategories();
        $communes = EateryApiService::getCommunes();

        return view('admin.eatery-form', compact('eatery', 'categories', 'communes'));
    }

    public function updateEatery(Request $request, $slug, EateryService $eateryService)
    {
        $this->verifyAdmin();

        $eatery = EateryApiService::getEateryBySlug($slug);
        if (!$eatery) {
            abort(404, 'Địa điểm không tồn tại!');
        }

        if (in_array(session('user_role'), ['seller', 'manager']) && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền sửa đổi cơ sở này! Bạn chỉ có quyền điều hành Ban Quản lý Chợ được phân công.');
        }

        $request->validate([
            'name'            => 'required|string|max:100',
            'category_id'     => 'required',
            'commune_id'      => 'required',
            'address'         => 'required|string|max:200',
            'phone'           => 'nullable|string|max:20',
            'opening_hours'   => 'nullable|string|max:50',
            'latitude'        => 'required|numeric|between:-90,90',
            'longitude'       => 'required|numeric|between:-180,180',
            'price_range'     => 'nullable|string|max:50',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url'       => 'nullable|url',
            'description'     => 'nullable|string',
            'is_featured'     => 'nullable|boolean',
            'heritage_year'   => 'nullable|string|max:100',
            'story'           => 'nullable|string',
            'artisans'        => 'nullable|string',
            'fun_fact'        => 'nullable|string',
            'audio_narrative' => 'nullable|string',
            'ocop_stars'      => 'nullable|integer|between:3,5',
            'ingredients_raw' => 'nullable|string',
            'timeline_raw'    => 'nullable|string',
        ]);

        $dto = EateryData::fromRequest($request);
        $eateryService->update($eatery->id, $dto, $eatery->category->slug, $eatery->image_path);

        return redirect()->back()->with('success', 'Cập nhật thông tin quán thành công!');
    }

    public function destroyEatery($slug, EateryService $eateryService)
    {
        $this->verifyAdmin();

        $eatery = EateryApiService::getEateryBySlug($slug);
        if (!$eatery) {
            abort(404, 'Địa điểm không tồn tại!');
        }

        if (session('user_role') === 'seller') {
            abort(403, 'Chủ quán không được phép tự xóa địa điểm kinh doanh của mình! Vui lòng liên hệ Quản trị viên.');
        }

        $eateryService->delete($eatery->category->slug, $eatery->id);

        return redirect('/admin/dashboard')->with('success', 'Xóa địa điểm thành công!');
    }

    public function parseGoogleMapsUrl(Request $request)
    {
        $this->verifyAdmin();
        $url = $request->input('url', '');
        list($lat, $lng) = $this->adminService->parseGoogleMapsUrl($url);

        if ($lat !== null && $lng !== null) {
            return response()->json(['success' => true, 'latitude' => $lat, 'longitude' => $lng]);
        }

        return response()->json(['success' => false, 'message' => 'Không thể nhận diện tọa độ GPS từ đường dẫn này.'], 422);
    }

    public function storeDish(Request $request, DishService $dishService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id'   => 'required|exists:eateries,id',
            'name'        => 'required|string|max:100',
            'price'       => 'nullable|numeric|min:0',
            'unit'        => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url'   => 'nullable|url',
        ]);

        $dto = DishData::fromRequest($request);
        $dishService->create($dto);

        return redirect()->back()->with('success', 'Thêm món ăn thành công!');
    }

    public function updateDish(Request $request, $id, DishService $dishService)
    {
        $this->verifyAdmin();
        $request->validate([
            'name'        => 'required|string|max:100',
            'price'       => 'nullable|numeric|min:0',
            'unit'        => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url'   => 'nullable|url',
        ]);

        $dto = DishData::fromRequest($request);
        $dishService->update((int)$id, $dto);

        return redirect()->back()->with('success', 'Cập nhật món ăn thành công!');
    }

    public function toggleSignatureDish($id, DishService $dishService)
    {
        $this->verifyAdmin();
        $dish = $dishService->toggleSignature((int)$id);

        $msg = $dish->is_signature ? 'Đã ghim món này thành Món Đặc Sắc!' : 'Đã bỏ ghim Món Đặc Sắc!';
        return redirect()->back()->with('success', $msg);
    }

    public function destroyDish($id, DishService $dishService)
    {
        $this->verifyAdmin();
        $dishService->delete((int)$id);

        return redirect()->back()->with('success', 'Xóa món ăn thành công!');
    }

    public function storeEateryPhoto(Request $request)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id' => 'required|exists:eateries,id',
            'photo'     => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'caption'   => 'nullable|string|max:200',
        ]);

        $this->adminService->storeEateryPhoto(
            (int) $request->eatery_id,
            $request->caption,
            $request->file('photo')
        );

        return redirect()->back()->with('success', 'Thêm ảnh mới thành công!');
    }

    public function destroyEateryPhoto($id)
    {
        $this->verifyAdmin();
        $this->adminService->destroyEateryPhoto((int) $id);

        return redirect()->back()->with('success', 'Xóa ảnh thành công!');
    }

    public function storeOcopProduct(Request $request, OcopProductService $ocopProductService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id'       => 'required|exists:eateries,id',
            'name'            => 'required|string|max:200',
            'star_rating'     => 'required|string',
            'price'           => 'nullable|string|max:100',
            'unit'            => 'nullable|string|max:50',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url'       => 'nullable|url',
            'description'     => 'nullable|string',
            'heritage_year'   => 'nullable|string|max:100',
            'story'           => 'nullable|string',
            'artisans'        => 'nullable|string',
            'fun_fact'        => 'nullable|string',
            'audio_narrative' => 'nullable|string',
            'ingredients_raw' => 'nullable|string',
            'timeline_raw'    => 'nullable|string',
        ]);

        $dto = OcopProductData::fromRequest($request);
        $ocopProductService->create($dto);

        return redirect()->back()->with('success', 'Thêm sản phẩm OCOP thành công!');
    }

    public function updateOcopProduct(Request $request, $id, OcopProductService $ocopProductService)
    {
        $this->verifyAdmin();
        $request->validate([
            'name'            => 'required|string|max:200',
            'star_rating'     => 'required|string',
            'price'           => 'nullable|string|max:100',
            'unit'            => 'nullable|string|max:50',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url'       => 'nullable|url',
            'description'     => 'nullable|string',
            'heritage_year'   => 'nullable|string|max:100',
            'story'           => 'nullable|string',
            'artisans'        => 'nullable|string',
            'fun_fact'        => 'nullable|string',
            'audio_narrative' => 'nullable|string',
            'ingredients_raw' => 'nullable|string',
            'timeline_raw'    => 'nullable|string',
        ]);

        $dto = OcopProductData::fromRequest($request);
        $ocopProductService->update((int)$id, $dto);

        return redirect()->back()->with('success', 'Cập nhật sản phẩm OCOP thành công!');
    }

    public function destroyOcopProduct($id, OcopProductService $ocopProductService)
    {
        $this->verifyAdmin();
        $ocopProductService->delete((int)$id);

        return redirect()->back()->with('success', 'Xóa sản phẩm OCOP thành công!');
    }

    public function storeRoom(Request $request, RoomService $roomService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id'     => 'required|exists:eateries,id',
            'name'          => 'required|string|max:100',
            'type'          => 'nullable|string|max:50',
            'price_per_day' => 'required|numeric|min:0',
            'capacity'      => 'nullable|integer|min:1',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url'     => 'nullable|url',
            'amenities'     => 'nullable|string',
            'description'   => 'nullable|string',
        ]);

        $dto = RoomData::fromRequest($request);
        $roomService->create($dto);

        return redirect()->back()->with('success', 'Thêm phòng nghỉ thành công!');
    }

    public function updateRoom(Request $request, $id, RoomService $roomService)
    {
        $this->verifyAdmin();
        $request->validate([
            'name'          => 'required|string|max:100',
            'type'          => 'nullable|string|max:50',
            'price_per_day' => 'required|numeric|min:0',
            'capacity'      => 'nullable|integer|min:1',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url'     => 'nullable|url',
            'amenities'     => 'nullable|string',
            'description'   => 'nullable|string',
        ]);

        $dto = RoomData::fromRequest($request);
        $roomService->update((int)$id, $dto);

        return redirect()->back()->with('success', 'Cập nhật phòng nghỉ thành công!');
    }

    public function destroyRoom($id, RoomService $roomService)
    {
        $this->verifyAdmin();
        $roomService->delete((int)$id);

        return redirect()->back()->with('success', 'Xóa phòng nghỉ thành công!');
    }

    public function storeWellnessService(Request $request, WellnessMapService $wellnessService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id'   => 'required|exists:eateries,id',
            'name'        => 'required|string|max:100',
            'price'       => 'nullable|numeric|min:0',
            'duration'    => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url'   => 'nullable|url',
        ]);

        $dto = WellnessMapData::fromRequest($request);
        $wellnessService->create($dto);

        return redirect()->back()->with('success', 'Thêm dịch vụ chăm sóc sức khỏe thành công!');
    }

    public function updateWellnessService(Request $request, $id, WellnessMapService $wellnessService)
    {
        $this->verifyAdmin();
        $request->validate([
            'name'        => 'required|string|max:100',
            'price'       => 'nullable|numeric|min:0',
            'duration'    => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url'   => 'nullable|url',
        ]);

        $dto = WellnessMapData::fromRequest($request);
        $wellnessService->update((int)$id, $dto);

        return redirect()->back()->with('success', 'Cập nhật dịch vụ chăm sóc sức khỏe thành công!');
    }

    public function destroyWellnessService($id, WellnessMapService $wellnessService)
    {
        $this->verifyAdmin();
        $wellnessService->delete((int)$id);

        return redirect()->back()->with('success', 'Xóa dịch vụ chăm sóc sức khỏe thành công!');
    }

    public function storeEducationProgram(Request $request, EducationProgramService $educationService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id'   => 'required|exists:eateries,id',
            'name'        => 'required|string|max:100',
            'level'       => 'nullable|string|max:50',
            'tuition'     => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url'   => 'nullable|url',
        ]);

        $dto = EducationProgramData::fromRequest($request);
        $educationService->create($dto);

        return redirect()->back()->with('success', 'Thêm chương trình đào tạo thành công!');
    }

    public function updateEducationProgram(Request $request, $id, EducationProgramService $educationService)
    {
        $this->verifyAdmin();
        $request->validate([
            'name'        => 'required|string|max:100',
            'level'       => 'nullable|string|max:50',
            'tuition'     => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url'   => 'nullable|url',
        ]);

        $dto = EducationProgramData::fromRequest($request);
        $educationService->update((int)$id, $dto);

        return redirect()->back()->with('success', 'Cập nhật chương trình đào tạo thành công!');
    }

    public function destroyEducationProgram($id, EducationProgramService $educationService)
    {
        $this->verifyAdmin();
        $educationService->delete((int)$id);

        return redirect()->back()->with('success', 'Xóa chương trình đào tạo thành công!');
    }

    public function storeVideo(Request $request, ReviewVideoService $videoService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id'       => 'required|exists:eateries,id',
            'title'           => 'required|string|max:200',
            'video_url'       => 'required|url',
            'video_type'      => 'required|string|in:youtube,tiktok,facebook,direct',
            'thumbnail_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $dto = ReviewVideoData::fromRequest($request);
        $videoService->create($dto);

        return redirect()->back()->with('success', 'Thêm video review thành công!');
    }

    public function updateVideo(Request $request, $id, ReviewVideoService $videoService)
    {
        $this->verifyAdmin();
        $request->validate([
            'title'           => 'required|string|max:200',
            'video_url'       => 'required|url',
            'video_type'      => 'required|string|in:youtube,tiktok,facebook,direct',
            'thumbnail_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $dto = ReviewVideoData::fromRequest($request);
        $videoService->update((int)$id, $dto);

        return redirect()->back()->with('success', 'Cập nhật video review thành công!');
    }

    public function approveVideo($id, ReviewVideoService $videoService)
    {
        $this->verifyAdmin();
        $videoService->approve((int)$id);

        return redirect()->back()->with('success', 'Đã duyệt video review thành công!');
    }

    public function rejectVideo($id, ReviewVideoService $videoService)
    {
        $this->verifyAdmin();
        $videoService->reject((int)$id);

        return redirect()->back()->with('success', 'Đã từ chối video review!');
    }

    public function destroyVideo($id, ReviewVideoService $videoService)
    {
        $this->verifyAdmin();
        $videoService->delete((int)$id);

        return redirect()->back()->with('success', 'Xóa video review thành công!');
    }

    public function storeFoodSafetyCertificate(Request $request, TrustHubService $trustHubService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id'      => 'required|exists:eateries,id',
            'certificate_no' => 'required|string|max:100',
            'issued_by'      => 'required|string|max:200',
            'issued_date'    => 'required|date',
            'expiry_date'    => 'nullable|date',
            'image'          => 'required|image|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        $dto = TrustHubDocumentData::fromRequest($request);
        $trustHubService->storeFoodSafetyCertificate($dto);

        return redirect()->back()->with('success', 'Thêm chứng nhận ATTP thành công!');
    }

    public function storeDailyFoodLog(Request $request, TrustHubService $trustHubService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id' => 'required|exists:eateries,id',
            'log_date'  => 'required|date',
            'menu_item' => 'required|string|max:200',
            'source'    => 'required|string|max:200',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        $dto = TrustHubDocumentData::fromRequest($request);
        $trustHubService->storeDailyFoodLog($dto);

        return redirect()->back()->with('success', 'Thêm nhật ký thực phẩm hằng ngày thành công!');
    }

    public function storeFoodSupplyContract(Request $request, TrustHubService $trustHubService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id'     => 'required|exists:eateries,id',
            'supplier_name' => 'required|string|max:200',
            'contract_no'   => 'nullable|string|max:100',
            'image'         => 'required|image|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        $dto = TrustHubDocumentData::fromRequest($request);
        $trustHubService->storeFoodSupplyContract($dto);

        return redirect()->back()->with('success', 'Thêm hợp đồng cung cấp thực phẩm thành công!');
    }

    public function storePurchaseInvoice(Request $request, TrustHubService $trustHubService)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id'    => 'required|exists:eateries,id',
            'invoice_no'   => 'nullable|string|max:100',
            'invoice_date' => 'required|date',
            'image'        => 'required|image|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        $dto = TrustHubDocumentData::fromRequest($request);
        $trustHubService->storePurchaseInvoice($dto);

        return redirect()->back()->with('success', 'Thêm hóa đơn mua hàng thành công!');
    }

    public function destroyFoodSupplyContract($id, TrustHubService $trustHubService)
    {
        $this->verifyAdmin();
        $trustHubService->destroyFoodSupplyContract((int)$id);

        return redirect()->back()->with('success', 'Xóa hợp đồng cung cấp thực phẩm thành công!');
    }

    public function destroyPurchaseInvoice($id, TrustHubService $trustHubService)
    {
        $this->verifyAdmin();
        $trustHubService->destroyPurchaseInvoice((int)$id);

        return redirect()->back()->with('success', 'Xóa hóa đơn mua hàng thành công!');
    }

    public function destroyDailyFoodLog($id, TrustHubService $trustHubService)
    {
        $this->verifyAdmin();
        $trustHubService->destroyDailyFoodLog((int)$id);

        return redirect()->back()->with('success', 'Xóa nhật ký thực phẩm thành công!');
    }

    public function destroyReview($id)
    {
        $this->verifyAdmin();
        $this->adminService->destroyReview((int) $id);

        return redirect()->back()->with('success', 'Xóa đánh giá thành công!');
    }

    public function replyReview(Request $request, $id)
    {
        $this->verifyAdmin();
        $request->validate([
            'reply' => 'required|string|max:1000',
        ]);

        $this->adminService->replyReview((int) $id, $request->input('reply'));

        return redirect()->back()->with('success', 'Phản hồi đánh giá thành công!');
    }

    public function indexUsers(Request $request)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền quản lý tài khoản người dùng!');
        }

        $data = $this->adminService->getUsersList(
            $request->only(['search', 'user_type', 'market_id', 'commune_id', 'status']),
            session('user_id'),
            $role,
            10
        );

        if ($request->ajax()) {
            return view('admin.users.partial-table', $data)->render();
        }

        return view('admin.users.index', $data);
    }

    public function exportUsers(Request $request)
    {
        set_time_limit(180);
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền xuất dữ liệu!');
        }

        return $this->adminService->exportUsers(
            $request->only(['search', 'user_type', 'market_id', 'commune_id', 'status']),
            session('user_id'),
            $role
        );
    }

    public function createUser()
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền thêm người dùng mới!');
        }

        $data = $this->adminService->getCreateUserData(session('user_id'), $role);
        return view('admin.users.create', $data);
    }

    public function storeUser(Request $request)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền thêm người dùng mới!');
        }

        $allowedRoles = $role === 'admin' ? 'admin,manager,seller,user,principal' : 'seller';

        $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => ['required', 'string', 'max:50', 'unique:users', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'email'     => 'nullable|string|email|max:255|unique:users',
            'password'  => 'required|string|min:6',
            'role'      => 'required|string|in:' . $allowedRoles,
            'phone'     => ['nullable', 'string', 'regex:/^0[0-9]{9}$/'],
            'avatar'    => 'nullable|string|max:10',
            'eatery_id' => 'nullable|integer',
            'stall_id'  => 'nullable|integer',
        ], [
            'username.required' => 'Vui lòng nhập tên đăng nhập (username)!',
            'username.unique'   => 'Tên đăng nhập này đã tồn tại trên hệ thống!',
            'username.regex'    => 'Tên đăng nhập chỉ gồm các ký tự không dấu!',
            'phone.regex'       => 'Số điện thoại Việt Nam phải có đúng 10 chữ số và bắt đầu bằng số 0!',
        ]);

        $this->adminService->storeUser(
            $request->only(['name', 'username', 'email', 'password', 'role', 'phone', 'avatar', 'eatery_id', 'stall_id']),
            session('user_id'),
            $role
        );

        return redirect('/admin/users')->with('success', 'Thêm mới tài khoản người dùng thành công!');
    }

    public function showUser($id)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền xem thông tin chi tiết người dùng!');
        }

        $data = $this->adminService->getUserDetails((int) $id, $role);
        return view('admin.users.show', $data);
    }

    public function editUser($id)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền chỉnh sửa tài khoản người dùng!');
        }

        $data = $this->adminService->getUserForEdit((int) $id, session('user_id'), $role);
        return view('admin.users.edit', $data);
    }

    public function updateUser(Request $request, $id)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền cập nhật tài khoản người dùng!');
        }

        $allowedRoles = $role === 'admin' ? 'admin,manager,seller,user,principal' : 'seller';

        $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => ['required', 'string', 'max:50', 'unique:users,username,' . $id, 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'email'     => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'role'      => 'required|string|in:' . $allowedRoles,
            'phone'     => ['nullable', 'string', 'regex:/^0[0-9]{9}$/'],
            'avatar'    => 'nullable|string|max:10',
            'status'    => 'required|string|in:active,disabled',
            'password'  => 'nullable|string|min:6',
            'eatery_id' => 'nullable|integer',
            'stall_id'  => 'nullable|integer',
        ]);

        $this->adminService->updateUser(
            (int) $id,
            $request->all(),
            session('user_id'),
            $role
        );

        return redirect('/admin/users')->with('success', 'Cập nhật tài khoản người dùng thành công!');
    }

    public function destroyUser($id)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền xóa tài khoản người dùng!');
        }

        try {
            $name = $this->adminService->destroyUser((int) $id, session('user_id'), $role);
            return redirect('/admin/users')->with('success', "Đã xóa tài khoản tiểu thương '{$name}' thành công!");
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function toggleUserStatus($id)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền thay đổi trạng thái tài khoản!');
        }

        try {
            $msg = $this->adminService->toggleUserStatus((int) $id, session('user_id'), $role);
            return redirect()->back()->with('success', $msg);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function storeCulturalActivity(Request $request, CulturalActivityService $service)
    {
        $this->verifyAdmin();
        $request->validate([
            'eatery_id'              => 'required|integer',
            'activity_name'          => 'required|string|max:255',
            'activity_type'          => 'required|string|in:experience,ticket,service,other',
            'activity_price'         => 'nullable|numeric|min:0',
            'activity_unit'          => 'required|string|max:100',
            'activity_discount_note' => 'nullable|string|max:255',
            'activity_description'   => 'nullable|string',
            'activity_image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'activity_image_url'     => 'nullable|url',
        ]);

        $eateries = EateryApiService::getEateries();
        $eatery = $eateries->firstWhere('id', $request->eatery_id);
        if (!$eatery) abort(404, 'Cơ sở không tồn tại!');

        if (session('user_role') === 'seller' && $eatery->user_id !== session('user_id')) {
            abort(403, 'Bạn không có quyền quản trị cơ sở này!');
        }

        $dto = new CulturalActivityData(
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

    public function updateCulturalActivity(Request $request, $id, CulturalActivityService $service)
    {
        $this->verifyAdmin();
        $request->validate([
            'activity_name'          => 'required|string|max:255',
            'activity_type'          => 'required|string|in:experience,ticket,service,other',
            'activity_price'         => 'nullable|numeric|min:0',
            'activity_unit'          => 'required|string|max:100',
            'activity_discount_note' => 'nullable|string|max:255',
            'activity_description'   => 'nullable|string',
            'activity_image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'activity_image_url'     => 'nullable|url',
        ]);

        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        $activity = null;
        foreach ($connections as $conn) {
            $act = CulturalActivity::on($conn)->find($id);
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

        $dto = new CulturalActivityData(
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

        $service->update((int)$id, $dto);

        return redirect()->back()->with('success', 'Cập nhật hoạt động văn hóa thành công!');
    }

    public function destroyCulturalActivity($id, CulturalActivityService $service)
    {
        $this->verifyAdmin();
        if (session('user_role') === 'seller') {
            $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
            $activity = null;
            foreach ($connections as $conn) {
                $act = CulturalActivity::on($conn)->find($id);
                if ($act) { $activity = $act; break; }
            }
            if (!$activity) abort(404, 'Hoạt động không tồn tại!');

            $eateries = EateryApiService::getEateries();
            $eatery = $eateries->firstWhere('id', $activity->eatery_id);
            if (!$eatery || $eatery->user_id !== session('user_id')) {
                abort(403, 'Bạn không có quyền xóa hoạt động của cơ sở này!');
            }
        }

        $service->delete((int)$id);

        return redirect()->back()->with('success', 'Xóa hoạt động văn hóa thành công!');
    }

    public function storeAnnouncement(Request $request, $id)
    {
        $this->verifyAdmin();
        $request->validate([
            'tag'     => 'required|string|max:100',
            'title'   => 'required|string|max:200',
            'content' => 'required|string|max:500',
            'time'    => 'nullable|string|max:50',
            'color'   => 'nullable|string|max:30',
        ]);

        $this->adminService->storeAnnouncement(
            (int) $id,
            $request->only(['tag', 'title', 'content', 'time', 'color']),
            session('user_id'),
            session('user_role')
        );

        return redirect()->back()->with('success', '🎉 Đã phát bản tin số mới thành công trên Loa Chợ Số & Bảng Tin BQL Chợ!');
    }

    public function destroyAnnouncement($eateryId, $announcementId)
    {
        $this->verifyAdmin();
        $this->adminService->destroyAnnouncement(
            (int) $eateryId,
            (int) $announcementId,
            session('user_id'),
            session('user_role')
        );

        return redirect()->back()->with('success', 'Đã xóa bản tin số thành công!');
    }

    public function indexStalls(Request $request)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền truy cập trang quản lý gian hàng!');
        }

        $page = (int) $request->input('page', 1);
        $data = $this->adminService->getStallsData(
            $request->only(['search', 'category', 'market_id']),
            session('user_id'),
            $role,
            $page,
            10
        );

        if ($request->ajax()) {
            return view('admin.stalls.partial-table', $data)->render();
        }

        return view('admin.stalls.index', $data);
    }

    public function createStall()
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền thêm gian hàng mới!');
        }

        $data = $this->adminService->getCreateStallData(session('user_id'), $role);
        return view('admin.stalls.create', $data);
    }

    public function storeStall(Request $request)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền thêm gian hàng mới!');
        }

        $request->validate([
            'eatery_id'   => 'required',
            'unit'        => 'nullable|string|max:50',
            'star_rating' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'image_url'   => 'nullable|url',
        ]);

        $this->adminService->storeStall(
            $request->all(),
            session('user_id'),
            $role,
            $request->file('qr_code'),
            $request->input('qr_code_url'),
            $request->file('images') ?? [],
            $request->file('image'),
            $request->input('image_url'),
            $request->input('image_urls')
        );

        return redirect('/admin/stalls')->with('success', '🎉 Thêm mới Sản phẩm trưng bày thành công!');
    }

    public function editStall($id)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền chỉnh sửa gian hàng!');
        }

        $data = $this->adminService->getStallForEdit((int)$id, session('user_id'), $role);
        return view('admin.stalls.edit', $data);
    }

    public function updateStall(Request $request, $id)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền cập nhật gian hàng!');
        }

        $request->validate([
            'eatery_id'    => 'required',
            'stall_name'   => 'nullable|string|max:200',
            'seller_name'  => 'nullable|string|max:100',
            'seller_phone' => 'nullable|string|max:20',
            'bank_name'    => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'bank_holder'  => 'nullable|string|max:100',
            'qr_code'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'qr_code_url'  => 'nullable|url',
            'name'         => 'required|string|max:200',
            'price'        => 'nullable|string|max:100',
            'unit'         => 'nullable|string|max:50',
            'star_rating'  => 'nullable|string|max:50',
            'description'  => 'nullable|string',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'images.*'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'image_url'    => 'nullable|url',
        ]);

        $this->adminService->updateStall(
            (int) $id,
            $request->all(),
            session('user_id'),
            $role,
            $request->file('qr_code'),
            $request->input('qr_code_url'),
            $request->file('images') ?? [],
            $request->file('image'),
            $request->input('image_url'),
            $request->input('image_urls')
        );

        return redirect('/admin/stalls')->with('success', '✓ Cập nhật thông tin Gian hàng số thành công!');
    }

    public function destroyStall($id)
    {
        $this->verifyAdmin();
        $role = session('user_role');
        if (!in_array($role, ['admin', 'manager'])) {
            abort(403, 'Bạn không có quyền xóa gian hàng!');
        }

        $this->adminService->destroyStall((int) $id, session('user_id'), $role);

        return redirect('/admin/stalls')->with('success', 'Đã xóa Gian hàng số thành công khỏi hệ thống!');
    }

    public function moderationIndex(Request $request)
    {
        $this->verifyAdmin();
        $status = $request->query('status', 'all');
        $reports = \App\Services\ModerationService::getReports($status);
        $stats = \App\Services\ModerationService::getReportStats();

        return view('admin.moderation.index', compact('reports', 'stats', 'status'));
    }

    public function resolveReport(Request $request, $id)
    {
        $this->verifyAdmin();
        $action = $request->input('action', 'dismiss');
        $duration = $request->input('ban_duration', '24h');
        $note = $request->input('resolution_note', null);
        $adminId = session('user_id') ?: Auth::id();

        $msg = $this->adminService->resolveReport((int) $id, $action, $adminId, $duration, $note);

        return redirect()->back()->with('success', $msg);
    }
}
