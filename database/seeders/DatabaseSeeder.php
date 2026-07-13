<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder — Bộ điều phối toàn bộ seeder
 *
 * Chạy TẤT CẢ: php artisan db:seed
 * Chạy MỘT seeder: php artisan db:seed --class=EaterySeeder
 *
 * ⚠️  KHÔNG CHẠY php artisan migrate:fresh hay migrate:refresh
 *     trừ khi bạn muốn XÓA toàn bộ dữ liệu và bắt đầu lại từ đầu.
 *
 * ✅  Khi thêm migration mới (bảng mới hoặc cột mới):
 *     php artisan migrate          ← chỉ chạy migration chưa chạy, KHÔNG xóa dữ liệu cũ
 *
 * ✅  Khi muốn thêm dữ liệu mẫu cho bảng mới:
 *     1. Tạo file seeder mới: php artisan make:seeder TenBangSeeder
 *     2. Thêm vào danh sách $this->call() bên dưới
 *     3. Chạy: php artisan db:seed --class=TenBangSeeder
 *
 * Thứ tự seed phải tuân theo thứ tự phụ thuộc khóa ngoại (FK):
 *   Users → Categories → Communes → Eateries → Dishes → Reviews → ReviewVideos → FoodTours
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Bảng độc lập (không có FK)
            UserSeeder::class,
            CategorySeeder::class,
            CommuneSeeder::class,

            // 2. Bảng phụ thuộc vào users + categories + communes
            // EaterySeeder::class,
            // MarketAndMartSeeder::class,
            // OcopHeritageSeeder::class,
            WellnessAndEducationSeeder::class,
            CulturalActivitySeeder::class,
            // CultureHubSeeder::class,

            // 3. Bảng phụ thuộc vào eateries
            // DishSeeder::class,
            // ReviewSeeder::class,
            // ReviewVideoSeeder::class,
            // FoodSafetyTrustSeeder::class,

            // 4. Bảng Food Tours (độc lập với eateries)
            // FoodTourSeeder::class,

            // 5. Kết nối bạn bè & Tin nhắn Realtime
            SocialHubSeeder::class,
        ]);
    }
}
