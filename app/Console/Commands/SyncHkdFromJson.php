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

            if (!$phone && !$name) { $skipped++; continue; }

            // ── Try to find existing eatery ──────────────────────────────
            $eatery = null;
            if ($phone) {
                $phoneNorm = ltrim($phone, '0');
                $eatery = DB::table('eateries')
                    ->where(function ($q) use ($phone, $phoneNorm) {
                        $q->where('phone', $phone)
                          ->orWhere('phone', '0' . $phoneNorm)
                          ->orWhere('phone', '+84' . $phoneNorm);
                    })
                    ->first();

                // Fallback: user → eatery_id
                if (!$eatery) {
                    $user = DB::table('users')
                        ->where(function ($q) use ($phone, $phoneNorm) {
                            $q->where('phone', $phone)
                              ->orWhere('phone', '0' . $phoneNorm);
                        })
                        ->first();
                    if ($user && $user->eatery_id) {
                        $eatery = DB::table('eateries')->where('id', $user->eatery_id)->first();
                    }
                }
            }

            // ── UPDATE existing ──────────────────────────────────────────
            if ($eatery) {
                $story = [];
                if (!empty($eatery->storytelling_data)) {
                    $story = is_string($eatery->storytelling_data)
                        ? (json_decode($eatery->storytelling_data, true) ?: [])
                        : (array) $eatery->storytelling_data;
                }

                $story['mst']      = $mst ?: ($story['mst'] ?? '');
                $story['industry'] = $story['industry'] ?? ($rec['industry'] ?? '');

                $updates = [
                    'storytelling_data' => json_encode($story, JSON_UNESCAPED_UNICODE),
                    'updated_at'        => now(),
                ];
                if (empty($eatery->address) && !empty($rec['address'])) {
                    $updates['address'] = $rec['address'];
                }
                if (empty($eatery->latitude) && !empty($rec['latitude'])) {
                    $updates['latitude']  = $rec['latitude'];
                    $updates['longitude'] = $rec['longitude'];
                }

                $this->line("  <info>UPDATE</info>  id={$eatery->id}  phone={$phone}  mst={$mst}  name={$eatery->name}");
                if (!$dry) {
                    DB::table('eateries')->where('id', $eatery->id)->update($updates);
                }
                $updated++;
                continue;
            }

            // ── CREATE new ───────────────────────────────────────────────
            // Ensure user exists
            $userRecord = null;
            if ($phone) {
                $phoneNorm  = ltrim($phone, '0');
                $userRecord = DB::table('users')
                    ->where(function ($q) use ($phone, $phoneNorm) {
                        $q->where('phone', $phone)
                          ->orWhere('phone', '0' . $phoneNorm);
                    })
                    ->first();
            }

            if (!$dry) {
                if (!$userRecord) {
                    $userPhone = $phone ?: ('09' . rand(10000000, 99999999));
                    $userId = DB::table('users')->insertGetId([
                        'name'        => $name ?: 'Chủ Hộ Kinh Doanh',
                        'email'       => ($phone ?: Str::slug($name)) . '@hkd.donganh.gov.vn',
                        'phone'       => $userPhone,
                        'password'    => bcrypt('12345678'),
                        'role'        => 'seller',
                        'is_active'   => true,
                        'is_verified' => true,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                } else {
                    $userId = $userRecord->id;
                }

                $baseSlug = Str::slug($name) ?: ('hkd-' . rand(1000, 9999));
                $slug     = $baseSlug . '-' . strtolower(Str::random(5));

                DB::table('eateries')->insert([
                    'user_id'           => $userId,
                    'name'              => $name ?: 'Hộ Kinh Doanh',
                    'slug'              => $slug,
                    'category_id'       => $categoryId,
                    'commune_id'        => $communeId,
                    'address'           => $rec['address']    ?? '',
                    'phone'             => $phone,
                    'description'       => $rec['industry']   ?? 'Hộ kinh doanh trên địa bàn xã Đông Anh',
                    'price_range'       => $rec['price_range'] ?? 'Liên hệ',
                    'status'            => 'active',
                    'is_featured'       => false,
                    'latitude'          => $rec['latitude']   ?? 21.1352,
                    'longitude'         => $rec['longitude']  ?? 105.8458,
                    'storytelling_data' => json_encode([
                        'mst'        => $mst,
                        'owner_name' => $name,
                        'industry'   => $rec['industry'] ?? '',
                    ], JSON_UNESCAPED_UNICODE),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            $this->line("  <comment>CREATE</comment>  phone={$phone}  mst={$mst}  name={$name}");
            $created++;
        }

        $this->newLine();
        $this->info("Done. Updated={$updated}  Created={$created}  Skipped={$skipped}");
        if ($dry) $this->warn('DRY RUN — nothing was saved.');

        return 0;
    }
}
