<?php

namespace App\Http\Controllers;

use App\Contracts\NongSanCatalogInterface;
use App\Contracts\OrderInquiryServiceInterface;
use App\Http\Requests\SubmitNongSanOrderRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NongSanDongAnhController extends Controller
{
    /**
     * Dependency Inversion Principle (DIP):
     * Inject abstractions rather than depending on concrete implementations or hardcoded state.
     */
    public function __construct(
        protected NongSanCatalogInterface $catalogService,
        protected OrderInquiryServiceInterface $inquiryService
    ) {}

    /**
     * Display the official catalog of Dong Anh agricultural produce and local specialties.
     * Single Responsibility: Handles request filtering and returns the catalog view.
     */
    public function index(Request $request): View
    {
        $currentCat = $request->query('cat', 'all');
        $products   = $this->catalogService->getProducts($currentCat);

        return view('nong-san.index', [
            'products'         => $products,
            'spotlightProduct' => $this->catalogService->getSpotlightProduct(),
            'currentCat'       => $currentCat,
            'totalCount'       => $this->catalogService->getTotalCount(),
            'combos'           => $this->catalogService->getCombos(),
            'seasons'          => $this->catalogService->getSeasonalCalendar(),
        ]);
    }

    /**
     * Handle quick order / callback inquiry from visitor.
     * Single Responsibility: Delegates persistence to OrderInquiryService and returns JSON response.
     */
    public function submitOrder(SubmitNongSanOrderRequest $request): JsonResponse
    {
        $order = $this->inquiryService->createInquiry($request->validated());

        return response()->json([
            'success'  => true,
            'order_id' => $order->id,
            'message'  => 'Cảm ơn quý khách! Yêu cầu đặt mua "' . $request->input('product_name') . '" (Đơn hàng #' . $order->id . ') đã được lưu vào hệ thống. Cơ sở sản xuất sẽ liên hệ lại qua SĐT ' . $request->input('phone') . ' trong thời gian sớm nhất.'
        ]);
    }
}
