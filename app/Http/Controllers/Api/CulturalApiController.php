<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\HasMultiConnectionAccess;
use App\Models\CulturalActivity;
use Illuminate\Http\Request;

/**
 * CulturalApiController — Quản lý hoạt động trải nghiệm văn hóa & di tích lịch sử Đông Anh
 */
class CulturalApiController extends Controller
{
    use HasMultiConnectionAccess;

    /**
     * POST /api/v1/cultural-activities — Thêm hoạt động văn hóa / trải nghiệm làng nghề mới
     */
    public function storeCulturalActivity(Request $request)
    {
        if (!$this->checkAccess($request->eatery_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền quản lý hoạt động của cơ sở này!'], 403);
        }

        list($eatery, $conn) = $this->findEateryAndConnection($request->eatery_id);
        if (!$eatery) {
            return response()->json(['success' => false, 'message' => 'Eatery không tồn tại'], 404);
        }

        $activity = new CulturalActivity();
        $activity->setConnection($conn);
        $activity->fill($request->all());
        $activity->save();

        return response()->json($activity, 201);
    }

    /**
     * PUT /api/v1/cultural-activities/{id} — Cập nhật hoạt động trải nghiệm văn hóa
     */
    public function updateCulturalActivity($id, Request $request)
    {
        if (!$this->checkModelAccess(CulturalActivity::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền chỉnh sửa hoạt động này!'], 403);
        }

        list($activity, $conn) = $this->findModelAndConnection(CulturalActivity::class, $id);
        if (!$activity) {
            return response()->json(['success' => false, 'message' => 'Hoạt động không tồn tại'], 404);
        }

        $activity->update($request->all());
        return response()->json($activity);
    }

    /**
     * DELETE /api/v1/cultural-activities/{id} — Xóa hoạt động trải nghiệm văn hóa
     */
    public function destroyCulturalActivity($id)
    {
        if (!$this->checkModelAccess(CulturalActivity::class, $id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa hoạt động này!'], 403);
        }

        list($activity, $conn) = $this->findModelAndConnection(CulturalActivity::class, $id);
        if (!$activity) {
            return response()->json(['success' => false, 'message' => 'Hoạt động không tồn tại'], 404);
        }

        $activity->delete();
        return response()->json(['success' => true]);
    }
}
