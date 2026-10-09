<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Eatery;
use App\Models\Category;
use App\Models\Commune;
use Illuminate\Support\Str;

class ImportDoanhNghiep extends Command
{
    protected $signature = 'doanh-nghiep:import';
    protected $description = 'Import / Update enterprises from database/data/doanh_nghiep_with_phones.json using MST (Tax Code) as unique key';

    public function handle()
    {
        $jsonPath = database_path('data/doanh_nghiep_with_phones.json');
        if (!file_exists($jsonPath)) {
            $this->error("File not found at: {$jsonPath}");
            return 1;
        }

        $items = json_decode(file_get_contents($jsonPath), true) ?: [];
        $this->info("Found " . count($items) . " enterprise records in JSON.");

        $category = Category::where('slug', 'co-so-kinh-doanh')->first();
        if (!$category) {
            $this->error("Category 'co-so-kinh-doanh' not found in database.");
            return 1;
        }

        $commune = Commune::where('name', 'LIKE', '%Đông Anh%')->first() 
            ?? Commune::first();
        $communeId = $commune ? $commune->id : 1;

        $createdUsers = 0;
        $updatedUsers = 0;
        $createdEateries = 0;
        $updatedEateries = 0;

        foreach ($items as $item) {
            $mst = trim($item['mst'] ?? '');
            $phone = trim($item['phone'] ?? '');
            $name = trim($item['name'] ?? '');
            $address = trim($item['address'] ?? '');
            $industry = trim($item['industry'] ?? 'Cơ sở kinh doanh, Doanh nghiệp');

            if (empty($mst) && empty($phone) && empty($name)) {
                continue;
            }

            // 1. Find existing Eatery (prefer match by MST, then by Phone, then by Name)
            $existingEatery = null;
            if (!empty($mst)) {
                $existingEatery = Eatery::where('storytelling_data->mst', $mst)
                    ->orWhere('storytelling_data->tax_code', $mst)
                    ->first();
            }
            if (!$existingEatery && !empty($phone)) {
                $existingEatery = Eatery::where('phone', $phone)->first();
            }
            if (!$existingEatery && !empty($name)) {
                $existingEatery = Eatery::where('name', $name)->first();
            }

            // 2. Find or Create User
            $user = null;
            if ($existingEatery && $existingEatery->user_id) {
                $user = User::find($existingEatery->user_id);
            }
            if (!$user && !empty($mst)) {
                $user = User::where('username', $mst)
                    ->orWhere('email', "{$mst}@donganh.gov.vn")
                    ->first();
            }
            if (!$user && !empty($phone)) {
                $user = User::where('phone', $phone)->first();
            }

            $userEmail = ($mst ?: ($phone ?: Str::slug($name))) . '@donganh.gov.vn';

            if (!$user) {
                // Create user
                $user = User::create([
                    'name' => $name ?: 'Chủ Doanh Nghiệp',
                    'username' => $mst ?: null,
                    'email' => $userEmail,
                    'phone' => $phone ?: null,
                    'password' => bcrypt('123456'),
                    'role' => 'dn',
                    'is_active' => true,
                    'is_verified' => true,
                ]);
                $createdUsers++;
            } else {
                // Update existing user fields if new values present
                $dirtyUser = false;
                if (!empty($mst) && empty($user->username)) {
                    $user->username = $mst;
                    $dirtyUser = true;
                }
                if (!empty($phone) && (empty($user->phone) || $user->phone !== $phone)) {
                    $user->phone = $phone;
                    $dirtyUser = true;
                }
                if (!empty($name) && $user->name !== $name) {
                    $user->name = $name;
                    $dirtyUser = true;
                }
                if ($dirtyUser) {
                    $user->save();
                    $updatedUsers++;
                }
            }

            // 3. Create or Update Eatery
            if ($existingEatery) {
                // Update existing eatery
                $existingEatery->user_id = $user->id;
                if (!empty($name)) {
                    $existingEatery->name = $name;
                }
                if (!empty($address)) {
                    $existingEatery->address = $address;
                }
                if (!empty($phone)) {
                    $existingEatery->phone = $phone;
                }
                if (!empty($industry)) {
                    $existingEatery->description = $industry;
                }

                $storyData = is_array($existingEatery->storytelling_data) ? $existingEatery->storytelling_data : [];
                if (!empty($mst)) {
                    $storyData['mst'] = $mst;
                    $storyData['tax_code'] = $mst;
                }
                if (!empty($industry)) {
                    $storyData['industry'] = $industry;
                }
                $existingEatery->storytelling_data = $storyData;

                if (isset($item['latitude'])) $existingEatery->latitude = $item['latitude'];
                if (isset($item['longitude'])) $existingEatery->longitude = $item['longitude'];

                $existingEatery->save();
                $updatedEateries++;
            } else {
                // Create new eatery
                $baseSlug = Str::slug($name);
                if (empty($baseSlug)) {
                    $baseSlug = 'dn-' . rand(1000, 9999);
                }
                $slug = $baseSlug . '-' . strtolower(Str::random(5));

                $storyData = [
                    'tax_code' => $mst,
                    'mst' => $mst,
                    'owner_name' => '',
                    'industry' => $industry,
                ];

                Eatery::create([
                    'user_id' => $user->id,
                    'name' => $name ?: 'Doanh Nghiệp',
                    'slug' => $slug,
                    'category_id' => $category->id,
                    'commune_id' => $communeId,
                    'address' => $address,
                    'phone' => $phone,
                    'description' => $industry,
                    'price_range' => $item['price_range'] ?? 'Liên hệ',
                    'status' => 'active',
                    'is_featured' => false,
                    'latitude' => $item['latitude'] ?? 21.1352,
                    'longitude' => $item['longitude'] ?? 105.8458,
                    'storytelling_data' => $storyData,
                ]);

                $createdEateries++;
                $this->info(" + Added new: {$name} (MST: {$mst})");
            }
        }

        $this->info("Import finished!");
        $this->info(" - Created Users: {$createdUsers}");
        $this->info(" - Updated Users: {$updatedUsers}");
        $this->info(" - Created New Enterprises: {$createdEateries}");
        $this->info(" - Updated Enterprises: {$updatedEateries}");

        return 0;
    }
}
