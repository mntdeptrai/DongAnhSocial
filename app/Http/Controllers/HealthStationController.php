<?php

namespace App\Http\Controllers;

use App\Contracts\HealthStationServiceInterface;
use App\Http\Requests\StoreHealthDoctorRequest;
use App\Http\Requests\StoreHealthServiceRequest;
use App\Http\Requests\UpdateHealthStationProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HealthStationController extends Controller
{
    public function __construct(
        protected HealthStationServiceInterface $healthStationService
    ) {}

    /**
     * Lấy ngữ cảnh trạm y tế
     */
    private function getContext(): array
    {
        return $this->healthStationService->getHealthStationContext(Auth::user());
    }

    /**
     * Tổng quan Bảng Điều Hành Trạm Y Tế (Dashboard)
     * GET /health-station/dashboard
     */
    public function dashboard(): View
    {
        $context = $this->getContext();
        $data = $this->healthStationService->getDashboardData($context);

        return view('health-station.dashboard', $data);
    }

    /**
     * Quản lý Hồ sơ & Hotline Cấp cứu Trạm Y tế
     * GET /health-station/profile
     */
    public function showProfile(): View
    {
        $context = $this->getContext();
        $data = $this->healthStationService->getProfileData($context);

        return view('health-station.profile', $data);
    }

    /**
     * Cập nhật Hồ sơ Trạm Y tế
     * POST /health-station/profile
     */
    public function updateProfile(UpdateHealthStationProfileRequest $request): RedirectResponse
    {
        $context = $this->getContext();
        $this->healthStationService->updateProfile($context, $request->validated(), $request);

        return redirect()->route('health-station.profile')->with('success', 'Cập nhật thông tin Trạm Y tế & Tọa độ Bản đồ thành công!');
    }

    /**
     * API giải mã Link Google Maps rút gọn (maps.app.goo.gl) ra Tọa độ Lat, Lng tức thì cho Frontend
     * POST /health-station/api/resolve-map-link
     */
    public function resolveMapLink(Request $request): JsonResponse
    {
        $url = trim($request->input('url', ''));
        [$lat, $lng] = $this->healthStationService->parseGoogleMapsUrl($url);

        if ($lat !== null && $lng !== null) {
            return response()->json([
                'success' => true,
                'lat'     => $lat,
                'lng'     => $lng,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Không tìm thấy tọa độ']);
    }

    /**
     * Danh mục Dịch vụ Y tế & Kỹ thuật
     * GET /health-station/services
     */
    public function services(): View
    {
        $context = $this->getContext();
        $services = $this->healthStationService->getServices($context);

        return view('health-station.services', [
            'eatery'   => $context['eatery'],
            'services' => $services,
            'user'     => $context['user'],
        ]);
    }

    /**
     * Thêm Dịch vụ Y tế mới
     * POST /health-station/services
     */
    public function storeService(StoreHealthServiceRequest $request): RedirectResponse
    {
        $context = $this->getContext();
        $this->healthStationService->storeService($context, $request->validated());

        return redirect()->route('health-station.services')->with('success', 'Thêm dịch vụ y tế mới thành công!');
    }

    /**
     * Cập nhật Dịch vụ Y tế
     * PUT /health-station/services/{id}
     */
    public function updateService(StoreHealthServiceRequest $request, int|string $id): RedirectResponse
    {
        $context = $this->getContext();
        $this->healthStationService->updateService($context, $id, $request->validated());

        return redirect()->route('health-station.services')->with('success', 'Cập nhật dịch vụ y tế thành công!');
    }

    /**
     * Xóa Dịch vụ Y tế
     * DELETE /health-station/services/{id}
     */
    public function destroyService(int|string $id): RedirectResponse
    {
        $context = $this->getContext();
        $this->healthStationService->deleteService($context, $id);

        return redirect()->route('health-station.services')->with('success', 'Đã xóa dịch vụ y tế!');
    }

    /**
     * Quản lý Đội ngũ Bác sĩ & Lịch trực
     * GET /health-station/doctors
     */
    public function doctors(): View
    {
        $context = $this->getContext();
        $doctors = $this->healthStationService->getDoctors($context);

        return view('health-station.doctors', [
            'eatery'  => $context['eatery'],
            'doctors' => $doctors,
            'user'    => $context['user'],
        ]);
    }

    /**
     * Thêm Bác sĩ / Cán bộ Y tế
     * POST /health-station/doctors
     */
    public function storeDoctor(StoreHealthDoctorRequest $request): RedirectResponse
    {
        $context = $this->getContext();
        $this->healthStationService->storeDoctor($context, $request->validated());

        return redirect()->route('health-station.doctors')->with('success', 'Thêm bác sĩ / cán bộ y tế thành công!');
    }

    /**
     * Cập nhật Bác sĩ / Cán bộ Y tế
     * PUT /health-station/doctors/{id}
     */
    public function updateDoctor(StoreHealthDoctorRequest $request, int|string $id): RedirectResponse
    {
        $context = $this->getContext();
        $this->healthStationService->updateDoctor($context, $id, $request->validated());

        return redirect()->route('health-station.doctors')->with('success', 'Cập nhật thông tin cán bộ y tế thành công!');
    }

    /**
     * Xóa Bác sĩ / Cán bộ Y tế
     * DELETE /health-station/doctors/{id}
     */
    public function destroyDoctor(int|string $id): RedirectResponse
    {
        $context = $this->getContext();
        $this->healthStationService->deleteDoctor($context, $id);

        return redirect()->route('health-station.doctors')->with('success', 'Đã xóa bác sĩ khỏi danh sách!');
    }
}
