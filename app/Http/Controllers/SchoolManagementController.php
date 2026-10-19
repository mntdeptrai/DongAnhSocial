<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\SchoolManagementServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolManagementController extends Controller
{
    public function __construct(
        protected SchoolManagementServiceInterface $schoolService
    ) {}

    public function dashboardRedirect()
    {
        $school = $this->schoolService->getDashboardRedirectSchool();
        if ($school) {
            return redirect()->route('principal.schools.dashboard', $school->slug ?: $school->id);
        }
        return redirect()->route('principal.schools.index');
    }

    public function index()
    {
        $schools = $this->schoolService->getSchoolsList(Auth::user());

        if (!Auth::user()->isAdmin() && $schools->count() === 1) {
            return redirect()->route('principal.schools.dashboard', $schools->first()->slug ?: $schools->first()->id);
        }

        return view('principal.index', compact('schools'));
    }

    public function edit($id)
    {
        $data = $this->schoolService->getSchoolForEdit($id, Auth::user());
        if (!$data) {
            return redirect()->route(request()->is('admin*') ? 'admin.schools.index' : 'principal.schools.index')
                ->with('error', 'Không tìm thấy thông tin trường học!');
        }

        return view('principal.school-edit', $data);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'phone'                => 'nullable|string|max:50',
            'address'              => 'nullable|string|max:500',
            'opening_hours'        => 'nullable|string',
            'founded_year'         => 'nullable|numeric',
            'total_teachers'       => 'nullable|numeric',
            'total_students'       => 'nullable|numeric',
            'awards_count'         => 'nullable|numeric',
            'website'              => 'nullable|string',
            'principal_name'       => 'nullable|string|max:255',
            'components'           => 'nullable|array',
            'components.*.name'    => 'required|string|max:255',
            'components.*.address' => 'nullable|string|max:500',
            'components.*.principal'=> 'nullable|string|max:255',
            'components.*.phone'   => 'nullable|string|max:50',
            'components.*.classes' => 'nullable|numeric',
            'components.*.students'=> 'nullable|numeric',
            'components.*.staff'   => 'nullable|numeric',
            'components.*.area'    => 'nullable|numeric',
            'components.*.lat'     => 'nullable|numeric',
            'components.*.lng'     => 'nullable|numeric',
            'components.*.gmap_link'=> 'nullable|string|max:1000',
            'components.*.existing_photo'=> 'nullable|string',
            'components.*.photo_file'    => 'nullable|file|max:10240',
        ]);

        $school = $this->schoolService->updateSchool(
            $id,
            Auth::user(),
            $validated,
            $request->file('image'),
            $request->input('image_url')
        );

        if (!$school) {
            return redirect()->back()->with('error', 'Không tìm thấy thông tin trường học!');
        }

        return redirect()->route(request()->is('admin*') ? 'admin.schools.edit' : 'principal.schools.edit', $school->id)
            ->with('success', 'Đã cập nhật thông tin trường học & các điểm trường sáp nhập thành công!');
    }

    public function dashboard($id)
    {
        $data = $this->schoolService->getDashboardData($id, Auth::user());
        return view('principal.dashboard', $data);
    }

    public function storePost(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Vui lòng đăng nhập để thực hiện chức năng này.'], 401);
        }

        $validated = $request->validate([
            'eatery_id'   => 'nullable|integer',
            'name'        => 'required|string|max:1000',
            'description' => 'required|string',
            'duration'    => 'nullable|string|max:255',
            'tuition_fee' => 'nullable|numeric',
            'title'       => 'nullable|string',
        ]);

        try {
            $result = $this->schoolService->storePost(
                $user,
                $validated,
                $request->file('image'),
                $request->file('images'),
                $request->file('videos'),
                $request->input('image_urls')
            );

            if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Đăng bài viết mới thành công!',
                    'post'    => $result['post'],
                    'school'  => $result['school']
                ]);
            }

            return redirect()->back()->with('success', 'Đăng bài viết mới thành công!');
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lưu bài viết. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    public function updatePost(Request $request, $id)
    {
        $userId = session('user_id') ?: Auth::id();
        $user = Auth::user() ?: \App\Models\User::find($userId);
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
            }
            return redirect('/auth/login');
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:1000',
            'description' => 'required|string',
            'duration'    => 'nullable|string|max:255',
            'tuition_fee' => 'nullable|numeric',
        ]);

        try {
            $result = $this->schoolService->updatePost(
                $id,
                $user,
                $validated,
                $request->file('image'),
                $request->file('images')
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Cập nhật bài viết thành công!', 'post' => $result['post']]);
            }

            return redirect()->back()->with('success', 'Cập nhật bài viết thành công!');
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroyPost($id)
    {
        $userId = session('user_id') ?: Auth::id();
        $user = Auth::user() ?: \App\Models\User::find($userId);
        if (!$user) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
            }
            return redirect('/auth/login');
        }

        $success = $this->schoolService->destroyPost($id, $user);
        if (!$success) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy bài viết!'], 404);
            }
            return redirect()->back()->with('error', 'Không tìm thấy bài viết cần xóa!');
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Đã xóa bài viết thành công!']);
        }

        return redirect()->back()->with('success', 'Đã xóa bài viết thành công!');
    }

    public function storePhoto(Request $request)
    {
        $validated = $request->validate([
            'eatery_id' => 'required|integer',
            'image'     => 'required|image|max:5120',
            'caption'   => 'nullable|string|max:255',
        ]);

        $this->schoolService->storePhoto($validated['eatery_id'], Auth::user(), $request->file('image'), $validated['caption'] ?? null);
        return redirect()->back()->with('success', 'Tải lên hình ảnh thư viện thành công!');
    }

    public function destroyPhoto($id)
    {
        $this->schoolService->destroyPhoto($id, Auth::user());
        return redirect()->back()->with('success', 'Đã xóa hình ảnh khỏi thư viện!');
    }

    public function storeVideo(Request $request)
    {
        $validated = $request->validate([
            'eatery_id'   => 'required|integer',
            'title'       => 'required|string|max:255',
            'video_url'   => 'required|string|max:1000',
            'video_type'  => 'required|string|in:youtube,tiktok,file',
            'thumbnail'   => 'nullable|image|max:2048',
        ]);

        $this->schoolService->storeVideo($validated['eatery_id'], Auth::user(), $validated, $request->file('thumbnail'));
        return redirect()->back()->with('success', 'Thêm video giới thiệu trường học thành công!');
    }

    public function destroyVideo($id)
    {
        $this->schoolService->destroyVideo($id, Auth::user());
        return redirect()->back()->with('success', 'Đã xóa video trường học!');
    }
}
