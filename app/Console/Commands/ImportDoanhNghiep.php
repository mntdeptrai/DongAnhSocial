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

            // 2. Upsert User (giảm thiểu if-else và try-catch bằng updateOrCreate)
            $userSearch = [];
            if ($existingEatery && $existingEatery->user_id) {
                $userSearch = ['id' => $existingEatery->user_id];
            } elseif (!empty($mst)) {
                $userSearch = ['username' => $mst];
            } elseif (!empty($phone)) {
                $userSearch = ['phone' => $phone];
            } else {
                $userSearch = ['email' => $userEmail];
            }

            $user = User::updateOrCreate(
                $userSearch,
                array_filter([
                    'name'        => $name ?: 'Chủ Doanh Nghiệp',
                    'username'    => $mst ?: null,
                    'email'       => $userEmail,
                    'phone'       => $phone ?: null,
                    'role'        => 'dn',
                    'is_active'   => true,
                    'is_verified' => true,
                ], fn($val) => !is_null($val)) + ['password' => bcrypt('123456')]
            );

            if ($user->wasRecentlyCreated) {
                $createdUsers++;
            } else {
                $updatedUsers++;
            }

            // 3. Upsert Eatery
            $storyData = is_array($existingEatery?->storytelling_data) ? $existingEatery->storytelling_data : [];
            if (!empty($mst)) {
                $storyData['mst'] = $mst;
                $storyData['tax_code'] = $mst;
            }
            if (!empty($industry)) {
                $storyData['industry'] = $industry;
            }

            $eateryPayload = [
                'user_id'           => $user->id,
                'name'              => $name ?: ($existingEatery?->name ?: 'Doanh Nghiệp'),
                'category_id'       => $category->id,
                'commune_id'        => $communeId,
                'address'           => $address ?: ($existingEatery?->address ?? ''),
                'phone'             => $phone ?: ($existingEatery?->phone ?? ''),
                'description'       => $industry ?: ($existingEatery?->description ?? ''),
                'price_range'       => $item['price_range'] ?? ($existingEatery?->price_range ?? 'Liên hệ'),
                'status'            => 'active',
                'is_featured'       => false,
                'latitude'          => $item['latitude'] ?? ($existingEatery?->latitude ?? 21.1352),
                'longitude'         => $item['longitude'] ?? ($existingEatery?->longitude ?? 105.8458),
                'storytelling_data' => $storyData,
            ];

            $matchAttributes = $existingEatery 
                ? ['id' => $existingEatery->id] 
                : ['slug' => (Str::slug($name) ?: ('dn-' . rand(1000, 9999))) . '-' . strtolower(Str::random(5))];

            $eatery = Eatery::updateOrCreate($matchAttributes, $eateryPayload);

            if ($eatery->wasRecentlyCreated) {
                $createdEateries++;
                $this->info(" + Added new: {$name} (MST: {$mst})");
            } else {
                $updatedEateries++;
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
