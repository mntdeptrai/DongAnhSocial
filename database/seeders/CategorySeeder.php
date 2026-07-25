<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * CategorySeeder — Tạo dữ liệu mẫu bảng categories
 *
 * Chạy: php artisan db:seed --class=CategorySeeder
 * Lưu ý: Seeder này dùng firstOrCreate để không tạo trùng nếu category đã tồn tại.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'DONGANH DISCOVERY',
                'slug'        => 'dong-anh-food-map',
                'icon'        => '🍲',
                'description' => 'Bản đồ khám phá đông anh - Bún phở, lẩu nướng, quán cafe,...',
            ],
            [
                'name'        => 'Stay in Đông Anh',
                'slug'        => 'stay-in-dong-anh',
                'icon'        => '🏨',
                'description' => 'Nhà nghỉ, khách sạn, biệt thự, homestay và các khu nghỉ dưỡng tiện nghi tại Đông Anh.',
            ],
            [
                'name'        => 'Wellness & Care',
                'slug'        => 'wellness-care',
                'icon'        => '🏥',
                'description' => 'Hệ thống cơ sở y tế, phòng khám, chăm sóc sức khỏe và spa thư giãn hàng đầu Đông Anh.',
            ],
            [
                'name'        => 'Đông Anh Market',
                'slug'        => 'dong-anh-market',
                'icon'        => '🛍️',
                'description' => 'Nơi hội tụ các sản phẩm OCOP, đặc sản địa phương, chợ truyền thống và trung tâm mua sắm sầm uất.',
            ],
            [
                'name'        => 'Smart Education Map',
                'slug'        => 'smart-education-map',
                'icon'        => '🏫',
                'description' => 'Hệ thống trường học và cơ sở giáo dục chất lượng cao trên địa bàn xã Đông Anh.',
            ],
            [
                'name'        => 'Hành trình di sản',
                'slug'        => 'hanh-trinh-di-san',
                'icon'        => '🏛️',
                'description' => 'Kết nối hành trình khám phá di tích lịch sử và văn hóa thông qua nền tảng Donganh360.vn.',
            ],
            [
                'name'        => 'Discover Dong Anh Community & Culture Hub',
                'slug'        => 'discover-dong-anh-community-culture-hub',
                'icon'        => '🏛️',
                'description' => 'Khám phá hệ thống thiết chế văn hóa - thể thao Đông Anh: Nhà văn hóa, nhà thi đấu, trung tâm triển lãm, nhà văn hóa thôn và tổ dân phố.',
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }
    }
}
