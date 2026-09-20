<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\HasMultiConnectionAccess;
use App\Models\ReviewVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * VideoApiController — Quản lý video ngắn Reels, lượt thích & kiểm duyệt video
 */
class VideoApiController extends Controller
{
    use HasMultiConnectionAccess;

    /**
     * GET /api/v1/videos — Lấy danh sách video Reels đã được phê duyệt từ tất cả các cơ sở
     */
    public function getVideos()
    {
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

    /**
     * POST /api/v1/videos/{id}/like — Thích video Reels
     */
    public function likeVideo($id)
    {
        list($video, $conn) = $this->findModelAndConnection(ReviewVideo::class, $id);
        if (!$video) {
            return response()->json(['success' => false, 'message' => 'Video không tồn tại'], 404);
        }

        $video->increment('likes_count');
        return response()->json([
            'success'     => true,
            'likes_count' => $video->likes_count
        ]);
    }

    /**
     * POST /api/v1/videos — Đăng tải video ngắn Reels mới
     */
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
        $video->user_id = Auth::id();

        $video->save();

        return response()->json($video, 201);
    }

    /**
     * PUT /api/v1/videos/{id} — Cập nhật thông tin video
     */
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

    /**
     * DELETE /api/v1/videos/{id} — Xóa video
     */
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

    /**
     * POST /api/v1/videos/{id}/approve — Quản trị viên duyệt video
     */
    public function approveVideo($id)
    {
        $role = session('user_role') ?? (Auth::check() ? Auth::user()->role : 'user');
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

    /**
     * POST /api/v1/videos/{id}/reject — Quản trị viên từ chối video
     */
    public function rejectVideo($id)
    {
        $role = session('user_role') ?? (Auth::check() ? Auth::user()->role : 'user');
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
}
