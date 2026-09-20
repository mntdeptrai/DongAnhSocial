<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\HasMultiConnectionAccess;
use App\Models\OcopProduct;
use Illuminate\Http\Request;

/**
 * OcopProductApiController — Quản lý sản phẩm OCOP Tinh hoa bản địa Đông Anh
 */
class OcopProductApiController extends Controller
{
    use HasMultiConnectionAccess;

    /**
     * POST /api/v1/ocop-products — Thêm sản phẩm OCOP mới cho cơ sở
     */
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

    /**
     * PUT /api/v1/ocop-products/{id} — Cập nhật sản phẩm OCOP
     */
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

    /**
     * DELETE /api/v1/ocop-products/{id} — Xóa sản phẩm OCOP
     */
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
}
