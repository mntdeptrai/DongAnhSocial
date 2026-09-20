<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\HasMultiConnectionAccess;
use App\Models\FoodSafetyCertificate;
use App\Models\DailyFoodLog;
use App\Models\FoodSupplyContract;
use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;

/**
 * TrustHubApiController — Quản lý hồ sơ An toàn thực phẩm (ATTP), Nhật ký vệ sinh, Hợp đồng & Hóa đơn nguồn gốc
 */
class TrustHubApiController extends Controller
{
    use HasMultiConnectionAccess;

    /**
     * POST /api/v1/trust/certificate — Thêm chứng nhận vệ sinh ATTP
     */
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

    /**
     * POST /api/v1/trust/logs — Thêm nhật ký kiểm thực / vệ sinh hàng ngày
     */
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

    /**
     * DELETE /api/v1/trust/logs/{id} — Xóa nhật ký kiểm thực
     */
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

    /**
     * POST /api/v1/trust/contracts — Thêm hợp đồng cung cấp thực phẩm
     */
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

    /**
     * DELETE /api/v1/trust/contracts/{id} — Xóa hợp đồng cung cấp
     */
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

    /**
     * POST /api/v1/trust/invoices — Thêm hóa đơn mua bán nguồn gốc thực phẩm
     */
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

    /**
     * DELETE /api/v1/trust/invoices/{id} — Xóa hóa đơn mua bán
     */
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
}
