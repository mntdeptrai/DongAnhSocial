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
    protected $description = 'Import new enterprises from database/data/doanh_nghiep_with_phones.json without touching existing ones';

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
        $createdEateries = 0;
        $skippedEateries = 0;

        foreach ($items as $item) {
            $phone = trim($item['phone'] ?? '');
            $name = trim($item['name'] ?? '');

            if (empty($phone) && empty($name)) {
                continue;
            }

            // Check existing user by phone
            $user = null;
            if (!empty($phone)) {
                $user = User::where('phone', $phone)->first();
            }
            if (!$user) {
                $userPhone = $phone ?: ('09' . rand(10000000, 99999999));
                $user = User::create([
                    'name' => $name ?: 'Chủ Doanh Nghiệp',
                    'email' => ($phone ?: Str::slug($name)) . '@donganh.gov.vn',
                    'phone' => $userPhone,
                    'password' => bcrypt('12345678'),
                    'role' => 'dn',
                    'is_active' => true,
                    'is_verified' => true,
                ]);
                $createdUsers++;
            }

            // Check existing eatery by phone OR name
            $existingEatery = null;
            if (!empty($phone)) {
                $existingEatery = Eatery::where('phone', $phone)->first();
            }
            if (!$existingEatery && !empty($name)) {
                $existingEatery = Eatery::where('name', $name)->first();
            }

            if ($existingEatery) {
                // DO NOT overwrite existing enterprises as requested by user
                $skippedEateries++;
                continue;
            }

            // Create new eatery
            $baseSlug = Str::slug($name);
            if (empty($baseSlug)) {
                $baseSlug = 'dn-' . rand(1000, 9999);
            }
            $slug = $baseSlug . '-' . strtolower(Str::random(5));

            $storyData = [
                'tax_code' => $item['mst'] ?? '',
                'mst' => $item['mst'] ?? '',
                'owner_name' => '',
                'industry' => $item['industry'] ?? 'Cơ sở kinh doanh, Doanh nghiệp',
            ];

            Eatery::create([
                'user_id' => $user->id,
                'name' => $name ?: 'Doanh Nghiệp',
                'slug' => $slug,
                'category_id' => $category->id,
                'commune_id' => $communeId,
                'address' => $item['address'] ?? '',
                'phone' => $phone,
                'description' => $item['industry'] ?? 'Doanh nghiệp trên địa bàn xã Đông Anh',
                'price_range' => $item['price_range'] ?? 'Liên hệ',
                'status' => 'active',
                'is_featured' => false,
                'latitude' => $item['latitude'] ?? 21.1352,
                'longitude' => $item['longitude'] ?? 105.8458,
                'storytelling_data' => $storyData,
            ]);

            $createdEateries++;
            $this->info(" + Added new: {$name}");
        }

        $this->info("Import finished!");
        $this->info(" - Created Users: {$createdUsers}");
        $this->info(" - Created New Enterprises: {$createdEateries}");
        $this->info(" - Preserved (Skipped Existing): {$skippedEateries}");

        return 0;
    }
}
