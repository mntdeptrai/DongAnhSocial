<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Lấy hoặc tạo Category 'co-so-kinh-doanh' (Cơ sở kinh doanh, Doanh nghiệp)
        $category = Category::where('slug', 'co-so-kinh-doanh')->first();
        if (!$category) {
            $category = Category::create([
                'name'        => 'Cơ sở kinh doanh, Doanh nghiệp',
                'slug'        => 'co-so-kinh-doanh',
                'icon'        => '🏪',
                'description' => 'Cơ sở kinh doanh độc lập, cửa hàng, siêu thị mini, doanh nghiệp và dịch vụ bán lẻ trên địa bàn.',
            ]);
        }

        // 2. Lấy danh sách các xã / địa bàn
        $communes = Commune::all();
        $defaultCommune = $communes->first();

        // 3. Đọc dữ liệu từ file JSON hkd_with_phones.json
        $jsonPath = database_path('data/hkd_with_phones.json');
        if (!file_exists($jsonPath)) {
            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $hkdList = json_decode($jsonContent, true);

        if (!is_array($hkdList)) {
            return;
        }

        foreach ($hkdList as $item) {
            $rawPhone = preg_replace('/[^0-9]/', '', $item['phone'] ?? '');
            if (empty($rawPhone) || strlen($rawPhone) < 8) {
                continue;
            }

            // Tìm xã tương ứng từ địa chỉ
            $communeId = $defaultCommune ? $defaultCommune->id : null;
            foreach ($communes as $c) {
                if (mb_stripos($item['address'], $c->name) !== false) {
                    $communeId = $c->id;
                    break;
                }
            }

            // Tạo hoặc cập nhật tài khoản User (role: seller)
            $username = $rawPhone;
            $user = User::where('phone', $rawPhone)->orWhere('username', $username)->first();

            if (!$user) {
                $user = User::create([
                    'name'         => $item['name'],
                    'username'     => $username,
                    'email'        => null,
                    'phone'        => $rawPhone,
                    'password'     => Hash::make('12345678'),
                    'role'         => 'seller',
                    'status'       => 'active',
                    'is_verified'  => true,
                ]);
            } else {
                $user->update([
                    'name'        => $item['name'] ?: $user->name,
                    'role'        => 'seller',
                    'status'      => 'active',
                    'is_verified' => true,
                ]);
            }

            // Tạo hoặc cập nhật Eatery cho Hộ kinh doanh
            $eatery = Eatery::where('phone', $rawPhone)->first();
            if (!$eatery) {
                $baseSlug = Str::slug($item['name']);
                if (empty($baseSlug)) {
                    $baseSlug = 'hkd-' . $rawPhone;
                }
                $slug = $baseSlug . '-' . strtolower(Str::random(5));

                $eatery = Eatery::create([
                    'user_id'           => $user->id,
                    'name'              => $item['name'],
                    'slug'              => $slug,
                    'category_id'       => $category->id,
                    'commune_id'        => $communeId,
                    'address'           => $item['address'],
                    'phone'             => $rawPhone,
                    'description'       => $item['industry'] ?? 'Hộ kinh doanh trên địa bàn xã Đông Anh',
                    'price_range'       => 'Liên hệ',
                    'status'            => 'active',
                    'is_featured'       => false,
                    'latitude'          => 21.1352,
                    'longitude'         => 105.8458,
                    'storytelling_data' => [
                        'tax_code'      => $item['mst'] ?? null,
                        'business_type' => 'Hộ kinh doanh',
                        'stt'           => $item['stt'] ?? null,
                    ],
                ]);
            } else {
                $eatery->update([
                    'user_id'     => $user->id,
                    'category_id' => $category->id,
                    'address'     => $item['address'] ?: $eatery->address,
                    'description' => $item['industry'] ?: $eatery->description,
                ]);
            }

            // Gán eatery_id vào User
            $user->update(['eatery_id' => $eatery->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback
    }
};
