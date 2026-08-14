<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

echo "=== SYNCING ALL HKD AND ENTERPRISES TO DB ===\n";

$category = Category::where('slug', 'co-so-kinh-doanh')->first();
if (!$category) {
    $category = Category::create([
        'name'        => 'Cơ sở kinh doanh, Doanh nghiệp',
        'slug'        => 'co-so-kinh-doanh',
        'icon'        => '🏪',
        'description' => 'Cơ sở kinh doanh độc lập, cửa hàng, siêu thị mini, doanh nghiệp và dịch vụ bán lẻ trên địa bàn.',
    ]);
    echo "Created category 'co-so-kinh-doanh'\n";
}

$communes = Commune::all();
$defaultCommune = $communes->first();

$jsonPath = database_path('data/hkd_with_phones.json');
if (!file_exists($jsonPath)) {
    echo "ERROR: hkd_with_phones.json not found!\n";
    exit(1);
}

$hkdList = json_decode(file_get_contents($jsonPath), true);
echo "Total items in JSON: " . count($hkdList) . "\n";

$createdUsers = 0;
$updatedUsers = 0;
$createdEateries = 0;
$updatedEateries = 0;

foreach ($hkdList as $item) {
    $rawPhone = preg_replace('/[^0-9]/', '', $item['phone'] ?? '');
    if (empty($rawPhone) || strlen($rawPhone) < 8) {
        continue;
    }

    $communeId = $defaultCommune ? $defaultCommune->id : null;
    foreach ($communes as $c) {
        if (mb_stripos($item['address'], $c->name) !== false) {
            $communeId = $c->id;
            break;
        }
    }

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
        $createdUsers++;
    } else {
        // Keep existing user account intact, ensuring seller status
        if ($user->role !== 'admin') {
            $user->update([
                'status'      => 'active',
                'is_verified' => true,
            ]);
        }
        $updatedUsers++;
    }

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
            'description'       => $item['industry'] ?? 'Cơ sở kinh doanh trên địa bàn xã Đông Anh',
            'price_range'       => 'Liên hệ',
            'status'            => 'active',
            'is_featured'       => false,
            'latitude'          => 21.1352,
            'longitude'         => 105.8458,
            'storytelling_data' => [
                'tax_code'      => $item['mst'] ?? null,
                'business_type' => 'Cơ sở kinh doanh, Doanh nghiệp',
                'stt'           => $item['stt'] ?? null,
            ],
        ]);
        $createdEateries++;
    } else {
        $storyData = is_array($eatery->storytelling_data) ? $eatery->storytelling_data : [];
        if (!empty($item['mst'])) {
            $storyData['tax_code'] = $item['mst'];
        }
        $storyData['business_type'] = 'Cơ sở kinh doanh, Doanh nghiệp';
        if (!empty($item['stt'])) {
            $storyData['stt'] = $item['stt'];
        }

        $eatery->update([
            'category_id'       => $category->id,
            'address'           => $item['address'] ?: $eatery->address,
            'description'       => $item['industry'] ?: $eatery->description,
            'storytelling_data' => $storyData,
        ]);
        $updatedEateries++;
    }
}

echo "=== SYNC RESULT ===\n";
echo "Created Users: $createdUsers\n";
echo "Updated Users: $updatedUsers\n";
echo "Created Eateries: $createdEateries\n";
echo "Updated Eateries: $updatedEateries\n";
echo "Total in Category 'co-so-kinh-doanh': " . Eatery::where('category_id', $category->id)->count() . "\n";
echo "Total Users in DB: " . User::count() . "\n";
