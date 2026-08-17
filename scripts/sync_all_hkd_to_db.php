<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$startTime = microtime(true);

echo "=== SYNCING ALL HKD AND ENTERPRISES TO DB ===\n";

// 1. Ensure Category exists
$category = Category::firstOrCreate(
    ['slug' => 'co-so-kinh-doanh'],
    [
        'name'        => 'Cơ sở kinh doanh, Doanh nghiệp',
        'icon'        => '🏪',
        'description' => 'Cơ sở kinh doanh độc lập, cửa hàng, siêu thị mini, doanh nghiệp và dịch vụ bán lẻ trên địa bàn.',
    ]
);

// 2. Load communes & default commune (selecting only id and name)
$communes = Commune::select('id', 'name')->get();
$defaultCommune = $communes->first();

// 3. Load input dataset
$jsonPath = database_path('data/hkd_with_phones.json');
if (!file_exists($jsonPath)) {
    echo "ERROR: hkd_with_phones.json not found!\n";
    exit(1);
}

$hkdList = json_decode(file_get_contents($jsonPath), true);
if (!is_array($hkdList)) {
    echo "ERROR: Could not parse hkd_with_phones.json!\n";
    exit(1);
}

echo "Total items in JSON: " . count($hkdList) . "\n";

/**
 * Standard Phone Normalization Function
 */
function cleanPhone(?string $phone): ?string
{
    if (!$phone) {
        return null;
    }
    $s = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($s, '84') && strlen($s) >= 11) {
        $s = '0' . substr($s, 2);
    } elseif (!str_starts_with($s, '0') && strlen($s) >= 9 && strlen($s) <= 10) {
        $s = '0' . $s;
    }
    return (strlen($s) >= 9 && strlen($s) <= 11) ? $s : null;
}

// 4. Pre-fetch existing Users & Eateries selecting ONLY required columns in a single DB query
$users = User::select('id', 'username', 'phone', 'role')->get();
$existingUsersByPhone = $users->whereNotNull('phone')->keyBy('phone');
$existingUsersByUsername = $users->whereNotNull('username')->keyBy('username');

$existingEateriesByPhone = Eatery::select('id', 'phone', 'address', 'description', 'storytelling_data')
    ->whereNotNull('phone')
    ->get()
    ->keyBy('phone');


$defaultPassword = Hash::make('12345678');
$createdUsers = 0;
$updatedUsers = 0;
$createdEateries = 0;
$updatedEateries = 0;

DB::transaction(function () use (
    $hkdList,
    $communes,
    $defaultCommune,
    $category,
    $defaultPassword,
    &$existingUsersByPhone,
    &$existingUsersByUsername,
    &$existingEateriesByPhone,
    &$createdUsers,
    &$updatedUsers,
    &$createdEateries,
    &$updatedEateries
) {
    foreach ($hkdList as $item) {
        $rawPhone = cleanPhone($item['phone'] ?? null);
        if (!$rawPhone) {
            continue;
        }

        // Determine commune ID by matching address
        $communeId = $defaultCommune ? $defaultCommune->id : null;
        if (!empty($item['address'])) {
            foreach ($communes as $c) {
                if (mb_stripos($item['address'], $c->name) !== false) {
                    $communeId = $c->id;
                    break;
                }
            }
        }

        $username = $rawPhone;
        
        // Find existing user in memory
        $user = $existingUsersByPhone->get($rawPhone) ?? $existingUsersByUsername->get($username);

        if (!$user) {
            $user = User::create([
                'name'         => $item['name'] ?? 'Hộ kinh doanh',
                'username'     => $username,
                'email'        => null,
                'phone'        => $rawPhone,
                'password'     => $defaultPassword,
                'role'         => 'seller',
                'status'       => 'active',
                'is_verified'  => true,
            ]);
            $existingUsersByPhone->put($rawPhone, $user);
            $existingUsersByUsername->put($username, $user);
            $createdUsers++;
        } else {
            if ($user->role !== 'admin') {
                $user->update([
                    'status'      => 'active',
                    'is_verified' => true,
                ]);
            }
            $updatedUsers++;
        }

        // Find existing eatery in memory
        $eatery = $existingEateriesByPhone->get($rawPhone);
        
        if (!$eatery) {
            $baseSlug = Str::slug($item['name'] ?? '');
            if (empty($baseSlug)) {
                $baseSlug = 'hkd-' . $rawPhone;
            }
            $slug = $baseSlug . '-' . strtolower(Str::random(5));

            $eatery = Eatery::create([
                'user_id'           => $user->id,
                'name'              => $item['name'] ?? 'Hộ kinh doanh',
                'slug'              => $slug,
                'category_id'       => $category->id,
                'commune_id'        => $communeId,
                'address'           => $item['address'] ?? '',
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
            $existingEateriesByPhone->put($rawPhone, $eatery);
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
                'address'           => !empty($item['address']) ? $item['address'] : $eatery->address,
                'description'       => !empty($item['industry']) ? $item['industry'] : $eatery->description,
                'storytelling_data' => $storyData,
            ]);
            $updatedEateries++;
        }
    }
});

$elapsed = round(microtime(true) - $startTime, 2);

echo "=== SYNC RESULT ===\n";
echo "Created Users: $createdUsers\n";
echo "Updated Users: $updatedUsers\n";
echo "Created Eateries: $createdEateries\n";
echo "Updated Eateries: $updatedEateries\n";
echo "Total in Category 'co-so-kinh-doanh': " . Eatery::where('category_id', $category->id)->count() . "\n";
echo "Total Users in DB: " . User::count() . "\n";
echo "Execution Time: {$elapsed}s\n";

