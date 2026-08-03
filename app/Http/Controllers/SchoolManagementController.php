<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Eatery;
use App\Services\EateryApiService;
use App\Helpers\VietnameseSeoHelper;
use App\Helpers\R2Helper;

class SchoolManagementController extends Controller
{
    /**
     * Kiểm tra quyền truy cập của Principal (Hiệu trưởng) hoặc Admin
     */
    private function verifyPrincipalOrAdmin()
    {
        $role = session('user_role') ?: (Auth::user() ? Auth::user()->role : null);
        if (!in_array($role, ['principal', 'admin'])) {
            abort(403, 'Bạn không có quyền truy cập Kênh Quản Lý Trường Học! Chỉ tài khoản Hiệu trưởng hoặc Quản trị viên hệ thống mới có quyền truy cập.');
        }
    }

    /**
     * Tìm thông tin trường học bằng ID hoặc Slug
     */
    private function findSchool($idOrSlug)
    {
        if (is_numeric($idOrSlug)) {
            $school = Eatery::on('mysql_education')->find($idOrSlug);
            if (!$school) {
                $school = Eatery::on('mysql')->find($idOrSlug);
            }
        } else {
            $school = Eatery::on('mysql_education')->where('slug', $idOrSlug)->first();
            if (!$school) {
                $school = Eatery::on('mysql')->where('slug', $idOrSlug)->first();
            }
        }
        return $school;
    }

    /**
     * Redirect Hiệu Trưởng trực tiếp vào Dashboard trường mình quản lý
     */
    public function dashboardRedirect()
    {
        $this->verifyPrincipalOrAdmin();
        $user = Auth::user();

        $school = Eatery::on('mysql_education')->where('user_id', $user->id)->first();
        if (!$school) {
            $school = Eatery::on('mysql')->where('user_id', $user->id)->first();
        }

        if ($school) {
            return redirect()->route('principal.schools.dashboard', $school->slug ?: $school->id);
        }

        return redirect()->route('principal.schools.index');
    }

    /**
     * Danh sách các trường học thuộc quyền quản lý của Hiệu trưởng / Admin
     */
    public function index()
    {
        $this->verifyPrincipalOrAdmin();
        $user = Auth::user();

        if ($user->isAdmin()) {
            // QTV Hệ thống (Admin) có quyền quản lý & chỉnh sửa tất cả các trường học
            $schools = Eatery::on('mysql_education')
                ->with('commune')
                ->whereHas('category', function($q) {
                    $q->where('slug', 'smart-education-map');
                })
                ->get();

            if ($schools->isEmpty()) {
                $schools = Eatery::on('mysql')
                    ->with('commune')
                    ->whereHas('category', function($q) {
                        $q->where('slug', 'smart-education-map');
                    })
                    ->get();
            }
        } else {
            // Tài khoản Hiệu trưởng (Principal) CHỈ quản lý duy nhất 1 trường được phân công
            $schools = Eatery::on('mysql_education')
                ->where('user_id', $user->id)
                ->with('commune')
                ->get();

            if ($schools->isEmpty()) {
                $schools = Eatery::on('mysql')
                    ->where('user_id', $user->id)
                    ->with('commune')
                    ->get();
            }

            // Nếu Hiệu trưởng đã được phân công 1 trường, chuyển thẳng vào trang dashboard trường đó
            if ($schools->count() === 1) {
                return redirect()->route('principal.schools.dashboard', $schools->first()->slug ?: $schools->first()->id);
            }
        }

        return view('principal.index', compact('schools'));
    }

    /**
     * Trang chỉnh sửa thông tin trường học & danh sách trường sáp nhập vào
     */
    public function edit($id)
    {
        $this->verifyPrincipalOrAdmin();
        $user = Auth::user();

        $school = $this->findSchool($id);

        if (!$school) {
            return redirect()->route(request()->is('admin*') ? 'admin.schools.index' : 'principal.schools.index')->with('error', 'Không tìm thấy thông tin trường học!');
        }

        // Kiểm tra quyền: Hiệu trưởng CHỈ được chỉnh sửa 1 trường học mà mình được phân công (user_id = Auth::id())
        if (!$user->isAdmin() && $school->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền chỉnh sửa trường học này! Tài khoản Hiệu trưởng chỉ được phép quản lý duy nhất 1 trường học được phân công.');
        }

        $storyData = $school->storytelling_data ?? [];
        $components = $school->merged_components;

        return view('principal.school-edit', compact('school', 'storyData', 'components'));
    }

