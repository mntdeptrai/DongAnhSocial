<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\Category;
use App\Models\Eatery;
use App\Models\FoodTour;

class SetupDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-databases {--fresh : Whether to run migrate:fresh instead of migrate} {--clean-only : Reset databases and seed only users, categories, and communes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo, chạy migration, seeding và làm sạch dữ liệu cho các database phân hệ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $connections = [
            'mysql' => [
                'db_var' => 'DB_DATABASE',
                'default_name' => 'food_map',
                'categories' => ['dong-anh-food-map', 'hanh-trinh-di-san'],
                'keep_tours' => true
            ],
            'mysql_stay' => [
                'db_var' => 'DB_STAY_DATABASE',
                'default_name' => 'stay_in_dong_anh',
                'categories' => ['stay-in-dong-anh'],
                'keep_tours' => false
            ],
            'mysql_wellness' => [
                'db_var' => 'DB_WELLNESS_DATABASE',
                'default_name' => 'wellness_care',
                'categories' => ['wellness-care'],
                'keep_tours' => false
            ],
            'mysql_market' => [
                'db_var' => 'DB_MARKET_DATABASE',
                'default_name' => 'dong_anh_market',
                'categories' => ['dong-anh-market'],
                'keep_tours' => false
            ],
            'mysql_education' => [
                'db_var' => 'DB_EDUCATION_DATABASE',
                'default_name' => 'smart_education_map',
                'categories' => ['smart-education-map'],
                'keep_tours' => false
            ],
            'mysql_culture' => [
                'db_var' => 'DB_CULTURE_DATABASE',
                'default_name' => 'dong_anh_culture_hub',
                'categories' => ['discover-dong-anh-community-culture-hub'],
                'keep_tours' => false
            ]
        ];

        $this->info('=== BẮT ĐẦU THIẾT LẬP HỆ THỐNG ĐA CƠ SỞ DỮ LIỆU ===');

        // 1. Tạo các database nếu chưa tồn tại
        $this->info("\n1. Đang kiểm tra và tạo các database nếu chưa tồn tại...");
        $defaultHost = config('database.connections.mysql.host', '127.0.0.1');
        $defaultPort = config('database.connections.mysql.port', '3306');
        $defaultUser = config('database.connections.mysql.username', 'root');
        $defaultPass = config('database.connections.mysql.password', '');

        try {
            $pdo = new \PDO("mysql:host={$defaultHost};port={$defaultPort}", $defaultUser, $defaultPass);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            foreach ($connections as $conn => $info) {
                $dbName = config("database.connections.{$conn}.database");
                if (!$dbName) {
                    $dbName = env($info['db_var'], $info['default_name']);
                }
                
                $this->line(" - Kiểm tra database: <comment>{$dbName}</comment>");
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            }
            $this->info("✔ Đã kiểm tra/tạo xong tất cả database!");
        } catch (\PDOException $e) {
            $this->warn("⚠ Không thể kết nối bằng quyền root của MySQL để tự động tạo database: " . $e->getMessage());
            $this->warn("Hãy đảm bảo các cơ sở dữ liệu đã được tạo thủ công trước khi tiếp tục.");
        }

        // 2. Chạy Migration và Seeding trên từng Connection
        $isCleanOnly = $this->option('clean-only');
        $isFresh = $this->option('fresh') || $isCleanOnly;
        $migrateCmd = $isFresh ? 'migrate:fresh' : 'migrate';

        foreach ($connections as $conn => $info) {
            $dbName = config("database.connections.{$conn}.database");
            $this->info("\n--------------------------------------------------");
            $this->info("Đang xử lý kết nối: [{$conn}] -> Database: [{$dbName}]");
            $this->info("--------------------------------------------------");

            // Chạy migration
            $this->line(" -> Chạy migration...");
            Artisan::call($migrateCmd, [
                '--database' => $conn,
                '--force' => true
            ]);
            $this->line(Artisan::output());

            if ($isCleanOnly) {
                // Chỉ chạy seeding cho các bảng cơ bản cấu trúc và người dùng
                $this->line(" -> Chạy seeding các bảng cấu trúc cơ bản (User, Category, Commune)...");
                Artisan::call('db:seed', [
                    '--database' => $conn,
                    '--class' => 'UserSeeder',
                    '--force' => true
                ]);
                Artisan::call('db:seed', [
                    '--database' => $conn,
                    '--class' => 'CategorySeeder',
                    '--force' => true
                ]);
                Artisan::call('db:seed', [
                    '--database' => $conn,
                    '--class' => 'CommuneSeeder',
                    '--force' => true
                ]);
                Artisan::call('db:seed', [
                    '--database' => $conn,
                    '--class' => 'SocialHubSeeder',
                    '--force' => true
                ]);
                $this->info("   ✔ Thiết lập sạch cho database [{$dbName}] hoàn tất.");
                continue;
            }

            // Chạy seeding
            $this->line(" -> Chạy seeding dữ liệu mẫu...");
            Artisan::call('db:seed', [
                '--database' => $conn,
                '--force' => true
            ]);
            $this->line(Artisan::output());

            // Làm sạch dữ liệu không thuộc phân hệ của database này
            $this->line(" -> Đang dọn dẹp dữ liệu không thuộc danh mục của phân hệ...");
            
            // Tìm ID của các categories được phép giữ lại
            $allowedCategoryIds = DB::connection($conn)
                ->table('categories')
                ->whereIn('slug', $info['categories'])
                ->pluck('id')
                ->toArray();

            if (empty($allowedCategoryIds)) {
                $this->warn("   ! Không tìm thấy category hợp lệ trên connection [{$conn}]. Bỏ qua bước xóa.");
                continue;
            }

            // Xóa tất cả các eateries không thuộc các categories được phép
            // cascade delete trong database sẽ tự động xóa dishes, reviews, rooms, v.v. liên quan.
            $deletedCount = DB::connection($conn)
                ->table('eateries')
                ->whereNotIn('category_id', $allowedCategoryIds)
                ->delete();

            $this->info("   ✔ Đã dọn dẹp {$deletedCount} địa điểm không thuộc phân hệ khỏi database [{$dbName}].");

            // Xóa food tours nếu không được giữ lại
            if (!$info['keep_tours']) {
                $deletedTours = DB::connection($conn)->table('food_tours')->delete();
                $this->info("   ✔ Đã xóa {$deletedTours} Food Tours khỏi database [{$dbName}].");
            }
        }

        $this->info("\n==================================================");
        $this->info("✔ THIẾT LẬP ĐA CƠ SỞ DỮ LIỆU HOÀN TẤT THÀNH CÔNG!");
        $this->info("==================================================");
        
        return self::SUCCESS;
    }
}
