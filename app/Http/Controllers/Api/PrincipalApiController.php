<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Eatery;
use App\Models\Post;
use App\Models\EducationProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * PrincipalApiController — API dành riêng cho Role Principal (Hiệu Trưởng / Quản Lý Trường Học)
 */
class PrincipalApiController extends Controller
{
    /**
     * Lấy dữ liệu tổng quan Dashboard Trường học cho Principal Mobile App
     */
    public function getPrincipalDashboardData(Request $request)
    {
        $user = Auth::user() ?: auth('sanctum')->user();
        $userId = $user ? $user->id : 0;

        // Lấy thông tin trường học thuộc quản lý của tài khoản này
        $school = Eatery::on('mysql_education')
            ->where('user_id', $userId)
            ->with(['category', 'commune', 'educationPrograms'])
            ->first();

        if (!$school) {
            $school = Eatery::on('mysql_education')
                ->whereHas('category', function($q) {
                    $q->where('slug', 'smart-education-map');
                })
                ->first();
        }

        $schoolId = $school ? $school->id : 0;

        // Lấy bài viết truyền thông của trường học
        $posts = Post::on('mysql_education')
            ->where('eatery_id', $schoolId)
            ->latest()
            ->get();

        $programs = $school ? EducationProgram::on('mysql_education')->where('eatery_id', $schoolId)->get() : collect();

        return response()->json([
            'success' => true,
            'school'  => $school ? [
                'id'          => $school->id,
                'name'        => $school->name,
                'address'     => $school->address,
                'phone'       => $school->phone,
                'image_path'  => $school->image_path,
                'category'    => $school->category?->name ?? 'Giáo dục / Đào tạo',
                'commune'     => $school->commune?->name ?? 'Đông Anh',
                'components'  => $school->merged_components ?? [],
            ] : null,
            'posts'     => $posts,
            'programs'  => $programs,
            'stats'     => [
                'total_posts'    => $posts->count(),
                'total_programs' => $programs->count(),
                'total_likes'    => $posts->sum('likes_count'),
                'total_shares'   => $posts->sum('shares_count'),
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Lấy danh sách tin bài truyền thông của Trường học
     */
    public function getSchoolPosts(Request $request)
    {
        $user = Auth::user() ?: auth('sanctum')->user();
        $userId = $user ? $user->id : 0;

        $posts = Post::on('mysql_education')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'posts'   => $posts,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Đăng bài viết truyền thông trường học mới
     */
    public function storeSchoolPost(Request $request)
    {
        $user = Auth::user() ?: auth('sanctum')->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $post = Post::on('mysql_education')->create([
            'user_id'     => $user->id,
            'eatery_id'   => $request->eatery_id ?? null,
            'name'        => $request->name,
            'description' => $request->description,
            'image_path'  => $request->image_path ?? null,
            'images'      => $request->images ?? [],
            'video_path'  => $request->video_path ?? null,
            'videos'      => $request->videos ?? [],
            'status'      => 'published',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đăng bài viết truyền thông trường học thành công!',
            'post'    => $post,
        ], 201, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Xóa bài viết truyền thông trường học
     */
    public function deleteSchoolPost(Request $request, $id)
    {
        $post = Post::on('mysql_education')->find($id);
        if ($post) {
            $post->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa bài viết truyền thông trường học thành công.',
        ]);
    }

    /**
     * Thêm chương trình giáo dục mới cho Trường học
     */
    public function storeEducationProgram(Request $request)
    {
        $request->validate([
            'eatery_id'    => 'required',
            'program_name' => 'required|string|max:255',
        ]);

        $program = EducationProgram::on('mysql_education')->create([
            'eatery_id'    => $request->eatery_id,
            'program_name' => $request->program_name,
            'description'  => $request->description ?? 'Chương trình đào tạo chất lượng cao',
            'duration'     => $request->duration ?? '1 Học kỳ',
            'tuition_fee'  => $request->tuition_fee ?? 'Theo quy định',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm chương trình giáo dục mới!',
            'program' => $program,
        ], 201, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Xóa chương trình giáo dục
     */
    public function deleteEducationProgram(Request $request, $id)
    {
        $program = EducationProgram::on('mysql_education')->find($id);
        if ($program) {
            $program->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa chương trình giáo dục.',
        ]);
    }
}