    /**
     * Cập nhật kịch bản sáp nhập & thông tin các trường thành phần
     */
    public function update(Request $request, $id)
    {
        $this->verifyPrincipalOrAdmin();
        $user = Auth::user();

        $school = $this->findSchool($id);
        if (!$school) {
            return redirect()->back()->with('error', 'Không tìm thấy thông tin trường học!');
        }

        if (!$user->isAdmin() && $school->user_id !== $user->id) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'principal_name' => 'nullable|string|max:255',
            'components' => 'nullable|array',
            'components.*.name' => 'required|string|max:255',
            'components.*.address' => 'nullable|string|max:500',
            'components.*.principal' => 'nullable|string|max:255',
            'components.*.phone' => 'nullable|string|max:50',
            'components.*.classes' => 'nullable|numeric',
            'components.*.students' => 'nullable|numeric',
            'components.*.staff' => 'nullable|numeric',
            'components.*.area' => 'nullable|numeric',
            'components.*.lat' => 'nullable|numeric',
            'components.*.lng' => 'nullable|numeric',
            'components.*.gmap_link' => 'nullable|string|max:1000',
        ]);

        // Chuẩn hóa từ viết tắt MN -> Mầm non, TH -> Tiểu học
        $stdName = VietnameseSeoHelper::standardizeSchoolName($request->input('name'));

        // Cập nhật thông tin cơ bản
        $school->name = $stdName;
        $school->phone = $request->input('phone', $school->phone);
        $school->address = $request->input('address', $school->address);
        if ($request->has('opening_hours')) {
            $school->opening_hours = $request->input('opening_hours');
        }

        // Upload ảnh đại diện trường nếu có file mới được chọn
        if ($request->hasFile('image')) {
            $imagePath = R2Helper::upload($request->file('image'), 'education');
            if ($imagePath) {
                $school->image_path = $imagePath;
            }
        } elseif ($request->filled('image_url')) {
            $school->image_path = $request->input('image_url');
        }

        // Cấu trúc dữ liệu storytelling_data
        $existingData = $school->storytelling_data ?? [];

        $components = [];
        $totalClasses = 0;
        $totalStudents = 0;
        $totalStaff = 0;
        $totalArea = 0;
        $locations = [];

        $existingComponents = $existingData['components'] ?? [];

        if ($request->has('components') && is_array($request->input('components'))) {
            foreach ($request->input('components') as $index => $comp) {
                $oldComp = $existingComponents[$index] ?? [];
                
                if ($user->isAdmin()) {
                    // Admin có toàn quyền chỉnh sửa toàn bộ thuộc tính
                    $compName = VietnameseSeoHelper::standardizeSchoolName($comp['name'] ?? '');
                    $compAddress = $comp['address'] ?? '';
                    $compPrincipal = $comp['principal'] ?? '';
                    $compPhone = $comp['phone'] ?? '';
                    $gmapLink = $comp['gmap_link'] ?? '';
                    $latVal = (float)($comp['lat'] ?? $school->latitude);
                    $lngVal = (float)($comp['lng'] ?? $school->longitude);

                    $photoUrl = $comp['existing_photo'] ?? ($oldComp['photo'] ?? 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=600&q=80');
                    if ($request->hasFile("components.{$index}.photo_file")) {
                        $uploaded = R2Helper::upload($request->file("components.{$index}.photo_file"), 'education');
                        if ($uploaded) {
                            $photoUrl = $uploaded;
                        }
                    }
                } else {
                    // Hiệu trưởng CHỈ ĐƯỢC CHỈNH SỬA số lượng Lớp, Học sinh, CBGVNV, Diện tích.
                    // Các thông tin Cố định (Tên, Địa chỉ, Đại diện, Bản đồ, Ảnh) được giữ nguyên từ hệ thống
                    $compName = $oldComp['name'] ?? VietnameseSeoHelper::standardizeSchoolName($comp['name'] ?? '');
                    $compAddress = $oldComp['address'] ?? ($comp['address'] ?? '');
                    $compPrincipal = $oldComp['principal'] ?? ($comp['principal'] ?? '');
                    $compPhone = $oldComp['phone'] ?? ($comp['phone'] ?? '');
                    $gmapLink = $oldComp['gmap_link'] ?? ($comp['gmap_link'] ?? '');
                    $latVal = (float)($oldComp['lat'] ?? $school->latitude);
                    $lngVal = (float)($oldComp['lng'] ?? $school->longitude);
                    $photoUrl = $oldComp['photo'] ?? ($comp['existing_photo'] ?? 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=600&q=80');
                }

                if (empty($gmapLink) && $latVal && $lngVal) {
                    $gmapLink = "https://www.google.com/maps?q={$latVal},{$lngVal}";
                }

                if ($user->isAdmin()) {
                    // Admin có quyền sửa toàn bộ bao gồm cả Tổng diện tích đất
                    $areaVal = (float)($comp['area'] ?? ($oldComp['area'] ?? 0));
                } else {
                    // Hiệu trưởng KHÔNG ĐƯỢC PHÉP sửa Tổng diện tích đất (chỉ Admin mới được sửa)
                    $areaVal = (float)($oldComp['area'] ?? 0);
                }

                $classesVal = (int)($comp['classes'] ?? ($oldComp['classes'] ?? 0));
                $studentsVal = (int)($comp['students'] ?? ($oldComp['students'] ?? 0));
                $staffVal = (int)($comp['staff'] ?? ($oldComp['staff'] ?? 0));

                $totalClasses += $classesVal;
                $totalStudents += $studentsVal;
                $totalStaff += $staffVal;
                $totalArea += $areaVal;

                if (!empty($compAddress)) {
                    $locations[] = [
                        'label' => 'Địa điểm ' . ($index + 1),
                        'name' => $compName,
                        'address' => $compAddress,
                        'gmap_link' => $gmapLink
                    ];
                }

                $components[] = [
                    'name' => $compName,
                    'address' => $compAddress,
                    'principal' => $compPrincipal,
                    'phone' => $compPhone,
                    'classes' => $classesVal,
                    'students' => $studentsVal,
                    'staff' => $staffVal,
                    'area' => $areaVal,
                    'lat' => $latVal,
                    'lng' => $lngVal,
                    'gmap_link' => $gmapLink,
                    'photo' => $photoUrl,
                ];
            }
        }

        $mergedSchoolData = $existingData['mergedSchool'] ?? [];
        $mergedSchoolData['name'] = $stdName;
        $mergedSchoolData['address'] = $school->address;
        $mergedSchoolData['phone'] = $school->phone;
        $mergedSchoolData['opening_hours'] = $school->opening_hours;
        $mergedSchoolData['photo'] = $school->image_path ?: ($mergedSchoolData['photo'] ?? '');
        $mergedSchoolData['lat'] = (float)$school->latitude;
        $mergedSchoolData['lng'] = (float)$school->longitude;
        $mergedSchoolData['total_classes'] = $totalClasses;

        // Cập nhật các chỉ số trường học
        $mergedSchoolData['founded_year'] = $request->filled('founded_year') ? (int)$request->input('founded_year') : ($mergedSchoolData['founded_year'] ?? 2008);
        $mergedSchoolData['total_staff'] = $request->filled('total_teachers') ? (int)$request->input('total_teachers') : ($totalStaff ?: ($mergedSchoolData['total_staff'] ?? 63));
        $mergedSchoolData['total_students'] = $request->filled('total_students') ? (int)$request->input('total_students') : ($totalStudents ?: ($mergedSchoolData['total_students'] ?? 759));
        $mergedSchoolData['awards_count'] = $request->filled('awards_count') ? (int)$request->input('awards_count') : ($mergedSchoolData['awards_count'] ?? 12);
        $mergedSchoolData['website'] = $request->input('website', $mergedSchoolData['website'] ?? 'phucloc.edu.vn');

        $mergedSchoolData['total_area'] = $totalArea;
        $mergedSchoolData['locations'] = $locations;

        if ($request->filled('principal_name')) {
            $mergedSchoolData['principal'] = $request->input('principal_name');
        }

        $updatedStoryData = [
            'mergedSchool' => $mergedSchoolData,
            'components' => $components,
            'distanceText' => $existingData['distanceText'] ?? '2.0 km',
            'durationText' => $existingData['durationText'] ?? '5 phút'
        ];

        $school->storytelling_data = $updatedStoryData;
        $school->save();

        return redirect()->route(request()->is('admin*') ? 'admin.schools.edit' : 'principal.schools.edit', $school->id)
            ->with('success', 'Đã cập nhật thông tin trường học & các điểm trường sáp nhập thành công!');
    }

    /**
     * Trang dashboard quản lý trường học dành cho Hiệu trưởng
     */
    public function dashboard($id)
    {
        $this->verifyPrincipalOrAdmin();
        $user = Auth::user();

        $school = $this->findSchool($id);

        if (!$school) {
            return redirect()->route('principal.schools.index')->with('error', 'Không tìm thấy thông tin trường học!');
        }

        // Kiểm tra quyền
        if (!$user->isAdmin() && $school->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền quản trị trường học này!');
        }

        // Collect all pre-stored photos (main photo + merged component school photos)
        $preStoredPhotos = [];
        if ($school->image_path) {
            $preStoredPhotos[] = $school->image_path;
        }

        $components = $school->merged_components;
        if (!empty($components) && is_array($components)) {
            foreach ($components as $comp) {
                if (!empty($comp['photo']) && !in_array($comp['photo'], $preStoredPhotos)) {
                    $preStoredPhotos[] = $comp['photo'];
                }
            }
        }

        // 1. Sync photos into eatery_photos table if empty
        $existingPhotos = \App\Models\EateryPhoto::on('mysql_education')->where('eatery_id', $school->id)->orderBy('sort_order')->get();
        if ($existingPhotos->isEmpty()) {
            $existingPhotos = \App\Models\EateryPhoto::on('mysql')->where('eatery_id', $school->id)->orderBy('sort_order')->get();
        }

        if ($existingPhotos->isEmpty() && !empty($preStoredPhotos)) {
            foreach ($preStoredPhotos as $idx => $pUrl) {
                try {
                    \App\Models\EateryPhoto::on('mysql_education')->create([
                        'eatery_id' => $school->id,
                        'image_path' => $pUrl,
                        'caption' => 'Hình ảnh cơ sở vật chất & điểm trường - ' . $school->standardized_name,
                        'sort_order' => $idx + 1,
                    ]);
                } catch (\Exception $e) {
                    try {
                        \App\Models\EateryPhoto::on('mysql')->create([
                            'eatery_id' => $school->id,
                            'image_path' => $pUrl,
                            'caption' => 'Hình ảnh cơ sở vật chất & điểm trường - ' . $school->standardized_name,
                            'sort_order' => $idx + 1,
                        ]);
                    } catch (\Exception $e2) {}
                }
            }
            $photos = \App\Models\EateryPhoto::on('mysql_education')->where('eatery_id', $school->id)->orderBy('sort_order')->get();
            if ($photos->isEmpty()) {
                $photos = \App\Models\EateryPhoto::on('mysql')->where('eatery_id', $school->id)->orderBy('sort_order')->get();
            }
        } else {
            $photos = $existingPhotos;
        }

        // 2. Fetch Facebook posts
        $posts = \App\Models\EducationProgram::on('mysql_education')->where('eatery_id', $school->id)->orderBy('created_at', 'desc')->get();
        if ($posts->isEmpty()) {
            $posts = \App\Models\EducationProgram::on('mysql')->where('eatery_id', $school->id)->orderBy('created_at', 'desc')->get();
        }

        // Attach real reaction & comment metrics (no mockdata)
        $currentUserId = \Illuminate\Support\Facades\Auth::id() ?? session('user_id');
        $currentSessionId = session()->getId();
        $postIds = $posts->pluck('id')->toArray();

        $realLikesMap = [];
        $userLikedMap = [];
        $realCommentsMap = [];

        if (!empty($postIds)) {
            $realLikesMap = \App\Models\CheckinReaction::where('reactionable_type', 'post')
                ->whereIn('reactionable_id', $postIds)
                ->selectRaw('reactionable_id, count(*) as total')
                ->groupBy('reactionable_id')
                ->pluck('total', 'reactionable_id')
                ->toArray();

            $uQuery = \App\Models\CheckinReaction::where('reactionable_type', 'post')
                ->whereIn('reactionable_id', $postIds);
            if ($currentUserId) {
                $uQuery->where('user_id', $currentUserId);
            } else {
                $uQuery->where('session_id', $currentSessionId);
            }
            $userLikedMap = $uQuery->pluck('reactionable_id')->toArray();

            $realCommentsMap = \App\Models\Comment::where('commentable_type', 'post')
                ->whereIn('commentable_id', $postIds)
                ->selectRaw('commentable_id, count(*) as total')
                ->groupBy('commentable_id')
                ->pluck('total', 'commentable_id')
                ->toArray();
        }

        foreach ($posts as $p) {
            $p->real_likes_count = (int) ($realLikesMap[$p->id] ?? $p->likes_count ?? 0);
            $p->is_liked = in_array($p->id, $userLikedMap);
            $p->real_comments_count = (int) ($realCommentsMap[$p->id] ?? 0);
            $p->real_shares_count = (int) ($p->shares_count ?? 0);
        }

        $videos = $school->reviewVideos()->orderBy('created_at', 'desc')->get();
        $storyData = $school->storytelling_data ?? [];

        return view('principal.dashboard', compact('school', 'posts', 'photos', 'videos', 'storyData'));
    }

    /**
     * Đăng bài viết / Chương trình học mới
     */
    public function storePost(Request $request)
    {
        $this->verifyPrincipalOrAdmin();
        $user = Auth::user();

        $request->validate([
            'eatery_id' => 'required|integer',
            'name' => 'required|string|max:1000',
            'description' => 'required|string',
            'duration' => 'nullable|string|max:255',
            'tuition_fee' => 'nullable|numeric',
            'image' => 'nullable|image|max:512000',
            'images.*' => 'nullable|image|max:512000',
        ]);

        $school = Eatery::on('mysql_education')->find($request->eatery_id);
        if (!$school) {
            $school = Eatery::on('mysql')->find($request->eatery_id);
        }

        if (!$school || (!$user->isAdmin() && $school->user_id !== $user->id)) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $uploadedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = R2Helper::upload($file, 'education');
                if ($path) {
                    $uploadedImages[] = $path;
                }
            }
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = R2Helper::upload($request->file('image'), 'education');
            if ($imagePath && empty($uploadedImages)) {
                $uploadedImages[] = $imagePath;
            }
        }

        if (empty($imagePath) && !empty($uploadedImages)) {
            $imagePath = $uploadedImages[0];
        }

        $postData = [
            'eatery_id' => $school->id,
            'name' => $request->name,
            'description' => $request->description,
            'duration' => $request->duration,
            'tuition_fee' => $request->tuition_fee,
            'image_path' => $imagePath,
            'images' => $uploadedImages,
        ];

        try {
            $post = \App\Models\EducationProgram::on('mysql_education')->create($postData);
        } catch (\Exception $e) {
            $post = \App\Models\EducationProgram::on('mysql')->create($postData);
        }

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $post->all_images = $post->all_images;
            $post->formatted_created_at = 'Vừa xong';
            $post->real_likes_count = 0;
            $post->is_liked = false;
            $post->real_comments_count = 0;
            $post->real_shares_count = 0;

            return response()->json([
                'success' => true,
                'message' => 'Đăng bài viết mới thành công!',
                'post' => $post,
                'school' => [
                    'id' => $school->id,
                    'name' => $school->standardized_name,
                    'image_path' => $school->image_path ?: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=150&q=80',
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Đăng bài viết mới thành công!');
    }

    /**
     * Cập nhật bài viết / Chương trình học
     */
    public function updatePost(Request $request, $id)
    {
        $this->verifyPrincipalOrAdmin();
        $user = Auth::user();

        $post = \App\Models\EducationProgram::findOrFail($id);
        
        $school = Eatery::on('mysql_education')->find($post->eatery_id);
        if (!$school) {
            $school = Eatery::on('mysql')->find($post->eatery_id);
        }

        if (!$school || (!$user->isAdmin() && $school->user_id !== $user->id)) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $request->validate([
            'name' => 'required|string|max:1000',
            'description' => 'required|string',
            'duration' => 'nullable|string|max:255',
            'tuition_fee' => 'nullable|numeric',
            'image' => 'nullable|image|max:512000',
            'images.*' => 'nullable|image|max:512000',
        ]);

        $post->name = $request->name;
        $post->description = $request->description;
        $post->duration = $request->duration;
        $post->tuition_fee = $request->tuition_fee;

        $existingImages = is_array($post->images) ? $post->images : [];

        if ($request->hasFile('images')) {
            $newImages = [];
            foreach ($request->file('images') as $file) {
                $path = R2Helper::upload($file, 'education');
                if ($path) {
                    $newImages[] = $path;
                }
            }
            if (!empty($newImages)) {
                $existingImages = array_merge($existingImages, $newImages);
            }
        }

        if ($request->hasFile('image')) {
            $imagePath = R2Helper::upload($request->file('image'), 'education');
            if ($imagePath) {
                $post->image_path = $imagePath;
                if (!in_array($imagePath, $existingImages)) {
                    array_unshift($existingImages, $imagePath);
                }
            }
        }

        $post->images = $existingImages;
        if (!empty($existingImages)) {
            $post->image_path = $existingImages[0];
        }

        $post->save();

        return redirect()->back()->with('success', 'Cập nhật bài viết thành công!');
    }

    /**
     * Xóa bài viết / Chương trình học
     */
    public function destroyPost($id)
    {
        $this->verifyPrincipalOrAdmin();
        $user = Auth::user();

        $post = \App\Models\EducationProgram::on('mysql_education')->find($id);
        if (!$post) {
            $post = \App\Models\EducationProgram::on('mysql')->find($id);
        }

        if (!$post) {
            return redirect()->back()->with('error', 'Không tìm thấy bài viết cần xóa!');
        }
        
        $school = Eatery::on('mysql_education')->find($post->eatery_id);
        if (!$school) {
            $school = Eatery::on('mysql')->find($post->eatery_id);
        }

        if (!$school || (!$user->isAdmin() && $school->user_id !== $user->id)) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $post->delete();

        return redirect()->back()->with('success', 'Đã xóa bài viết thành công!');
    }

    /**
     * Thêm ảnh mới vào thư viện trường học
     */
    public function storePhoto(Request $request)
    {
        $this->verifyPrincipalOrAdmin();
        $user = Auth::user();

        $request->validate([
            'eatery_id' => 'required|integer',
            'image' => 'required|image|max:5120',
            'caption' => 'nullable|string|max:255',
        ]);

        $school = Eatery::on('mysql_education')->find($request->eatery_id);
        if (!$school) {
            $school = Eatery::on('mysql')->find($request->eatery_id);
        }

        if (!$school || (!$user->isAdmin() && $school->user_id !== $user->id)) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $imagePath = R2Helper::upload($request->file('image'), 'education');

        // Tìm sort order cao nhất hiện tại
        $maxSort = \App\Models\EateryPhoto::where('eatery_id', $school->id)->max('sort_order') ?? 0;

        \App\Models\EateryPhoto::create([
            'eatery_id' => $school->id,
            'image_path' => $imagePath,
            'caption' => $request->caption ?: '',
            'sort_order' => $maxSort + 1,
        ]);

        return redirect()->back()->with('success', 'Tải lên hình ảnh thư viện thành công!');
    }

    /**
     * Xóa ảnh trong thư viện
     */
    public function destroyPhoto($id)
    {
        $this->verifyPrincipalOrAdmin();
        $user = Auth::user();

        $photo = \App\Models\EateryPhoto::findOrFail($id);

        $school = Eatery::on('mysql_education')->find($photo->eatery_id);
        if (!$school) {
            $school = Eatery::on('mysql')->find($photo->eatery_id);
        }

        if (!$school || (!$user->isAdmin() && $school->user_id !== $user->id)) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $photo->delete();

        return redirect()->back()->with('success', 'Đã xóa hình ảnh khỏi thư viện!');
    }

    /**
     * Đăng video giới thiệu trường học mới
     */
    public function storeVideo(Request $request)
    {
        $this->verifyPrincipalOrAdmin();
        $user = Auth::user();

        $request->validate([
            'eatery_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'video_url' => 'required|string|max:1000',
            'video_type' => 'required|string|in:youtube,tiktok,file',
            'thumbnail' => 'nullable|image|max:2048',
        ]);

        $school = Eatery::on('mysql_education')->find($request->eatery_id);
        if (!$school) {
            $school = Eatery::on('mysql')->find($request->eatery_id);
        }

        if (!$school || (!$user->isAdmin() && $school->user_id !== $user->id)) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        // Upload thumbnail
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = R2Helper::upload($request->file('thumbnail'), 'education');
        }

        \App\Models\ReviewVideo::create([
            'eatery_id' => $school->id,
            'user_id' => $user->id,
            'title' => $request->title,
            'video_url' => $request->video_url,
            'video_type' => $request->video_type,
            'thumbnail_path' => $thumbnailPath,
            'likes_count' => 0,
            'status' => 'approved',
        ]);

        return redirect()->back()->with('success', 'Thêm video giới thiệu trường học thành công!');
    }

    /**
     * Xóa video trường học
     */
    public function destroyVideo($id)
    {
        $this->verifyPrincipalOrAdmin();
        $user = Auth::user();

        $video = \App\Models\ReviewVideo::findOrFail($id);

        $school = Eatery::on('mysql_education')->find($video->eatery_id);
        if (!$school) {
            $school = Eatery::on('mysql')->find($video->eatery_id);
        }

        if (!$school || (!$user->isAdmin() && $school->user_id !== $user->id)) {
            abort(403, 'Quyền truy cập bị từ chối!');
        }

        $video->delete();

        return redirect()->back()->with('success', 'Đã xóa video trường học!');
    }
}
