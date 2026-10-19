<?php

namespace App\Services;

use App\Contracts\HealthStationServiceInterface;
use App\Helpers\R2Helper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthStationService implements HealthStationServiceInterface
{
    /**
     * Resolve health station context for authenticated user.
     */
    public function getHealthStationContext(?User $user): array
    {
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập để truy cập Cổng Quản lý Trạm Y tế.');
        }

        $db = DB::connection('mysql_market');

        $wellnessCat = $db->table('categories')->where('slug', 'wellness-care')->first();
        $catId = $wellnessCat ? $wellnessCat->id : null;

        $eatery = null;
        if ($user->eatery_id) {
            $eatery = $db->table('eateries')->where('id', $user->eatery_id)->where('category_id', $catId)->first();
        }
        if (!$eatery) {
            $eatery = $db->table('eateries')->where('user_id', $user->id)->where('category_id', $catId)->first();
        }
        if (!$eatery && !empty($user->phone)) {
            $eatery = $db->table('eateries')->where('phone', $user->phone)->where('category_id', $catId)->first();
        }

        // Admin/manager default fallback
        if (!$eatery && in_array($user->role, ['admin', 'manager'])) {
            $eatery = $db->table('eateries')->where('slug', 'tram-y-te-xa-dong-anh')->first();
        }

        if (!$eatery) {
            if (in_array($user->role, ['seller', 'hkd', 'dn', 'business'])) {
                redirect()->route('hkd.dashboard')->send();
                exit;
            }
            abort(403, 'Tài khoản của bạn chưa liên kết với Cơ sở Y tế nào trên hệ thống.');
        }

        return [
            'user'   => $user,
            'eatery' => $eatery,
            'db'     => $db,
        ];
    }

    /**
     * Get dashboard view data for the health station.
     */
    public function getDashboardData(array $context): array
    {
        $eatery = $context['eatery'];
        $db = $context['db'];

        $services = $db->table('wellness_services')
            ->where('eatery_id', $eatery->id)
            ->orderBy('id', 'asc')
            ->get();

        $doctors = $db->table('wellness_doctors')
            ->where('eatery_id', $eatery->id)
            ->where('status', 'active')
            ->orderBy('id', 'asc')
            ->get();

        $rawStory = $eatery->storytelling_data ?? $eatery->storytelling_json ?? null;
        $storytelling = [];
        if (!empty($rawStory)) {
            $storytelling = is_array($rawStory) ? $rawStory : (json_decode($rawStory, true) ?: []);
        }

        return [
            'eatery'       => $eatery,
            'services'     => $services,
            'doctors'      => $doctors,
            'storytelling' => $storytelling,
            'user'         => $context['user'],
        ];
    }

    /**
     * Get profile view data including photos and storytelling.
     */
    public function getProfileData(array $context): array
    {
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

        return [
            'eatery'       => $eatery,
            'storytelling' => $storytelling,
            'photos'       => $photos,
            'user'         => $context['user'],
        ];
    }

    /**
     * Update health station profile, coordinates, photos, and storytelling data.
     */
    public function updateProfile(array $context, array $validated, Request $request): bool
    {
        $eatery = $context['eatery'];
        $db = $context['db'];

        $imagePath = $eatery->image_path;

        if ($request->hasFile('image')) {
            $uploaded = R2Helper::upload($request->file('image'), 'health-stations');
            if ($uploaded) {
                $imagePath = $uploaded;
            }
        } elseif ($request->filled('image_url')) {
            $imagePath = trim($request->input('image_url'));
        }

        $latitude = $request->input('latitude') !== null && $request->input('latitude') !== '' ? (float)$request->input('latitude') : $eatery->latitude;
        $longitude = $request->input('longitude') !== null && $request->input('longitude') !== '' ? (float)$request->input('longitude') : $eatery->longitude;

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

        if ($request->has('delete_photo_ids') && is_array($request->input('delete_photo_ids'))) {
            $db->table('eatery_photos')
                ->where('eatery_id', $eatery->id)
                ->whereIn('id', $request->input('delete_photo_ids'))
                ->delete();
        }

        $maxSort = (int) ($db->table('eatery_photos')->where('eatery_id', $eatery->id)->max('sort_order') ?? 0);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $uploaded = R2Helper::upload($file, 'health-stations');
                    if ($uploaded) {
                        $maxSort++;
                        $db->table('eatery_photos')->insert([
                            'eatery_id'   => $eatery->id,
                            'image_path'  => $uploaded,
                            'caption'     => 'Hình ảnh ' . $request->input('name'),
                            'sort_order'  => $maxSort,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                        if (empty($imagePath)) {
                            $imagePath = $uploaded;
                        }
                    }
                }
            }
        }

        if ($request->has('image_urls') && is_array($request->input('image_urls'))) {
            foreach ($request->input('image_urls') as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $maxSort++;
                    $db->table('eatery_photos')->insert([
                        'eatery_id'   => $eatery->id,
                        'image_path'  => $url,
                        'caption'     => 'Hình ảnh ' . $request->input('name'),
                        'sort_order'  => $maxSort,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    if (empty($imagePath)) {
                        $imagePath = $url;
                    }
                }
            }
        }

        if (!empty($imagePath)) {
            $existsInPhotos = $db->table('eatery_photos')
                ->where('eatery_id', $eatery->id)
                ->where('image_path', $imagePath)
                ->exists();

            if (!$existsInPhotos) {
                $db->table('eatery_photos')->insert([
                    'eatery_id'   => $eatery->id,
                    'image_path'  => $imagePath,
                    'caption'     => 'Ảnh đại diện chính - ' . $request->input('name'),
                    'sort_order'  => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        if (empty($imagePath)) {
            $firstPhoto = $db->table('eatery_photos')
                ->where('eatery_id', $eatery->id)
                ->orderBy('sort_order', 'asc')
                ->first();
            if ($firstPhoto) {
                $imagePath = $firstPhoto->image_path;
            }
        }

        if ($request->has('area')) $storytelling['area'] = $request->input('area');
        if ($request->has('staff_count')) $storytelling['staff_count'] = $request->input('staff_count');
        if ($request->has('staff_total')) $storytelling['staff_total'] = $request->input('staff_total');
        if ($request->has('heritage_year')) $storytelling['heritage_year'] = $request->input('heritage_year');

        $storyJson = !empty($storytelling) ? json_encode($storytelling, JSON_UNESCAPED_UNICODE) : null;

        $db->table('eateries')->where('id', $eatery->id)->update([
            'name'              => $request->input('name'),
            'phone'             => $request->input('phone'),
            'address'           => $request->input('address'),
            'opening_hours'     => $request->input('opening_hours'),
            'price_range'       => $request->input('price_range'),
            'description'       => $request->input('description'),
            'latitude'          => $latitude,
            'longitude'         => $longitude,
            'image_path'        => $imagePath,
            'storytelling_data' => $storyJson ?: $eatery->storytelling_data,
            'updated_at'        => now(),
        ]);

        return true;
    }

    /**
     * Parse Google Maps URL / share link / coordinate string into [latitude, longitude].
     */
    public function parseGoogleMapsUrl(?string $url): array
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
     * Get services list for the health station.
     */
    public function getServices(array $context)
    {
        return $context['db']->table('wellness_services')
            ->where('eatery_id', $context['eatery']->id)
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Store new health service.
     */
    public function storeService(array $context, array $data): bool
    {
        $context['db']->table('wellness_services')->insert([
            'eatery_id'   => $context['eatery']->id,
            'name'        => $data['name'],
            'price'       => $data['price'] ?: 'Khám BHYT / Miễn phí',
            'description' => $data['description'] ?? null,
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return true;
    }

    /**
     * Update existing health service.
     */
    public function updateService(array $context, int|string $id, array $data): bool
    {
        $context['db']->table('wellness_services')
            ->where('id', $id)
            ->where('eatery_id', $context['eatery']->id)
            ->update([
                'name'        => $data['name'],
                'price'       => $data['price'] ?: 'Khám BHYT / Miễn phí',
                'description' => $data['description'] ?? null,
                'updated_at'  => now(),
            ]);

        return true;
    }

    /**
     * Delete health service.
     */
    public function deleteService(array $context, int|string $id): bool
    {
        $context['db']->table('wellness_services')
            ->where('id', $id)
            ->where('eatery_id', $context['eatery']->id)
            ->delete();

        return true;
    }

    /**
     * Get doctors list for the health station.
     */
    public function getDoctors(array $context)
    {
        return $context['db']->table('wellness_doctors')
            ->where('eatery_id', $context['eatery']->id)
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Store new doctor / medical staff.
     */
    public function storeDoctor(array $context, array $data): bool
    {
        $context['db']->table('wellness_doctors')->insert([
            'eatery_id'     => $context['eatery']->id,
            'name'          => $data['name'],
            'title'         => $data['title'] ?: 'Cán bộ Y tế',
            'specialty'     => $data['specialty'] ?: 'Khám Chữa Bệnh BHYT',
            'duty_schedule' => $data['duty_schedule'] ?: 'Giờ hành chính & Trực ban',
            'phone'         => $data['phone'] ?: ($context['eatery']->phone ?? ''),
            'avatar'        => $data['avatar'] ?: 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=400&q=80',
            'status'        => 'active',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return true;
    }

    /**
     * Update existing doctor / medical staff.
     */
    public function updateDoctor(array $context, int|string $id, array $data): bool
    {
        $context['db']->table('wellness_doctors')
            ->where('id', $id)
            ->where('eatery_id', $context['eatery']->id)
            ->update([
                'name'          => $data['name'],
                'title'         => $data['title'] ?? null,
                'specialty'     => $data['specialty'] ?? null,
                'duty_schedule' => $data['duty_schedule'] ?? null,
                'phone'         => $data['phone'] ?? null,
                'avatar'        => $data['avatar'] ?? null,
                'updated_at'    => now(),
            ]);

        return true;
    }

    /**
     * Delete doctor / medical staff.
     */
    public function deleteDoctor(array $context, int|string $id): bool
    {
        $context['db']->table('wellness_doctors')
            ->where('id', $id)
            ->where('eatery_id', $context['eatery']->id)
            ->delete();

        return true;
    }
}
