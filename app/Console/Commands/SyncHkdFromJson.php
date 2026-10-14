<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Eatery;
use App\Models\Category;
use App\Models\Commune;

class SyncHkdFromJson extends Command
{
    protected $signature   = 'hkd:sync-json {--dry-run : Preview without saving}';
    protected $description = 'Upsert HKD records from hkd_with_phones.json: update existing, create new ones';

    public function handle(): int
    {
        $jsonPath = database_path('data/hkd_with_phones.json');
        if (!file_exists($jsonPath)) {
            $this->error("File not found: $jsonPath");
            return 1;
        }

        $records = json_decode(file_get_contents($jsonPath), true);
        $dry     = $this->option('dry-run');

        $updated  = 0;
        $created  = 0;
        $skipped  = 0;

        // Resolve category & commune once
        $category = Category::where('slug', 'co-so-kinh-doanh')->first()
            ?? Category::first();
        $commune  = Commune::where('name', 'LIKE', '%Đông Anh%')->first()
            ?? Commune::first();
        $communeId  = $commune  ? $commune->id  : 1;
        $categoryId = $category ? $category->id : 1;

        $this->info("Processing " . count($records) . " records from JSON...\n");

        foreach ($records as $rec) {
            $phone = trim($rec['phone'] ?? '');
            $name  = trim($rec['name']  ?? '');
            $mst   = trim($rec['mst']   ?? '');

            if (!$phone) { $skipped++; continue; }

            // ── Try to find existing eatery by MST or STT ─────────────────
            $eatery = null;
            if ($mst) {
                $eatery = DB::table('eateries')
                    ->where('storytelling_data', 'LIKE', '%"mst":"' . $mst . '"%')
                    ->first();
            }

            if (!$eatery && isset($rec['stt'])) {
                $eatery = DB::table('eateries')
                    ->where('storytelling_data', 'LIKE', '%"stt":' . $rec['stt'] . ',%')
                    ->orWhere('storytelling_data', 'LIKE', '%"stt": ' . $rec['stt'] . '%')
                    ->first();
            }

            if (!$eatery && $name && $phone) {
                $cand = DB::table('eateries')
                    ->where('name', $name)
                    ->where('phone', $phone)
                    ->first();
                if ($cand) {
                    $candStory = !empty($cand->storytelling_data) ? (json_decode($cand->storytelling_data, true) ?: []) : [];
                    $candMst = $candStory['mst'] ?? '';
                    if (empty($candMst) || $candMst === $mst) {
                        $eatery = $cand;
                    }
                }
            }

            $userId = null;
            if (!$dry) {
                $userPhone = $phone ?: ('09' . rand(10000000, 99999999));
                $userEmail = ($phone ?: (Str::slug($name) . '-' . strtolower(Str::random(6)))) . '@hkd.donganh.gov.vn';

                $user = User::firstOrCreate(
                    ['phone' => $phone],
                    [
                        'name'        => $name ?: 'Chủ Hộ Kinh Doanh',
                        'email'       => $userEmail,
                        'phone'       => $userPhone,
                        'password'    => bcrypt('12345678'),
                        'role'        => 'hkd',
                        'status'      => 'active',
                        'is_verified' => true,
                    ]
                );
                $userId = $user->id;
            }

            // ── Upsert Eatery (updateOrCreate) ───────────────────────────
            $story = [];
            if ($eatery && !empty($eatery->storytelling_data)) {
                $story = is_string($eatery->storytelling_data)
                    ? (json_decode($eatery->storytelling_data, true) ?: [])
                    : (array) $eatery->storytelling_data;
            }
            $story['stt']        = $rec['stt'] ?? ($story['stt'] ?? null);
            $story['mst']        = $mst ?: ($story['mst'] ?? '');
            $story['owner_name'] = $name ?: ($story['owner_name'] ?? '');
            $story['industry']   = $story['industry'] ?? ($rec['industry'] ?? '');

            $matchAttributes = $eatery 
                ? ['id' => $eatery->id] 
                : ['slug' => (Str::slug($name) ?: ('hkd-' . rand(1000, 9999))) . '-' . strtolower(Str::random(5))];

            $payload = [
                'user_id'           => $userId ?: ($eatery?->user_id ?? 1),
                'name'              => $name ?: ($eatery?->name ?? 'Hộ Kinh Doanh'),
                'category_id'       => $categoryId,
                'commune_id'        => $communeId,
                'address'           => $rec['address'] ?? ($eatery?->address ?? ''),
                'phone'             => $phone,
                'description'       => $rec['industry'] ?? ($eatery?->description ?? 'Hộ kinh doanh trên địa bàn xã Đông Anh'),
                'price_range'       => $rec['price_range'] ?? ($eatery?->price_range ?? 'Liên hệ'),
                'status'            => 'active',
                'is_featured'       => false,
                'latitude'          => $rec['latitude'] ?? ($eatery?->latitude ?? 21.1352),
                'longitude'         => $rec['longitude'] ?? ($eatery?->longitude ?? 105.8458),
                'storytelling_data' => $story,
            ];

            if (!$dry) {
                $eateryModel = Eatery::updateOrCreate($matchAttributes, $payload);
                if ($eateryModel->wasRecentlyCreated) {
                    $this->line("  <comment>CREATE</comment>  phone={$phone}  mst={$mst}  name={$name}");
                    $created++;
                } else {
                    $this->line("  <info>UPDATE</info>  id={$eatery->id}  phone={$phone}  mst={$mst}  name={$name}");
                    $updated++;
                }
            } else {
                if ($eatery) {
                    $this->line("  <info>UPDATE (dry)</info>  id={$eatery->id}  phone={$phone}  mst={$mst}  name={$name}");
                    $updated++;
                } else {
                    $this->line("  <comment>CREATE (dry)</comment>  phone={$phone}  mst={$mst}  name={$name}");
                    $created++;
                }
            }
        }

        $this->newLine();
        $this->info("Done. Updated={$updated}  Created={$created}  Skipped={$skipped}");
        if ($dry) $this->warn('DRY RUN — nothing was saved.');

        return 0;
    }
}
