<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Nap toan bo 1.184 Ho Kinh Doanh & Doanh Nghiep Dong Anh tu file JSON
     * Dong bo 100% giua Database Local va Production:
     * - Tao / Cap nhat tai khoan User (role: seller, phone, password: 12345678)
     * - Tao / Cap nhat Co so kinh doanh Eatery (category: co-so-kinh-doanh)
     * - Gan dung toa do GPS, Nganh nghe (description), MST (tax_code trong storytelling_data)
     * - Lien ket 2 chieu: eateries.user_id = user.id VA users.eatery_id = eatery.id
     */
    public function up(): void
    {
        $jsonPath = database_path('data/hkd_with_phones.json');
        if (!file_exists($jsonPath)) {
            \Illuminate\Support\Facades\Log::warning("[Migration HKD] File hkd_with_phones.json khong ton tai!");
            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $hkdList = json_decode($jsonContent, true);
        if (!is_array($hkdList) || empty($hkdList)) {
            return;
        }

        // 1. Lay hoac tao Category 'co-so-kinh-doanh'
        $cat = DB::table('categories')->where('slug', 'co-so-kinh-doanh')->first();
        if (!$cat) {
            $catId = DB::table('categories')->insertGetId([
                'name'        => 'Cơ sở kinh doanh, Doanh nghiệp',
                'slug'        => 'co-so-kinh-doanh',
                'icon'        => '🏪',
                'description' => 'Cơ sở kinh doanh độc lập, cửa hàng, siêu thị mini, doanh nghiệp và dịch vụ bán lẻ trên địa bàn.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } else {
            $catId = $cat->id;
            DB::table('categories')->where('id', $catId)->update([
                'name'        => 'Cơ sở kinh doanh, Doanh nghiệp',
                'icon'        => '🏪',
                'updated_at'  => now(),
            ]);
        }

        $communes = DB::table('communes')->get();
        $defaultCommuneId = $communes->first()?->id ?? 1;

        foreach ($hkdList as $item) {
            $rawPhone = preg_replace('/[^0-9]/', '', $item['phone'] ?? '');
            if (empty($rawPhone) || strlen($rawPhone) < 8) {
                continue;
            }

            // Match commune tu dia chi
            $communeId = $defaultCommuneId;
            foreach ($communes as $c) {
                if (mb_stripos($item['address'] ?? '', $c->name) !== false) {
                    $communeId = $c->id;
                    break;
                }
            }

            // 2. Tao hoac Cap nhat User
            $user = DB::table('users')->where('phone', $rawPhone)->orWhere('username', $rawPhone)->first();
            if (!$user) {
                $userId = DB::table('users')->insertGetId([
                    'name'         => $item['name'],
                    'username'     => $rawPhone,
                    'email'        => null,
                    'phone'        => $rawPhone,
                    'password'     => Hash::make('12345678'),
                    'role'         => 'seller',
                    'status'       => 'active',
                    'is_verified'  => 1,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            } else {
                $userId = $user->id;
                if ($user->role !== 'admin') {
                    DB::table('users')->where('id', $userId)->update([
                        'status'      => 'active',
                        'is_verified' => 1,
                        'updated_at'  => now(),
                    ]);
                }
            }

            // 3. Chuan bi storytelling_data
            $storyData = json_encode([
                'tax_code'      => $item['mst'] ?? null,
                'business_type' => $item['section_type'] ?? 'Hộ kinh doanh',
                'stt'           => $item['stt'] ?? null,
            ], JSON_UNESCAPED_UNICODE);

            $descText = $item['industry'] ?: 'Cơ sở kinh doanh trên địa bàn xã Đông Anh';
            $lat = !empty($item['latitude']) ? (float)$item['latitude'] : 21.1352;
            $lng = !empty($item['longitude']) ? (float)$item['longitude'] : 105.8458;
            $rating = !empty($item['rating']) ? (float)$item['rating'] : 5.0;
            $priceRange = $item['price_range'] ?: 'Liên hệ';

            // 4. Tao hoac Cap nhat Co so kinh doanh (Eatery)
            $existing = DB::table('eateries')
                ->where('category_id', $catId)
                ->where(function($q) use ($rawPhone, $userId) {
                    $q->where('phone', $rawPhone)->orWhere('user_id', $userId);
                })
                ->first();

            if (!$existing) {
                $baseSlug = Str::slug($item['name']) ?: ('hkd-' . $rawPhone);
                $slug = $baseSlug . '-' . strtolower(Str::random(5));

                $eateryId = DB::table('eateries')->insertGetId([
                    'user_id'           => $userId,
                    'name'              => $item['name'],
                    'slug'              => $slug,
                    'category_id'       => $catId,
                    'commune_id'        => $communeId,
                    'address'           => $item['address'],
                    'phone'             => $rawPhone,
                    'description'       => $descText,
                    'price_range'       => $priceRange,
                    'status'            => 'active',
                    'is_featured'       => 0,
                    'rating'            => $rating,
                    'latitude'          => $lat,
                    'longitude'         => $lng,
                    'storytelling_data' => $storyData,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            } else {
                $eateryId = $existing->id;
                DB::table('eateries')->where('id', $eateryId)->update([
                    'user_id'           => $userId,
                    'name'              => $item['name'],
                    'category_id'       => $catId,
                    'commune_id'        => $communeId,
                    'address'           => $item['address'] ?: $existing->address,
                    'phone'             => $rawPhone,
                    'description'       => $descText,
                    'price_range'       => $priceRange,
                    'rating'            => $rating,
                    'latitude'          => $lat,
                    'longitude'         => $lng,
                    'storytelling_data' => $storyData,
                    'updated_at'        => now(),
                ]);
            }

            // 5. QUAN TRONG: Cap nhat lien ket nguoc users.eatery_id tro chinh xac vao Co so nay
            DB::table('users')->where('id', $userId)->update([
                'eatery_id' => $eateryId,
            ]);
        }
    }

    public function down(): void
    {
        $cat = DB::table('categories')->where('slug', 'co-so-kinh-doanh')->first();
        if ($cat) {
            DB::table('eateries')->where('category_id', $cat->id)->delete();
        }
    }
};
