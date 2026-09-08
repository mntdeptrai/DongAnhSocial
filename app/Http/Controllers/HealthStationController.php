<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\R2Helper;

class HealthStationController extends Controller
{
    /**
     * Kiểm tra quyền truy cập Cổng Quản Lý Trạm Y Tế & Cơ Sở Sức Khỏe
     */
    private function getHealthStationContext()
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập để truy cập Cổng Quản lý Trạm Y tế.');
        }

        $db = DB::connection('mysql_market');

        // Tìm cơ sở y tế thuộc sở hữu của User (category_id thuộc wellness-care)
        $wellnessCat = $db->table('categories')->where('slug', 'wellness-care')->first();
        $catId = $wellnessCat ? $wellnessCat->id : null;

        $eatery = null;
        if ($user->eatery_id) {
            $eatery = $db->table('eateries')->where('id', $user->eatery_id)->first();
        }
        if (!$eatery) {
            $eatery = $db->table('eateries')->where('user_id', $user->id)->first();
        }
        if (!$eatery && !empty($user->phone)) {
            $eatery = $db->table('eateries')->where('phone', $user->phone)->first();
        }

        // Nếu người dùng là admin/manager và chưa có eatery cá nhân, lấy Trạm Y tế xã Đông Anh làm mặc định
        if (!$eatery && in_array($user->role, ['admin', 'manager'])) {
            $eatery = $db->table('eateries')->where('slug', 'tram-y-te-xa-dong-anh')->first();
        }

        if (!$eatery) {
            abort(403, 'Tài khoản của bạn chưa liên kết với Cơ sở Y tế nào trên hệ thống.');
        }

        return [
            'user' => $user,
            'eatery' => $eatery,
            'db' => $db,
        ];
    }

    /**
     * Tổng quan Bảng Điều Hành Trạm Y Tế (Dashboard)
     */
    public function dashboard()
    {
        $context = $this->getHealthStationContext();
        $eatery = $context['eatery'];
        $db = $context['db'];

        // Lấy danh sách Dịch vụ Y tế & Kỹ thuật
        $services = $db->table('wellness_services')
            ->where('eatery_id', $eatery->id)
            ->orderBy('id', 'asc')
            ->get();

        // Lấy danh sách Bác sĩ & Lịch trực
        $doctors = $db->table('wellness_doctors')
            ->where('eatery_id', $eatery->id)
            ->where('status', 'active')
            ->orderBy('id', 'asc')
            ->get();

        // Decode storytelling data từ DB
        $rawStory = $eatery->storytelling_data ?? $eatery->storytelling_json ?? null;
        $storytelling = [];
        if (!empty($rawStory)) {
            $storytelling = is_array($rawStory) ? $rawStory : (json_decode($rawStory, true) ?: []);
        }

        return view('health-station.dashboard', [
            'eatery' => $eatery,
            'services' => $services,
            'doctors' => $doctors,
            'storytelling' => $storytelling,
            'user' => $context['user'],
        ]);
    }

    /**
     * Quản lý Hồ sơ & Hotline Cấp cứu Trạm Y tế
     */
    public function showProfile()
    {
        $context = $this->getHealthStationContext();
        $eatery = $context['eatery'];
        $db = $context['db'];

        $rawStory = $eatery->storytelling_data ?? $eatery->storytelling_json ?? null;
        $storytelling = [];
        if (!empty($rawStory)) {
            $storytelling = is_array($rawStory) ? $rawStory : (json_decode($rawStory, true) ?: []);
        }

        $photos = $db->table('eatery_photos')
            ->where('eatery_id', $eatery->id)
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('health-station.profile', [
            'eatery' => $eatery,
            'storytelling' => $storytelling,
            'photos' => $photos,
            'user' => $context['user'],
        ]);
    }

    /**
     * Cập nhật Hồ sơ Trạm Y tế
     */
    public function updateProfile(Request $request)
    {
        $context = $this->getHealthStationContext();
        $eatery = $context['eatery'];
        $db = $context['db'];

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'required|string|max:550',
            'opening_hours' => 'nullable|string|max:255',
            'price_range' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'map_link' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'image' => 'nullable|image|max:10240',
            'image_url' => 'nullable|string',
            'images.*' => 'nullable|image|max:10240',
            'image_urls' => 'nullable|array',
            'delete_photo_ids' => 'nullable|array',
        ]);

        $imagePath = $eatery->image_path;

        // Upload ảnh đại diện đơn lẻ nếu có
        if ($request->hasFile('image')) {
            $uploaded = R2Helper::upload($request->file('image'), 'health-stations');
            if ($uploaded) {
                $imagePath = $uploaded;
            }
        } elseif ($request->filled('image_url')) {
            $imagePath = trim($request->input('image_url'));
        }

        // Xử lý tọa độ & Link Google Maps
        $latitude = $request->input('latitude') !== null && $request->input('latitude') !== '' ? (float)$request->input('latitude') : $eatery->latitude;
        $longitude = $request->input('longitude') !== null && $request->input('longitude') !== '' ? (float)$request->input('longitude') : $eatery->longitude;

        // Cập nhật thông tin storytelling
        $rawStory = $eatery->storytelling_data ?? $eatery->storytelling_json ?? null;
        $storytelling = [];
        if (!empty($rawStory)) {
            $storytelling = is_array($rawStory) ? $rawStory : (json_decode($rawStory, true) ?: []);
        }

        if ($request->filled('map_link')) {
            $mapLink = trim($request->input('map_link'));
            list($autoLat, $autoLng) = $this->parseGoogleMapsUrl($mapLink);
            if ($autoLat !== null && $autoLng !== null) {
                $latitude = $autoLat;
                $longitude = $autoLng;
            }
            $storytelling['map_link'] = $mapLink;
        }

        // Xử lý xóa ảnh đã chọn
        if ($request->has('delete_photo_ids') && is_array($request->input('delete_photo_ids'))) {
            $db->table('eatery_photos')
                ->where('eatery_id', $eatery->id)
                ->whereIn('id', $request->input('delete_photo_ids'))
                ->delete();
        }

        $maxSort = (int) ($db->table('eatery_photos')->where('eatery_id', $eatery->id)->max('sort_order') ?? 0);

        // Upload nhiều ảnh được chọn từ máy
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $uploaded = R2Helper::upload($file, 'health-stations');
                    if ($uploaded) {
                        $maxSort++;
                        $db->table('eatery_photos')->insert([
                            'eatery_id' => $eatery->id,
                            'image_path' => $uploaded,
                            'caption' => 'Hình ảnh ' . $request->input('name'),
                            'sort_order' => $maxSort,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        if (empty($imagePath)) {
                            $imagePath = $uploaded;
                        }
                    }
                }
            }
        }

        // Xử lý thêm các URL ảnh trực tiếp được nhập
        if ($request->has('image_urls') && is_array($request->input('image_urls'))) {
            foreach ($request->input('image_urls') as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $maxSort++;
                    $db->table('eatery_photos')->insert([
                        'eatery_id' => $eatery->id,
                        'image_path' => $url,
                        'caption' => 'Hình ảnh ' . $request->input('name'),
                        'sort_order' => $maxSort,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    if (empty($imagePath)) {
                        $imagePath = $url;
                    }
                }
            }
        }

        // Đảm bảo ảnh đại diện chính (imagePath) cũng có mặt trong eatery_photos nếu có
        if (!empty($imagePath)) {
            $existsInPhotos = $db->table('eatery_photos')
                ->where('eatery_id', $eatery->id)
                ->where('image_path', $imagePath)
                ->exists();

            if (!$existsInPhotos) {
                $db->table('eatery_photos')->insert([
                    'eatery_id' => $eatery->id,
                    'image_path' => $imagePath,
                    'caption' => 'Ảnh đại diện chính - ' . $request->input('name'),
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Nếu imagePath hiện tại rỗng nhưng eatery_photos có ảnh, lấy ảnh đầu tiên làm imagePath
        if (empty($imagePath)) {
            $firstPhoto = $db->table('eatery_photos')
                ->where('eatery_id', $eatery->id)
                ->orderBy('sort_order', 'asc')
                ->first();
            if ($firstPhoto) {
                $imagePath = $firstPhoto->image_path;
            }
        }

        if ($request->has('area')) {
            $storytelling['area'] = $request->input('area');
        }
        if ($request->has('staff_count')) {
            $storytelling['staff_count'] = $request->input('staff_count');
        }
        if ($request->has('staff_total')) {
            $storytelling['staff_total'] = $request->input('staff_total');
        }
        if ($request->has('heritage_year')) {
            $storytelling['heritage_year'] = $request->input('heritage_year');
        }

        $storyJson = !empty($storytelling) ? json_encode($storytelling, JSON_UNESCAPED_UNICODE) : null;

        $db->table('eateries')->where('id', $eatery->id)->update([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'opening_hours' => $request->input('opening_hours'),
            'price_range' => $request->input('price_range'),
            'description' => $request->input('description'),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'image_path' => $imagePath,
            'storytelling_data' => $storyJson ?: $eatery->storytelling_data,
            'updated_at' => now(),
        ]);

        return redirect()->route('health-station.profile')->with('success', 'Cập nhật thông tin Trạm Y tế & Tọa độ Bản đồ thành công!');
    }

    /**
     * API giải mã Link Google Maps rút gọn (maps.app.goo.gl) ra Tọa độ Lat, Lng tức thì cho Frontend
     */
    public function resolveMapLink(Request $request)
    {
        $url = trim($request->input('url', ''));
        list($lat, $lng) = $this->parseGoogleMapsUrl($url);
        if ($lat !== null && $lng !== null) {
            return response()->json([
                'success' => true,
                'lat' => $lat,
                'lng' => $lng,
            ]);
        }
        return response()->json(['success' => false, 'message' => 'Không tìm thấy tọa độ']);
    }

    /**
     * Parse Google Maps URL / share link / coordinates string to extract [lat, lng]
     */
    private function parseGoogleMapsUrl($url)
    {
        if (empty($url)) return [null, null];

        if (preg_match('/^(-?\d+\.\d+)\s*,\s*(-?\d+\.\d+)$/', trim($url), $m)) {
            return [(float)$m[1], (float)$m[2]];
        }

        if (str_contains($url, 'maps.app.goo.gl') || str_contains($url, 'goo.gl/maps')) {
            try {
                $headers = @get_headers($url, 1);
                if (isset($headers['Location'])) {
                    $url = is_array($headers['Location']) ? (is_array(end($headers['Location'])) ? end(end($headers['Location'])) : end($headers['Location'])) : $headers['Location'];
                }
            } catch (\Throwable $e) {}
        }

        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }

        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }

        if (preg_match('/[?&](?:q|ll)=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }

        return [null, null];
    }

    /**
     * Danh mục Dịch vụ Y tế & Kỹ thuật
     */
    public function services()
    {
        $context = $this->getHealthStationContext();
        $eatery = $context['eatery'];
        $db = $context['db'];

        $services = $db->table('wellness_services')
            ->where('eatery_id', $eatery->id)
            ->orderBy('id', 'asc')
            ->get();

        return view('health-station.services', [
            'eatery' => $eatery,
            'services' => $services,
            'user' => $context['user'],
        ]);
    }

    /**
     * Thêm Dịch vụ Y tế mới
     */
    public function storeService(Request $request)
    {
        $context = $this->getHealthStationContext();
        $eatery = $context['eatery'];
        $db = $context['db'];

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $db->table('wellness_services')->insert([
            'eatery_id' => $eatery->id,
            'name' => $request->input('name'),
            'price' => $request->input('price') ?: 'Khám BHYT / Miễn phí',
            'description' => $request->input('description'),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('health-station.services')->with('success', 'Thêm dịch vụ y tế mới thành công!');
    }

    /**
     * Cập nhật Dịch vụ Y tế
     */
    public function updateService(Request $request, $id)
    {
        $context = $this->getHealthStationContext();
        $eatery = $context['eatery'];
        $db = $context['db'];

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $db->table('wellness_services')
            ->where('id', $id)
            ->where('eatery_id', $eatery->id)
            ->update([
                'name' => $request->input('name'),
                'price' => $request->input('price') ?: 'Khám BHYT / Miễn phí',
                'description' => $request->input('description'),
                'updated_at' => now(),
            ]);

        return redirect()->route('health-station.services')->with('success', 'Cập nhật dịch vụ y tế thành công!');
    }

    /**
     * Xóa Dịch vụ Y tế
     */
    public function destroyService($id)
    {
        $context = $this->getHealthStationContext();
        $eatery = $context['eatery'];
        $db = $context['db'];

        $db->table('wellness_services')
            ->where('id', $id)
            ->where('eatery_id', $eatery->id)
            ->delete();

        return redirect()->route('health-station.services')->with('success', 'Đã xóa dịch vụ y tế!');
    }

    /**
     * Quản lý Đội ngũ Bác sĩ & Lịch trực
     */
    public function doctors()
    {
        $context = $this->getHealthStationContext();
        $eatery = $context['eatery'];
        $db = $context['db'];

        $doctors = $db->table('wellness_doctors')
            ->where('eatery_id', $eatery->id)
            ->orderBy('id', 'asc')
            ->get();

        return view('health-station.doctors', [
            'eatery' => $eatery,
            'doctors' => $doctors,
            'user' => $context['user'],
        ]);
    }

    /**
     * Thêm Bác sĩ / Cán bộ Y tế
     */
    public function storeDoctor(Request $request)
    {
        $context = $this->getHealthStationContext();
        $eatery = $context['eatery'];
        $db = $context['db'];

        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'duty_schedule' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'avatar' => 'nullable|string',
        ]);

        $db->table('wellness_doctors')->insert([
            'eatery_id' => $eatery->id,
            'name' => $request->input('name'),
            'title' => $request->input('title') ?: 'Cán bộ Y tế',
            'specialty' => $request->input('specialty') ?: 'Khám Chữa Bệnh BHYT',
            'duty_schedule' => $request->input('duty_schedule') ?: 'Giờ hành chính & Trực ban',
            'phone' => $request->input('phone') ?: $eatery->phone,
            'avatar' => $request->input('avatar') ?: 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=400&q=80',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('health-station.doctors')->with('success', 'Thêm bác sĩ / cán bộ y tế thành công!');
    }

    /**
     * Cập nhật Bác sĩ / Cán bộ Y tế
     */
    public function updateDoctor(Request $request, $id)
    {
        $context = $this->getHealthStationContext();
        $eatery = $context['eatery'];
        $db = $context['db'];

        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'duty_schedule' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'avatar' => 'nullable|string',
        ]);

        $db->table('wellness_doctors')
            ->where('id', $id)
            ->where('eatery_id', $eatery->id)
            ->update([
                'name' => $request->input('name'),
                'title' => $request->input('title'),
                'specialty' => $request->input('specialty'),
                'duty_schedule' => $request->input('duty_schedule'),
                'phone' => $request->input('phone'),
                'avatar' => $request->input('avatar'),
                'updated_at' => now(),
            ]);

        return redirect()->route('health-station.doctors')->with('success', 'Cập nhật thông tin cán bộ y tế thành công!');
    }

    /**
     * Xóa Bác sĩ / Cán bộ Y tế
     */
    public function destroyDoctor($id)
    {
        $context = $this->getHealthStationContext();
        $eatery = $context['eatery'];
        $db = $context['db'];

        $db->table('wellness_doctors')
            ->where('id', $id)
            ->where('eatery_id', $eatery->id)
            ->delete();

        return redirect()->route('health-station.doctors')->with('success', 'Đã xóa bác sĩ khỏi danh sách!');
    }
}
