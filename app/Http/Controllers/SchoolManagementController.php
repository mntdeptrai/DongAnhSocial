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

            // Nếu Hiệu trưởng đã được phân công 1 trường, chuyển thẳng vào trang quản lý trường đó
            if ($schools->count() === 1) {
                return redirect()->route('principal.schools.edit', $schools->first()->id);
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

        $school = Eatery::on('mysql_education')->find($id);
        if (!$school) {
            $school = Eatery::on('mysql')->find($id);
        }

        if (!$school) {
            return redirect()->route('principal.schools.index')->with('error', 'Không tìm thấy thông tin trường học!');
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

        $school = Eatery::on('mysql_education')->find($id);
        $conn = 'mysql_education';
        if (!$school) {
            $school = Eatery::on('mysql')->find($id);
            $conn = 'mysql';
        }

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

        if ($request->has('components') && is_array($request->input('components'))) {
            foreach ($request->input('components') as $index => $comp) {
                $compName = VietnameseSeoHelper::standardizeSchoolName($comp['name'] ?? '');
                
                $photoUrl = $comp['existing_photo'] ?? 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=600&q=80';
                if ($request->hasFile("components.{$index}.photo_file")) {
                    $uploaded = R2Helper::upload($request->file("components.{$index}.photo_file"), 'education');
                    if ($uploaded) {
                        $photoUrl = $uploaded;
                    }
                }

                $latVal = (float)($comp['lat'] ?? $school->latitude);
                $lngVal = (float)($comp['lng'] ?? $school->longitude);
                $gmapLink = $comp['gmap_link'] ?? '';
                if (empty($gmapLink) && $latVal && $lngVal) {
                    $gmapLink = "https://www.google.com/maps?q={$latVal},{$lngVal}";
                }

                $classesVal = (int)($comp['classes'] ?? 0);
                $studentsVal = (int)($comp['students'] ?? 0);
                $staffVal = (int)($comp['staff'] ?? 0);
                $areaVal = (float)($comp['area'] ?? 0);

                $totalClasses += $classesVal;
                $totalStudents += $studentsVal;
                $totalStaff += $staffVal;
                $totalArea += $areaVal;

                if (!empty($comp['address'])) {
                    $locations[] = [
                        'label' => 'Địa điểm ' . ($index + 1),
                        'name' => $compName,
                        'address' => $comp['address'],
                        'gmap_link' => $gmapLink
                    ];
                }

                $components[] = [
                    'name' => $compName,
                    'address' => $comp['address'] ?? '',
                    'principal' => $comp['principal'] ?? '',
                    'phone' => $comp['phone'] ?? '',
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
        $mergedSchoolData['total_classes'] = $totalClasses;
        $mergedSchoolData['total_students'] = $totalStudents;
        $mergedSchoolData['total_staff'] = $totalStaff;
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

        return redirect()->route('principal.schools.edit', $school->id)
            ->with('success', 'Đã cập nhật thông tin trường học & các điểm trường sáp nhập thành công!');
    }
}
