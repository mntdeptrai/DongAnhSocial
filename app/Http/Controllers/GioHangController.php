<?php

namespace App\Http\Controllers;

use App\Contracts\CartServiceInterface;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Dish;
use App\Models\OcopProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GioHangController extends Controller
{
    public function __construct(
        protected CartServiceInterface $cartService
    ) {}

    /**
     * Resolve active cart for user or guest session
     */
    private function getCurrentCart()
    {
        $user = Auth::user() ?: auth('sanctum')->user();
        $sessionId = request()->header('X-Session-ID');

        return $this->cartService->resolveCart($user, $sessionId);
    }

    /**
     * Lấy toàn bộ thông tin giỏ hàng hiện tại
     * GET /gio-hang /api/cart
     */
    public function index(): JsonResponse
    {
        $cart = $this->getCurrentCart();
        return response()->json($this->cartService->formatCartResponse($cart));
    }

    /**
     * Thêm sản phẩm (Món ăn hoặc OCOP) vào giỏ hàng
     * POST /gio-hang /api/cart
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $dishId = $request->input('dish_id');
        $ocopId = $request->input('ocop_product_id');

        if ($dishId) {
            $dish = Dish::on('mysql')->find($dishId);
            if (!$dish) {
                return response()->json(['success' => false, 'message' => 'Món ăn không tồn tại'], 404);
            }
        } elseif ($ocopId) {
            $ocop = OcopProduct::on('mysql_market')->find($ocopId);
            if (!$ocop) {
                return response()->json(['success' => false, 'message' => 'Sản phẩm OCOP không tồn tại'], 404);
            }
        }

        $cart = $this->getCurrentCart();
        $result = $this->cartService->addItem($cart, $dishId, $ocopId, (int)($request->input('quantity', 1)));

        $responseData = $this->cartService->formatCartResponse($result['cart']);
        $responseData['added_new'] = $result['added_new'];

        return response()->json($responseData);
    }

    /**
     * Cập nhật số lượng của một mục trong giỏ
     * PUT /gio-hang/{id} /api/cart/{id}
     */
    public function update(UpdateCartItemRequest $request, int|string $id): JsonResponse
    {
        $cart = $this->getCurrentCart();
        $cart = $this->cartService->updateItemQuantity($cart, $id, (int)$request->input('quantity'));

        return response()->json($this->cartService->formatCartResponse($cart));
    }

    /**
     * Xóa một mục khỏi giỏ hàng
     * DELETE /gio-hang/{id} /api/cart/{id}
     */
    public function destroy(int|string $id): JsonResponse
    {
        $cart = $this->getCurrentCart();
        $cart = $this->cartService->removeItem($cart, $id);

        return response()->json($this->cartService->formatCartResponse($cart));
    }

    /**
     * Làm rỗng toàn bộ giỏ hàng
     * DELETE /gio-hang /api/cart
     */
    public function clear(): JsonResponse
    {
        $cart = $this->getCurrentCart();
        $cart = $this->cartService->clearCart($cart);

        return response()->json($this->cartService->formatCartResponse($cart));
    }
}
