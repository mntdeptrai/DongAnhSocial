<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\HasMultiConnectionAccess;
use App\Models\EducationProgram;
use Illuminate\Http\Request;

/**
 * EducationApiController — Quản lý chương trình giáo dục đào tạo trên Smart Education Map
 */
class EducationApiController extends Controller
{
    use HasMultiConnectionAccess;

    /**
     * POST /api/v1/education-programs — Thêm chương trình đào tạo
     */
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

    /**
     * PUT /api/v1/education-programs/{id} — Cập nhật chương trình đào tạo
     */
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

    /**
     * DELETE /api/v1/education-programs/{id} — Xóa chương trình đào tạo
     */
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
}
